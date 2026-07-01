<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use App\Models\Empleado;
use App\Models\UserRol;
use App\Models\EmpleadoUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Capacitacion;
use App\Models\EmpleadoCapacitacion;
use App\Models\HistorialCapacitacionEmpleado;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['rolesSistema', 'empleadoUser.empleado'])
            ->orderBy('id', 'desc')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Rol::where('estado', 1)->orderBy('rol')->get();
        $empleados = Empleado::orderBy('nombre_completo')->get();

        return view('usuarios.create', compact('roles', 'empleados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'estado' => ['required', 'in:0,1'],
            'id_rol' => ['required', 'exists:rol,id_rol'],
            'id_empleado' => ['nullable', 'exists:empleado,id_empleado'],
        ], [
            'email.regex' => 'El correo debe tener un formato válido, por ejemplo: correo.prueba123@dominio.com',
        ]);

        if ($request->filled('id_empleado')) {
            $empleadoYaVinculado = EmpleadoUser::where('id_empleado', $request->id_empleado)->exists();

            if ($empleadoYaVinculado) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'id_empleado' => 'Ese empleado ya está vinculado a otro usuario.',
                    ]);
            }
        }

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'password_temporal_notificacion' => Crypt::encryptString($request->password),
                'estado' => $request->estado,
            ]);

            UserRol::create([
                'id_user' => $user->id,
                'id_rol' => $request->id_rol,
            ]);

            $this->sincronizarRolesSpatie($user, [$request->id_rol]);

            if ($request->filled('id_empleado')) {
                EmpleadoUser::create([
                    'id_user' => $user->id,
                    'id_empleado' => $request->id_empleado,
                ]);
            }
        });

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = User::with(['rolesSistema', 'empleadoUser'])->findOrFail($id);
        $roles = Rol::where('estado', 1)->orderBy('rol')->get();
        $empleados = Empleado::orderBy('nombre_completo')->get();

        $usuarioLogueado = Auth::user();
        $esPropioUsuario = (int) $usuarioLogueado->id === (int) $usuario->id;

        return view('usuarios.edit', compact('usuario', 'roles', 'empleados', 'esPropioUsuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::with(['userRoles', 'empleadoUser', 'rolesSistema'])->findOrFail($id);
        $usuarioLogueado = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/',
                'unique:users,email,' . $usuario->id,
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'estado' => ['required', 'in:0,1'],
            'id_rol' => ['required', 'exists:rol,id_rol'],
            'id_empleado' => ['nullable', 'exists:empleado,id_empleado'],
        ], [
            'email.regex' => 'El correo debe tener un formato válido, por ejemplo: correo.prueba123@dominio.com',
        ]);

        if ($request->filled('id_empleado')) {
            $empleadoYaVinculado = EmpleadoUser::where('id_empleado', $request->id_empleado)
                ->where('id_user', '!=', $usuario->id)
                ->exists();

            if ($empleadoYaVinculado) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'id_empleado' => 'Ese empleado ya está vinculado a otro usuario.',
                    ]);
            }
        }

        if ($usuarioLogueado->id === $usuario->id && (int) $request->estado === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'estado' => 'No puedes desactivarte a vos misma mientras estás usando esta cuenta.',
                ]);
        }

        $rolActualId = $usuario->rolesSistema->first()?->id_rol;

        if ($usuarioLogueado->id === $usuario->id && (int) $request->id_rol !== (int) $rolActualId) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_rol' => 'No puedes cambiar tu propio rol desde esta pantalla.',
                ]);
        }

        DB::transaction(function () use ($request, $usuario) {
            $datosActualizar = [
                'name' => $request->name,
                'email' => $request->email,
                'estado' => $request->estado,
            ];

            if ($request->filled('password')) {
                $datosActualizar['password'] = $request->password;
                $datosActualizar['password_temporal_notificacion'] = Crypt::encryptString($request->password);
            }

            $usuario->update($datosActualizar);

            UserRol::where('id_user', $usuario->id)->delete();

            UserRol::create([
                'id_user' => $usuario->id,
                'id_rol' => $request->id_rol,
            ]);

            $this->sincronizarRolesSpatie($usuario, [$request->id_rol]);

            EmpleadoUser::where('id_user', $usuario->id)->delete();

            if ($request->filled('id_empleado')) {
                EmpleadoUser::create([
                    'id_user' => $usuario->id,
                    'id_empleado' => $request->id_empleado,
                ]);
            }
        });

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado($id)
    {
        $usuario = User::with('rolesSistema')->findOrFail($id);
        $usuarioLogueado = Auth::user();

        if ((int) $usuarioLogueado->id === (int) $usuario->id) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No puedes cambiar tu propio estado desde el listado de usuarios.',
            ]);
        }

        $esAdmin = $usuario->rolesSistema->contains(function ($rol) {
            return $rol->rol === 'admin';
        });

        if ($esAdmin) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No se permite inactivar ni reactivar cuentas con rol administrador desde esta opción.',
            ]);
        }

        $usuario->estado = (int) $usuario->estado === 1 ? 0 : 1;
        $usuario->save();

        $mensaje = (int) $usuario->estado === 1
            ? 'Usuario reactivado correctamente.'
            : 'Usuario inactivado correctamente.';

        return redirect()->route('usuarios.index')->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $usuario = User::with(['rolesSistema', 'userRoles', 'empleadoUser'])->findOrFail($id);
        $usuarioLogueado = Auth::user();

        if ((int) $usuarioLogueado->id === (int) $usuario->id) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No puedes eliminar tu propia cuenta.',
            ]);
        }

        $esAdmin = $usuario->rolesSistema->contains(function ($rol) {
            return $rol->rol === 'admin';
        });

        if ($esAdmin) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'No se permite eliminar cuentas con rol administrador.',
            ]);
        }

        $tieneCapacitacionesCreadas = Capacitacion::where('created_by', $usuario->id)->exists();
        $tieneAsignacionesRealizadas = EmpleadoCapacitacion::where('id_usuario_asigno', $usuario->id)->exists();
        $tieneHistorial = HistorialCapacitacionEmpleado::where('id_user', $usuario->id)->exists();

        if ($tieneCapacitacionesCreadas || $tieneAsignacionesRealizadas || $tieneHistorial) {
            return redirect()->route('usuarios.index')->withErrors([
                'general' => 'Este usuario ya tiene movimientos reales dentro del sistema. No se puede eliminar; solo se puede inactivar.',
            ]);
        }

        DB::transaction(function () use ($usuario) {
            if ($usuario->empleadoUser) {
                $usuario->empleadoUser->delete();
            }

            if ($usuario->userRoles()->exists()) {
                $usuario->userRoles()->delete();
            }

            $usuario->syncRoles([]);

            $usuario->delete();
        });

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function sincronizarRolesSpatie(User $usuario, array $idsRoles): void
    {
        $nombresRoles = Rol::whereIn('id_rol', $idsRoles)
            ->get()
            ->map(function ($rol) {
                $nombreRol = $rol->rol
                    ?? $rol->nombre
                    ?? $rol->nombre_rol
                    ?? $rol->name
                    ?? '';

                $nombreRol = strtolower(trim($nombreRol));

                if (in_array($nombreRol, ['empleado', 'empleados', 'user', 'usuario normal'])) {
                    return 'usuario';
                }

                if (in_array($nombreRol, ['administrador'])) {
                    return 'admin';
                }

                return $nombreRol;
            })
            ->filter()
            ->values()
            ->toArray();

        foreach ($nombresRoles as $nombreRol) {
            SpatieRole::firstOrCreate([
                'name' => $nombreRol,
                'guard_name' => 'web',
            ]);
        }

        $usuario->syncRoles($nombresRoles);
    }

}