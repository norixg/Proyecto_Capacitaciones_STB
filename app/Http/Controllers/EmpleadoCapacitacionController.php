<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\EmpleadoRrhh;
use App\Models\EmpleadoCapacitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Services\AvisoCorreoService;
use App\Services\EliminacionCapacitacionService;
use Illuminate\Support\Str;


class EmpleadoCapacitacionController extends Controller
{
    private const ESTADOS = [
        'pendiente',
        'en_proceso',
        'aprobada',
        'reprobada',
        'vencida',
        'cancelada',
    ];

    private function opcionesCapacitaciones($capacitaciones)
    {
        return $capacitaciones->map(function (Capacitacion $capacitacion) {
            $etiqueta = '#'.$capacitacion->id_capacitacion.' — '.$capacitacion->capacitacion;

            if ($capacitacion->id_capacitacion_instructor) {
                $etiqueta .= ' — RR. HH. #'.$capacitacion->id_capacitacion_instructor;
            }

            return [
                'id' => $capacitacion->id_capacitacion,
                'etiqueta' => $etiqueta,
                'busqueda' => Str::of($etiqueta)->ascii()->lower()->toString(),
            ];
        })->values();
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado');
        $obligatoria = $request->query('obligatoria');

        $idsEmpleadosCoincidentes = $buscar !== ''
            ? EmpleadoRrhh::query()
                ->where('nombre_completo', 'like', '%' . $buscar . '%')
                ->orWhere('codigo_empleado', 'like', '%' . $buscar . '%')
                ->pluck('id_empleado')
            : collect();

        $asignaciones = EmpleadoCapacitacion::with(['empleado', 'capacitacion', 'usuarioAsigno'])
            ->when($buscar !== '', function ($query) use ($buscar, $idsEmpleadosCoincidentes) {
                // La capacitación permanece en la base local.
                $query->where(function ($subQuery) use ($buscar, $idsEmpleadosCoincidentes) {
                    $subQuery->whereIn('id_empleado', $idsEmpleadosCoincidentes)
                        ->orWhereHas('capacitacion', function ($capacitacionQuery) use ($buscar) {
                            $capacitacionQuery->where('capacitacion', 'like', '%' . $buscar . '%')
                                ->orWhere('codigo', 'like', '%' . $buscar . '%');
                        });
                });
            })
            ->when(in_array($estado, self::ESTADOS, true), function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->when(in_array($obligatoria, ['0', '1'], true), function ($query) use ($obligatoria) {
                $query->where('obligatoria', $obligatoria);
            })
            ->orderByDesc('id_empleado_capacitacion')
            ->paginate(15)
            ->withQueryString();

        $estados = self::ESTADOS;

        return view('empleado_capacitaciones.index', compact(
            'asignaciones',
            'buscar',
            'estado',
            'obligatoria',
            'estados'
        ));
    }

    public function create()
    {
        $empleados = EmpleadoRrhh::where('estado', 1)
            ->orderBy('nombre_completo')
            ->get();

        $capacitaciones = Capacitacion::where('estado', 1)
            ->orderBy('capacitacion')
            ->get();
        $opcionesCapacitaciones = $this->opcionesCapacitaciones($capacitaciones);

        return view('empleado_capacitaciones.create', compact('empleados', 'opcionesCapacitaciones'));
    }

    public function store(Request $request, AvisoCorreoService $avisoCorreoService)
    {
        $data = $request->validate([
            'id_capacitacion' => ['required', 'exists:capacitacion,id_capacitacion'],
            'id_empleados' => ['required', 'array', 'min:1'],
            'id_empleados.*' => [
                'integer',
                'distinct',
                Rule::exists('rrhh.empleado', 'id_empleado')
                    ->where(fn ($query) => $query->where('estado', 1)),
            ],
            'obligatoria' => ['required', Rule::in(['0', '1'])],
            'fecha_asignacion' => ['required', 'date'],
            'fecha_limite' => ['required', 'date', 'after_or_equal:fecha_asignacion'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_asignacion'],
        ], [
            'id_empleados.required' => 'Debes seleccionar al menos un empleado.',
            'id_empleados.array' => 'La selección de empleados no es válida.',
            'id_empleados.min' => 'Debes seleccionar al menos un empleado.',
            'id_empleados.*.distinct' => 'Hay empleados repetidos en la selección.',
            'fecha_limite.after_or_equal' => 'La fecha límite no puede ser menor que la fecha de asignación.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser menor que la fecha de asignación.',
        ]);

        if (!empty($data['fecha_limite']) && !empty($data['fecha_vencimiento']) && $data['fecha_vencimiento'] < $data['fecha_limite']) {
            return back()->withErrors([
                'fecha_vencimiento' => 'La fecha de vencimiento no puede ser menor que la fecha límite.',
            ])->withInput();
        }

        $idsEmpleados = collect($data['id_empleados'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $idsYaAsignados = EmpleadoCapacitacion::where('id_capacitacion', $data['id_capacitacion'])
            ->whereIn('id_empleado', $idsEmpleados)
            ->pluck('id_empleado')
            ->map(fn ($id) => (int) $id)
            ->all();

        $idsNuevos = $idsEmpleados->reject(function ($idEmpleado) use ($idsYaAsignados) {
            return in_array($idEmpleado, $idsYaAsignados, true);
        })->values();

        if ($idsNuevos->isEmpty()) {
            return back()->withErrors([
                'id_empleados' => 'Todos los empleados seleccionados ya tienen asignada esta capacitación.',
            ])->withInput();
        }

        $fechaAsignacion = Carbon::parse($data['fecha_asignacion'])->format('Ymd H:i:s');

        $fechaLimite = Carbon::parse($data['fecha_limite'])->format('Ymd H:i:s');

        $fechaVencimiento = Carbon::parse($data['fecha_vencimiento'])->format('Ymd H:i:s');

        $avisosAsignacion = [
            'creados' => 0,
            'enviados' => 0,
            'errores' => 0,
        ];

        foreach ($idsNuevos as $idEmpleado) {
            $asignacionCreada = EmpleadoCapacitacion::create([
                'id_empleado' => $idEmpleado,
                'id_capacitacion' => $data['id_capacitacion'],
                'origen_asignacion' => 'manual',
                'id_referencia_asignacion' => null,
                'obligatoria' => $data['obligatoria'],
                'fecha_asignacion' => $fechaAsignacion,
                'fecha_inicio' => null,
                'fecha_limite' => $fechaLimite,
                'fecha_vencimiento' => $fechaVencimiento,
                'fecha_finalizacion' => null,
                'estado' => 'pendiente',
                'progreso' => 0,
                'nota_final' => null,
                'aprobado' => 0,
                'id_usuario_asigno' => Auth::id(),
            ]);

            $resultadoAviso = $avisoCorreoService->generarYEnviarAvisoAsignacion($asignacionCreada);

            $avisosAsignacion['creados'] += $resultadoAviso['creados'];
            $avisosAsignacion['enviados'] += $resultadoAviso['enviados'];
            $avisosAsignacion['errores'] += $resultadoAviso['errores'];

    }

        $cantidadCreadas = $idsNuevos->count();
        $cantidadOmitidas = count($idsYaAsignados);

        $mensaje = $cantidadCreadas === 1
            ? 'Se creó 1 asignación correctamente.'
            : "Se crearon {$cantidadCreadas} asignaciones correctamente.";

        if ($cantidadOmitidas > 0) {
            $mensaje .= " {$cantidadOmitidas} ya existían y se omitieron.";
        }

        $mensaje .= " Avisos de asignación creados: {$avisosAsignacion['creados']}.";
        $mensaje .= " Enviados: {$avisosAsignacion['enviados']}.";
        $mensaje .= " Errores: {$avisosAsignacion['errores']}.";

        return redirect()->route('empleado_capacitaciones.index')
            ->with('success', $mensaje);
    }

    public function edit($id)
    {
        $asignacion = EmpleadoCapacitacion::with(['modulosAvance', 'intentosEvaluacion'])->findOrFail($id);

        $empleados = EmpleadoRrhh::where(function ($query) use ($asignacion) {
                $query->where('estado', 1)
                    ->orWhere('id_empleado', $asignacion->id_empleado);
            })
            ->orderBy('nombre_completo')
            ->get();

        $capacitaciones = Capacitacion::where(function ($query) use ($asignacion) {
                $query->where('estado', 1)
                    ->orWhere('id_capacitacion', $asignacion->id_capacitacion);
            })
            ->orderBy('capacitacion')
            ->get();

        $tieneSeguimiento = $this->tieneSeguimiento($asignacion);

        return view('empleado_capacitaciones.edit', compact(
            'asignacion',
            'empleados',
            'capacitaciones',
            'tieneSeguimiento'
        ));
    }

    public function update(Request $request, $id)
    {
        $asignacion = EmpleadoCapacitacion::with(['modulosAvance', 'intentosEvaluacion'])->findOrFail($id);
        $tieneSeguimiento = $this->tieneSeguimiento($asignacion);

        $data = $request->validate([
            'id_empleado' => [
                'required',
                Rule::exists('rrhh.empleado', 'id_empleado'),
            ],
            'id_capacitacion' => ['required', 'exists:capacitacion,id_capacitacion'],
            'obligatoria' => ['required', Rule::in(['0', '1'])],
            'fecha_asignacion' => ['required', 'date'],
            'fecha_limite' => ['required', 'date', 'after_or_equal:fecha_asignacion'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_asignacion'],
            'estado' => ['required', Rule::in(self::ESTADOS)],
        ], [
            'fecha_limite.after_or_equal' => 'La fecha límite no puede ser menor que la fecha de asignación.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser menor que la fecha de asignación.',
        ]);

        if (!empty($data['fecha_limite']) && !empty($data['fecha_vencimiento']) && $data['fecha_vencimiento'] < $data['fecha_limite']) {
            return back()->withErrors([
                'fecha_vencimiento' => 'La fecha de vencimiento no puede ser menor que la fecha límite.',
            ])->withInput();
        }

        if ($tieneSeguimiento) {
            if (
                (int) $data['id_empleado'] !== (int) $asignacion->id_empleado ||
                (int) $data['id_capacitacion'] !== (int) $asignacion->id_capacitacion
            ) {
                return back()->withErrors([
                    'general' => 'No se puede cambiar el empleado ni la capacitación porque esta asignación ya tiene seguimiento.',
                ])->withInput();
            }

            $data['estado'] = $asignacion->estado;
        } else {
            if (!in_array($data['estado'], ['pendiente', 'cancelada'], true)) {
                return back()->withErrors([
                    'estado' => 'Cuando la asignación todavía no tiene avance, el estado solo puede ser pendiente o cancelada.',
                ])->withInput();
            }
        }

        $existeAsignacion = EmpleadoCapacitacion::where('id_empleado', $data['id_empleado'])
            ->where('id_capacitacion', $data['id_capacitacion'])
            ->where('id_empleado_capacitacion', '!=', $asignacion->id_empleado_capacitacion)
            ->exists();

        if ($existeAsignacion) {
            return back()->withErrors([
                'id_capacitacion' => 'Esa capacitación ya está asignada a este empleado.',
            ])->withInput();
        }

        $fechaAsignacion = Carbon::parse($data['fecha_asignacion'])->format('Ymd H:i:s');
        $fechaLimite = Carbon::parse($data['fecha_limite'])->format('Ymd H:i:s');
        $fechaVencimiento = Carbon::parse($data['fecha_vencimiento'])->format('Ymd H:i:s');

        $asignacion->update([
            'id_empleado' => $data['id_empleado'],
            'id_capacitacion' => $data['id_capacitacion'],
            'obligatoria' => $data['obligatoria'],
            'fecha_asignacion' => $fechaAsignacion,
            'fecha_limite' => $fechaLimite,
            'fecha_vencimiento' => $fechaVencimiento,
            'estado' => $data['estado'],
        ]);

        return redirect()->route('empleado_capacitaciones.index')
            ->with('success', 'La asignación fue actualizada correctamente.');
    }

    public function destroy($id, EliminacionCapacitacionService $eliminacionCapacitacionService)
    {
        $asignacion = EmpleadoCapacitacion::findOrFail($id);

        $eliminacionCapacitacionService->eliminarAsignacionEmpleado(
            (int) $asignacion->id_empleado_capacitacion
        );

        return redirect()->route('empleado_capacitaciones.index')
            ->with('success', 'La asignación y todo su seguimiento fueron eliminados correctamente.');
    }

    private function tieneSeguimiento(EmpleadoCapacitacion $asignacion): bool
    {
        return (float) $asignacion->progreso > 0
            || $asignacion->modulosAvance->isNotEmpty()
            || $asignacion->intentosEvaluacion->isNotEmpty()
            || !is_null($asignacion->fecha_inicio)
            || !is_null($asignacion->fecha_finalizacion);
    }
}
