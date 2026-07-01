<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CapacitacionRecurso;
use App\Models\Capacitacion;
use App\Models\Evaluacion;
use App\Models\Ejercicio;
use App\Models\CapacitacionModuloSeccion;

class CapacitacionModulo extends Model
{
    use HasFactory;

    protected $table = 'capacitacion_modulo';
    protected $primaryKey = 'id_capacitacion_modulo';
    public $timestamps = false;

    protected $fillable = [
        'id_capacitacion',
        'titulo',
        'descripcion',
        'objetivo',
        'orden',
        'duracion_horas',
        'requiere_evaluacion',
        'porcentaje_aprobacion',
        'estado',
    ];

    protected $casts = [
        'id_capacitacion_modulo' => 'integer',
        'id_capacitacion' => 'integer',
        'orden' => 'integer',
        'duracion_horas' => 'decimal:2',
        'requiere_evaluacion' => 'integer',
        'porcentaje_aprobacion' => 'decimal:2',
        'estado' => 'integer',
    ];

    public function capacitacion()
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function secciones()
    {
        return $this->hasMany(CapacitacionModuloSeccion::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo')
            ->orderBy('orden');
    }

    public function recursos()
    {
        return $this->hasMany(CapacitacionRecurso::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function ejercicios()
    {
        return $this->hasMany(Ejercicio::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function avancesEmpleado()
    {
        return $this->hasMany(EmpleadoModuloAvance::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }
}
