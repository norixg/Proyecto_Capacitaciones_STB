<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente de capacitaciones</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h1, h2, h3, h4 {
            margin: 0;
            padding: 0;
        }

        .encabezado {
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .titulo {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitulo {
            font-size: 11px;
            color: #374151;
            margin-top: 4px;
        }

        .seccion {
            margin-bottom: 14px;
        }

        .seccion-titulo {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f3f4f6;
            border: 1px solid #9ca3af;
            padding: 5px;
            font-size: 9px;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 5px;
            font-size: 9px;
        }

        .label {
            font-weight: bold;
            background: #f9fafb;
            width: 22%;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .capacitacion {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .capacitacion-titulo {
            background: #e5e7eb;
            border: 1px solid #9ca3af;
            padding: 7px;
            font-weight: bold;
            margin-bottom: 6px;
        }
    </style>
</head>

<body>
    <div class="encabezado">
        <div class="titulo">Expediente de capacitaciones del empleado</div>
        <div class="subtitulo">Generado el {{ $fechaGeneracion }}</div>
    </div>

    <div class="seccion">
        <div class="seccion-titulo">Datos del empleado</div>

        <table>
            <tr>
                <td class="label">Empleado</td>
                <td>{{ $empleado->nombre_completo }}</td>
                <td class="label">Código</td>
                <td>{{ $empleado->codigo_empleado ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Identidad</td>
                <td>{{ $empleado->identidad ?? '-' }}</td>
                <td class="label">Correo</td>
                <td>{{ $empleado->correo ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Teléfono</td>
                <td>{{ $empleado->telefono ?? '-' }}</td>
                <td class="label">Fecha ingreso</td>
                <td>{{ $empleado->fecha_ingreso?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Puesto</td>
                <td>{{ $empleado->puestoTrabajo?->puesto_trabajo_matriz ?? '-' }}</td>
                <td class="label">Departamento</td>
                <td>{{ $empleado->puestoTrabajo?->departamento?->departamento ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="seccion">
        <div class="seccion-titulo">Resumen del expediente</div>

        <table>
            <tr>
                <th>Total asignadas</th>
                <th>Pendientes</th>
                <th>En proceso</th>
                <th>Aprobadas</th>
                <th>Reprobadas</th>
                <th>Vencidas</th>
                <th>Próximas a vencer</th>
                <th>Nota promedio</th>
            </tr>
            <tr class="center">
                <td>{{ $resumen['total'] }}</td>
                <td>{{ $resumen['pendientes'] }}</td>
                <td>{{ $resumen['en_proceso'] }}</td>
                <td>{{ $resumen['aprobadas'] }}</td>
                <td>{{ $resumen['reprobadas'] }}</td>
                <td>{{ $resumen['vencidas'] }}</td>
                <td>{{ $resumen['por_vencer'] }}</td>
                <td>{{ !is_null($resumen['nota_promedio']) ? number_format((float) $resumen['nota_promedio'], 2) . '%' : '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="seccion">
        <div class="seccion-titulo">Historial de capacitaciones</div>

        @forelse($capacitaciones as $item)
            <div class="capacitacion">
                <div class="capacitacion-titulo">
                    {{ $item->capacitacion?->capacitacion ?? 'Capacitación' }}
                </div>

                <table>
                    <tr>
                        <td class="label">Estado</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item->estado ?? 'pendiente')) }}</td>
                        <td class="label">Progreso</td>
                        <td>{{ number_format((float) ($item->progreso ?? 0), 2) }}%</td>
                    </tr>
                    <tr>
                        <td class="label">Nota final</td>
                        <td>{{ !is_null($item->nota_final) ? number_format((float) $item->nota_final, 2) . '%' : '-' }}</td>
                        <td class="label">Aprobado</td>
                        <td>{{ (int) ($item->aprobado ?? 0) === 1 ? 'Sí' : 'No' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha asignación</td>
                        <td>{{ $item->fecha_asignacion?->format('d/m/Y') ?? '-' }}</td>
                        <td class="label">Fecha vencimiento</td>
                        <td>{{ $item->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha inicio</td>
                        <td>{{ $item->fecha_inicio?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="label">Fecha finalización</td>
                        <td>{{ $item->fecha_finalizacion?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                </table>

                <br>

                <table>
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Estado</th>
                            <th>Progreso</th>
                            <th>Nota</th>
                            <th>Intentos evaluación</th>
                            <th>Intentos ejercicios</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($item->capacitacion?->capacitacionModulos ?? collect() as $modulo)
                            @php
                                $avanceModulo = $item->modulosAvance->firstWhere('id_capacitacion_modulo', $modulo->id_capacitacion_modulo);

                                $intentosEvaluacionModulo = $item->intentosEvaluacion->filter(function ($intento) use ($modulo) {
                                    return optional($intento->evaluacion)->id_capacitacion_modulo === $modulo->id_capacitacion_modulo;
                                });

                                $intentosEjercicioModulo = $item->intentosEjercicio->filter(function ($intento) use ($modulo) {
                                    return optional($intento->ejercicio)->id_capacitacion_modulo === $modulo->id_capacitacion_modulo;
                                });
                            @endphp

                            <tr>
                                <td>{{ $modulo->orden }}. {{ $modulo->titulo }}</td>
                                <td class="center">{{ ucfirst(str_replace('_', ' ', $avanceModulo->estado ?? 'pendiente')) }}</td>
                                <td class="right">{{ number_format((float) ($avanceModulo->progreso ?? 0), 2) }}%</td>
                                <td class="right">{{ !is_null($avanceModulo?->nota) ? number_format((float) $avanceModulo->nota, 2) . '%' : '-' }}</td>
                                <td class="center">{{ $intentosEvaluacionModulo->count() }}</td>
                                <td class="center">{{ $intentosEjercicioModulo->count() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="center">No hay módulos activos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($item->historial->count() > 0)
                    <br>

                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Estado anterior</th>
                                <th>Estado nuevo</th>
                                <th>Observación</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($item->historial->sortByDesc('fecha_movimiento') as $movimiento)
                                <tr>
                                    <td class="center">{{ $movimiento->fecha_movimiento?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="center">{{ $movimiento->estado_anterior ? ucfirst(str_replace('_', ' ', $movimiento->estado_anterior)) : '-' }}</td>
                                    <td class="center">{{ ucfirst(str_replace('_', ' ', $movimiento->estado_nuevo ?? '-')) }}</td>
                                    <td>{{ $movimiento->observacion ?? '-' }}</td>
                                    <td class="center">{{ $movimiento->usuario?->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @empty
            <table>
                <tr>
                    <td class="center">Este empleado todavía no tiene capacitaciones asignadas.</td>
                </tr>
            </table>
        @endforelse
    </div>
</body>
</html>