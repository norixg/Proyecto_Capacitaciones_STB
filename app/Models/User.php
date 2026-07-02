<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Rol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\EmpleadoUser;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Instructor;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false; //mientras...

    protected ?Instructor $instructorInternoActualCache = null;

    protected bool $instructorInternoActualConsultado = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    /*protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    } */

    ///////////////////////////////////////////////////////////////////

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_temporal_notificacion',
        'email_verified_at',
        'remember_token',
        'estado',
    ];

    protected $hidden = [
        'password',
        'password_temporal_notificacion',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => 'integer',
        ];
    }

    public function capacitacionesCreadas()
    {
        return $this->hasMany(Capacitacion::class, 'created_by', 'id');
    }

    public function capacitacionesAsignadas()
    {
        return $this->hasMany(EmpleadoCapacitacion::class, 'id_usuario_asigno', 'id');
    }

    public function historialCapacitaciones()
    {
        return $this->hasMany(HistorialCapacitacionEmpleado::class, 'id_user', 'id');
    }

    public function userRoles()
    {
        return $this->hasMany(UserRol::class, 'id_user', 'id');
    }

    public function empleadoUser()
    {
        return $this->hasOne(EmpleadoUser::class, 'id_user', 'id');
    }

    public function rolesSistema()
    {
        return $this->belongsToMany(
            Rol::class,
            'user_rol',
            'id_user',
            'id_rol',
            'id',
            'id_rol'
        );
    }

    public function tieneRolSistema($roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        $roles = collect($roles)
            ->map(fn ($rol) => strtolower(trim((string) $rol)))
            ->filter()
            ->values();

        if ($roles->isEmpty()) {
            return false;
        }

        $this->loadMissing('rolesSistema');

        if ($this->rolesSistema->isEmpty()) {
            return false;
        }

        return $this->rolesSistema->contains(function ($rol) use ($roles) {
            $nombreRol = $rol->rol
                ?? $rol->nombre
                ?? $rol->nombre_rol
                ?? $rol->name
                ?? null;

            return $nombreRol && $roles->contains(strtolower(trim((string) $nombreRol)));
        });
    }

    public function tieneRol(string $rol): bool
    {
        return $this->tieneRolSistema($rol);
    }

    public function esAdminSistema(): bool
    {
        return $this->tieneRolSistema('admin');
    }

    public function esInstructorSistema(): bool
    {
        return $this->tieneRolSistema('instructor');
    }

    public function instructorInternoActual(): ?Instructor
    {
        if ($this->instructorInternoActualConsultado) {
            return $this->instructorInternoActualCache;
        }

        $this->instructorInternoActualConsultado = true;

        $this->loadMissing('empleadoUser');

        $idEmpleado = $this->empleadoUser?->id_empleado;

        if (!$idEmpleado) {
            $this->instructorInternoActualCache = null;

            return null;
        }

        $this->instructorInternoActualCache = Instructor::query()
            ->where('id_empleado', $idEmpleado)
            ->where('tipo', 'interno')
            ->first();

        return $this->instructorInternoActualCache;
    }
}
