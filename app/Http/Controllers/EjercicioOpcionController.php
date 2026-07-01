<?php

namespace App\Http\Controllers;

use App\Models\EjercicioPregunta;
use App\Models\EjercicioOpcion;
use Illuminate\Http\Request;
use App\Services\EliminacionCapacitacionService;


class EjercicioOpcionController extends Controller
{
    public function store(Request $request, $id_ejercicio_pregunta)
    {
        $pregunta = EjercicioPregunta::with('ejercicio.modulo.capacitacion')->findOrFail($id_ejercicio_pregunta);

        $ejercicio = $pregunta->ejercicio;

        $request->merge([
            'opcion' => trim((string) $request->opcion),
            'lado' => $request->lado !== null && $request->lado !== '' ? trim((string) $request->lado) : null,
            'clave_relacion' => $request->clave_relacion !== null && $request->clave_relacion !== '' ? trim((string) $request->clave_relacion) : null,
        ]);

        $request->validate([
            'opcion' => ['required', 'string', 'min:1', 'max:1000'],
            'lado' => ['nullable', 'in:izquierda,derecha'],
            'clave_relacion' => ['nullable', 'string', 'max:100'],
            'es_correcta' => ['nullable', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
        ]);

        $mensajeTipo = $this->validarSegunTipoPregunta($pregunta, $request);

        if ($mensajeTipo) {
            return back()
                ->withErrors(['opcion' => $mensajeTipo])
                ->withInput();
        }

        $ordenRepetido = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
            ->where('orden', $request->orden)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra opción con ese orden en esta pregunta de ejercicio.'])
                ->withInput();
        }

        $textoRepetido = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
            ->where('opcion', $request->opcion)
            ->exists();

        if ($textoRepetido) {
            return back()
                ->withErrors(['opcion' => 'Ya existe otra opción con ese mismo texto en esta pregunta de ejercicio.'])
                ->withInput();
        }

        if ($pregunta->tipo_pregunta === 'relacionar') {
            $claveDuplicadaMismoLado = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
                ->where('lado', $request->lado)
                ->where('clave_relacion', $request->clave_relacion)
                ->exists();

            if ($claveDuplicadaMismoLado) {
                return back()
                    ->withErrors(['clave_relacion' => 'Ya existe otra opción con esa misma clave en ese mismo lado.'])
                    ->withInput();
            }
        }

        $tipoPregunta = $pregunta->tipo_pregunta;
        $cantidadOpciones = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)->count();
        $cantidadCorrectas = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
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

