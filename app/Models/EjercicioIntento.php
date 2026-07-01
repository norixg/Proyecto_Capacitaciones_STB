<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ejercicio;
use App\Models\EjercicioIntentoRespuesta;
use App\Models\Empleado;
use App\Models\EmpleadoCapacitacion;

class EjercicioIntento extends Model
{
    use HasFactory;

    protected $dateFormat = 'Ymd H:i:s';

    protected $table = 'ejercicio_intento';
    protected $primaryKey = 'id_ejercicio_intento';
    public $timestamps = false;

    protected $fillable = [
        'id_ejercicio',
        'id_empleado',
        'id_empleado_capacitacion',
        'numero_intento',
        'fecha_inicio',
        'fecha_fin',
        'puntaje_obtenido',
        'porcentaje_obtenido',
        'aprobado',
        'estado',
        'comentario_revision',
    ];

    protected $casts = [
        'id_ejercicio_intento' => 'integer',
        'id_ejercicio' => 'integer',
        'id_empleado' => 'integer',
        'id_empleado_capacitacion' => 'integer',
        'numero_intento' => 'integer',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'puntaje_obtenido' => 'decimal:2',
        'porcentaje_obtenido' => 'decimal:2',
        'aprobado' => 'integer',
    ];

    public function ejercicio()
    {
        return $this->belongsTo(Ejercicio::class, 'id_ejercicio', 'id_ejercicio');
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
        return $this->hasMany(EjercicioIntentoRespuesta::class, 'id_ejercicio_intento', 'id_ejercicio_intento');
    }
}