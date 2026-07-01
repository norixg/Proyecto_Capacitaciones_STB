<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ejercicio;
use App\Models\EjercicioOpcion;
use App\Models\EjercicioIntentoRespuesta;

class EjercicioPregunta extends Model
{
    use HasFactory;

    protected $table = 'ejercicio_pregunta';
    protected $primaryKey = 'id_ejercicio_pregunta';
    public $timestamps = false;

    protected $fillable = [
        'id_ejercicio',
        'enunciado',
        'tipo_pregunta',
        'puntaje',
        'orden',
        'activa',
        'respuesta_correcta_texto',
        'configuracion_json',
        'requiere_revision_manual',
    ];

    protected $casts = [
        'id_ejercicio_pregunta' => 'integer',
        'id_ejercicio' => 'integer',
        'puntaje' => 'decimal:2',
        'orden' => 'integer',
        'activa' => 'integer',
        'requiere_revision_manual' => 'integer',
    ];

    public function ejercicio()
    {
        return $this->belongsTo(Ejercicio::class, 'id_ejercicio', 'id_ejercicio');
    }

    public function opciones()
    {
        return $this->hasMany(EjercicioOpcion::class, 'id_ejercicio_pregunta', 'id_ejercicio_pregunta');
    }

    public function respuestasIntento()
    {
        return $this->hasMany(EjercicioIntentoRespuesta::class, 'id_ejercicio_pregunta', 'id_ejercicio_pregunta');
    }
}