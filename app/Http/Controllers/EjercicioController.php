<?php

namespace App\Http\Controllers;

use App\Models\CapacitacionModulo;
use App\Models\Ejercicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EliminacionCapacitacionService;


class EjercicioController extends Controller
{
    public function index(int $id_capacitacion_modulo)
    {
        $modulo = CapacitacionModulo::with([
            'capacitacion',
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
            'ejercicios' => function ($query) {
                $query->orderBy('orden')->orderBy('id_ejercicio');
            },
            'ejercicios.seccion',
            'ejercicios.preguntas' => function ($query) {
                $query->orderBy('orden')->orderBy('id_ejercicio_pregunta');
            },
            'ejercicios.preguntas.opciones' => function ($query) {
                $query->orderBy('orden')->orderBy('id_ejercicio_opcion');
            },
        ])->findOrFail($id_capacitacion_modulo);

        $secciones = $modulo->secciones;

        $siguienteOrdenEjercicio = ((int) Ejercicio::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->max('orden')) + 1;

        return view('ejercicios.index', compact('modulo', 'secciones', 'siguienteOrdenEjercicio'));
    }

    public function create(int $id_capacitacion_modulo)
    {
        $modulo = CapacitacionModulo::with([
            'capacitacion',
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ])->findOrFail($id_capacitacion_modulo);

        $secciones = $modulo->secciones;

        $siguienteOrden = ((int) Ejercicio::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->max('orden')) + 1;

        return view('ejercicios.create', compact('modulo', 'siguienteOrden', 'secciones'));
    }

    public function store(Request $request, int $id_capacitacion_modulo)
    {
        $modulo = CapacitacionModulo::with('capacitacion')->findOrFail($id_capacitacion_modulo);

        $request->merge([
            'titulo' => trim((string) $request->titulo),
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'instrucciones' => $request->instrucciones !== null ? trim((string) $request->instrucciones) : null,
        ]);

        $request->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:250'],
            'id_capacitacion_modulo_seccion' => ['nullable', 'integer', 'exists:capacitacion_modulo_seccion,id_capacitacion_modulo_seccion'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'instrucciones' => ['nullable', 'string'],
            'intentos_maximos' => ['nullable', 'integer', 'min:1'],
            'tiempo_limite_minutos' => ['nullable', 'integer', 'min:1'],
            'porcentaje_aprobacion' => ['required', 'numeric', 'min:1', 'max:100'],
            'obligatorio' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:0,1'],
            'mostrar_resultado_inmediato' => ['required', 'in:0,1'],
            'requiere_revision_manual' => ['required', 'in:0,1'],
        ]);

        $idSeccionSeleccionada = $request->filled('id_capacitacion_modulo_seccion')
            ? (int) $request->id_capacitacion_modulo_seccion
            : null;

        $secciones = $modulo->secciones;

        if ($idSeccionSeleccionada && !$secciones->contains('id_capacitacion_modulo_seccion', $idSeccionSeleccionada)) {
            return back()
                ->withErrors(['id_capacitacion_modulo_seccion' => 'La sección seleccionada no pertenece a este módulo.'])
                ->withInput();
        }

