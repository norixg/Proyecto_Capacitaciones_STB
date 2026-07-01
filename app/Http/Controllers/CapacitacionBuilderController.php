<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;

class CapacitacionBuilderController extends Controller
{
    public function show($id)
    {
        $capacitacion = Capacitacion::with([
            'instructor',
            'modulos' => function ($query) {
                $query->orderBy('orden')->orderBy('id_capacitacion_modulo');
            },
            'modulos.secciones' => function ($query) {
                $query->reorder()
                    ->where('estado', 1)
                    ->orderBy('nivel')
                    ->orderBy('id_seccion_padre')
                    ->orderBy('orden')
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
            'modulos.recursos' => function ($query) {
                $query->orderBy('orden')->orderBy('id_capacitacion_recurso');
            },
            'modulos.evaluaciones' => function ($query) {
                $query->orderBy('orden')
                    ->orderBy('id_evaluacion');
            },
            'modulos.evaluaciones.preguntas' => function ($query) {
                $query->orderBy('orden')->orderBy('id_evaluacion_pregunta');
            },
            'modulos.evaluaciones.preguntas.opciones' => function ($query) {
                $query->orderBy('orden')->orderBy('id_evaluacion_opcion');
            },
            'modulos.ejercicios' => function ($query) {
                $query->orderBy('orden')->orderBy('id_ejercicio');
            },
            'modulos.ejercicios.preguntas' => function ($query) {
                $query->orderBy('orden')->orderBy('id_ejercicio_pregunta');
            },
            'modulos.ejercicios.preguntas.opciones' => function ($query) {
                $query->orderBy('orden')->orderBy('id_ejercicio_opcion');
            },
        ])->findOrFail($id);

        return view('capacitaciones.builder', compact('capacitacion'));
    }
}