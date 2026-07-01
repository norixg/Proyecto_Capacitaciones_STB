<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CapacitacionModulo;

class EmpleadoModuloAvance extends Model
{
    protected $dateFormat = 'Ymd H:i:s';
     use HasFactory;

    protected $table = 'empleado_modulo_avance';
    protected $primaryKey = 'id_empleado_modulo_avance';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado_capacitacion',
        'id_capacitacion_modulo',
        'fecha_inicio',
        'fecha_ultima_actividad',
        'fecha_finalizacion',
        'estado',
        'progreso',
        'nota',
        'aprobado',
    ];

    protected $casts = [
        'id_empleado_modulo_avance' => 'integer',
        'id_empleado_capacitacion' => 'integer',
        'id_capacitacion_modulo' => 'integer',
        'fecha_inicio' => 'datetime',
        'fecha_ultima_actividad' => 'datetime',
        'fecha_finalizacion' => 'datetime',
        'progreso' => 'decimal:2',
        'nota' => 'decimal:2',
        'aprobado' => 'integer',
    ];

    public function empleadoCapacitacion()
    {
        return $this->belongsTo(EmpleadoCapacitacion::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }

    public function modulo()
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function capacitacionModulo()
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }
}
