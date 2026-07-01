<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\Departamento;
use App\Models\EmpleadoCapacitacion;
use App\Models\PuestoTrabajoMatriz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NecesidadCapacitacionController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $idCapacitacion = $request->query('id_capacitacion');
        $idDepartamento = $request->query('id_departamento');
        $idPuestoTrabajoMatriz = $request->query('id_puesto_trabajo_matriz');
        $estadoNecesidad = $request->query('estado_necesidad');

        $ultimaAsignacionSub = EmpleadoCapacitacion::query()
            ->selectRaw('MAX(id_empleado_capacitacion) as id_empleado_capacitacion, id_empleado, id_capacitacion')
            ->groupBy('id_empleado', 'id_capacitacion');

        $baseQuery = DB::table('empleado as e')
            ->join('puesto_trabajo_matriz as pt', 'pt.id_puesto_trabajo_matriz', '=', 'e.id_puesto_trabajo_matriz')
            ->leftJoin('departamento as d', 'd.id_departamento', '=', 'pt.id_departamento')
            ->join('puestos_capacitacion as pc', function ($join) {
                $join->on('pc.id_puesto_trabajo_matriz', '=', 'e.id_puesto_trabajo_matriz')
                    ->where('pc.estado', 1);
            })
            ->join('capacitacion as c', function ($join) {
                $join->on('c.id_capacitacion', '=', 'pc.id_capacitacion')
                    ->where('c.estado', 1);
            })
            ->leftJoinSub($ultimaAsignacionSub, 'ua', function ($join) {
                $join->on('ua.id_empleado', '=', 'e.id_empleado')
                    ->on('ua.id_capacitacion', '=', 'pc.id_capacitacion');
            })
            ->leftJoin('empleado_capacitacion as ec', 'ec.id_empleado_capacitacion', '=', 'ua.id_empleado_capacitacion')
            ->where('e.estado', 1);

        if ($buscar !== '') {
            $baseQuery->where(function ($query) use ($buscar) {
                $query->where('e.nombre_completo', 'like', '%' . $buscar . '%')
                    ->orWhere('e.codigo_empleado', 'like', '%' . $buscar . '%')
                    ->orWhere('e.identidad', 'like', '%' . $buscar . '%')
                    ->orWhere('e.correo', 'like', '%' . $buscar . '%')
                    ->orWhere('pt.puesto_trabajo_matriz', 'like', '%' . $buscar . '%')
                    ->orWhere('c.capacitacion', 'like', '%' . $buscar . '%')
                    ->orWhere('c.codigo', 'like', '%' . $buscar . '%');
            });
        }

        if (!empty($idCapacitacion)) {
            $baseQuery->where('c.id_capacitacion', $idCapacitacion);
        }

        if (!empty($idDepartamento)) {
            $baseQuery->where('pt.id_departamento', $idDepartamento);
        }

        if (!empty($idPuestoTrabajoMatriz)) {
            $baseQuery->where('pt.id_puesto_trabajo_matriz', $idPuestoTrabajoMatriz);
        }

        if ($estadoNecesidad === 'necesita_asignacion') {
            $baseQuery->whereNull('ec.id_empleado_capacitacion');
        } elseif (in_array($estadoNecesidad, ['pendiente', 'en_proceso', 'aprobada', 'reprobada', 'vencida', 'cancelada'], true)) {
            $baseQuery->where('ec.estado', $estadoNecesidad);
        }

        $totalRequeridas = (clone $baseQuery)->count();
        $totalSinAsignar = (clone $baseQuery)->whereNull('ec.id_empleado_capacitacion')->count();
        $totalConAsignacion = $totalRequeridas - $totalSinAsignar;
        $totalAprobadas = (clone $baseQuery)->where('ec.estado', 'aprobada')->count();

        $necesidades = $baseQuery
            ->select([
                'e.id_empleado',
                'e.nombre_completo',
                'e.codigo_empleado',
                'e.identidad',
                'e.correo',
                'pt.id_puesto_trabajo_matriz',
                'pt.puesto_trabajo_matriz',
                'd.departamento',
                'pc.id_puestos_capacitacion',
                'pc.obligatoria',
                'pc.dias_para_vencer',
                'c.id_capacitacion',
                'c.capacitacion',
                'c.codigo as codigo_capacitacion',
                'ec.id_empleado_capacitacion',
                'ec.estado as estado_asignacion',
                'ec.progreso',
                'ec.nota_final',
                'ec.aprobado',
                'ec.fecha_asignacion',
                'ec.fecha_limite',
                'ec.fecha_vencimiento',
            ])
            ->selectRaw("
                CASE
                    WHEN ec.id_empleado_capacitacion IS NULL THEN 'necesita_asignacion'
                    ELSE ec.estado
                END as estado_necesidad
            ")
            ->orderBy('e.nombre_completo')
            ->orderBy('c.capacitacion')
            ->paginate(20)
            ->withQueryString();

        $capacitaciones = Capacitacion::query()
            ->where('estado', 1)
            ->orderBy('capacitacion')
            ->get();

        $departamentos = Departamento::query()
            ->orderBy('departamento')
            ->get();

        $puestos = PuestoTrabajoMatriz::query()
            ->with('departamento')
            ->where('estado', 1)
            ->orderBy('puesto_trabajo_matriz')
            ->get();

        $estadosNecesidad = [
            'necesita_asignacion' => 'Necesita asignación',
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'aprobada' => 'Aprobada',
            'reprobada' => 'Reprobada',
            'vencida' => 'Vencida',
            'cancelada' => 'Cancelada',
        ];

        return view('necesidades_capacitacion.index', compact(
            'necesidades',
            'capacitaciones',
            'departamentos',
            'puestos',
            'estadosNecesidad',
            'buscar',
            'idCapacitacion',
            'idDepartamento',
            'idPuestoTrabajoMatriz',
            'estadoNecesidad',
            'totalRequeridas',
            'totalSinAsignar',
            'totalConAsignacion',
            'totalAprobadas'
        ));
    }
}