<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\EliminacionCapacitacionService;
use App\Models\User;

class CapacitacionController extends Controller
{
    private function usuarioEsAdmin(): bool
    {
        $usuario = Auth::user();

        if (!$usuario instanceof User) {
            return false;
        }

        return $usuario->esAdminSistema();
    }

    private function instructorActual(): ?Instructor
    {
        if ($this->usuarioEsAdmin()) {
            return null;
        }

        $usuario = Auth::user();

        if (!$usuario instanceof User) {
            return null;
        }

        return $usuario->instructorInternoActual();
    }

    private function consultaCapacitacionesAutorizadas()
    {
        $query = Capacitacion::with('instructor');

        if ($this->usuarioEsAdmin()) {
            return $query;
        }

        $instructor = $this->instructorActual();

        if (!$instructor) {
            abort(403, 'Tu usuario instructor debe estar vinculado a un empleado interno y a un registro de instructor activo.');
        }

        $idUsuario = Auth::id();

        return $query->where(function ($subQuery) use ($instructor, $idUsuario) {
            $subQuery->where('id_instructor', $instructor->id_instructor);

            if ($idUsuario) {
                $subQuery->orWhere('created_by', $idUsuario);
            }
        });
    }

    private function validarAccesoCapacitacion(Capacitacion $capacitacion): void
    {
        if ($this->usuarioEsAdmin()) {
            return;
        }

        $instructor = $this->instructorActual();

        $idUsuario = Auth::id();

        $perteneceAlInstructor = $instructor
            && (int) $capacitacion->id_instructor === (int) $instructor->id_instructor;

        $fueCreadaPorInstructor = $idUsuario
            && (int) $capacitacion->created_by === (int) $idUsuario;

        if (!$perteneceAlInstructor && !$fueCreadaPorInstructor) {
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
            abort(403, 'Tu usuario instructor debe estar vinculado a un empleado interno y a un registro de instructor activo.');
        }

        $instructores = $esAdminCapacitacion
            ? Instructor::where('estado', 1)->orderBy('instructor')->get()
            : collect([$instructorActual]);

        return view('capacitaciones.create', compact(
            'instructores',
            'esAdminCapacitacion',
            'instructorActual'
        ));
    }

    public function store(Request $request)
    {
        $esAdminCapacitacion = $this->usuarioEsAdmin();
        $instructorActual = $this->instructorActual();

        if (!$esAdminCapacitacion && !$instructorActual) {
            abort(403, 'Tu usuario instructor debe estar vinculado a un empleado interno y a un registro de instructor activo.');
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
        ];

        if ($esAdminCapacitacion) {
            $reglas['id_instructor'] = ['nullable', 'exists:instructor,id_instructor'];
        }

        $request->validate($reglas, [
            'capacitacion.not_regex' => 'El nombre de la capacitación no puede ser solo números.',
        ]);

        $rutaPortada = null;

        if ($request->hasFile('portada')) {
            $rutaPortada = $request->file('portada')->store('capacitaciones/portadas', 'public');
        }

        $idInstructor = $esAdminCapacitacion
            ? ($request->id_instructor ?: null)
            : $instructorActual->id_instructor;

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

        $instructores = $esAdminCapacitacion
            ? Instructor::where('estado', 1)->orderBy('instructor')->get()
            : collect([$instructorActual]);

        return view('capacitaciones.edit', compact(
            'capacitacion',
            'instructores',
            'esAdminCapacitacion',
            'instructorActual'
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
        ];

        if ($esAdminCapacitacion) {
            $reglas['id_instructor'] = ['nullable', 'exists:instructor,id_instructor'];
        }

        $request->validate($reglas);

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
            'id_instructor' => $esAdminCapacitacion
                ? ($request->id_instructor ?: null)
                : $instructorActual->id_instructor,
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