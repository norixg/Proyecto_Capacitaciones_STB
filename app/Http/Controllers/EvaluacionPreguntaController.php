<?php

namespace App\Http\Controllers;

use App\Models\Evaluacion;
use App\Models\EvaluacionPregunta;
use Illuminate\Http\Request;
use App\Models\EvaluacionOpcion;
use Illuminate\Support\Facades\DB;
use App\Services\EliminacionCapacitacionService;
use Illuminate\Support\Facades\Storage;


class EvaluacionPreguntaController extends Controller
{
    public function index($id_evaluacion)
    {
        $evaluacion = Evaluacion::with('capacitacionModulo.capacitacion')->findOrFail($id_evaluacion);

        $preguntas = EvaluacionPregunta::where('id_evaluacion', $id_evaluacion)
            ->orderBy('orden')
            ->orderBy('id_evaluacion_pregunta')
            ->get();

        return view('evaluacion_preguntas.index', compact('evaluacion', 'preguntas'));
    }

    public function create($id_evaluacion)
    {
        $evaluacion = Evaluacion::with('capacitacionModulo.capacitacion')->findOrFail($id_evaluacion);

        return view('evaluacion_preguntas.create', compact('evaluacion'));
    }

    public function store(Request $request, $id_evaluacion)
    {
        $evaluacion = Evaluacion::with('capacitacionModulo.capacitacion')->findOrFail($id_evaluacion);

        $tipoPreguntaNormalizado = $request->tipo_pregunta !== null
            ? trim((string) $request->tipo_pregunta)
            : null;

        if (in_array($tipoPreguntaNormalizado, ['opcion_multiple', 'multiple'], true)) {
            $tipoPreguntaNormalizado = 'checklist_guiado';
        }

        $request->merge([
            'pregunta' => trim((string) $request->pregunta),
            'tipo_pregunta' => $tipoPreguntaNormalizado,
            'respuesta_correcta_texto' => $request->respuesta_correcta_texto !== null ? trim((string) $request->respuesta_correcta_texto) : null,
            'configuracion_json' => $request->configuracion_json !== null ? trim((string) $request->configuracion_json) : null,
        ]);

        $request->validate([
            'pregunta' => ['required_unless:tipo_pregunta,completar', 'nullable', 'string', 'min:3', 'max:2000'],
            'tipo_pregunta' => 'required|in:opcion_unica,verdadero_falso,completar,respuesta_corta,checklist_guiado,seleccionar_posicion_imagen',
            'puntaje' => ['required', 'numeric', 'min:0.01'],
            'orden' => ['required', 'integer', 'min:1'],
            'activa' => ['required', 'in:0,1'],
            'respuesta_correcta_texto' => ['nullable', 'string'],
            'configuracion_json' => ['nullable', 'string'],
            'requiere_revision_manual' => ['required', 'in:0,1'],
            'completar_texto_antes' => ['nullable', 'string'],
            'completar_texto_despues' => ['nullable', 'string'],
            'respuesta_breve_placeholder' => ['nullable', 'string', 'max:500'],
            'respuesta_breve_min' => ['nullable', 'integer', 'min:0'],
            'respuesta_breve_max' => ['nullable', 'integer', 'min:1'],
            'posicion_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'posicion_texto_apoyo' => ['nullable', 'string', 'max:1000'],
            'posicion_cantidad' => ['nullable', 'integer', 'min:1', 'max:50'],
            'opciones_iniciales' => ['nullable', 'array'],
            'opciones_iniciales.*.opcion' => ['nullable', 'string', 'max:1000'],
            'opciones_iniciales.*.es_correcta' => ['nullable', 'in:0,1'],
            'opciones_iniciales.*.orden' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->tipo_pregunta === 'completar' && trim((string) $request->respuesta_correcta_texto) === '') {
            return back()
                ->withErrors(['respuesta_correcta_texto' => 'En preguntas tipo completar debes escribir la respuesta correcta.'])
                ->withInput();
        }

        if ($request->tipo_pregunta === 'completar') {
            if (trim((string) $request->completar_texto_antes) === '' && trim((string) $request->completar_texto_despues) === '') {
                return back()
                    ->withErrors(['completar_texto_antes' => 'Debes escribir texto antes o después del espacio en blanco.'])
                    ->withInput();
            }
        }

        $ordenRepetido = EvaluacionPregunta::where('id_evaluacion', $evaluacion->id_evaluacion)
            ->where('orden', $request->orden)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra pregunta con ese orden en esta evaluación.'])
                ->withInput();
        }

        $opcionesIniciales = $this->normalizarOpcionesEvaluacion($request->input('opciones_iniciales', []));

        $mensajeOpciones = $this->validarOpcionesEvaluacion($request->tipo_pregunta, $opcionesIniciales);

        if ($mensajeOpciones) {
            return back()
                ->withErrors(['opciones_iniciales' => $mensajeOpciones])
                ->withInput();
        }


        $preguntaFinal = $request->pregunta;

        if ($request->tipo_pregunta === 'completar') {
            $textoAntes = trim((string) $request->completar_texto_antes);
            $textoDespues = trim((string) $request->completar_texto_despues);

            $preguntaFinal = trim($textoAntes . ' [[blank]] ' . $textoDespues);
        }

        $configuracionJsonFinal = $request->configuracion_json;

        if ($request->tipo_pregunta === 'respuesta_corta') {
            $configuracionJsonFinal = json_encode([
                'placeholder' => trim((string) $request->respuesta_breve_placeholder),
                'min_caracteres' => $request->respuesta_breve_min !== null && $request->respuesta_breve_min !== '' ? (int) $request->respuesta_breve_min : null,
                'max_caracteres' => $request->respuesta_breve_max !== null && $request->respuesta_breve_max !== '' ? (int) $request->respuesta_breve_max : null,
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($request->tipo_pregunta === 'seleccionar_posicion_imagen') {
            $rutaImagen = $request->hasFile('posicion_imagen')
                ? $request->file('posicion_imagen')->store('evaluaciones/posiciones', 'public')
                : null;

            $cantidadPosiciones = max(
                (int) ($request->posicion_cantidad ?: 0),
                (int) $opcionesIniciales->count(),
                (int) ($opcionesIniciales->max('orden') ?? 0),
                1
            );

            $configuracionJsonFinal = json_encode([
                'imagen' => $rutaImagen,
                'texto_apoyo' => trim((string) $request->posicion_texto_apoyo),
                'cantidad_posiciones' => $cantidadPosiciones,
            ], JSON_UNESCAPED_UNICODE);
        }

        if (in_array($request->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true)) {
            $configuracionJsonFinal = $this->configuracionImagenPreguntaEvaluacion($request);
        }

        $requiereRevisionManual = $request->tipo_pregunta === 'respuesta_corta'
            ? 1
            : (int) $request->requiere_revision_manual;

        DB::transaction(function () use ($evaluacion, $request, $preguntaFinal, $configuracionJsonFinal, $requiereRevisionManual, $opcionesIniciales) {
            $pregunta = EvaluacionPregunta::create([
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'pregunta' => $preguntaFinal,
                'tipo_pregunta' => $request->tipo_pregunta,
                'puntaje' => $request->puntaje,
                'orden' => $request->orden,
                'activa' => $request->activa,
                'respuesta_correcta_texto' => in_array($request->tipo_pregunta, ['completar', 'respuesta_corta'], true)
                    ? ($request->respuesta_correcta_texto ?: null)
                    : null,
                'configuracion_json' => $configuracionJsonFinal,
                'requiere_revision_manual' => $requiereRevisionManual,
            ]);

            foreach ($opcionesIniciales as $opcionInicial) {
                EvaluacionOpcion::create([
                    'id_evaluacion_pregunta' => $pregunta->id_evaluacion_pregunta,
                    'opcion' => $opcionInicial['opcion'],
                    'es_correcta' => (int) ($opcionInicial['es_correcta'] ?? 0),
                    'orden' => $opcionInicial['orden'],
                ]);
            }
        });

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $this->parametrosRetornoEvaluacion($request, $evaluacion))
            ->with('success', 'La pregunta fue creada correctamente.');
    }

    public function edit($id)
    {
        $pregunta = EvaluacionPregunta::with('evaluacion.capacitacionModulo.capacitacion')->findOrFail($id);
        $evaluacion = $pregunta->evaluacion;

        return view('evaluacion_preguntas.edit', compact('pregunta', 'evaluacion'));
    }

    public function update(Request $request, $id)
    {
        $pregunta = EvaluacionPregunta::with(['evaluacion.capacitacionModulo.capacitacion', 'opciones'])->findOrFail($id);
        $evaluacion = $pregunta->evaluacion;
        $modulo = $evaluacion->capacitacionModulo;

        $tipoPreguntaNormalizado = $request->tipo_pregunta !== null
            ? trim((string) $request->tipo_pregunta)
            : null;

        if (in_array($tipoPreguntaNormalizado, ['opcion_multiple', 'multiple'], true)) {
            $tipoPreguntaNormalizado = 'checklist_guiado';
        }

        $request->merge([
            'pregunta' => trim((string) $request->pregunta),
            'tipo_pregunta' => $tipoPreguntaNormalizado,
            'respuesta_correcta_texto' => $request->respuesta_correcta_texto !== null ? trim((string) $request->respuesta_correcta_texto) : null,
            'configuracion_json' => $request->configuracion_json !== null ? trim((string) $request->configuracion_json) : null,
        ]);

        $request->validate([
            'pregunta' => ['required_unless:tipo_pregunta,completar', 'nullable', 'string', 'min:3', 'max:2000'],
            'tipo_pregunta' => 'required|in:opcion_unica,verdadero_falso,completar,respuesta_corta,checklist_guiado,seleccionar_posicion_imagen',
            'puntaje' => ['required', 'numeric', 'min:0.01'],
            'orden' => ['required', 'integer', 'min:1'],
            'activa' => ['required', 'in:0,1'],
            'respuesta_correcta_texto' => ['nullable', 'string'],
            'configuracion_json' => ['nullable', 'string'],
            'requiere_revision_manual' => ['required', 'in:0,1'],
            'completar_texto_antes' => ['nullable', 'string'],
            'completar_texto_despues' => ['nullable', 'string'],
            'respuesta_breve_placeholder' => ['nullable', 'string', 'max:500'],
            'respuesta_breve_min' => ['nullable', 'integer', 'min:0'],
            'respuesta_breve_max' => ['nullable', 'integer', 'min:1'],
            'posicion_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagen_pregunta' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'quitar_imagen_pregunta' => ['nullable', 'in:1'],
            'posicion_texto_apoyo' => ['nullable', 'string', 'max:1000'],
            'posicion_cantidad' => ['nullable', 'integer', 'min:1', 'max:50'],

            'opciones_existentes' => ['nullable', 'array'],
            'opciones_existentes.*.id_evaluacion_opcion' => ['nullable'],
            'opciones_existentes.*.opcion' => ['nullable', 'string', 'max:1000'],
            'opciones_existentes.*.es_correcta' => ['nullable', 'in:0,1'],
            'opciones_existentes.*.orden' => ['nullable', 'integer', 'min:1'],
            'opciones_existentes.*.eliminar' => ['nullable', 'in:1'],

            'opciones_nuevas' => ['nullable', 'array'],
            'opciones_nuevas.*.opcion' => ['nullable', 'string', 'max:1000'],
            'opciones_nuevas.*.es_correcta' => ['nullable', 'in:0,1'],
            'opciones_nuevas.*.orden' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($request->tipo_pregunta === 'completar' && trim((string) $request->respuesta_correcta_texto) === '') {
            return back()
                ->withErrors(['respuesta_correcta_texto' => 'En preguntas tipo completar debes escribir la respuesta correcta.'])
                ->withInput();
        }

        if ($request->tipo_pregunta === 'completar') {
            if (trim((string) $request->completar_texto_antes) === '' && trim((string) $request->completar_texto_despues) === '') {
                return back()
                    ->withErrors(['completar_texto_antes' => 'Debes escribir texto antes o después del espacio en blanco.'])
                    ->withInput();
            }
        }

        if (!in_array($request->tipo_pregunta, $this->tiposConOpcionesEvaluacion(), true) && $pregunta->opciones->count() > 0) {
            return back()
                ->withErrors(['tipo_pregunta' => 'Este tipo de pregunta no usa opciones. Elimina las opciones antes de cambiar a este tipo.'])
                ->withInput();
        }

        $ordenRepetido = EvaluacionPregunta::where('id_evaluacion', $evaluacion->id_evaluacion)
            ->where('orden', $request->orden)
            ->where('id_evaluacion_pregunta', '!=', $pregunta->id_evaluacion_pregunta)
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra pregunta con ese orden en esta evaluación.'])
                ->withInput();
        }

        $opcionesParaValidar = [];

        foreach ($request->input('opciones_existentes', []) as $datosOpcion) {
            if (($datosOpcion['eliminar'] ?? null) == 1) {
                continue;
            }

            $opcionesParaValidar[] = [
                'opcion' => $datosOpcion['opcion'] ?? '',
                'es_correcta' => $datosOpcion['es_correcta'] ?? null,
                'orden' => $datosOpcion['orden'] ?? null,
            ];
        }

        foreach ($request->input('opciones_nuevas', []) as $datosOpcionNueva) {
            $opcionesParaValidar[] = [
                'opcion' => $datosOpcionNueva['opcion'] ?? '',
                'es_correcta' => $datosOpcionNueva['es_correcta'] ?? null,
                'orden' => $datosOpcionNueva['orden'] ?? null,
            ];
        }

        $opcionesNormalizadas = $this->normalizarOpcionesEvaluacion($opcionesParaValidar);

        $mensajeOpciones = $this->validarOpcionesEvaluacion($request->tipo_pregunta, $opcionesNormalizadas);

        if ($mensajeOpciones) {
            return back()
                ->withErrors(['opciones_existentes' => $mensajeOpciones])
                ->withInput();
        }

        $preguntaFinal = $request->pregunta;

        if ($request->tipo_pregunta === 'completar') {
            $textoAntes = trim((string) $request->completar_texto_antes);
            $textoDespues = trim((string) $request->completar_texto_despues);

            $preguntaFinal = trim($textoAntes . ' [[blank]] ' . $textoDespues);
        }

        $configuracionJsonFinal = $request->configuracion_json;

        if ($request->tipo_pregunta === 'respuesta_corta') {
            $configuracionJsonFinal = json_encode([
                'placeholder' => trim((string) $request->respuesta_breve_placeholder),
                'min_caracteres' => $request->respuesta_breve_min !== null && $request->respuesta_breve_min !== '' ? (int) $request->respuesta_breve_min : null,
                'max_caracteres' => $request->respuesta_breve_max !== null && $request->respuesta_breve_max !== '' ? (int) $request->respuesta_breve_max : null,
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($request->tipo_pregunta === 'seleccionar_posicion_imagen') {
            $configAnterior = json_decode($pregunta->configuracion_json ?? '{}', true);
            $rutaImagen = $configAnterior['imagen'] ?? null;

            if ($request->hasFile('posicion_imagen')) {
                $rutaImagen = $request->file('posicion_imagen')->store('evaluaciones/posiciones', 'public');
            }

            $cantidadPosiciones = max(
                (int) ($request->posicion_cantidad ?: 0),
                (int) $opcionesNormalizadas->count(),
                (int) ($opcionesNormalizadas->max('orden') ?? 0),
                1
            );

            $configuracionJsonFinal = json_encode([
                'imagen' => $rutaImagen,
                'texto_apoyo' => trim((string) $request->posicion_texto_apoyo),
                'cantidad_posiciones' => $cantidadPosiciones,
            ], JSON_UNESCAPED_UNICODE);
        }

        if (in_array($request->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true)) {
            $configuracionJsonFinal = $this->configuracionImagenPreguntaEvaluacion($request, $pregunta->configuracion_json);
        }

        $requiereRevisionManual = $request->tipo_pregunta === 'respuesta_corta'
            ? 1
            : (int) $request->requiere_revision_manual;

        DB::transaction(function () use ($request, $pregunta, $preguntaFinal, $configuracionJsonFinal, $requiereRevisionManual) {
            $pregunta->update([
                'pregunta' => $preguntaFinal,
                'tipo_pregunta' => $request->tipo_pregunta,
                'puntaje' => $request->puntaje,
                'orden' => $request->orden,
                'activa' => $request->activa,
                'respuesta_correcta_texto' => in_array($request->tipo_pregunta, ['completar', 'respuesta_corta'], true)
                    ? ($request->respuesta_correcta_texto ?: null)
                    : null,
                'configuracion_json' => $configuracionJsonFinal,
                'requiere_revision_manual' => $requiereRevisionManual,
            ]);

            if (in_array($pregunta->tipo_pregunta, $this->tiposConOpcionesEvaluacion(), true)) {
                foreach ($request->input('opciones_existentes', []) as $datosOpcion) {
                    $idOpcion = $datosOpcion['id_evaluacion_opcion'] ?? null;

                    if (!$idOpcion || str_starts_with((string) $idOpcion, 'nuevo_')) {
                        $textoOpcionNueva = trim((string) ($datosOpcion['opcion'] ?? ''));

                        if ($textoOpcionNueva !== '') {
                            EvaluacionOpcion::create([
                                'id_evaluacion_pregunta' => $pregunta->id_evaluacion_pregunta,
                                'opcion' => $textoOpcionNueva,
                                'es_correcta' => (int) ($datosOpcion['es_correcta'] ?? 0),
                                'orden' => (int) ($datosOpcion['orden'] ?? 1),
                            ]);
                        }

                        continue;
                    }

                    $opcion = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
                        ->where('id_evaluacion_opcion', $idOpcion)
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
                        'es_correcta' => (int) ($datosOpcion['es_correcta'] ?? 0),
                        'orden' => (int) ($datosOpcion['orden'] ?? $opcion->orden),
                    ]);
                }

                foreach ($request->input('opciones_nuevas', []) as $datosOpcionNueva) {
                    $textoOpcion = trim((string) ($datosOpcionNueva['opcion'] ?? ''));

                    if ($textoOpcion === '') {
                        continue;
                    }

                    EvaluacionOpcion::create([
                        'id_evaluacion_pregunta' => $pregunta->id_evaluacion_pregunta,
                        'opcion' => $textoOpcion,
                        'es_correcta' => (int) ($datosOpcionNueva['es_correcta'] ?? 0),
                        'orden' => (int) ($datosOpcionNueva['orden'] ?? 1),
                    ]);
                }
            }
        });

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $this->parametrosRetornoEvaluacion($request, $evaluacion))
            ->with('success', 'La pregunta fue actualizada correctamente.');
    }

    private function configuracionImagenPreguntaEvaluacion(Request $request, ?string $configuracionActual = null): ?string
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

            $rutaImagenPregunta = $request->file('imagen_pregunta')->store('evaluaciones/preguntas', 'public');
        }

        $configuracion['imagen_pregunta'] = $rutaImagenPregunta;

        $configuracion = array_filter($configuracion, function ($valor) {
            return !is_null($valor) && $valor !== '';
        });

        return empty($configuracion)
            ? null
            : json_encode($configuracion, JSON_UNESCAPED_UNICODE);
    }

    private function tiposConOpcionesEvaluacion(): array
    {
        return [
            'opcion_unica',
            'verdadero_falso',
            'checklist_guiado',
            'seleccionar_posicion_imagen',
        ];
    }

    private function parametrosRetornoEvaluacion(Request $request, Evaluacion $evaluacion): array
    {
        $parametros = [
            'id_capacitacion_modulo' => $evaluacion->id_capacitacion_modulo,
            'open' => 'evaluacion-' . $evaluacion->id_evaluacion,
        ];

        $idSeccionRetorno = $request->input('id_capacitacion_modulo_seccion', $evaluacion->id_capacitacion_modulo_seccion);

        if ((int) $request->input('volver_modulo', $idSeccionRetorno ? 1 : 0) === 1 && $idSeccionRetorno) {
            $parametros['volver_modulo'] = 1;
            $parametros['id_capacitacion_modulo_seccion'] = $idSeccionRetorno;
        }

        return $parametros;
    }

    private function normalizarOpcionesEvaluacion(array $opciones)
    {
        return collect($opciones)
            ->map(function ($opcion, $indice) {
                return [
                    'opcion' => trim((string) ($opcion['opcion'] ?? '')),
                    'es_correcta' => isset($opcion['es_correcta']) && $opcion['es_correcta'] !== ''
                        ? (int) $opcion['es_correcta']
                        : 0,
                    'orden' => isset($opcion['orden']) && $opcion['orden'] !== ''
                        ? (int) $opcion['orden']
                        : ((int) $indice + 1),
                ];
            })
            ->filter(function ($opcion) {
                return $opcion['opcion'] !== '';
            })
            ->values();
    }

    private function validarOpcionesEvaluacion(string $tipoPregunta, $opciones): ?string
    {
        if (!in_array($tipoPregunta, $this->tiposConOpcionesEvaluacion(), true)) {
            return null;
        }

        if ($opciones->isEmpty()) {
            return 'Este tipo de pregunta necesita opciones. Agrega al menos una opción.';
        }

        if (in_array($tipoPregunta, ['opcion_unica', 'checklist_guiado'], true) && $opciones->count() < 2) {
            return 'Las preguntas de selección necesitan al menos 2 opciones.';
        }

        if ($tipoPregunta === 'checklist_guiado' && $opciones->count() < 1) {
            return 'El checklist guiado necesita al menos un ítem.';
        }

        if ($tipoPregunta === 'verdadero_falso' && $opciones->count() !== 2) {
            return 'Las preguntas verdadero/falso deben tener exactamente 2 opciones.';
        }

        if ($tipoPregunta === 'verdadero_falso') {
            $textosVerdaderoFalso = $opciones
                ->pluck('opcion')
                ->map(function ($texto) {
                    return mb_strtolower(trim($texto));
                })
                ->sort()
                ->values()
                ->all();

            if ($textosVerdaderoFalso !== ['falso', 'verdadero']) {
                return 'Las preguntas verdadero/falso solo pueden tener las opciones Verdadero y Falso.';
            }
        }

        if (in_array($tipoPregunta, ['opcion_unica', 'verdadero_falso'], true)) {
            $correctas = $opciones->where('es_correcta', 1)->count();

            if ($correctas !== 1) {
                return 'Debes marcar exactamente una opción correcta.';
            }
        }

        if ($tipoPregunta === 'checklist_guiado') {
            $correctas = $opciones->where('es_correcta', 1)->count();

            if ($correctas < 1) {
                return 'Debes marcar al menos una opción correcta.';
            }
        }

        if ($tipoPregunta === 'seleccionar_posicion_imagen') {
            if ($opciones->count() < 2) {
                return 'Debes agregar al menos 2 campos para ordenar.';
            }

            $ordenes = $opciones->pluck('orden')->map(fn ($orden) => (int) $orden);

            if ($ordenes->unique()->count() !== $ordenes->count()) {
                return 'No debes repetir el mismo número de orden en dos campos diferentes.';
            }
        }

        return null;
    }

    public function toggleEstado($id)
    {
        $pregunta = EvaluacionPregunta::with('evaluacion.capacitacionModulo.capacitacion')->findOrFail($id);
        $evaluacion = $pregunta->evaluacion;

        if ((int) $pregunta->activa === 0) {
            $totalOpciones = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)->count();

            $correctas = EvaluacionOpcion::where('id_evaluacion_pregunta', $pregunta->id_evaluacion_pregunta)
                ->where('es_correcta', 1)
                ->count();

            if (in_array($pregunta->tipo_pregunta, ['opcion_unica', 'opcion_multiple', 'verdadero_falso', 'checklist_guiado'], true)) {
                if ($totalOpciones === 0) {
                    return back()->withErrors([
                        'pregunta' => 'No puedes activar esta pregunta porque no tiene opciones registradas.'
                    ]);
                }

                if ($correctas === 0) {
                    return back()->withErrors([
                        'pregunta' => 'No puedes activar esta pregunta porque no tiene ninguna opción correcta.'
                    ]);
                }

                if ($pregunta->tipo_pregunta === 'opcion_unica' && $correctas > 1) {
                    return back()->withErrors([
                        'pregunta' => 'No puedes activar esta pregunta porque en opción única solo debe existir una opción correcta.'
                    ]);
                }

                if ($pregunta->tipo_pregunta === 'verdadero_falso') {
                    if ($totalOpciones !== 2) {
                        return back()->withErrors([
                            'pregunta' => 'No puedes activar esta pregunta porque verdadero/falso requiere exactamente 2 opciones.'
                        ]);
                    }

                    if ($correctas !== 1) {
                        return back()->withErrors([
                            'pregunta' => 'No puedes activar esta pregunta porque verdadero/falso requiere exactamente 1 opción correcta.'
                        ]);
                    }
                }
            }
        }

        $pregunta->activa = (int) $pregunta->activa === 1 ? 0 : 1;
        $pregunta->save();

        $mensaje = (int) $pregunta->activa === 1
            ? 'La pregunta fue activada correctamente.'
            : 'La pregunta fue inactivada correctamente.';

        return redirect()
            ->route('evaluaciones.preguntas.index', $evaluacion->id_evaluacion)
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, int $id)
    {
        $pregunta = EvaluacionPregunta::with('evaluacion.capacitacionModulo')->findOrFail($id);
        $evaluacion = $pregunta->evaluacion;
        $modulo = $evaluacion->capacitacionModulo;

        app(EliminacionCapacitacionService::class)
            ->eliminarPreguntaEvaluacion((int) $id);

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $this->parametrosRetornoEvaluacion($request, $evaluacion))
            ->with('success', 'La pregunta de evaluación, sus opciones y respuestas de intentos fueron eliminadas correctamente.');
    }
}