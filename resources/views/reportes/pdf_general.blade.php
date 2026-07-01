<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte administrativo de capacitaciones</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        .encabezado {
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 12px;
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

        .grid {
            width: 100%;
            margin-bottom: 12px;
        }

        .grid td {
            width: 20%;
            padding: 6px;
            border: 1px solid #d1d5db;
        }

        .label {
            font-weight: bold;
            color: #374151;
        }

        .valor {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }

        .filtros {
            margin-bottom: 12px;
            border: 1px solid #d1d5db;
            padding: 8px;
        }

        .filtros table {
            width: 100%;
            border-collapse: collapse;
        }

        .filtros td {
            padding: 3px 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        table.reporte {
            width: 100%;
            border-collapse: collapse;
        }

        table.reporte th {
            background: #f3f4f6;
            color: #111827;
            border: 1px solid #9ca3af;
            padding: 5px;
            font-size: 8px;
        }

        table.reporte td {
            border: 1px solid #d1d5db;
            padding: 4px;
            font-size: 8px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="encabezado">
        <div class="titulo">Reporte administrativo de capacitaciones</div>
        <div class="subtitulo">Generado el {{ $fechaGeneracion }}</div>
    </div>

    <table class="grid">
        <tr>
            <td>
                <div class="label">Total</div>
                <div class="valor">{{ $resumen['total'] }}</div>
            </td>
            <td>
                <div class="label">Pendientes</div>
                <div class="valor">{{ $resumen['pendientes'] }}</div>
            </td>
            <td>
                <div class="label">En proceso</div>
                <div class="valor">{{ $resumen['en_proceso'] }}</div>
            </td>
            <td>
                <div class="label">Aprobadas</div>
                <div class="valor">{{ $resumen['aprobadas'] }}</div>
            </td>
            <td>
                <div class="label">Reprobadas</div>
                <div class="valor">{{ $resumen['reprobadas'] }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Vencidas</div>
                <div class="valor">{{ $resumen['vencidas'] }}</div>
            </td>
            <td>
                <div class="label">Próximas a vencer</div>
                <div class="valor">{{ $resumen['por_vencer'] }}</div>
            </td>
            <td>
                <div class="label">Canceladas</div>
                <div class="valor">{{ $resumen['canceladas'] }}</div>
            </td>
            <td colspan="2">
                <div class="label">Nota promedio</div>
                <div class="valor">
                    {{ !is_null($resumen['nota_promedio']) ? number_format((float) $resumen['nota_promedio'], 2) . '%' : '-' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="filtros">
        <strong>Filtros aplicados</strong>

        <table>
            @foreach($filtrosLegibles as $campo => $valor)
                <tr>
                    <td style="width: 25%;"><strong>{{ $campo }}</strong></td>
                    <td>{{ $valor }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <table class="reporte">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Código</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>Capacitación</th>
                <th>Estado</th>
                <th>Progreso</th>
                <th>Nota</th>
                <th>Aprobado</th>
                <th>Obligatoria</th>
                <th>Asignación</th>
                <th>Inicio</th>
                <th>Finalización</th>
                <th>Límite</th>
                <th>Vencimiento</th>
            </tr>
        </thead>

        <tbody>
            @forelse($reportes as $reporte)
                <tr>
                    <td>{{ $reporte->empleado?->nombre_completo ?? '-' }}</td>
                    <td>{{ $reporte->empleado?->codigo_empleado ?? '-' }}</td>
                    <td>{{ $reporte->empleado?->puestoTrabajo?->puesto_trabajo_matriz ?? '-' }}</td>
                    <td>{{ $reporte->empleado?->puestoTrabajo?->departamento?->departamento ?? '-' }}</td>
                    <td>{{ $reporte->capacitacion?->capacitacion ?? '-' }}</td>
                    <td class="center">{{ ucfirst(str_replace('_', ' ', $reporte->estado ?? 'pendiente')) }}</td>
                    <td class="right">{{ number_format((float) ($reporte->progreso ?? 0), 2) }}%</td>
                    <td class="right">{{ !is_null($reporte->nota_final) ? number_format((float) $reporte->nota_final, 2) . '%' : '-' }}</td>
                    <td class="center">{{ (int) ($reporte->aprobado ?? 0) === 1 ? 'Sí' : 'No' }}</td>
                    <td class="center">{{ (int) ($reporte->obligatoria ?? 0) === 1 ? 'Sí' : 'No' }}</td>
                    <td class="center">{{ $reporte->fecha_asignacion?->format('d/m/Y') ?? '-' }}</td>
                    <td class="center">{{ $reporte->fecha_inicio?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="center">{{ $reporte->fecha_finalizacion?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="center">{{ $reporte->fecha_limite?->format('d/m/Y') ?? '-' }}</td>
                    <td class="center">{{ $reporte->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="center">
                        No hay resultados para los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>