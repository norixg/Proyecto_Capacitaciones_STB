<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NecesidadCapacitacionController extends Controller
{
    public function index(Request $request)
    {
        return view('necesidades_capacitacion.index', $this->construirDetalle($request));
    }

    public function exportar(Request $request): StreamedResponse
    {
        $datos = $this->construirDetalle($request);
        abort_if(!$datos['empleadoSeleccionado'], 422, 'Selecciona un empleado para exportar.');

        $codigo = $datos['empleadoSeleccionado']->codigo_empleado ?: $datos['empleadoSeleccionado']->id_empleado;

        return response()->streamDownload(function () use ($datos) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Empleado', $datos['empleadoSeleccionado']->nombre_completo], ';');
            fputcsv($salida, ['Código', $datos['empleadoSeleccionado']->codigo_empleado], ';');
            fputcsv($salida, ['Puesto matriz', $datos['puestoSeleccionado']?->puesto_trabajo_matriz ?? 'Sin correspondencia'], ';');
            fputcsv($salida, ['Departamento', $datos['puestoSeleccionado']?->departamento ?? 'Sin departamento'], ';');
            fputcsv($salida, ['Año consultado', $datos['anio']], ';');
            fputcsv($salida, [], ';');
            fputcsv($salida, ['Capacitación necesaria', 'Estado', 'Fecha recibida'], ';');
            foreach ($datos['necesidades'] as $necesidad) {
                fputcsv($salida, [
                    $necesidad['capacitacion'],
                    $necesidad['recibida'] ? 'Recibida' : 'Pendiente',
                    $necesidad['fecha'] ?: '',
                ], ';');
            }
            fclose($salida);
        }, 'necesidades-'.$codigo.'-'.$datos['anio'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function construirDetalle(Request $request): array
    {
        $conexion = DB::connection('rrhh');
        $anioActual = (int) now()->year;
        $anios = range($anioActual, $anioActual - 10);
        $anio = (int) $request->query('anio', $anioActual);
        if (!in_array($anio, $anios, true)) {
            $anio = $anioActual;
        }

        $idEmpleado = (int) $request->query('id_empleado', 0);
        $filtroCapacitacion = trim((string) $request->query('cap', ''));
        $capacitacionBuscada = $this->normalizar($filtroCapacitacion);

        $puestos = $conexion->table('puesto_trabajo_matriz as ptm')
            ->leftJoin('departamento as d', 'd.id_departamento', '=', 'ptm.id_departamento')
            ->where(fn ($q) => $q->where('ptm.estado', 1)->orWhereNull('ptm.estado'))
            ->get(['ptm.id_puesto_trabajo_matriz', 'ptm.puesto_trabajo_matriz', 'ptm.id_departamento', 'd.departamento']);
        $puestosPorId = $puestos->keyBy('id_puesto_trabajo_matriz');
        $puestosPorNombre = $puestos->mapWithKeys(fn ($puesto) => [
            $this->normalizar($puesto->puesto_trabajo_matriz) => (int) $puesto->id_puesto_trabajo_matriz,
        ]);
        $legacyPorId = $conexion->table('puesto_trabajo')->get(['id_puesto_trabajo', 'puesto_trabajo'])
            ->mapWithKeys(fn ($puesto) => [
                (int) $puesto->id_puesto_trabajo => $puestosPorNombre[$this->normalizar($puesto->puesto_trabajo)] ?? null,
            ]);

        $empleados = $conexion->table('empleado')
            ->where('estado', 1)
            ->whereNotNull('anio')
            ->where('anio', '<=', $anio)
            ->orderBy('nombre_completo')
            ->get(['id_empleado', 'nombre_completo', 'codigo_empleado', 'identidad', 'id_puesto_trabajo_matriz', 'id_puesto_trabajo', 'anio']);

        $opcionesEmpleados = $empleados->map(function ($empleado) {
            $etiqueta = $empleado->nombre_completo
                .($empleado->codigo_empleado ? ' — '.$empleado->codigo_empleado : '')
                .($empleado->identidad ? ' · '.$empleado->identidad : '');
            return ['id' => $empleado->id_empleado, 'etiqueta' => $etiqueta, 'busqueda' => $this->normalizar($etiqueta)];
        })->values();

        $empleadoSeleccionado = $idEmpleado > 0 ? $empleados->firstWhere('id_empleado', $idEmpleado) : null;
        $puestoSeleccionado = null;
        $necesidades = collect();
        $sinCorrespondenciaPuesto = false;

        if ($empleadoSeleccionado) {
            $puestoId = $this->resolverPuesto($empleadoSeleccionado, $puestosPorId, $legacyPorId);
            $puestoSeleccionado = $puestoId ? $puestosPorId->get($puestoId) : null;
            $sinCorrespondenciaPuesto = !$puestoSeleccionado;

            if ($puestoSeleccionado) {
                $tieneMatriz = Schema::connection('rrhh')->hasColumn('puestos_capacitacion', 'id_puesto_trabajo_matriz');
                $tieneLegacy = Schema::connection('rrhh')->hasColumn('puestos_capacitacion', 'id_puesto_trabajo');
                $columnas = ['pc.id_capacitacion', 'c.capacitacion'];
                if ($tieneMatriz) {
                    $columnas[] = 'pc.id_puesto_trabajo_matriz';
                }
                if ($tieneLegacy) {
                    $columnas[] = 'pc.id_puesto_trabajo';
                }

                $obligatorias = $conexion->table('puestos_capacitacion as pc')
                    ->join('capacitacion as c', 'c.id_capacitacion', '=', 'pc.id_capacitacion')
                    ->get($columnas)
                    ->filter(fn ($relacion) => $this->resolverPuesto($relacion, $puestosPorId, $legacyPorId) === $puestoId)
                    ->filter(fn ($relacion) => $capacitacionBuscada === '' || str_contains($this->normalizar($relacion->capacitacion), $capacitacionBuscada))
                    ->unique('id_capacitacion');

                $asistencias = $conexion->table('asistencia_capacitacion as ac')
                    ->join('capacitacion_instructor as ci', 'ci.id_capacitacion_instructor', '=', 'ac.id_capacitacion_instructor')
                    ->where('ac.id_empleado', $empleadoSeleccionado->id_empleado)
                    ->whereIn('ci.id_capacitacion', $obligatorias->pluck('id_capacitacion'))
                    ->get(['ci.id_capacitacion', 'ac.fecha_recibida'])
                    ->filter(fn ($asistencia) => $this->extraerAnio($asistencia->fecha_recibida) === $anio)
                    ->groupBy('id_capacitacion');

                $necesidades = $obligatorias->map(function ($capacitacion) use ($asistencias) {
                    $registros = $asistencias->get($capacitacion->id_capacitacion, collect());
                    $fecha = $registros->pluck('fecha_recibida')->filter()->last();
                    return [
                        'id_capacitacion' => (int) $capacitacion->id_capacitacion,
                        'capacitacion' => $capacitacion->capacitacion,
                        'recibida' => $registros->isNotEmpty(),
                        'fecha' => $fecha ? (string) $fecha : null,
                    ];
                })->sortBy(fn ($item) => $this->normalizar($item['capacitacion']))->values();
            }
        }

        $resumen = [
            'total' => $necesidades->count(),
            'recibidas' => $necesidades->where('recibida', true)->count(),
            'pendientes' => $necesidades->where('recibida', false)->count(),
        ];
        $resumen['porcentaje'] = $resumen['total'] > 0 ? ($resumen['recibidas'] * 100) / $resumen['total'] : 0;

        $todasCapacitaciones = $conexion->table('capacitacion')->orderBy('capacitacion')->get(['id_capacitacion', 'capacitacion']);

        return compact(
            'opcionesEmpleados', 'empleadoSeleccionado', 'puestoSeleccionado', 'sinCorrespondenciaPuesto',
            'necesidades', 'resumen', 'todasCapacitaciones', 'filtroCapacitacion', 'idEmpleado', 'anio', 'anios'
        );
    }

    private function resolverPuesto(object $registro, Collection $puestosPorId, Collection $legacyPorId): ?int
    {
        $matriz = (int) ($registro->id_puesto_trabajo_matriz ?? 0);
        if ($matriz > 0 && $puestosPorId->has($matriz)) {
            return $matriz;
        }
        $legacy = (int) ($registro->id_puesto_trabajo ?? 0);
        $resuelto = $legacyPorId[$legacy] ?? null;
        return $resuelto ? (int) $resuelto : null;
    }

    private function normalizar(?string $texto): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', Str::ascii(Str::lower($texto ?? '')))));
    }

    private function extraerAnio(mixed $fecha): ?int
    {
        $texto = trim((string) $fecha);
        if ($texto === '') return null;
        if (preg_match('/\b(19|20)\d{2}\b/', $texto, $coincidencia)) return (int) $coincidencia[0];
        try {
            return Carbon::parse($texto)->year;
        } catch (\Throwable) {
            return null;
        }
    }
}
