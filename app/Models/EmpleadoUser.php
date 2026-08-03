<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpleadoUser extends Model
{
    use HasFactory;

    protected $table = 'empleado_user';
    protected $primaryKey = 'id_empleado_user';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'id_user',
        'fecha_asignacion',
    ];

    protected $casts = [
        'id_empleado_user' => 'integer',
        'id_empleado' => 'integer',
        'id_user' => 'integer',
        'fecha_asignacion' => 'datetime',
    ];

    // empleado_user vive en la base de datos local. Cuando esta relación se
    // recorre desde un modelo en otra conexión (p. ej. EmpleadoRrhh, en 'rrhh'),
    // Eloquent le propaga esa conexión por no tener una propia declarada aquí.
    // Fijarla explícitamente evita que la consulta se dispare contra la BD equivocada.
    public function getConnectionName()
    {
        return config('database.default');
    }

    public function empleado()
    {
        return $this->belongsTo(EmpleadoRrhh::class, 'id_empleado', 'id_empleado');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
