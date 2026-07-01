<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CapacitacionModulo;
use App\Models\EjercicioPregunta;
use App\Models\EjercicioIntento;
use App\Models\CapacitacionModuloSeccion;

class Ejercicio extends Model
{
    use HasFactory;

    protected $table = 'ejercicio';
    protected $primaryKey = 'id_ejercicio';
    public $timestamps = false;

    protected $fillable = [
        'id_capacitacion_modulo',
        'id_capacitacion_modulo_seccion',
        'titulo',
        'descripcion',
        'instrucciones',
        'intentos_maximos',
        'tiempo_limite_minutos',
        'porcentaje_aprobacion',
        'obligatorio',
        'orden',
        'estado',
        'mostrar_resultado_inmediato',
        'requiere_revision_manual',
    ];

    protected $casts = [
        'id_ejercicio' => 'integer',
        'id_capacitacion_modulo' => 'integer',
        'id_capacitacion_modulo_seccion' => 'integer',
        'intentos_maximos' => 'integer',
        'tiempo_limite_minutos' => 'integer',
        'porcentaje_aprobacion' => 'decimal:2',
        'obligatorio' => 'integer',
        'orden' => 'integer',
        'estado' => 'integer',
        'mostrar_resultado_inmediato' => 'integer',
        'requiere_revision_manual' => 'integer',
    ];

    public function modulo()
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function preguntas()
    {
        return $this->hasMany(EjercicioPregunta::class, 'id_ejercicio', 'id_ejercicio');
    }

    public function intentos()
    {
        return $this->hasMany(EjercicioIntento::class, 'id_ejercicio', 'id_ejercicio');
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