<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Evaluacion;
use App\Models\EvaluacionOpcion;

class EvaluacionPregunta extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_pregunta';
    protected $primaryKey = 'id_evaluacion_pregunta';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion',
        'pregunta',
        'tipo_pregunta',
        'puntaje',
        'orden',
        'activa',
        'respuesta_correcta_texto',
        'configuracion_json',
        'requiere_revision_manual',
    ];

    protected $casts = [
        'id_evaluacion_pregunta' => 'integer',
        'id_evaluacion' => 'integer',
        'puntaje' => 'decimal:2',
        'orden' => 'integer',
        'activa' => 'integer',
        'requiere_revision_manual' => 'integer',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }

    public function opciones()
    {
        return $this->hasMany(EvaluacionOpcion::class, 'id_evaluacion_pregunta', 'id_evaluacion_pregunta');
    }

    public function respuestasIntento()
    {
        return $this->hasMany(EvaluacionIntentoRespuesta::class, 'id_evaluacion_pregunta', 'id_evaluacion_pregunta');
    }
}