        $ordenRepetido = Ejercicio::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('orden', $request->orden)
            ->when($idSeccionSeleccionada, function ($query) use ($idSeccionSeleccionada) {
                $query->where('id_capacitacion_modulo_seccion', $idSeccionSeleccionada);
            }, function ($query) {
                $query->whereNull('id_capacitacion_modulo_seccion');
            })
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otro ejercicio con ese orden en este módulo.'])
                ->withInput();
        }

        $ejercicio = Ejercicio::create([
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'id_capacitacion_modulo_seccion' => $idSeccionSeleccionada,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion ?: null,
            'instrucciones' => $request->instrucciones ?: null,
            'intentos_maximos' => $request->intentos_maximos ?: null,
            'tiempo_limite_minutos' => $request->tiempo_limite_minutos ?: null,
            'porcentaje_aprobacion' => $request->porcentaje_aprobacion,
            'obligatorio' => $request->obligatorio,
            'orden' => $request->orden,
            'estado' => $request->estado,
            'mostrar_resultado_inmediato' => $request->mostrar_resultado_inmediato,
            'requiere_revision_manual' => $request->requiere_revision_manual,
        ]);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'open' => 'ejercicio-' . $ejercicio->id_ejercicio,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionSeleccionada ? 1 : 0) === 1 && $idSeccionSeleccionada) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionSeleccionada;
        }

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', $parametrosRedireccion)
            ->with('success', 'El ejercicio fue creado correctamente. Ahora puedes agregar preguntas y opciones.');

    }

    public function update(Request $request, int $id)
    {
        $ejercicio = Ejercicio::with('modulo.capacitacion')->findOrFail($id);
        $modulo = $ejercicio->modulo;

        $request->merge([
            'titulo' => trim((string) $request->titulo),
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'instrucciones' => $request->instrucciones !== null ? trim((string) $request->instrucciones) : null,
        ]);

        $request->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:250'],
            'id_capacitacion_modulo_seccion' => ['nullable', 'integer', 'exists:capacitacion_modulo_seccion,id_capacitacion_modulo_seccion'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'instrucciones' => ['nullable', 'string'],
            'intentos_maximos' => ['nullable', 'integer', 'min:1'],
            'tiempo_limite_minutos' => ['nullable', 'integer', 'min:1'],
            'porcentaje_aprobacion' => ['required', 'numeric', 'min:1', 'max:100'],
            'obligatorio' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:0,1'],
            'mostrar_resultado_inmediato' => ['required', 'in:0,1'],
            'requiere_revision_manual' => ['required', 'in:0,1'],
        ]);

        $idSeccionSeleccionada = $request->filled('id_capacitacion_modulo_seccion')
            ? (int) $request->id_capacitacion_modulo_seccion
            : null;

        $modulo->loadMissing([
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ]);

        $secciones = $modulo->secciones;

        if ($idSeccionSeleccionada && !$secciones->contains('id_capacitacion_modulo_seccion', $idSeccionSeleccionada)) {
            return back()
                ->withErrors(['id_capacitacion_modulo_seccion' => 'La sección seleccionada no pertenece a este módulo.'])
                ->withInput();
        }

        $ordenRepetido = Ejercicio::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('orden', $request->orden)
            ->where('id_ejercicio', '!=', $ejercicio->id_ejercicio)
            ->when($idSeccionSeleccionada, function ($query) use ($idSeccionSeleccionada) {
                $query->where('id_capacitacion_modulo_seccion', $idSeccionSeleccionada);
            }, function ($query) {
                $query->whereNull('id_capacitacion_modulo_seccion');
            })
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otro ejercicio con ese orden en este módulo.'])
                ->withInput();
        }

        if ((int) $request->estado === 1) {
            $errorPublicacion = $this->validarEjercicioParaActivar($ejercicio);

            if ($errorPublicacion) {
                return back()
                    ->withErrors(['estado' => $errorPublicacion])
                    ->withInput();
            }
        }

        $ejercicio->update([
            'id_capacitacion_modulo_seccion' => $idSeccionSeleccionada,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion ?: null,
            'instrucciones' => $request->instrucciones ?: null,
            'intentos_maximos' => $request->intentos_maximos ?: null,
            'tiempo_limite_minutos' => $request->tiempo_limite_minutos ?: null,
            'porcentaje_aprobacion' => $request->porcentaje_aprobacion,
            'obligatorio' => $request->obligatorio,
            'orden' => $request->orden,
            'estado' => $request->estado,
            'mostrar_resultado_inmediato' => $request->mostrar_resultado_inmediato,
            'requiere_revision_manual' => $request->requiere_revision_manual,
        ]);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'open' => 'ejercicio-' . $ejercicio->id_ejercicio,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionSeleccionada ? 1 : 0) === 1 && $idSeccionSeleccionada) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionSeleccionada;
        }

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', $parametrosRedireccion)
            ->with('success', 'El ejercicio fue actualizado correctamente.');
    }

    public function edit(int $id)
    {
        $ejercicio = Ejercicio::with('modulo.capacitacion')->findOrFail($id);
        $modulo = $ejercicio->modulo;

        $modulo->load([
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ]);

        $secciones = $modulo->secciones;

        return view('ejercicios.edit', compact('ejercicio', 'modulo', 'secciones'));
    }

    public function destroy(Request $request, int $id)
    {
        $ejercicio = Ejercicio::with('modulo')->findOrFail($id);
        $modulo = $ejercicio->modulo;
        $idSeccionRetorno = $request->input('id_capacitacion_modulo_seccion') ?: $ejercicio->id_capacitacion_modulo_seccion;

        app(EliminacionCapacitacionService::class)
            ->eliminarEjercicio((int) $id);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionRetorno ? 1 : 0) === 1 && $idSeccionRetorno) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionRetorno;
        }

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', $parametrosRedireccion)
            ->with('success', 'El ejercicio, sus preguntas, opciones, intentos, respuestas y avances fueron eliminados correctamente.');
    }

    private function validarEjercicioParaActivar(Ejercicio $ejercicio): ?string
    {
        $ejercicio->load(['preguntas.opciones']);

        $preguntasActivas = $ejercicio->preguntas
            ->where('activa', 1)
            ->values();

        if ($preguntasActivas->isEmpty()) {
            return 'No puedes activar este ejercicio porque todavía no tiene preguntas activas.';
        }

        $tiposQueNecesitanOpciones = [
            'opcion_unica',
            'opcion_multiple',
            'verdadero_falso',
            'ordenar',
            'relacionar',
            'checklist_guiado',
        ];

        foreach ($preguntasActivas as $pregunta) {
            if (!in_array($pregunta->tipo_pregunta, $tiposQueNecesitanOpciones, true)) {
                continue;
            }

            if ($pregunta->opciones->count() === 0) {
                return 'No puedes activar este ejercicio porque la pregunta "' . $pregunta->enunciado . '" no tiene opciones.';
            }

            if (in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado', 'opcion_multiple', 'verdadero_falso'], true)) {
                $tieneCorrecta = $pregunta->opciones->where('es_correcta', 1)->isNotEmpty();

                if (!$tieneCorrecta) {
                    return 'No puedes activar este ejercicio porque la pregunta "' . $pregunta->enunciado . '" no tiene una opción correcta marcada.';
                }
            }

            if ($pregunta->tipo_pregunta === 'relacionar') {
                $tieneIzquierda = $pregunta->opciones->where('lado', 'izquierda')->isNotEmpty();
                $tieneDerecha = $pregunta->opciones->where('lado', 'derecha')->isNotEmpty();

                if (!$tieneIzquierda || !$tieneDerecha) {
                    return 'No puedes activar este ejercicio porque la pregunta "' . $pregunta->enunciado . '" debe tener opciones de izquierda y derecha.';
                }
            }
        }

        return null;
    }
}