<?php

namespace App\Http\Controllers;

use App\Models\CapacitacionModulo;
use App\Models\Evaluacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EliminacionCapacitacionService;

class EvaluacionController extends Controller
{
    public function index(int $id_capacitacion_modulo)
    {
        $modulo = CapacitacionModulo::with([
            'capacitacion',
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
            'evaluaciones.seccion',
            'evaluaciones.preguntas.opciones',
        ])->findOrFail($id_capacitacion_modulo);

        $secciones = $modulo->secciones;

        return view('evaluaciones.index', compact('modulo', 'secciones'));
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

        $siguienteOrden = ((int) Evaluacion::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->max('orden')) + 1;

        return view('evaluaciones.create', compact('modulo', 'siguienteOrden', 'secciones'));
    }

    public function store(Request $request, $id_capacitacion_modulo)
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
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'instrucciones' => ['nullable', 'string'],
            'porcentaje_aprobacion' => ['required', 'numeric', 'min:1', 'max:100'],
            'tiempo_limite_minutos' => ['nullable', 'integer', 'min:1'],
            'intentos_maximos' => ['nullable', 'integer', 'min:1'],
            'obligatorio' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
            'activa' => ['required', 'in:0,1'],
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

        $ordenRepetido = Evaluacion::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('orden', $request->orden)
            ->when($idSeccionSeleccionada, function ($query) use ($idSeccionSeleccionada) {
                $query->where('id_capacitacion_modulo_seccion', $idSeccionSeleccionada);
            }, function ($query) {
                $query->whereNull('id_capacitacion_modulo_seccion');
            })
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra evaluación con ese orden en este módulo.'])
                ->withInput();
        }

        $evaluacion = Evaluacion::create([
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'id_capacitacion_modulo_seccion' => $idSeccionSeleccionada,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion ?: null,
            'instrucciones' => $request->instrucciones ?: null,
            'porcentaje_aprobacion' => $request->porcentaje_aprobacion,
            'tiempo_limite_minutos' => $request->tiempo_limite_minutos ?: null,
            'intentos_maximos' => $request->intentos_maximos ?: null,
            'obligatorio' => $request->obligatorio,
            'orden' => $request->orden,
            'activa' => $request->activa,
            'mostrar_resultado_inmediato' => $request->mostrar_resultado_inmediato,
            'requiere_revision_manual' => $request->requiere_revision_manual,
        ]);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'open' => 'evaluacion-' . $evaluacion->id_evaluacion,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionSeleccionada ? 1 : 0) === 1 && $idSeccionSeleccionada) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionSeleccionada;
        }

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $parametrosRedireccion)
            ->with('success', 'La evaluación fue creada correctamente. Ahora puedes agregar preguntas y opciones.');

    }

    public function edit(int $id)
    {
        $evaluacion = Evaluacion::with('capacitacionModulo.capacitacion')->findOrFail($id);
        $modulo = $evaluacion->capacitacionModulo;

        $modulo->load([
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
        ]);

        $secciones = $modulo->secciones;

        return view('evaluaciones.edit', compact('evaluacion', 'modulo', 'secciones'));
    }

    public function update(Request $request, $id)
    {
        $evaluacion = Evaluacion::with('capacitacionModulo.capacitacion')->findOrFail($id);
        $modulo = $evaluacion->capacitacionModulo;

        $request->merge([
            'titulo' => trim((string) $request->titulo),
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'instrucciones' => $request->instrucciones !== null ? trim((string) $request->instrucciones) : null,
        ]);

        $request->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:250'],
            'id_capacitacion_modulo_seccion' => ['nullable', 'integer', 'exists:capacitacion_modulo_seccion,id_capacitacion_modulo_seccion'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'instrucciones' => ['nullable', 'string'],
            'porcentaje_aprobacion' => ['required', 'numeric', 'min:1', 'max:100'],
            'tiempo_limite_minutos' => ['nullable', 'integer', 'min:1'],
            'intentos_maximos' => ['nullable', 'integer', 'min:1'],
            'obligatorio' => ['required', 'in:0,1'],
            'orden' => ['required', 'integer', 'min:1'],
            'activa' => ['required', 'in:0,1'],
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

        $ordenRepetido = Evaluacion::where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('orden', $request->orden)
            ->where('id_evaluacion', '!=', $evaluacion->id_evaluacion)
            ->when($idSeccionSeleccionada, function ($query) use ($idSeccionSeleccionada) {
                $query->where('id_capacitacion_modulo_seccion', $idSeccionSeleccionada);
            }, function ($query) {
                $query->whereNull('id_capacitacion_modulo_seccion');
            })
            ->exists();

        if ($ordenRepetido) {
            return back()
                ->withErrors(['orden' => 'Ya existe otra evaluación con ese orden en este módulo.'])
                ->withInput();
        }

        $evaluacion->update([
            'id_capacitacion_modulo_seccion' => $idSeccionSeleccionada,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion ?: null,
            'instrucciones' => $request->instrucciones ?: null,
            'porcentaje_aprobacion' => $request->porcentaje_aprobacion,
            'tiempo_limite_minutos' => $request->tiempo_limite_minutos ?: null,
            'intentos_maximos' => $request->intentos_maximos ?: null,
            'obligatorio' => $request->obligatorio,
            'orden' => $request->orden,
            'activa' => $request->activa,
            'mostrar_resultado_inmediato' => $request->mostrar_resultado_inmediato,
            'requiere_revision_manual' => $request->requiere_revision_manual,
        ]);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'open' => 'evaluacion-' . $evaluacion->id_evaluacion,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionSeleccionada ? 1 : 0) === 1 && $idSeccionSeleccionada) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionSeleccionada;
        }

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $parametrosRedireccion)
            ->with('success', 'La evaluación fue creada correctamente. Ahora puedes agregar preguntas y opciones.');
    }

    public function toggleEstado($id)
    {
        $evaluacion = Evaluacion::with('capacitacionModulo.capacitacion')->findOrFail($id);
        $modulo = $evaluacion->capacitacionModulo;

        $evaluacion->activa = (int) $evaluacion->activa === 1 ? 0 : 1;
        $evaluacion->save();

        $mensaje = (int) $evaluacion->activa === 1
            ? 'La evaluación fue activada correctamente.'
            : 'La evaluación fue inactivada correctamente.';

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $modulo->id_capacitacion_modulo)
            ->with('success', $mensaje);
    }

    public function destroy(Request $request, int $id)
    {
        $evaluacion = Evaluacion::with('capacitacionModulo')->findOrFail($id);
        $modulo = $evaluacion->capacitacionModulo;
        $idSeccionRetorno = $request->input('id_capacitacion_modulo_seccion') ?: $evaluacion->id_capacitacion_modulo_seccion;

        app(EliminacionCapacitacionService::class)
            ->eliminarEvaluacion((int) $id);

        $parametrosRedireccion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ];

        if ((int) $request->input('volver_modulo', $idSeccionRetorno ? 1 : 0) === 1 && $idSeccionRetorno) {
            $parametrosRedireccion['volver_modulo'] = 1;
            $parametrosRedireccion['id_capacitacion_modulo_seccion'] = $idSeccionRetorno;
        }

        return redirect()
            ->route('capacitacion_modulos.evaluaciones.index', $parametrosRedireccion)
            ->with('success', 'La evaluación, sus preguntas, opciones, intentos, respuestas y avances fueron eliminados correctamente.');
    }
}