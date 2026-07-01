<?php

namespace App\Http\Controllers;

use App\Models\Ejercicio;
use App\Models\EjercicioPregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\EjercicioOpcion;
use App\Services\EliminacionCapacitacionService;



class EjercicioPreguntaController extends Controller
{
    public function store(Request $request, $id_ejercicio)
    {
        $ejercicio = Ejercicio::with('modulo.capacitacion')->findOrFail($id_ejercicio);

        $tipoPreguntaNormalizado = $request->tipo_pregunta !== null
            ? trim((string) $request->tipo_pregunta)
            : null;

        if (in_array($tipoPreguntaNormalizado, ['opcion_multiple', 'multiple'], true)) {
            $tipoPreguntaNormalizado = 'checklist_guiado';
        }

        $request->merge([
            'enunciado' => trim((string) $request->enunciado),
            'tipo_pregunta' => $tipoPreguntaNormalizado,
            'respuesta_correcta_texto' => $request->respuesta_correcta_texto !== null ? trim((string) $request->respuesta_correcta_texto) : null,
            'configuracion_json' => $request->configuracion_json !== null ? trim((string) $request->configuracion_json) : null,
        ]);

        $request->validate([
            'enunciado' => ['required', 'string', 'min:3'],
            'tipo_pregunta' => 'required|in:opcion_unica,verdadero_falso,relacionar,completar,respuesta_corta,caso_practico,checklist_guiado,actividad_visual_identificacion,seleccionar_posicion_imagen',
            'puntaje' => ['required', 'numeric', 'min:0.01'],
            'orden' => ['required', 'integer', 'min:1'],
            'activa' => ['required', 'in:0,1'],
            'respuesta_correcta_texto' => ['nullable', 'string'],
            'configuracion_json' => ['nullable', 'string'],
            'requiere_revision_manual' => ['required', 'in:0,1'],
            'posicion_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagen_pregunta' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'quitar_imagen_pregunta' => ['nullable', 'in:1'],
            'posicion_texto_apoyo' => ['nullable', 'string', 'max:1000'],
            'posicion_cantidad' => ['nullable', 'integer', 'min:1', 'max:50'],
            'opciones_iniciales' => ['nullable', 'array'],
            'opciones_iniciales.*.opcion' => ['nullable', 'string', 'max:1000'],
            'opciones_iniciales.*.lado' => ['nullable', 'in:izquierda,derecha'],
            'opciones_iniciales.*.clave_relacion' => ['nullable', 'string', 'max:100'],
            'opciones_iniciales.*.es_correcta' => ['nullable', 'in:0,1'],
            'opciones_iniciales.*.orden' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->tipo_pregunta === 'completar' && trim((string) $request->respuesta_correcta_texto) === '') {
            return back()
                ->withErrors(['respuesta_correcta_texto' => 'En preguntas tipo completar debes escribir la respuesta correcta en texto.'])
                ->withInput();
        }

        if ($request->tipo_pregunta === 'completar') {
            if (trim((string) $request->completar_texto_antes) === '' && trim((string) $request->completar_texto_despues) === '') {
                return back()
                    ->withErrors(['completar_texto_antes' => 'Debes escribir texto antes o después del espacio en blanco.'])
                    ->withInput();
            }
        }

        $ordenRepetido = EjercicioPregunta::where('id_ejercicio', $ejercicio->id_ejercicio)
            ->where('orden', $request->orden)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra pregunta con ese orden en este ejercicio.'])
                ->withInput();
        }

        $requiereRevisionManual = in_array($request->tipo_pregunta, ['respuesta_corta', 'caso_practico'], true)
            ? 1
            : (int) $request->requiere_revision_manual;

        $tiposConOpciones = [
            'opcion_unica',
            'verdadero_falso',
            'ordenar',
            'relacionar',
            'checklist_guiado',
            'seleccionar_posicion_imagen',
        ];

        $opcionesIniciales = collect($request->input('opciones_iniciales', []))
            ->map(function ($opcion, $indice) {
                return [
                    'opcion' => trim((string) ($opcion['opcion'] ?? '')),
                    'lado' => isset($opcion['lado']) && $opcion['lado'] !== '' ? trim((string) $opcion['lado']) : null,
                    'clave_relacion' => isset($opcion['clave_relacion']) && $opcion['clave_relacion'] !== '' ? trim((string) $opcion['clave_relacion']) : null,
                    'es_correcta' => isset($opcion['es_correcta']) && $opcion['es_correcta'] !== '' ? (int) $opcion['es_correcta'] : null,
                    'orden' => isset($opcion['orden']) && $opcion['orden'] !== '' ? (int) $opcion['orden'] : ($indice + 1),
                ];
            })
            ->filter(function ($opcion) {
                return $opcion['opcion'] !== '';
            })
            ->values();

        if (in_array($request->tipo_pregunta, $tiposConOpciones, true)) {
            if ($opcionesIniciales->isEmpty()) {
                return back()
                    ->withErrors(['opciones_iniciales' => 'Este tipo de pregunta necesita opciones. Agrega al menos una opción.'])
                    ->withInput();
            }

            if (in_array($request->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true) && $opcionesIniciales->count() < 2) {
                return back()
                    ->withErrors(['opciones_iniciales' => 'Las preguntas de selección necesitan al menos 2 opciones.'])
                    ->withInput();
            }

            if ($request->tipo_pregunta === 'verdadero_falso' && $opcionesIniciales->count() !== 2) {
                return back()
                    ->withErrors(['opciones_iniciales' => 'Las preguntas verdadero/falso deben tener exactamente 2 opciones.'])
                    ->withInput();
            }

            if ($request->tipo_pregunta === 'verdadero_falso') {
                $textosVerdaderoFalso = $opcionesIniciales
                    ->pluck('opcion')
                    ->map(function ($texto) {
                        return mb_strtolower(trim($texto));
                    })
                    ->sort()
                    ->values()
                    ->all();

                if ($textosVerdaderoFalso !== ['falso', 'verdadero']) {
                    return back()
                        ->withErrors(['opciones_iniciales' => 'Las preguntas verdadero/falso solo pueden tener las opciones Verdadero y Falso.'])
                        ->withInput();
                }
            }



            if (in_array($request->tipo_pregunta, ['opcion_unica', 'verdadero_falso'], true)) {
                $correctas = $opcionesIniciales->where('es_correcta', 1)->count();

                if ($correctas !== 1) {
                    return back()
                        ->withErrors(['opciones_iniciales' => 'Debes marcar exactamente una opción correcta.'])
                        ->withInput();
                }
            }

            if ($request->tipo_pregunta === 'checklist_guiado') {
                $correctas = $opcionesIniciales->where('es_correcta', 1)->count();

                if ($correctas < 1) {
                    return back()
                        ->withErrors(['opciones_iniciales' => 'La opción múltiple necesita al menos una opción correcta.'])
                        ->withInput();
                }
            }

            if ($request->tipo_pregunta === 'relacionar') {
                foreach ($opcionesIniciales as $opcionInicial) {
                    if (blank($opcionInicial['lado']) || blank($opcionInicial['clave_relacion'])) {
                        return back()
                            ->withErrors(['opciones_iniciales' => 'En relacionar, cada opción debe tener lado y clave de relación.'])
                            ->withInput();
                    }
                }

                if ($opcionesIniciales->where('lado', 'izquierda')->isEmpty() || $opcionesIniciales->where('lado', 'derecha')->isEmpty()) {
                    return back()
                        ->withErrors(['opciones_iniciales' => 'En relacionar, debes agregar opciones de izquierda y derecha.'])
                        ->withInput();
                }
            }

            if ($request->tipo_pregunta === 'seleccionar_posicion_imagen') {
                if ($opcionesIniciales->count() < 2) {
                    return back()
                        ->withErrors(['opciones_iniciales' => 'Debes agregar al menos 2 campos para ordenar.'])
                        ->withInput();
                }

                $cantidadPosiciones = (int) ($request->posicion_cantidad ?: $opcionesIniciales->count());
                $ordenes = $opcionesIniciales->pluck('orden')->map(fn ($orden) => (int) $orden);

                if ($ordenes->contains(fn ($orden) => $orden < 1 || $orden > $cantidadPosiciones)) {
                    return back()
                        ->withErrors(['opciones_iniciales' => 'El orden correcto de cada campo debe estar dentro del rango de órdenes disponibles.'])
                        ->withInput();
                }

                if ($ordenes->unique()->count() !== $ordenes->count()) {
                    return back()
                        ->withErrors(['opciones_iniciales' => 'No debes repetir el mismo número de orden en dos campos diferentes.'])
                        ->withInput();
                }
            }

        }

        $enunciadoFinal = $request->enunciado;

        if ($request->tipo_pregunta === 'completar') {
            $textoAntes = trim((string) $request->completar_texto_antes);
            $textoDespues = trim((string) $request->completar_texto_despues);

            $enunciadoFinal = trim($textoAntes . ' [[blank]] ' . $textoDespues);
        }

        $configuracionJsonFinal = $request->configuracion_json;

        if ($request->tipo_pregunta === 'respuesta_corta') {
            $configuracionJsonFinal = json_encode([
                'placeholder' => trim((string) $request->respuesta_breve_placeholder),
                'min_caracteres' => $request->respuesta_breve_min !== null && $request->respuesta_breve_min !== '' ? (int) $request->respuesta_breve_min : null,
                'max_caracteres' => $request->respuesta_breve_max !== null && $request->respuesta_breve_max !== '' ? (int) $request->respuesta_breve_max : null,
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($request->tipo_pregunta === 'caso_practico') {
            $configuracionJsonFinal = json_encode([
                'placeholder' => trim((string) $request->caso_placeholder),
                'min_caracteres' => $request->caso_min !== null && $request->caso_min !== '' ? (int) $request->caso_min : null,
                'max_caracteres' => $request->caso_max !== null && $request->caso_max !== '' ? (int) $request->caso_max : null,
                'criterios_revision' => trim((string) $request->caso_criterios_revision),
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($request->tipo_pregunta === 'seleccionar_posicion_imagen') {
            $rutaImagen = $request->hasFile('posicion_imagen')
                ? $request->file('posicion_imagen')->store('ejercicios/posiciones', 'public')
                : null;

            $configuracionJsonFinal = json_encode([
                'imagen' => $rutaImagen,
                'texto_apoyo' => trim((string) $request->posicion_texto_apoyo),
                'cantidad_posiciones' => (int) ($request->posicion_cantidad ?: $opcionesIniciales->count()),
            ], JSON_UNESCAPED_UNICODE);
        }

        if (in_array($request->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true)) {
            $configuracionJsonFinal = $this->configuracionImagenPreguntaEjercicio($request);
        }

            $pregunta = EjercicioPregunta::create([
                'id_ejercicio' => $ejercicio->id_ejercicio,
                'enunciado' => $enunciadoFinal,
                'tipo_pregunta' => $request->tipo_pregunta,
                'puntaje' => $request->puntaje,
                'orden' => $request->orden,
                'activa' => $request->activa,
                'respuesta_correcta_texto' => $request->tipo_pregunta === 'completar'
                    ? ($request->respuesta_correcta_texto ?: null)
                    : null,
                'configuracion_json' => $configuracionJsonFinal,
                'requiere_revision_manual' => $requiereRevisionManual,
            ]);

        foreach ($opcionesIniciales as $opcionInicial) {
            EjercicioOpcion::create([
                'id_ejercicio_pregunta' => $pregunta->id_ejercicio_pregunta,
                'opcion' => $opcionInicial['opcion'],
                'lado' => $request->tipo_pregunta === 'relacionar' ? $opcionInicial['lado'] : null,
                'clave_relacion' => $request->tipo_pregunta === 'relacionar' ? $opcionInicial['clave_relacion'] : null,
                'es_correcta' => in_array($request->tipo_pregunta, ['opcion_unica', 'checklist_guiado', 'verdadero_falso'], true)
                    ? $opcionInicial['es_correcta']
                    : null,
                'orden' => $opcionInicial['orden'],
            ]);
        }

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', $this->parametrosRetornoEjercicio($request, $ejercicio))
            ->with('success', 'La pregunta del ejercicio fue creada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $pregunta = EjercicioPregunta::with(['ejercicio.modulo.capacitacion', 'opciones'])->findOrFail($id);
        $ejercicio = $pregunta->ejercicio;

        $tipoPreguntaNormalizado = $request->tipo_pregunta !== null
            ? trim((string) $request->tipo_pregunta)
            : null;

        if (in_array($tipoPreguntaNormalizado, ['opcion_multiple', 'multiple'], true)) {
            $tipoPreguntaNormalizado = 'checklist_guiado';
        }

        $tipoPreguntaNormalizado = $request->tipo_pregunta !== null
            ? trim((string) $request->tipo_pregunta)
            : null;

        if (in_array($tipoPreguntaNormalizado, ['opcion_multiple', 'multiple'], true)) {
            $tipoPreguntaNormalizado = 'checklist_guiado';
        }

        $request->merge([
            'enunciado' => trim((string) $request->enunciado),
            'tipo_pregunta' => $tipoPreguntaNormalizado,
            'respuesta_correcta_texto' => $request->respuesta_correcta_texto !== null ? trim((string) $request->respuesta_correcta_texto) : null,
            'configuracion_json' => $request->configuracion_json !== null ? trim((string) $request->configuracion_json) : null,
        ]);

        $request->validate([
            'enunciado' => ['required', 'string', 'min:3'],
            'tipo_pregunta' => 'required|in:opcion_unica,verdadero_falso,relacionar,completar,respuesta_corta,caso_practico,checklist_guiado,actividad_visual_identificacion,seleccionar_posicion_imagen',
            'puntaje' => ['required', 'numeric', 'min:0.01'],
            'orden' => ['required', 'integer', 'min:1'],
            'activa' => ['required', 'in:0,1'],
            'respuesta_correcta_texto' => ['nullable', 'string'],
            'configuracion_json' => ['nullable', 'string'],
            'requiere_revision_manual' => ['required', 'in:0,1'],
            'posicion_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagen_pregunta' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'quitar_imagen_pregunta' => ['nullable', 'in:1'],
            'posicion_texto_apoyo' => ['nullable', 'string', 'max:1000'],
            'posicion_cantidad' => ['nullable', 'integer', 'min:1', 'max:50'],
            'opciones_existentes' => ['nullable', 'array'],
            'opciones_existentes.*.id_ejercicio_opcion' => ['nullable'],
            'opciones_existentes.*.opcion' => ['nullable', 'string', 'max:1000'],
            'opciones_existentes.*.lado' => ['nullable', 'in:izquierda,derecha'],
            'opciones_existentes.*.clave_relacion' => ['nullable', 'string', 'max:100'],
            'opciones_existentes.*.es_correcta' => ['nullable', 'in:0,1'],
            'opciones_existentes.*.orden' => ['nullable', 'integer', 'min:1'],
            'opciones_existentes.*.eliminar' => ['nullable', 'in:1'],

            'opciones_nuevas' => ['nullable', 'array'],
            'opciones_nuevas.*.opcion' => ['nullable', 'string', 'max:1000'],
            'opciones_nuevas.*.lado' => ['nullable', 'in:izquierda,derecha'],
            'opciones_nuevas.*.clave_relacion' => ['nullable', 'string', 'max:100'],
            'opciones_nuevas.*.es_correcta' => ['nullable', 'in:0,1'],
            'opciones_nuevas.*.orden' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->tipo_pregunta === 'completar' && trim((string) $request->respuesta_correcta_texto) === '') {
            return back()
                ->withErrors(['respuesta_correcta_texto' => 'En preguntas tipo completar debes escribir la respuesta correcta en texto.'])
                ->withInput();
        }

        if ($request->tipo_pregunta === 'completar') {
            if (trim((string) $request->completar_texto_antes) === '' && trim((string) $request->completar_texto_despues) === '') {
                return back()
                    ->withErrors(['completar_texto_antes' => 'Debes escribir texto antes o después del espacio en blanco.'])
                    ->withInput();
            }
        }

        $ordenRepetido = EjercicioPregunta::where('id_ejercicio', $ejercicio->id_ejercicio)
            ->where('orden', $request->orden)
            ->where('id_ejercicio_pregunta', '!=', $pregunta->id_ejercicio_pregunta)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra pregunta con ese orden en este ejercicio.'])
                ->withInput();
        }

        $mensajeCompatibilidad = $this->validarCompatibilidadTipoPregunta(
            $pregunta,
            $request->tipo_pregunta,
            $request->respuesta_correcta_texto
        );

        if ($mensajeCompatibilidad) {
            return back()
                ->withErrors(['tipo_pregunta' => $mensajeCompatibilidad])
                ->withInput();
        }

        $requiereRevisionManual = in_array($request->tipo_pregunta, ['respuesta_corta', 'caso_practico'], true)
            ? 1
            : (int) $request->requiere_revision_manual;

        $enunciadoFinal = $request->enunciado;

        if ($request->tipo_pregunta === 'completar') {
            $textoAntes = trim((string) $request->completar_texto_antes);
            $textoDespues = trim((string) $request->completar_texto_despues);

            $enunciadoFinal = trim($textoAntes . ' [[blank]] ' . $textoDespues);
        }

        $configuracionJsonFinal = $request->configuracion_json;

        if ($request->tipo_pregunta === 'respuesta_corta') {
            $configuracionJsonFinal = json_encode([
                'placeholder' => trim((string) $request->respuesta_breve_placeholder),
                'min_caracteres' => $request->respuesta_breve_min !== null && $request->respuesta_breve_min !== '' ? (int) $request->respuesta_breve_min : null,
                'max_caracteres' => $request->respuesta_breve_max !== null && $request->respuesta_breve_max !== '' ? (int) $request->respuesta_breve_max : null,
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($request->tipo_pregunta === 'caso_practico') {
            $configuracionJsonFinal = json_encode([
                'placeholder' => trim((string) $request->caso_placeholder),
                'min_caracteres' => $request->caso_min !== null && $request->caso_min !== '' ? (int) $request->caso_min : null,
                'max_caracteres' => $request->caso_max !== null && $request->caso_max !== '' ? (int) $request->caso_max : null,
                'criterios_revision' => trim((string) $request->caso_criterios_revision),
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($request->tipo_pregunta === 'seleccionar_posicion_imagen') {
            $configAnterior = json_decode($pregunta->configuracion_json ?? '{}', true);
            $rutaImagen = $configAnterior['imagen'] ?? null;

            if ($request->hasFile('posicion_imagen')) {
                $rutaImagen = $request->file('posicion_imagen')->store('ejercicios/posiciones', 'public');
            }

            $configuracionJsonFinal = json_encode([
                'imagen' => $rutaImagen,
                'texto_apoyo' => trim((string) $request->posicion_texto_apoyo),
                'cantidad_posiciones' => (int) ($request->posicion_cantidad ?: $pregunta->opciones->count()),
            ], JSON_UNESCAPED_UNICODE);
        }

        if (in_array($request->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true)) {
            $configuracionJsonFinal = $this->configuracionImagenPreguntaEjercicio($request, $pregunta->configuracion_json);
        }

        $pregunta->update([
            'enunciado' => $enunciadoFinal,
            'tipo_pregunta' => $request->tipo_pregunta,
            'puntaje' => $request->puntaje,
            'orden' => $request->orden,
            'activa' => $request->activa,
            'respuesta_correcta_texto' => $request->tipo_pregunta === 'completar'
                ? ($request->respuesta_correcta_texto ?: null)
                : null,
            'configuracion_json' => $configuracionJsonFinal,
            'requiere_revision_manual' => $requiereRevisionManual,
        ]);

        $tiposConOpciones = [
            'opcion_unica',
            'verdadero_falso',
            'ordenar',
            'relacionar',
            'checklist_guiado',
            'seleccionar_posicion_imagen',
        ];

        if (in_array($pregunta->tipo_pregunta, $tiposConOpciones, true)) {
            foreach ($request->input('opciones_existentes', []) as $datosOpcion) {
                $idOpcion = $datosOpcion['id_ejercicio_opcion'] ?? null;

                if (!$idOpcion || str_starts_with((string) $idOpcion, 'nuevo_')) {
                    if (($datosOpcion['opcion'] ?? '') !== '') {
                        EjercicioOpcion::create([
                            'id_ejercicio_pregunta' => $pregunta->id_ejercicio_pregunta,
                            'opcion' => trim((string) $datosOpcion['opcion']),
                            'lado' => $pregunta->tipo_pregunta === 'relacionar' ? ($datosOpcion['lado'] ?? null) : null,
                            'clave_relacion' => $pregunta->tipo_pregunta === 'relacionar' ? ($datosOpcion['clave_relacion'] ?? null) : null,
                            'es_correcta' => in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado', 'verdadero_falso'], true)
                                ? (int) ($datosOpcion['es_correcta'] ?? 0)
                                : null,
                            'orden' => (int) ($datosOpcion['orden'] ?? 1),
                        ]);
                    }

                    continue;
                }

                $opcion = EjercicioOpcion::where('id_ejercicio_pregunta', $pregunta->id_ejercicio_pregunta)
                    ->where('id_ejercicio_opcion', $idOpcion)
                    ->first();

                if (!$opcion) {
                    continue;
                }

                if (($datosOpcion['eliminar'] ?? null) == 1) {
                    $opcion->delete();
                    continue;
                }

                $opcion->update([
                    'opcion' => trim((string) ($datosOpcion['opcion'] ?? $opcion->opcion)),
                    'lado' => $pregunta->tipo_pregunta === 'relacionar' ? ($datosOpcion['lado'] ?? null) : null,
                    'clave_relacion' => $pregunta->tipo_pregunta === 'relacionar' ? ($datosOpcion['clave_relacion'] ?? null) : null,
                    'es_correcta' => in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado', 'verdadero_falso'], true)
                        ? (int) ($datosOpcion['es_correcta'] ?? 0)
                        : null,
                    'orden' => (int) ($datosOpcion['orden'] ?? $opcion->orden),
                ]);
            }

            foreach ($request->input('opciones_nuevas', []) as $datosOpcionNueva) {
                $textoOpcion = trim((string) ($datosOpcionNueva['opcion'] ?? ''));

                if ($textoOpcion === '') {
                    continue;
                }

                EjercicioOpcion::create([
                    'id_ejercicio_pregunta' => $pregunta->id_ejercicio_pregunta,
                    'opcion' => $textoOpcion,
                    'lado' => $pregunta->tipo_pregunta === 'relacionar' ? ($datosOpcionNueva['lado'] ?? null) : null,
                    'clave_relacion' => $pregunta->tipo_pregunta === 'relacionar' ? ($datosOpcionNueva['clave_relacion'] ?? null) : null,
                    'es_correcta' => in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado', 'verdadero_falso'], true)
                        ? (int) ($datosOpcionNueva['es_correcta'] ?? 0)
                        : null,
                    'orden' => (int) ($datosOpcionNueva['orden'] ?? 1),
                ]);
            }
        }

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', $this->parametrosRetornoEjercicio($request, $ejercicio))
            ->with('success', 'La pregunta del ejercicio fue actualizada correctamente.');
    }

    public function destroy(Request $request, int $id)
    {
        $pregunta = EjercicioPregunta::with('ejercicio')->findOrFail($id);
        $ejercicio = $pregunta->ejercicio;

        app(EliminacionCapacitacionService::class)
            ->eliminarPreguntaEjercicio((int) $id);

        return redirect()
            ->route('capacitacion_modulos.ejercicios.index', $this->parametrosRetornoEjercicio($request, $ejercicio))
            ->with('success', 'La pregunta del ejercicio, sus opciones y respuestas de intentos fueron eliminadas correctamente.');
    }

    private function configuracionImagenPreguntaEjercicio(Request $request, ?string $configuracionActual = null): ?string
    {
        $configuracion = json_decode($configuracionActual ?: '{}', true);

        if (!is_array($configuracion)) {
            $configuracion = [];
        }

        $rutaImagenPregunta = $configuracion['imagen_pregunta'] ?? null;

        if ((int) $request->input('quitar_imagen_pregunta', 0) === 1 && $rutaImagenPregunta) {
            Storage::disk('public')->delete($rutaImagenPregunta);
            $rutaImagenPregunta = null;
        }

        if ($request->hasFile('imagen_pregunta')) {
            if ($rutaImagenPregunta) {
                Storage::disk('public')->delete($rutaImagenPregunta);
            }

            $rutaImagenPregunta = $request->file('imagen_pregunta')->store('ejercicios/preguntas', 'public');
        }

        $configuracion['imagen_pregunta'] = $rutaImagenPregunta;

        $configuracion = array_filter($configuracion, function ($valor) {
            return !is_null($valor) && $valor !== '';
        });

        return empty($configuracion)
            ? null
            : json_encode($configuracion, JSON_UNESCAPED_UNICODE);
    }

    private function parametrosRetornoEjercicio(Request $request, Ejercicio $ejercicio): array
    {
        $parametros = [
            'id_capacitacion_modulo' => $ejercicio->id_capacitacion_modulo,
            'open' => 'ejercicio-' . $ejercicio->id_ejercicio,
        ];

        $idSeccionRetorno = $request->input('id_capacitacion_modulo_seccion', $ejercicio->id_capacitacion_modulo_seccion);

        if ((int) $request->input('volver_modulo', $idSeccionRetorno ? 1 : 0) === 1 && $idSeccionRetorno) {
            $parametros['volver_modulo'] = 1;
            $parametros['id_capacitacion_modulo_seccion'] = $idSeccionRetorno;
        }

        return $parametros;
    }

    private function validarCompatibilidadTipoPregunta(EjercicioPregunta $pregunta, string $nuevoTipo, ?string $respuestaCorrectaTexto): ?string
    {
        $opciones = $pregunta->opciones;

        if ($nuevoTipo === 'completar') {
            if ($opciones->count() > 0) {
                return 'No puedes cambiar esta pregunta a "completar" mientras todavía tenga opciones. Elimina las opciones primero.';
            }

            if (trim((string) $respuestaCorrectaTexto) === '') {
                return 'En preguntas tipo completar debes escribir la respuesta correcta en texto.';
            }
        }

        if (in_array($nuevoTipo, ['respuesta_corta', 'caso_practico'], true) && $opciones->count() > 0) {
            return 'No puedes usar opciones en preguntas tipo respuesta corta o caso práctico. Elimina las opciones primero.';
        }

        if (in_array($nuevoTipo, ['opcion_unica', 'checklist_guiado', 'verdadero_falso', 'seleccionar_posicion_imagen'], true)) {
            if ($opciones->count() > 0 && $opciones->contains(function ($opcion) {
                return filled($opcion->lado) || filled($opcion->clave_relacion);
            })) {
                return 'Las preguntas de selección no deben usar "lado" ni "clave de relación". Corrige las opciones primero.';
            }

            $totalCorrectas = $opciones->where('es_correcta', 1)->count();

            if ($nuevoTipo === 'opcion_unica' && $opciones->count() > 0 && $totalCorrectas !== 1) {
                return 'La pregunta de opción única debe tener exactamente una opción correcta.';
            }

            if ($nuevoTipo === 'checklist_guiado' && $opciones->count() > 0 && $totalCorrectas < 1) {
                return 'La pregunta de opción múltiple debe tener al menos una opción correcta.';
            }

            if ($nuevoTipo === 'verdadero_falso') {
                if ($opciones->count() > 2) {
                    return 'La pregunta verdadero/falso no puede tener más de 2 opciones.';
                }

                if ($opciones->count() > 0 && $totalCorrectas !== 1) {
                    return 'La pregunta verdadero/falso debe tener exactamente una opción correcta.';
                }
            }

            if ($nuevoTipo === 'seleccionar_posicion_imagen') {
                if ($opciones->count() > 0 && $opciones->count() < 2) {
                    return 'La pregunta de seleccionar orden necesita al menos 2 campos para ordenar.';
                }

                $ordenes = $opciones->pluck('orden')->map(fn ($orden) => (int) $orden);

                if ($ordenes->unique()->count() !== $ordenes->count()) {
                    return 'No debes repetir el mismo número de orden en dos campos diferentes.';
                }
            }
        }


        if ($nuevoTipo === 'relacionar') {
            if ($opciones->count() > 0 && $opciones->contains(function ($opcion) {
                return !in_array($opcion->lado, ['izquierda', 'derecha'], true)
                    || blank($opcion->clave_relacion)
                    || !is_null($opcion->es_correcta);
            })) {
                return 'Las preguntas tipo relacionar requieren "lado" y "clave de relación", y no deben usar "es correcta".';
            }

            if ($opciones->count() > 0) {
                $izquierdas = $opciones->where('lado', 'izquierda');
                $derechas = $opciones->where('lado', 'derecha');

                if ($izquierdas->isEmpty() || $derechas->isEmpty()) {
                    return 'La pregunta tipo relacionar debe tener al menos una opción a la izquierda y una a la derecha.';
                }

                if ($izquierdas->count() !== $derechas->count()) {
                    return 'La pregunta tipo relacionar debe tener la misma cantidad de opciones a la izquierda y a la derecha.';
                }

                foreach ($opciones->groupBy('clave_relacion') as $clave => $grupo) {
                    if (
                        $grupo->count() !== 2 ||
                        $grupo->where('lado', 'izquierda')->count() !== 1 ||
                        $grupo->where('lado', 'derecha')->count() !== 1
                    ) {
                        return 'Cada clave de relación debe repetirse exactamente en 2 opciones: una izquierda y una derecha.';
                    }
                }
            }
        }

        return null;
    }
}