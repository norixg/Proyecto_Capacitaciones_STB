<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluacionIntentoRespuesta extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_intento_respuesta';
    protected $primaryKey = 'id_evaluacion_intento_respuesta';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion_intento',
        'id_evaluacion_pregunta',
        'id_evaluacion_opcion',
        'respuesta_texto',
        'es_correcta',
        'puntaje_obtenido',
        'comentario_revision',
    ];

    protected $casts = [
        'id_evaluacion_intento_respuesta' => 'integer',
        'id_evaluacion_intento' => 'integer',
        'id_evaluacion_pregunta' => 'integer',
        'id_evaluacion_opcion' => 'integer',
        'es_correcta' => 'integer',
        'puntaje_obtenido' => 'decimal:2',
    ];

    public function intento()
    {
        return $this->belongsTo(EvaluacionIntento::class, 'id_evaluacion_intento', 'id_evaluacion_intento');
    }

    public function pregunta()
    {
        return $this->belongsTo(EvaluacionPregunta::class, 'id_evaluacion_pregunta', 'id_evaluacion_pregunta');
    }

    public function opcion()
    {
        return $this->belongsTo(EvaluacionOpcion::class, 'id_evaluacion_opcion', 'id_evaluacion_opcion');
    }
}
