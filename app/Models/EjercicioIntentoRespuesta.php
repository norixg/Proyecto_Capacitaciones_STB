<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EjercicioIntento;
use App\Models\EjercicioPregunta;

class EjercicioIntentoRespuesta extends Model
{
    use HasFactory;

    protected $table = 'ejercicio_intento_respuesta';
    protected $primaryKey = 'id_ejercicio_intento_respuesta';
    public $timestamps = false;

    protected $fillable = [
        'id_ejercicio_intento',
        'id_ejercicio_pregunta',
        'respuesta_texto',
        'respuesta_json',
        'es_correcta',
        'puntaje_obtenido',
        'comentario_revision',
    ];

    protected $casts = [
        'id_ejercicio_intento_respuesta' => 'integer',
        'id_ejercicio_intento' => 'integer',
        'id_ejercicio_pregunta' => 'integer',
        'es_correcta' => 'integer',
        'puntaje_obtenido' => 'decimal:2',
    ];

    public function intento()
    {
        return $this->belongsTo(EjercicioIntento::class, 'id_ejercicio_intento', 'id_ejercicio_intento');
    }

    public function pregunta()
    {
        return $this->belongsTo(EjercicioPregunta::class, 'id_ejercicio_pregunta', 'id_ejercicio_pregunta');
    }
}