<?php

namespace App\Http\Middleware;

use App\Models\Capacitacion;
use App\Models\CapacitacionModulo;
use App\Models\CapacitacionRecurso;
use App\Models\Ejercicio;
use App\Models\EjercicioOpcion;
use App\Models\EjercicioPregunta;
use App\Models\EmpleadoCapacitacion;
use App\Models\Evaluacion;
use App\Models\EvaluacionOpcion;
use App\Models\EvaluacionPregunta;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AutorizaCapacitacionInstructor
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if (!$usuario instanceof User) {
            return redirect()->route('login');
        }

        if ($usuario->esAdminSistema()) {
            return $next($request);
        }

        if (!$usuario->esInstructorSistema()) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $instructor = $usuario->instructorRrhhActual();

        if (!$instructor) {
            abort(403, 'Tu usuario debe estar vinculado a un instructor de Recursos Humanos.');
        }

        $idCapacitacion = $this->resolverIdCapacitacion($request);

        if (!$idCapacitacion) {
            return $next($request);
        }

        $puedeAcceder = Capacitacion::where('id_capacitacion', $idCapacitacion)
            ->where('id_instructor', $instructor->id_instructor)
            ->exists();

        if (!$puedeAcceder) {
            abort(403, 'Solo puedes acceder a las capacitaciones donde estás asignado como instructor.');
        }

        return $next($request);
    }

    private function resolverIdCapacitacion(Request $request): ?int
    {
        $ruta = $request->route();
        $nombreRuta = $ruta?->getName() ?? '';
        $parametros = $ruta?->parameters() ?? [];

        if (isset($parametros['id_capacitacion'])) {
            return (int) $parametros['id_capacitacion'];
        }

        if (str_starts_with($nombreRuta, 'capacitaciones.') && isset($parametros['id'])) {
            return (int) $parametros['id'];
        }

        if (isset($parametros['id_capacitacion_modulo'])) {
            return $this->desdeModulo($parametros['id_capacitacion_modulo']);
        }

        if (str_starts_with($nombreRuta, 'capacitacion_modulos.') && isset($parametros['id'])) {
            return $this->desdeModulo($parametros['id']);
        }

        if (str_starts_with($nombreRuta, 'capacitacion_recursos.') && isset($parametros['id'])) {
            return $this->desdeRecurso($parametros['id']);
        }

        if (isset($parametros['id_ejercicio'])) {
            return $this->desdeEjercicio($parametros['id_ejercicio']);
        }

        if (str_starts_with($nombreRuta, 'ejercicios.') && isset($parametros['id'])) {
            return $this->desdeEjercicio($parametros['id']);
        }

        if (isset($parametros['id_ejercicio_pregunta'])) {
            return $this->desdeEjercicioPregunta($parametros['id_ejercicio_pregunta']);
        }

        if (str_starts_with($nombreRuta, 'ejercicio_preguntas.') && isset($parametros['id'])) {
            return $this->desdeEjercicioPregunta($parametros['id']);
        }

        if (str_starts_with($nombreRuta, 'ejercicio_opciones.') && isset($parametros['id'])) {
            return $this->desdeEjercicioOpcion($parametros['id']);
        }

        if (isset($parametros['id_evaluacion'])) {
            return $this->desdeEvaluacion($parametros['id_evaluacion']);
        }

        if (str_starts_with($nombreRuta, 'evaluaciones.') && isset($parametros['id'])) {
            return $this->desdeEvaluacion($parametros['id']);
        }

        if (isset($parametros['id_evaluacion_pregunta'])) {
            return $this->desdeEvaluacionPregunta($parametros['id_evaluacion_pregunta']);
        }

        if (str_starts_with($nombreRuta, 'evaluacion_preguntas.') && isset($parametros['id'])) {
            return $this->desdeEvaluacionPregunta($parametros['id']);
        }

        if (str_starts_with($nombreRuta, 'evaluacion_opciones.') && isset($parametros['id'])) {
            return $this->desdeEvaluacionOpcion($parametros['id']);
        }

        if (str_starts_with($nombreRuta, 'seguimiento_capacitaciones.') && isset($parametros['id'])) {
            return $this->desdeSeguimiento($parametros['id']);
        }

        return null;
    }

    private function desdeModulo($id): ?int
    {
        return CapacitacionModulo::where('id_capacitacion_modulo', (int) $id)
            ->value('id_capacitacion');
    }

    private function desdeRecurso($id): ?int
    {
        $recurso = CapacitacionRecurso::with('modulo')->find((int) $id);

        return $recurso?->modulo?->id_capacitacion;
    }

    private function desdeEjercicio($id): ?int
    {
        $ejercicio = Ejercicio::with('modulo')->find((int) $id);

        return $ejercicio?->modulo?->id_capacitacion;
    }

    private function desdeEjercicioPregunta($id): ?int
    {
        $pregunta = EjercicioPregunta::with('ejercicio.modulo')->find((int) $id);

        return $pregunta?->ejercicio?->modulo?->id_capacitacion;
    }

    private function desdeEjercicioOpcion($id): ?int
    {
        $opcion = EjercicioOpcion::with('pregunta.ejercicio.modulo')->find((int) $id);

        return $opcion?->pregunta?->ejercicio?->modulo?->id_capacitacion;
    }

    private function desdeEvaluacion($id): ?int
    {
        $evaluacion = Evaluacion::with('modulo')->find((int) $id);

        return $evaluacion?->modulo?->id_capacitacion;
    }

    private function desdeEvaluacionPregunta($id): ?int
    {
        $pregunta = EvaluacionPregunta::with('evaluacion.modulo')->find((int) $id);

        return $pregunta?->evaluacion?->modulo?->id_capacitacion;
    }

    private function desdeEvaluacionOpcion($id): ?int
    {
        $opcion = EvaluacionOpcion::with('pregunta.evaluacion.modulo')->find((int) $id);

        return $opcion?->pregunta?->evaluacion?->modulo?->id_capacitacion;
    }

    private function desdeSeguimiento($id): ?int
    {
        return EmpleadoCapacitacion::where('id_empleado_capacitacion', (int) $id)
            ->value('id_capacitacion');
    }
}
