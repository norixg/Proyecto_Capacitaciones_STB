<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class EmpleadosCapacitacion extends Model
{
    use HasFactory;

    protected $table = 'empleados_capacitacion';
    protected $primaryKey = 'id_empleados_capacitacion';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado',
        'id_capacitacion',
        'obligatoria',
        'dias_para_vencer',
        'fecha_asignacion',
        'estado',
    ];

    protected $casts = [
        'id_empleados_capacitacion' => 'integer',
        'id_empleado' => 'integer',
        'id_capacitacion' => 'integer',
        'obligatoria' => 'integer',
        'dias_para_vencer' => 'integer',
        'fecha_asignacion' => 'date',
        'estado' => 'integer',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }

    public function capacitacion()
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function modulosAvance()
    {
        return $this->hasMany(
            EmpleadoModuloAvance::class,
            'id_empleado_capacitacion',
            'id_empleado_capacitacion'
        );
    }

    public function evaluacionIntentos()
    {
        return $this->hasMany(
            EvaluacionIntento::class,
            'id_empleado_capacitacion',
            'id_empleado_capacitacion'
        );
    }

    public function ejercicioIntentos()
    {
        return $this->hasMany(
            EjercicioIntento::class,
            'id_empleado_capacitacion',
            'id_empleado_capacitacion'
        );
    }
}
