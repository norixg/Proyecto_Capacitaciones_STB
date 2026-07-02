<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\EmpleadoCapacitacion;
use App\Models\PuestoTrabajoMatriz;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\HistorialCapacitacionEmpleado;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $this->sincronizarAsignacionesParaReportes();

        $filtros = $this->obtenerFiltros($request);

        $reporteQuery = $this->construirQuery($request);

        $reportesParaResumen = (clone $reporteQuery)->get();
        $resumen = $this->armarResumen($reportesParaResumen);

        $reportes = (clone $reporteQuery)
            ->orderByDesc('id_empleado_capacitacion')
            ->paginate(15)
            ->withQueryString();

        $catalogos = $this->catalogos();
        $tiposReporte = $this->tiposReporte();

        return view('reportes.index', array_merge(
            $catalogos,
            $filtros,
            compact(
                'reportes',
                'resumen',
                'tiposReporte'
            )
        ));
    }

    public function excel(Request $request)
    {
        $this->sincronizarAsignacionesParaReportes();

        $reportes = $this->construirQuery($request)
            ->orderByDesc('id_empleado_capacitacion')
            ->get();

        $nombreArchivo = $this->nombreArchivo($request, 'csv');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ];

        return response()->streamDownload(function () use ($reportes) {
            $salida = fopen('php://output', 'w');

            fprintf($salida, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($salida, [
                'Empleado',
                'Código empleado',
                'Identidad',
                'Correo',
                'Puesto',
                'Departamento',
                'Capacitación',
                'Estado',
                'Progreso',
                'Nota final',
                'Aprobado',
                'Obligatoria',
                'Fecha asignación',
                'Fecha inicio',
                'Fecha finalización',
                'Fecha límite',
                'Fecha vencimiento',
            ], ';');

            foreach ($reportes as $reporte) {
                fputcsv($salida, [
                    $reporte->empleado?->nombre_completo ?? '-',
                    $this->textoExcel($reporte->empleado?->codigo_empleado),
                    $this->textoExcel($reporte->empleado?->identidad),
                    $reporte->empleado?->correo ?? '-',
                    $reporte->empleado?->puestoTrabajo?->puesto_trabajo_matriz ?? '-',
                    $reporte->empleado?->puestoTrabajo?->departamento?->departamento ?? '-',
                    $reporte->capacitacion?->capacitacion ?? '-',
                    $this->estadoLegibleReporte($reporte->estado ?? null),
                    number_format((float) ($reporte->progreso ?? 0), 2) . '%',
                    !is_null($reporte->nota_final) ? number_format((float) $reporte->nota_final, 2) . '%' : '-',
                    (int) ($reporte->aprobado ?? 0) === 1 ? 'Sí' : 'No',
                    (int) ($reporte->obligatoria ?? 0) === 1 ? 'Sí' : 'No',
                    $this->fechaExcel($reporte->fecha_asignacion, 'd/m/Y'),
                    $this->fechaExcel($reporte->fecha_inicio, 'd/m/Y H:i'),
                    $this->fechaExcel($reporte->fecha_finalizacion, 'd/m/Y H:i'),
                    $this->fechaExcel($reporte->fecha_limite, 'd/m/Y'),
                    $this->fechaExcel($reporte->fecha_vencimiento, 'd/m/Y'),
                ], ';');
            }

            fclose($salida);
        }, $nombreArchivo, $headers);
    }

    public function pdf(Request $request)
    {
        $this->sincronizarAsignacionesParaReportes();

        $reportes = $this->construirQuery($request)
            ->orderByDesc('id_empleado_capacitacion')
            ->get();

        $resumen = $this->armarResumen($reportes);
        $filtrosLegibles = $this->filtrosLegibles($request);
        $fechaGeneracion = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('reportes.pdf_general', compact(
            'reportes',
            'resumen',
            'filtrosLegibles',
            'fechaGeneracion'
        ))->setPaper('letter', 'landscape');

        return $pdf->download($this->nombreArchivo($request, 'pdf'));
    }

    public function expedienteEmpleadoPdf(int $id_empleado)
    {
        $empleado = Empleado::with('puestoTrabajo.departamento')
            ->findOrFail($id_empleado);

        $capacitaciones = EmpleadoCapacitacion::with([
            'capacitacion.capacitacionModulos' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'modulosAvance.capacitacionModulo',
            'intentosEvaluacion.evaluacion.capacitacionModulo',
            'intentosEjercicio.ejercicio.modulo',
            'historial.usuario',
        ])
            ->where('id_empleado', $empleado->id_empleado)
            ->orderByDesc('id_empleado_capacitacion')
            ->get();

        $resumen = $this->armarResumen($capacitaciones);
        $fechaGeneracion = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('reportes.expediente_empleado_pdf', compact(
            'empleado',
            'capacitaciones',
            'resumen',
            'fechaGeneracion'
        ))->setPaper('letter', 'portrait');

        $nombreEmpleado = Str::slug($empleado->nombre_completo ?? 'empleado');

        return $pdf->download('expediente_' . $nombreEmpleado . '_' . now()->format('Ymd_His') . '.pdf');
    }

    private function sincronizarAsignacionesParaReportes(): void
    {
        $ahora = now();

        EmpleadoCapacitacion::query()
            ->select('id_empleado_capacitacion')
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->where(function ($query) use ($ahora) {
                $query->where(function ($subQuery) use ($ahora) {
                    $subQuery->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '<', $ahora);
                })->orWhere(function ($subQuery) use ($ahora) {
                    $subQuery->whereNull('fecha_vencimiento')
                        ->whereNotNull('fecha_limite')
                        ->where('fecha_limite', '<', $ahora);
                });
            })
            ->orderBy('id_empleado_capacitacion')
            ->chunkById(200, function ($asignaciones) use ($ahora) {
                $ids = $asignaciones->pluck('id_empleado_capacitacion')->all();

                if (empty($ids)) {
                    return;
                }

                EmpleadoCapacitacion::query()
                    ->whereIn('id_empleado_capacitacion', $ids)
                    ->update([
                        'estado' => 'vencida',
                        'updated_at' => $ahora,
                    ]);

                foreach ($ids as $idEmpleadoCapacitacion) {
                    HistorialCapacitacionEmpleado::query()->firstOrCreate([
                        'id_empleado_capacitacion' => $idEmpleadoCapacitacion,
                        'accion' => 'vencida',
                    ], [
                        'descripcion' => 'La capacitación fue marcada como vencida automáticamente al consultar los reportes.',
                        'realizado_por' => Auth::id(),
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }
            }, 'id_empleado_capacitacion');
    }

    private function construirQuery(Request $request): Builder
    {
        $filtros = $this->obtenerFiltros($request);

        $reporteQuery = EmpleadoCapacitacion::with([
            'empleado.puestoTrabajo.departamento',
            'capacitacion',
        ]);

        if ($filtros['buscar'] !== '') {
            $buscar = $filtros['buscar'];

            $reporteQuery->where(function ($query) use ($buscar) {
                $query->whereHas('empleado', function ($subQuery) use ($buscar) {
                    $subQuery->where('nombre_completo', 'like', '%' . $buscar . '%')
                        ->orWhere('codigo_empleado', 'like', '%' . $buscar . '%')
                        ->orWhere('identidad', 'like', '%' . $buscar . '%')
                        ->orWhere('correo', 'like', '%' . $buscar . '%');
                })->orWhereHas('capacitacion', function ($subQuery) use ($buscar) {
                    $subQuery->where('capacitacion', 'like', '%' . $buscar . '%')
                        ->orWhere('codigo', 'like', '%' . $buscar . '%');
                });
            });
        }

        if (!empty($filtros['idEmpleado'])) {
            $reporteQuery->where('id_empleado', $filtros['idEmpleado']);
        }

        if (!empty($filtros['idCapacitacion'])) {
            $reporteQuery->where('id_capacitacion', $filtros['idCapacitacion']);
        }

        if (!empty($filtros['idDepartamento'])) {
            $reporteQuery->whereHas('empleado.puestoTrabajo', function ($query) use ($filtros) {
                $query->where('id_departamento', $filtros['idDepartamento']);
            });
        }

        if (!empty($filtros['idPuestoTrabajoMatriz'])) {
            $reporteQuery->whereHas('empleado', function ($query) use ($filtros) {
                $query->where('id_puesto_trabajo_matriz', $filtros['idPuestoTrabajoMatriz']);
            });
        }

        if ($filtros['estado'] !== null && $filtros['estado'] !== '') {
            $reporteQuery->where('estado', $filtros['estado']);
        }

        if ($filtros['aprobado'] !== null && $filtros['aprobado'] !== '') {
            $reporteQuery->where('aprobado', (int) $filtros['aprobado']);
        }

        if (!empty($filtros['fechaDesde'])) {
            $reporteQuery->whereDate('fecha_asignacion', '>=', $filtros['fechaDesde']);
        }

        if (!empty($filtros['fechaHasta'])) {
            $reporteQuery->whereDate('fecha_asignacion', '<=', $filtros['fechaHasta']);
        }

        $this->aplicarTipoReporte($reporteQuery, $filtros['tipoReporte']);

        return $reporteQuery;
    }

    private function aplicarTipoReporte(Builder $query, ?string $tipoReporte): void
    {
        $hoy = Carbon::today();
        $limiteProximo = Carbon::today()->addDays(30);

        if ($tipoReporte === 'vencidas') {
            $query->where(function ($subQuery) use ($hoy) {
                $subQuery->where('estado', 'vencida')
                    ->orWhere(function ($fechaQuery) use ($hoy) {
                        $fechaQuery->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<', $hoy)
                            ->whereNotIn('estado', ['aprobada', 'cancelada']);
                    });
            });
        }

        if ($tipoReporte === 'por_vencer') {
            $query->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '>=', $hoy)
                ->whereDate('fecha_vencimiento', '<=', $limiteProximo)
                ->whereNotIn('estado', ['aprobada', 'cancelada', 'vencida']);
        }

        if ($tipoReporte === 'pendientes') {
            $query->where('estado', 'pendiente');
        }

        if ($tipoReporte === 'aprobadas') {
            $query->where('estado', 'aprobada');
        }

        if ($tipoReporte === 'reprobadas') {
            $query->whereIn('estado', ['reprobada', 'vencida']);
        }
    }

    private function obtenerFiltros(Request $request): array
    {
        return [
            'tipoReporte' => $request->query('tipo_reporte', 'general'),
            'buscar' => trim((string) $request->query('buscar', '')),
            'idEmpleado' => $request->query('id_empleado'),
            'idCapacitacion' => $request->query('id_capacitacion'),
            'idDepartamento' => $request->query('id_departamento'),
            'idPuestoTrabajoMatriz' => $request->query('id_puesto_trabajo_matriz'),
            'estado' => $request->query('estado'),
            'aprobado' => $request->query('aprobado'),
            'fechaDesde' => $request->query('fecha_desde'),
            'fechaHasta' => $request->query('fecha_hasta'),
        ];
    }

    private function catalogos(): array
    {
        return [
            'capacitaciones' => Capacitacion::orderBy('capacitacion')->get(),
            'departamentos' => Departamento::orderBy('departamento')->get(),
            'puestos' => PuestoTrabajoMatriz::with('departamento')
                ->orderBy('puesto_trabajo_matriz')
                ->get(),
            'empleados' => Empleado::with('puestoTrabajo.departamento')
                ->orderBy('nombre_completo')
                ->get(),
            'estados' => [
                'pendiente',
                'en_proceso',
                'aprobada',
                'reprobada',
                'vencida',
                'cancelada',
            ],
        ];
    }

    private function tiposReporte(): array
    {
        return [
            'general' => 'Reporte general',
            'por_empleado' => 'Reporte por empleado',
            'por_capacitacion' => 'Reporte por capacitación',
            'por_puesto' => 'Reporte por puesto',
            'por_departamento' => 'Reporte por departamento',
            'vencidas' => 'Reporte de reprobadas por fecha límite',
            'por_vencer' => 'Reporte de próximas a vencer',
            'pendientes' => 'Reporte de pendientes',
            'aprobadas' => 'Reporte de aprobadas',
            'reprobadas' => 'Reporte de reprobadas',
        ];
    }

    private function armarResumen(Collection $reportes): array
    {
        $hoy = Carbon::today();
        $limiteProximo = Carbon::today()->addDays(30);

        return [
            'total' => $reportes->count(),
            'pendientes' => $reportes->where('estado', 'pendiente')->count(),
            'en_proceso' => $reportes->where('estado', 'en_proceso')->count(),
            'aprobadas' => $reportes->where('estado', 'aprobada')->count(),
            'reprobadas' => $reportes->whereIn('estado', ['reprobada', 'vencida'])->count(),
            'canceladas' => $reportes->where('estado', 'cancelada')->count(),
            'vencidas' => $reportes->filter(function ($item) use ($hoy) {
                if ($item->estado === 'vencida') {
                    return true;
                }

                if (!$item->fecha_vencimiento) {
                    return false;
                }

                return Carbon::parse($item->fecha_vencimiento)->lt($hoy)
                    && !in_array($item->estado, ['aprobada', 'cancelada'], true);
            })->count(),
            'por_vencer' => $reportes->filter(function ($item) use ($hoy, $limiteProximo) {
                if (!$item->fecha_vencimiento) {
                    return false;
                }

                $fechaVencimiento = Carbon::parse($item->fecha_vencimiento);

                return $fechaVencimiento->between($hoy, $limiteProximo, true)
                    && !in_array($item->estado, ['aprobada', 'cancelada', 'vencida'], true);
            })->count(),
            'nota_promedio' => $reportes->whereNotNull('nota_final')->avg('nota_final'),
        ];
    }

    private function filtrosLegibles(Request $request): array
    {
        $filtros = $this->obtenerFiltros($request);
        $tiposReporte = $this->tiposReporte();

        $empleado = !empty($filtros['idEmpleado'])
            ? Empleado::find($filtros['idEmpleado'])
            : null;

        $capacitacion = !empty($filtros['idCapacitacion'])
            ? Capacitacion::find($filtros['idCapacitacion'])
            : null;

        $departamento = !empty($filtros['idDepartamento'])
            ? Departamento::find($filtros['idDepartamento'])
            : null;

        $puesto = !empty($filtros['idPuestoTrabajoMatriz'])
            ? PuestoTrabajoMatriz::find($filtros['idPuestoTrabajoMatriz'])
            : null;

        return [
            'Tipo de reporte' => $tiposReporte[$filtros['tipoReporte']] ?? 'Reporte general',
            'Búsqueda' => $filtros['buscar'] !== '' ? $filtros['buscar'] : 'Todos',
            'Empleado' => $empleado?->nombre_completo ?? 'Todos',
            'Capacitación' => $capacitacion?->capacitacion ?? 'Todas',
            'Departamento' => $departamento?->departamento ?? 'Todos',
            'Puesto' => $puesto?->puesto_trabajo_matriz ?? 'Todos',
            'Estado' => $filtros['estado'] ? ucfirst(str_replace('_', ' ', $filtros['estado'])) : 'Todos',
            'Aprobado' => $filtros['aprobado'] === '1' ? 'Sí' : ($filtros['aprobado'] === '0' ? 'No' : 'Todos'),
            'Fecha desde' => $filtros['fechaDesde'] ?: 'Sin filtro',
            'Fecha hasta' => $filtros['fechaHasta'] ?: 'Sin filtro',
        ];
    }

    private function estadoLegibleReporte(?string $estado): string
    {
        return match($estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'aprobada' => 'Aprobada',
            'reprobada' => 'Reprobada por evaluación',
            'vencida' => 'Reprobada por fecha límite',
            'cancelada' => 'Cancelada',
            default => $estado ? ucfirst(str_replace('_', ' ', $estado)) : '-',
        };
    }

    private function textoExcel($valor): string
    {
        if (is_null($valor) || $valor === '') {
            return '-';
        }

        $valor = (string) $valor;

        $valor = str_replace('"', '""', $valor);

        return '="' . $valor . '"';
    }

    private function fechaExcel($fecha, string $formato): string
    {
        if (!$fecha) {
            return '-';
        }

        return $this->textoExcel(Carbon::parse($fecha)->format($formato));
    }

    private function nombreArchivo(Request $request, string $extension): string
    {
        $tipoReporte = $request->query('tipo_reporte', 'general');

        return 'reporte_capacitaciones_'
            . Str::slug($tipoReporte)
            . '_'
            . now()->format('Ymd_His')
            . '.'
            . $extension;
    }
}