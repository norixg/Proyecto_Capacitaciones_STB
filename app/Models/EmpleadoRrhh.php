<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoRrhh extends Model
{
    protected $connection = 'rrhh';
    protected $table = 'empleado';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'id_empleado' => 'integer',
        'estado' => 'integer',
    ];

    public function empleadoUser()
    {
        return $this->hasOne(EmpleadoUser::class, 'id_empleado', 'id_empleado');
    }

    public function puestoTrabajo()
    {
        return $this->belongsTo(PuestoTrabajoMatrizRrhh::class, 'id_puesto_trabajo_matriz', 'id_puesto_trabajo_matriz');
    }
}
