<?php

namespace App\Http\Controllers;

use App\Models\EvaluacionPregunta;
use App\Models\EvaluacionOpcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EliminacionCapacitacionService;

class EvaluacionOpcionController extends Controller
{
    public function index($id_evaluacion_pregunta)
    {
        $pregunta = EvaluacionPregunta::with('evaluacion.capacitacionModulo.capacitacion')->findOrFail($id_evaluacion_pregunta);

        $opciones = EvaluacionOpcion::where('id_evaluacion_pregunta', $id_evaluacion_pregunta)
            ->orderBy('orden')
            ->orderBy('id_evaluacion_opcion')
            ->get();

        return view('evaluacion_opciones.index', compact('pregunta', 'opciones'));
    }

    public function create($id_evaluacion_pregunta)
    {
        $pregunta = EvaluacionPregunta::with('evaluacion.capacitacionModulo.capacitacion')->findOrFail($id_evaluacion_pregunta);

        return view('evaluacion_opciones.create', compact('pregunta'));
    }

    public function store(Request $request, $id_evaluacion_pregunta)
    {
        $pregunta = EvaluacionPregunta::with('evaluacion.capacitacionModulo.capacitacion')->findOrFail($id_evaluacion_pregunta);
        $evaluacion = $pregunta->evaluacion;
        $modulo = $evaluacion->capacitacionModulo;

        $request->merge([
            'opcion' => trim((string) $request->opcion),
        ]);

        $request->validate([
            'opcion' => ['required', 'string', 'min:1', 'max:1000'],
            'es_correcta' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
        ]);

        $ordenRepetido = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
            ->where('orden', $request->orden)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra opción con ese orden en esta pregunta.'])
                ->withInput();
        }

        $textoRepetido = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
            ->where('opcion', $request->opcion)
            ->exists();

        if ($textoRepetido) {
            return back()
                ->withErrors(['opcion' => 'Ya existe otra opción con ese mismo texto en esta pregunta.'])
                ->withInput();
        }

        $tipoPregunta = $pregunta->tipo_pregunta;

        $cantidadOpciones = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)->count();

        $cantidadCorrectas = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
            ->where('es_correcta', 1)
            ->count();

        if ($tipoPregunta === 'opcion_unica' && (int) $request->es_correcta === 1 && $cantidadCorrectas >= 1) {
            return back()
                ->withErrors(['es_correcta' => 'Esta pregunta es de opción única y ya tiene una opción correcta.'])
                ->withInput();
        }

        if ($tipoPregunta === 'verdadero_falso' && $cantidadOpciones >= 2) {
            return back()
                ->withErrors(['opcion' => 'Las preguntas verdadero/falso solo permiten 2 opciones.'])
                ->withInput();
        }

        if ($tipoPregunta === 'verdadero_falso' && (int) $request->es_correcta === 1 && $cantidadCorrectas >= 1) {
            return back()
                ->withErrors(['es_correcta' => 'Las preguntas verdadero/falso solo pueden tener una opción correcta.'])
                ->withInput();
        }

        EvaluacionOpcion::create([
            'id_evaluacion_pregunta' => $pregunta->id_evaluacion_pregunta,
            'opcion' => $request->opcion,
            'es_correcta' => $request->es_correcta,
            'orden' => $request->orden,
        ]);

        if ($request->origen === 'builder') {
            return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $pregunta->evaluacion->id_capacitacion_modulo)
            ->with('success', 'La opción fue creada correctamente.');
        }

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', [
                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                'open' => 'evaluacion-' . $evaluacion->id_evaluacion,
            ])
            ->with('success', 'La opción fue guardada correctamente.');
    }

    public function edit($id)
    {
        $opcion = EvaluacionOpcion::with('evaluacionPregunta.evaluacion.capacitacionModulo.capacitacion')->findOrFail($id);
        $pregunta = $opcion->evaluacionPregunta;

        return view('evaluacion_opciones.edit', compact('opcion', 'pregunta'));
    }

    public function update(Request $request, $id)
    {
        $opcion = EvaluacionOpcion::with('evaluacionPregunta.evaluacion.capacitacionModulo.capacitacion')->findOrFail($id);
        $pregunta = $opcion->evaluacionPregunta;
        $evaluacion = $pregunta->evaluacion;
        $modulo = $evaluacion->capacitacionModulo;

        $request->merge([
            'opcion' => trim((string) $request->opcion),
        ]);

        $request->validate([
            'opcion' => ['required', 'string', 'min:1', 'max:1000'],
            'es_correcta' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
        ]);

        $ordenRepetido = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
            ->where('orden', $request->orden)
            ->where('id_evaluacion_opcion', '!=', $opcion->id_evaluacion_opcion)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra opción con ese orden en esta pregunta.'])
                ->withInput();
        }

        $textoRepetido = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
            ->where('opcion', $request->opcion)
            ->where('id_evaluacion_opcion', '!=', $opcion->id_evaluacion_opcion)
            ->exists();

        if ($textoRepetido) {
            return back()
                ->withErrors(['opcion' => 'Ya existe otra opción con ese mismo texto en esta pregunta.'])
                ->withInput();
        }

        $tipoPregunta = $pregunta->tipo_pregunta;

        $cantidadCorrectas = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
            ->where('es_correcta', 1)
            ->where('id_evaluacion_opcion', '!=', $opcion->id_evaluacion_opcion)
            ->count();

        if ($tipoPregunta === 'opcion_unica' && (int) $request->es_correcta === 1 && $cantidadCorrectas >= 1) {
            return back()
                ->withErrors(['es_correcta' => 'Esta pregunta es de opción única y ya tiene otra opción correcta.'])
                ->withInput();
        }

        if ($tipoPregunta === 'verdadero_falso' && (int) $request->es_correcta === 1 && $cantidadCorrectas >= 1) {
            return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $pregunta->evaluacion->id_capacitacion_modulo)
            ->with('success', 'La opción fue actualizada correctamente.');
        }

        $opcion->update([
            'opcion' => $request->opcion,
            'es_correcta' => $request->es_correcta,
            'orden' => $request->orden,
        ]);

        if ($request->origen === 'builder') {
            return redirect()
                ->route('capacitaciones.builder', $pregunta->evaluacion->capacitacionModulo->capacitacion->id_capacitacion)
                ->with('success', 'La opción fue actualizada correctamente.');
        }

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', [
                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                'open' => 'evaluacion-' . $evaluacion->id_evaluacion,
            ])
            ->with('success', 'La opción fue guardada correctamente.');
    }

    public function destroy(int $id)
    {
        $opcion = EvaluacionOpcion::with('pregunta.evaluacion.capacitacionModulo')->findOrFail($id);
        $pregunta = $opcion->pregunta;
        $evaluacion = $pregunta->evaluacion;
        $modulo = $evaluacion->capacitacionModulo;

        app(EliminacionCapacitacionService::class)
            ->eliminarOpcionEvaluacion((int) $id);

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', [
                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                'open' => 'evaluacion-' . $evaluacion->id_evaluacion,
            ])
            ->with('success', 'La opción de evaluación y las respuestas asociadas fueron eliminadas correctamente.');
    }

    private function volverAEvaluacionesDelModulo(EvaluacionPregunta $pregunta, string $mensaje)
    {
        $idCapacitacionModulo = DB::table('evaluacion')
            ->where('id_evaluacion', $pregunta->id_evaluacion)
            ->value('id_capacitacion_modulo');

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $idCapacitacionModulo)
            ->with('success', $mensaje);
    }
}