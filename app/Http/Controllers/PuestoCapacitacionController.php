<?php

namespace App\Http\Controllers;

use App\Models\PuestosCapacitacionRrhh;
use Illuminate\Http\Request;

class PuestoCapacitacionController extends Controller
{
    public function index(Request $request)
    {
        $buscarPuesto = trim((string) $request->query('buscar_puesto', ''));
        $buscarCapacitacion = trim((string) $request->query('buscar_capacitacion', ''));

        $idsPuestos = PuestosCapacitacionRrhh::query()
            ->distinct()
            ->pluck('id_puesto_trabajo_matriz');

        $idsCapacitaciones = PuestosCapacitacionRrhh::query()
            ->distinct()
            ->pluck('id_capacitacion');

        $puestos = PuestosCapacitacionRrhh::query()
            ->from('puesto_trabajo_matriz as p')
            ->leftJoin('departamento as d', 'd.id_departamento', '=', 'p.id_departamento')
            ->whereIn('p.id_puesto_trabajo_matriz', $idsPuestos)
            ->when($buscarPuesto !== '', function ($query) use ($buscarPuesto) {
                $query->where(function ($subQuery) use ($buscarPuesto) {
                    $subQuery->where('p.puesto_trabajo_matriz', 'like', '%'.$buscarPuesto.'%')
                        ->orWhere('d.departamento', 'like', '%'.$buscarPuesto.'%');
                });
            })
            ->orderBy('p.puesto_trabajo_matriz')
            ->get([
                'p.id_puesto_trabajo_matriz',
                'p.puesto_trabajo_matriz',
                'd.departamento',
            ]);

        $capacitaciones = PuestosCapacitacionRrhh::query()
            ->from('capacitacion as c')
            ->whereIn('c.id_capacitacion', $idsCapacitaciones)
            ->when($buscarCapacitacion !== '', function ($query) use ($buscarCapacitacion) {
                $query->where('c.capacitacion', 'like', '%'.$buscarCapacitacion.'%');
            })
            ->orderBy('c.capacitacion')
            ->get(['c.id_capacitacion', 'c.capacitacion']);

        $relaciones = PuestosCapacitacionRrhh::query()
            ->whereIn('id_puesto_trabajo_matriz', $puestos->pluck('id_puesto_trabajo_matriz'))
            ->whereIn('id_capacitacion', $capacitaciones->pluck('id_capacitacion'))
            ->get(['id_puesto_trabajo_matriz', 'id_capacitacion'])
            ->mapWithKeys(fn ($registro) => [
                $registro->id_puesto_trabajo_matriz.'-'.$registro->id_capacitacion => true,
            ]);

        return view('puestos_capacitacion.index', compact(
            'puestos',
            'capacitaciones',
            'relaciones',
            'buscarPuesto',
            'buscarCapacitacion'
        ));
    }
}
