<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\CapacitacionInstructorRrhh;
use App\Models\InstructorRrhh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\EliminacionCapacitacionService;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CapacitacionController extends Controller
{
    private function capacitacionesRrhh(?int $idInstructor = null): Collection
    {
        $query = CapacitacionInstructorRrhh::query()
            ->from('capacitacion_instructor as ci')
            ->leftJoin('capacitacion as c', 'c.id_capacitacion', '=', 'ci.id_capacitacion')
            ->leftJoin('instructor as i', 'i.id_instructor', '=', 'ci.id_instructor')
            ->whereNotNull('ci.id_instructor');

        if ($idInstructor) {
            $query->where('ci.id_instructor', $idInstructor);
        }

        return $query
            ->orderBy('c.capacitacion')
            ->orderBy('ci.id_capacitacion_instructor')
            ->get([
                'ci.id_capacitacion_instructor',
                'ci.id_instructor',
                'c.capacitacion',
                'i.instructor',
            ])
            ->map(function ($registro) {
                $etiqueta = '#'.$registro->id_capacitacion_instructor
                    .' — '.$registro->capacitacion
                    .($registro->instructor ? ' — '.$registro->instructor : '');

                return [
                    'id' => $registro->id_capacitacion_instructor,
                    'etiqueta' => $etiqueta,
                    'busqueda' => Str::of($etiqueta)->ascii()->lower()->toString(),
                    'id_instructor' => (int) $registro->id_instructor,
                    'instructor' => $registro->instructor,
                ];
            });
    }

    private function usuarioEsAdmin(): bool
    {
        $usuario = Auth::user();

        if (!$usuario instanceof User) {
            return false;
        }

        return $usuario->esAdminSistema();
    }

    private function instructorActual(): ?InstructorRrhh
    {
        if ($this->usuarioEsAdmin()) {
            return null;
        }

        $usuario = Auth::user();

        if (!$usuario instanceof User) {
            return null;
        }

        return $usuario->instructorRrhhActual();
    }

    private function consultaCapacitacionesAutorizadas()
    {
        $query = Capacitacion::with('instructor');

        if ($this->usuarioEsAdmin()) {
            return $query;
        }

        $instructor = $this->instructorActual();

        if (!$instructor) {
            abort(403, 'Tu usuario debe estar vinculado a un instructor de Recursos Humanos.');
        }

        return $query->where('id_instructor', $instructor->id_instructor);
    }

    private function validarAccesoCapacitacion(Capacitacion $capacitacion): void
    {
        if ($this->usuarioEsAdmin()) {
            return;
        }

        $instructor = $this->instructorActual();

        $perteneceAlInstructor = $instructor
            && (int) $capacitacion->id_instructor === (int) $instructor->id_instructor;

        if (!$perteneceAlInstructor) {
            abort(403, 'Solo puedes gestionar tus capacitaciones como instructor.');
        }
    }

    public function index()
    {
        $capacitaciones = $this->consultaCapacitacionesAutorizadas()
            ->where('estado', '<>', 2)
            ->orderBy('id_capacitacion', 'desc')
            ->get();

        $modoArchivadas = false;

        return view('capacitaciones.index', compact('capacitaciones', 'modoArchivadas'));
    }

    public function archivadas()
    {
        if (!$this->usuarioEsAdmin()) {
            abort(403, 'Solo el administrador puede ver capacitaciones archivadas.');
        }

        $capacitaciones = $this->consultaCapacitacionesAutorizadas()
            ->where('estado', 2)
            ->orderBy('id_capacitacion', 'desc')
            ->get();

        $modoArchivadas = true;

        return view('capacitaciones.index', compact('capacitaciones', 'modoArchivadas'));
    }

    public function create()
    {
        $esAdminCapacitacion = $this->usuarioEsAdmin();
        $instructorActual = $this->instructorActual();

        if (!$esAdminCapacitacion && !$instructorActual) {
            abort(403, 'Tu usuario debe estar vinculado a un instructor de Recursos Humanos.');
        }

        $capacitacionesRrhh = $this->capacitacionesRrhh($instructorActual?->id_instructor);

        return view('capacitaciones.create', compact(
            'capacitacionesRrhh'
        ));
    }

    public function store(Request $request)
    {
        $esAdminCapacitacion = $this->usuarioEsAdmin();
        $instructorActual = $this->instructorActual();

        if (!$esAdminCapacitacion && !$instructorActual) {
            abort(403, 'Tu usuario debe estar vinculado a un instructor de Recursos Humanos.');
        }

        $request->merge([
            'capacitacion' => trim((string) $request->capacitacion),
            'codigo' => $request->codigo !== null ? trim((string) $request->codigo) : null,
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'objetivo_general' => $request->objetivo_general !== null ? trim((string) $request->objetivo_general) : null,
        ]);

        if (!$esAdminCapacitacion) {
            $request->request->remove('id_instructor');
        }

        $reglas = [
            'capacitacion' => ['required', 'string', 'min:3', 'max:250', 'not_regex:/^\d+$/', 'unique:capacitacion,capacitacion'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:capacitacion,codigo'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'objetivo_general' => ['nullable', 'string', 'max:1000'],
            'portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'horas_estimadas' => ['nullable', 'integer', 'min:1'],
            'porcentaje_aprobacion' => ['required', 'numeric', 'min:1', 'max:100'],
            'dias_vigencia' => ['nullable', 'integer', 'min:1'],
            'obligatoria' => ['required', 'in:0,1'],
            'estado' => ['required', 'in:0,1'],
            'id_capacitacion_instructor' => [
                'required',
                'integer',
                Rule::exists('rrhh.capacitacion_instructor', 'id_capacitacion_instructor')
                    ->where(function ($query) use ($esAdminCapacitacion, $instructorActual) {
                        $query->whereNotNull('id_instructor');

                        if (!$esAdminCapacitacion) {
                            $query->where('id_instructor', $instructorActual->id_instructor);
                        }
                    }),
            ],
        ];

        $request->validate($reglas, [
            'capacitacion.not_regex' => 'El nombre de la capacitación no puede ser solo números.',
        ]);

        $rutaPortada = null;

        if ($request->hasFile('portada')) {
            $rutaPortada = $request->file('portada')->store('capacitaciones/portadas', 'public');
        }

        $capacitacionInstructorRrhh = CapacitacionInstructorRrhh::query()
            ->findOrFail($request->id_capacitacion_instructor);

        if (!$esAdminCapacitacion && (int) $capacitacionInstructorRrhh->id_instructor !== (int) $instructorActual->id_instructor) {
            abort(403, 'La relación de capacitación seleccionada no pertenece a tu instructor de Recursos Humanos.');
        }
        $idInstructor = $capacitacionInstructorRrhh->id_instructor;

        Capacitacion::create([
            'capacitacion' => $request->capacitacion,
            'codigo' => $request->codigo ?: null,
            'descripcion' => $request->descripcion ?: null,
            'objetivo_general' => $request->objetivo_general ?: null,
            'ruta_portada' => $rutaPortada,
            'horas_estimadas' => $request->horas_estimadas ?: null,
            'porcentaje_aprobacion' => $request->porcentaje_aprobacion,
            'dias_vigencia' => $request->dias_vigencia ?: null,
            'obligatoria' => $request->obligatoria,
            'permite_autogestion' => 0,
            'estado' => $request->estado,
            'created_by' => Auth::id(),
            'id_instructor' => $idInstructor,
            'id_capacitacion_instructor' => $request->id_capacitacion_instructor,
        ]);

        return redirect()->route('capacitaciones.index')
            ->with('success', 'La capacitación fue creada correctamente.');
    }

    public function edit($id)
    {
        $capacitacion = Capacitacion::findOrFail($id);

        $this->validarAccesoCapacitacion($capacitacion);

        $esAdminCapacitacion = $this->usuarioEsAdmin();
        $instructorActual = $this->instructorActual();

        $capacitacionesRrhh = $this->capacitacionesRrhh($instructorActual?->id_instructor);

        return view('capacitaciones.edit', compact(
            'capacitacion',
            'capacitacionesRrhh'
        ));
    }

    public function update(Request $request, $id)
    {
        $capacitacion = Capacitacion::findOrFail($id);

        $this->validarAccesoCapacitacion($capacitacion);

        $esAdminCapacitacion = $this->usuarioEsAdmin();
        $instructorActual = $this->instructorActual();

        $request->merge([
            'capacitacion' => trim((string) $request->capacitacion),
            'codigo' => $request->codigo !== null ? trim((string) $request->codigo) : null,
            'descripcion' => $request->descripcion !== null ? trim((string) $request->descripcion) : null,
            'objetivo_general' => $request->objetivo_general !== null ? trim((string) $request->objetivo_general) : null,
        ]);

        if (!$esAdminCapacitacion) {
            $request->request->remove('id_instructor');
        }

        $reglas = [
            'capacitacion' => ['required', 'string', 'min:3', 'max:250', 'unique:capacitacion,capacitacion,' . $capacitacion->id_capacitacion . ',id_capacitacion'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:capacitacion,codigo,' . $capacitacion->id_capacitacion . ',id_capacitacion'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'objetivo_general' => ['nullable', 'string', 'max:1000'],
            'portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'horas_estimadas' => ['nullable', 'integer', 'min:1'],
            'porcentaje_aprobacion' => ['required', 'numeric', 'min:1', 'max:100'],
            'dias_vigencia' => ['nullable', 'integer', 'min:1'],
            'obligatoria' => ['required', 'in:0,1'],
            'estado' => ['required', 'in:0,1'],
            'id_capacitacion_instructor' => [
                'required',
                'integer',
                Rule::exists('rrhh.capacitacion_instructor', 'id_capacitacion_instructor')
                    ->where(function ($query) use ($esAdminCapacitacion, $instructorActual) {
                        $query->whereNotNull('id_instructor');

                        if (!$esAdminCapacitacion) {
                            $query->where('id_instructor', $instructorActual->id_instructor);
                        }
                    }),
            ],
        ];

        $request->validate($reglas);

        $capacitacionInstructorRrhh = CapacitacionInstructorRrhh::query()
            ->findOrFail($request->id_capacitacion_instructor);

        if (!$esAdminCapacitacion && (int) $capacitacionInstructorRrhh->id_instructor !== (int) $instructorActual->id_instructor) {
            abort(403, 'La relación de capacitación seleccionada no pertenece a tu instructor de Recursos Humanos.');
        }

        $datosCapacitacion = [
            'capacitacion' => $request->capacitacion,
            'codigo' => $request->codigo ?: null,
            'descripcion' => $request->descripcion ?: null,
            'objetivo_general' => $request->objetivo_general ?: null,
            'horas_estimadas' => $request->horas_estimadas ?: null,
            'porcentaje_aprobacion' => $request->porcentaje_aprobacion,
            'dias_vigencia' => $request->dias_vigencia ?: null,
            'obligatoria' => $request->obligatoria,
            'permite_autogestion' => 0,
            'estado' => $request->estado,
            'id_instructor' => $capacitacionInstructorRrhh->id_instructor,
            'id_capacitacion_instructor' => $request->id_capacitacion_instructor,
        ];

        if ($request->hasFile('portada')) {
            if ($capacitacion->ruta_portada && Storage::disk('public')->exists($capacitacion->ruta_portada)) {
                Storage::disk('public')->delete($capacitacion->ruta_portada);
            }

            $datosCapacitacion['ruta_portada'] = $request->file('portada')->store('capacitaciones/portadas', 'public');
        }

        $capacitacion->update($datosCapacitacion);

        return redirect()->route('capacitaciones.index')
            ->with('success', 'La capacitación fue actualizada correctamente.');
    }

    public function archivar($id)
    {
        if (!$this->usuarioEsAdmin()) {
            abort(403, 'Solo el administrador puede archivar capacitaciones.');
        }

        $capacitacion = Capacitacion::findOrFail($id);
        $capacitacion->estado = 2;
        $capacitacion->save();

        return redirect()
            ->route('capacitaciones.index')
            ->with('success', 'La capacitación fue archivada correctamente. Ya no aparecerá en el catálogo activo ni a los usuarios asignados.');
    }

    public function restaurarArchivada($id)
    {
        if (!$this->usuarioEsAdmin()) {
            abort(403, 'Solo el administrador puede restaurar capacitaciones archivadas.');
        }

        $capacitacion = Capacitacion::findOrFail($id);
        $capacitacion->estado = 0;
        $capacitacion->save();

        return redirect()
            ->route('capacitaciones.archivadas')
            ->with('success', 'La capacitación fue restaurada como inactiva. Para que los usuarios la vean nuevamente, activala desde el catálogo.');
    }

    public function toggleEstado($id)
    {
        if (!$this->usuarioEsAdmin()) {
            abort(403, 'Solo el administrador puede activar o inactivar capacitaciones.');
        }

        $capacitacion = Capacitacion::findOrFail($id);

        $capacitacion->estado = (int) $capacitacion->estado === 1 ? 0 : 1;
        $capacitacion->save();

        $mensaje = (int) $capacitacion->estado === 1
            ? 'La capacitación fue activada correctamente.'
            : 'La capacitación fue inactivada correctamente.';

        return redirect()->route('capacitaciones.index')->with('success', $mensaje);
    }

    public function destroy($id)
    {
        return $this->archivar($id);
    }
}
