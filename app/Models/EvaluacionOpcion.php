<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EvaluacionPregunta;

class EvaluacionOpcion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_opcion';
    protected $primaryKey = 'id_evaluacion_opcion';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion_pregunta',
        'opcion',
        'es_correcta',
        'orden',
    ];

    protected $casts = [
        'id_evaluacion_opcion' => 'integer',
        'id_evaluacion_pregunta' => 'integer',
        'es_correcta' => 'integer',
        'orden' => 'integer',
    ];

    public function pregunta()
    {
        return $this->belongsTo(EvaluacionPregunta::class, 'id_evaluacion_pregunta', 'id_evaluacion_pregunta');
    }

    public function respuestasIntento()
    {
        return $this->hasMany(EvaluacionIntentoRespuesta::class, 'id_evaluacion_opcion', 'id_evaluacion_opcion');
    }

    public function evaluacionPregunta()
    {
        return $this->belongsTo(EvaluacionPregunta::class, 'id_evaluacion_pregunta', 'id_evaluacion_pregunta');
    }
}
