<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\PuestoTrabajoMatriz;
use App\Models\PuestosCapacitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\GenerarAsignacionesPorPuestoService;

class PuestoCapacitacionController extends Controller
{
    public function index(Request $request)
    {
        $buscarPuesto = trim((string) $request->query('buscar_puesto', ''));
        $buscarCapacitacion = trim((string) $request->query('buscar_capacitacion', ''));

        $puestos = PuestoTrabajoMatriz::query()
            ->with('departamento')
            ->where('estado', 1)
            ->when($buscarPuesto !== '', function ($query) use ($buscarPuesto) {
                $query->where('puesto_trabajo_matriz', 'like', '%' . $buscarPuesto . '%');
            })
            ->orderBy('puesto_trabajo_matriz')
            ->get();

        $capacitaciones = Capacitacion::query()
            ->where('estado', 1)
            ->when($buscarCapacitacion !== '', function ($query) use ($buscarCapacitacion) {
                $query->where(function ($subQuery) use ($buscarCapacitacion) {
                    $subQuery->where('capacitacion', 'like', '%' . $buscarCapacitacion . '%')
                        ->orWhere('codigo', 'like', '%' . $buscarCapacitacion . '%');
                });
            })
            ->orderBy('capacitacion')
            ->get();

        $pivot = PuestosCapacitacion::query()
            ->whereIn('id_puesto_trabajo_matriz', $puestos->pluck('id_puesto_trabajo_matriz'))
            ->whereIn('id_capacitacion', $capacitaciones->pluck('id_capacitacion'))
            ->get()
            ->keyBy(function ($item) {
                return $item->id_puesto_trabajo_matriz . '-' . $item->id_capacitacion;
            });

        return view('puestos_capacitacion.index', compact(
            'puestos',
            'capacitaciones',
            'pivot',
            'buscarPuesto',
            'buscarCapacitacion'
        ));
    }

    public function store(Request $request)
    {
        $puestoIds = collect((array) $request->input('puesto_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $capIds = collect((array) $request->input('cap_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($puestoIds->isEmpty() || $capIds->isEmpty()) {
            return back()->withErrors([
                'general' => 'No hay puestos o capacitaciones visibles para guardar en la matriz.',
            ]);
        }

        $matrix = (array) $request->input('matrix', []);

        $seleccionados = [];

        foreach ($matrix as $idPuesto => $columnas) {
            $idPuesto = (int) $idPuesto;

            if (!$puestoIds->contains($idPuesto)) {
                continue;
            }

            foreach ((array) $columnas as $idCapacitacion => $valor) {
                $idCapacitacion = (int) $idCapacitacion;

                if (!$capIds->contains($idCapacitacion)) {
                    continue;
                }

                $clave = $idPuesto . '-' . $idCapacitacion;

                $seleccionados[$clave] = [
                    'id_puesto_trabajo_matriz' => $idPuesto,
                    'id_capacitacion' => $idCapacitacion,
                ];
            }
        }

        $diasVigenciaPorCapacitacion = Capacitacion::query()
            ->whereIn('id_capacitacion', $capIds)
            ->pluck('dias_vigencia', 'id_capacitacion');

        $actuales = PuestosCapacitacion::query()
            ->whereIn('id_puesto_trabajo_matriz', $puestoIds)
            ->whereIn('id_capacitacion', $capIds)
            ->get()
            ->keyBy(function ($item) {
                return $item->id_puesto_trabajo_matriz . '-' . $item->id_capacitacion;
            });

        DB::transaction(function () use ($actuales, $seleccionados, $diasVigenciaPorCapacitacion) {
            foreach ($actuales as $clave => $registro) {
                if (isset($seleccionados[$clave])) {
                    $registro->update([
                        'obligatoria' => 1,
                        'dias_para_vencer' => $diasVigenciaPorCapacitacion[$registro->id_capacitacion] ?? $registro->dias_para_vencer,
                        'estado' => 1,
                    ]);
                } else {
                    $registro->update([
                        'estado' => 0,
                    ]);
                }
            }

            foreach ($seleccionados as $clave => $data) {
                if (isset($actuales[$clave])) {
                    continue;
                }

                PuestosCapacitacion::create([
                    'id_puesto_trabajo_matriz' => $data['id_puesto_trabajo_matriz'],
                    'id_capacitacion' => $data['id_capacitacion'],
                    'obligatoria' => 1,
                    'dias_para_vencer' => $diasVigenciaPorCapacitacion[$data['id_capacitacion']] ?? null,
                    'fecha_asignacion' => now()->toDateString(),
                    'estado' => 1,
                ]);
            }
        });

        return redirect()->route('puestos_capacitacion.index')
            ->with('success', 'La matriz puesto → capacitación fue guardada correctamente.');
    }

    public function generarAsignaciones(GenerarAsignacionesPorPuestoService $service)
    {
        $resultado = $service->ejecutar(Auth::id());

        $mensaje = 'Proceso completado. ';
        $mensaje .= 'Se crearon ' . $resultado['creadas'] . ' asignaciones automáticas';

        if ($resultado['omitidas'] > 0) {
            $mensaje .= ' y ' . $resultado['omitidas'] . ' ya existían.';
        } else {
            $mensaje .= '.';
        }

        return back()->with('success', $mensaje);
    }
}