<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstructorController extends Controller
{
    public function index()
    {
        $instructores = Instructor::with('empleado')
            ->orderBy('id_instructor', 'desc')
            ->get();

        return view('instructores.index', compact('instructores'));
    }

    public function create()
    {
        $empleados = Empleado::where('estado', 1)
            ->orderBy('nombre_completo')
            ->get();

        return view('instructores.create', compact('empleados'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'instructor' => trim((string) $request->instructor),
            'correo' => $request->correo ? trim((string) $request->correo) : null,
            'telefono' => $request->telefono ? trim((string) $request->telefono) : null,
            'id_empleado' => $request->id_empleado ?: null,
        ]);

        $request->validate([
            'instructor' => ['required', 'string', 'max:255', 'min:3', 'unique:instructor,instructor'],
            'correo' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/',
                'unique:instructor,correo',
            ],
            'telefono' => ['nullable', 'string', 'max:30', 'min:8'],
            'interno' => ['required', 'in:0,1'],
            'estado' => ['required', 'in:0,1'],
            'id_empleado' => [
                Rule::requiredIf((string) $request->interno === '1'),
                'nullable',
                'exists:empleado,id_empleado',
                'unique:instructor,id_empleado',
            ],
        ], [
            'correo.regex' => 'El correo debe tener un formato válido, por ejemplo: correo.prueba123@dominio.com',
            'id_empleado.required' => 'Los instructores internos deben estar vinculados a un empleado.',
            'id_empleado.unique' => 'Ese empleado ya está vinculado a otro instructor.',
        ]);

        Instructor::create([
            'instructor' => $request->instructor,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'interno' => $request->interno,
            'estado' => $request->estado,
            'id_empleado' => (string) $request->interno === '1' ? $request->id_empleado : null,
        ]);

        return redirect()->route('instructores.index')->with('success', 'Instructor creado correctamente.');
    }

    public function edit($id)
    {
        $instructor = Instructor::findOrFail($id);

        $empleados = Empleado::where('estado', 1)
            ->orderBy('nombre_completo')
            ->get();

        return view('instructores.edit', compact('instructor', 'empleados'));
    }

    public function update(Request $request, $id)
    {
        $instructor = Instructor::findOrFail($id);

        $request->merge([
            'instructor' => trim((string) $request->instructor),
            'correo' => $request->correo ? trim((string) $request->correo) : null,
            'telefono' => $request->telefono ? trim((string) $request->telefono) : null,
            'id_empleado' => $request->id_empleado ?: null,
        ]);

        $request->validate([
            'instructor' => ['required', 'string', 'max:255', 'min:3', 'unique:instructor,instructor,' . $instructor->id_instructor . ',id_instructor'],
            'correo' => ['nullable', 'email', 'max:255', 'unique:instructor,correo,' . $instructor->id_instructor . ',id_instructor'],
            'telefono' => ['nullable', 'string', 'max:30', 'min:8'],
            'interno' => ['required', 'in:0,1'],
            'estado' => ['required', 'in:0,1'],
            'id_empleado' => [
                Rule::requiredIf((string) $request->interno === '1'),
                'nullable',
                'exists:empleado,id_empleado',
                'unique:instructor,id_empleado,' . $instructor->id_instructor . ',id_instructor',
            ],
        ], [
            'id_empleado.required' => 'Los instructores internos deben estar vinculados a un empleado.',
            'id_empleado.unique' => 'Ese empleado ya está vinculado a otro instructor.',
        ]);

        $instructor->update([
            'instructor' => $request->instructor,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'interno' => $request->interno,
            'estado' => $request->estado,
            'id_empleado' => (string) $request->interno === '1' ? $request->id_empleado : null,
        ]);

        return redirect()->route('instructores.index')->with('success', 'Instructor actualizado correctamente.');
    }

    public function toggleEstado($id)
    {
        $instructor = Instructor::findOrFail($id);

        $instructor->estado = (int) $instructor->estado === 1 ? 0 : 1;
        $instructor->save();

        $mensaje = (int) $instructor->estado === 1
            ? 'Instructor reactivado correctamente.'
            : 'Instructor inactivado correctamente.';

        return redirect()->route('instructores.index')->with('success', $mensaje);
    }
}