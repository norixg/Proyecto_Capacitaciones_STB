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

    public function empleado()
    {
        return $this->belongsTo(EmpleadoRrhh::class, 'id_empleado', 'id_empleado');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