        EjercicioOpcion::create([
            'id_ejercicio_pregunta' => $pregunta->id_ejercicio_pregunta,
            'opcion' => $request->opcion,
            'lado' => $pregunta->tipo_pregunta === 'relacionar' ? $request->lado : null,
            'clave_relacion' => $pregunta->tipo_pregunta === 'relacionar' ? $request->clave_relacion : null,
            'es_correcta' => in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado', 'opcion_multiple', 'verdadero_falso'], true)
                ? $request->es_correcta
                : null,
            'orden' => $request->orden,
        ]);

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', [
                'id_capacitacion_modulo' => $ejercicio->id_capacitacion_modulo,
                'open' => 'ejercicio-' . $ejercicio->id_ejercicio,
            ])
            ->with('success', 'La opción del ejercicio fue creada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $opcion = EjercicioOpcion::with('pregunta.ejercicio.modulo.capacitacion')->findOrFail($id);
        $pregunta = $opcion->pregunta;
        $ejercicio = $pregunta->ejercicio;

        $request->merge([
            'opcion' => trim((string) $request->opcion),
            'lado' => $request->lado !== null && $request->lado !== '' ? trim((string) $request->lado) : null,
            'clave_relacion' => $request->clave_relacion !== null && $request->clave_relacion !== '' ? trim((string) $request->clave_relacion) : null,
        ]);

        if ($pregunta->tipo_pregunta === 'verdadero_falso' && !in_array($request->opcion, ['Verdadero', 'Falso'], true)) {
            return back()
                ->withErrors(['opcion' => 'Las preguntas verdadero/falso solo permiten las opciones Verdadero y Falso.'])
                ->withInput();
        }

        $request->validate([
            'opcion' => ['required', 'string', 'min:1', 'max:1000'],
            'lado' => ['nullable', 'in:izquierda,derecha'],
            'clave_relacion' => ['nullable', 'string', 'max:100'],
            'es_correcta' => ['nullable', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
        ]);

        $mensajeTipo = $this->validarSegunTipoPregunta($pregunta, $request, $opcion);

        if ($mensajeTipo) {
            return back()
                ->withErrors(['opcion' => $mensajeTipo])
                ->withInput();
        }

        $ordenRepetido = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
            ->where('orden', $request->orden)
            ->where('id_ejercicio_opcion', '!=', $opcion->id_ejercicio_opcion)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra opción con ese orden en esta pregunta de ejercicio.'])
                ->withInput();
        }

        $textoRepetido = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
            ->where('opcion', $request->opcion)
            ->where('id_ejercicio_opcion', '!=', $opcion->id_ejercicio_opcion)
            ->exists();

        if ($textoRepetido) {
            return back()
                ->withErrors(['opcion' => 'Ya existe otra opción con ese mismo texto en esta pregunta de ejercicio.'])
                ->withInput();
        }

        if ($pregunta->tipo_pregunta === 'relacionar') {
            $claveDuplicadaMismoLado = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
                ->where('lado', $request->lado)
                ->where('clave_relacion', $request->clave_relacion)
                ->where('id_ejercicio_opcion', '!=', $opcion->id_ejercicio_opcion)
                ->exists();

            if ($claveDuplicadaMismoLado) {
                return back()
                    ->withErrors(['clave_relacion' => 'Ya existe otra opción con esa misma clave en ese mismo lado.'])
                    ->withInput();
            }
        }

        $tipoPregunta = $pregunta->tipo_pregunta;
        $cantidadCorrectas = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
            ->where('es_correcta', 1)
            ->where('id_ejercicio_opcion', '!=', $opcion->id_ejercicio_opcion)
            ->count();

        if ($tipoPregunta === 'opcion_unica' && (int) $request->es_correcta === 1 && $cantidadCorrectas >= 1) {
            return back()
                ->withErrors(['es_correcta' => 'Esta pregunta es de opción única y ya tiene otra opción correcta.'])
                ->withInput();
        }

        if ($tipoPregunta === 'verdadero_falso' && (int) $request->es_correcta === 1 && $cantidadCorrectas >= 1) {
            return back()
                ->withErrors(['es_correcta' => 'Las preguntas verdadero/falso solo pueden tener una opción correcta.'])
                ->withInput();
        }

        $opcion->update([
            'opcion' => $request->opcion,
            'lado' => $pregunta->tipo_pregunta === 'relacionar' ? ($request->lado ?: null) : null,
            'clave_relacion' => $pregunta->tipo_pregunta === 'relacionar' ? ($request->clave_relacion ?: null) : null,
            'es_correcta' => in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado', 'opcion_multiple', 'verdadero_falso'], true)
                ? (int) $request->es_correcta
                : null,
            'orden' => $request->orden,
        ]);

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', [
                'id_capacitacion_modulo' => $ejercicio->id_capacitacion_modulo,
                'open' => 'ejercicio-' . $ejercicio->id_ejercicio,
            ])
            ->with('success', 'La opción del ejercicio fue actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $opcion = EjercicioOpcion::with('pregunta.ejercicio')->findOrFail($id);
        $pregunta = $opcion->pregunta;
        $ejercicio = $pregunta->ejercicio;

        app(EliminacionCapacitacionService::class)
            ->eliminarOpcionEjercicio((int) $id);

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', [
                'id_capacitacion_modulo' => $ejercicio->id_capacitacion_modulo,
                'open' => 'ejercicio-' . $ejercicio->id_ejercicio,
            ])
            ->with('success', 'La opción del ejercicio fue eliminada correctamente.');
    }

    private function validarSegunTipoPregunta(EjercicioPregunta $pregunta, Request $request, ?EjercicioOpcion $opcionActual = null): ?string
    {
        $tipoPregunta = $pregunta->tipo_pregunta;
        $esCorrecta = $request->es_correcta;
        $lado = $request->lado;
        $claveRelacion = $request->clave_relacion;

        if (in_array($tipoPregunta, ['completar', 'respuesta_corta', 'caso_practico'], true)) {
            return 'Este tipo de pregunta no usa opciones. Debes responderla con texto, no con opciones.';
        }

        if ($tipoPregunta === 'ordenar') {
            if (!is_null($esCorrecta) || filled($lado) || filled($claveRelacion)) {
                return 'Las preguntas tipo ordenar solo usan texto y orden. No debes llenar "es correcta", "lado" ni "clave de relación".';
            }

            return null;
        }

        if ($tipoPregunta === 'relacionar') {
            if (blank($lado) || blank($claveRelacion)) {
                return 'En preguntas tipo relacionar debes llenar "lado" y "clave de relación".';
            }

            if (!is_null($esCorrecta)) {
                return 'Las preguntas tipo relacionar no usan "es correcta".';
            }

            return null;
        }

        if (in_array($tipoPregunta, ['opcion_unica', 'checklist_guiado', 'opcion_multiple', 'verdadero_falso'], true)) {
            if (filled($lado) || filled($claveRelacion)) {
                return 'Las preguntas de selección no usan "lado" ni "clave de relación".';
            }

            if (is_null($esCorrecta)) {
                return 'Debes indicar si esta opción es correcta o no.';
            }

            return null;
        }

        return null;
    }
}