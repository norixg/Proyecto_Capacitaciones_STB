<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CapacitacionModulo;
use App\Models\EvaluacionPregunta;
use App\Models\EvaluacionIntento;
use App\Models\CapacitacionModuloSeccion;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion';
    protected $primaryKey = 'id_evaluacion';
    public $timestamps = false;

    protected $fillable = [
        'id_capacitacion_modulo',
        'id_capacitacion_modulo_seccion',
        'titulo',
        'descripcion',
        'instrucciones',
        'porcentaje_aprobacion',
        'tiempo_limite_minutos',
        'intentos_maximos',
        'obligatorio',
        'orden',
        'activa',
        'mostrar_resultado_inmediato',
        'requiere_revision_manual',
    ];

    protected $casts = [
            'id_evaluacion' => 'integer',
            'id_capacitacion_modulo' => 'integer',
            'id_capacitacion_modulo_seccion' => 'integer',
            'porcentaje_aprobacion' => 'decimal:2',
            'tiempo_limite_minutos' => 'integer',
            'intentos_maximos' => 'integer',
            'obligatorio' => 'integer',
            'orden' => 'integer',
            'activa' => 'integer',
            'mostrar_resultado_inmediato' => 'integer',
            'requiere_revision_manual' => 'integer',
        ];

    public function modulo()
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function preguntas()
    {
        return $this->hasMany(EvaluacionPregunta::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function intentos()
    {
        return $this->hasMany(EvaluacionIntento::class, 'id_evaluacion', 'id_evaluacion');
    }
    public function capacitacionModulo()
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function seccion()
    {
        return $this->belongsTo(
            CapacitacionModuloSeccion::class,
            'id_capacitacion_modulo_seccion',
            'id_capacitacion_modulo_seccion'
        );
    }
}
