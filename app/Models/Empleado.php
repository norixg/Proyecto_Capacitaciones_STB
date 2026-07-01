<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EmpleadoCapacitacion;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleado';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'identidad',
        'codigo_empleado',
        'correo',
        'telefono',
        'id_puesto_trabajo_matriz',
        'fecha_ingreso',
        'fecha_nacimiento',
        'estado',
    ];

    protected $casts = [
        'id_empleado' => 'integer',
        'id_puesto_trabajo_matriz' => 'integer',
        'fecha_ingreso' => 'date',
        'fecha_nacimiento' => 'date',
        'estado' => 'integer',
    ];

    public function puestoTrabajo()
    {
        return $this->belongsTo(PuestoTrabajoMatriz::class, 'id_puesto_trabajo_matriz', 'id_puesto_trabajo_matriz');
    }
    public function empleadosCapacitacionDirecta()
    {
        return $this->hasMany(EmpleadosCapacitacion::class, 'id_empleado', 'id_empleado');
    }

    public function capacitaciones()
    {
        return $this->hasMany(EmpleadoCapacitacion::class, 'id_empleado', 'id_empleado');
    }
    public function intentosEvaluacion()
    {
        return $this->hasMany(EvaluacionIntento::class, 'id_empleado', 'id_empleado');
    }

    public function empleadoUser()
    {
        return $this->hasOne(EmpleadoUser::class, 'id_empleado', 'id_empleado');
    }

    public function empleadoCapacitaciones()
    {
        return $this->hasMany(EmpleadoCapacitacion::class, 'id_empleado', 'id_empleado');
    }

    public function puestoTrabajoMatriz()
    {
        return $this->belongsTo(
            PuestoTrabajoMatriz::class,
            'id_puesto_trabajo_matriz',
            'id_puesto_trabajo_matriz'
        );
    }
}
