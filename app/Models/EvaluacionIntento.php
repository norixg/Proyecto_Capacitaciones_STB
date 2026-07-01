<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Evaluacion;
use App\Models\EvaluacionIntentoRespuesta;


class EvaluacionIntento extends Model
{
    protected $dateFormat = 'Ymd H:i:s';
    use HasFactory;

    protected $table = 'evaluacion_intento';
    protected $primaryKey = 'id_evaluacion_intento';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion',
        'id_empleado',
        'id_empleado_capacitacion',
        'numero_intento',
        'fecha_inicio',
        'fecha_fin',
        'nota',
        'aprobado',
        'estado',
    ];

    protected $casts = [
        'id_evaluacion_intento' => 'integer',
        'id_evaluacion' => 'integer',
        'id_empleado' => 'integer',
        'id_empleado_capacitacion' => 'integer',
        'numero_intento' => 'integer',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'nota' => 'decimal:2',
        'aprobado' => 'integer',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }

    public function empleadoCapacitacion()
    {
        return $this->belongsTo(EmpleadoCapacitacion::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }

    public function respuestas()
    {
        return $this->hasMany(EvaluacionIntentoRespuesta::class, 'id_evaluacion_intento', 'id_evaluacion_intento');
    }
}
