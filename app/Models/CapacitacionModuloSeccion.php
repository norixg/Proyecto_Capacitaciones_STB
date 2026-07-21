<?php

namespace App\Models;

use App\Services\ContenidoHtmlSeguro;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\CapacitacionRecurso;
use App\Models\Ejercicio;
use App\Models\Evaluacion;

class CapacitacionModuloSeccion extends Model
{
    protected $table = 'capacitacion_modulo_seccion';
    protected $primaryKey = 'id_capacitacion_modulo_seccion';
    public $timestamps = false;

    protected $fillable = [
        'id_capacitacion_modulo',
        'titulo',
        'id_seccion_padre',
        'contenido',
        'orden',
        'nivel',
        'estado',
    ];

    protected $casts = [
        'id_capacitacion_modulo_seccion' => 'integer',
        'id_capacitacion_modulo' => 'integer',
        'id_seccion_padre' => 'integer',
        'orden' => 'integer',
        'nivel' => 'integer',
        'estado' => 'integer',
    ];

    protected function contenido(): Attribute
    {
        return Attribute::make(
            get: fn ($valor) => app(ContenidoHtmlSeguro::class)->limpiar($valor),
            set: fn ($valor) => app(ContenidoHtmlSeguro::class)->limpiar($valor),
        );
    }

    public function modulo()
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function recursos()
    {
        return $this->hasMany(
            CapacitacionRecurso::class,
            'id_capacitacion_modulo_seccion',
            'id_capacitacion_modulo_seccion'
        )->orderBy('orden');
    }

    public function ejercicios()
    {
        return $this->hasMany(
            Ejercicio::class,
            'id_capacitacion_modulo_seccion',
            'id_capacitacion_modulo_seccion'
        )->orderBy('orden');
    }

    public function evaluaciones()
    {
        return $this->hasMany(
            Evaluacion::class,
            'id_capacitacion_modulo_seccion',
            'id_capacitacion_modulo_seccion'
        )->orderBy('orden');
    }

    public function padre()
    {
        return $this->belongsTo(
            CapacitacionModuloSeccion::class,
            'id_seccion_padre',
            'id_capacitacion_modulo_seccion'
        );
    }

    public function subsecciones()
    {
        return $this->hasMany(
            CapacitacionModuloSeccion::class,
            'id_seccion_padre',
            'id_capacitacion_modulo_seccion'
        )->orderBy('orden');
    }
}
