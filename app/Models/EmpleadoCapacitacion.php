<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Capacitacion;
use App\Models\Empleado;
use App\Models\EjercicioIntento;


class EmpleadoCapacitacion extends Model
{
    protected $dateFormat = 'Ymd H:i:s';
    use HasFactory;

    protected $table = 'empleado_capacitacion';
    protected $primaryKey = 'id_empleado_capacitacion';
    public $timestamps = true;

    protected $fillable = [
        'id_empleado',
        'id_capacitacion',
        'origen_asignacion',
        'id_referencia_asignacion',
        'obligatoria',
        'fecha_asignacion',
        'fecha_inicio',
        'fecha_limite',
        'fecha_vencimiento',
        'fecha_finalizacion',
        'estado',
        'progreso',
        'nota_final',
        'aprobado',
        'id_usuario_asigno',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id_empleado_capacitacion' => 'integer',
        'id_empleado' => 'integer',
        'id_capacitacion' => 'integer',
        'id_referencia_asignacion' => 'integer',
        'obligatoria' => 'integer',
        'fecha_asignacion' => 'date',
        'fecha_inicio' => 'datetime',
        'fecha_limite' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_finalizacion' => 'datetime',
        'progreso' => 'decimal:2',
        'nota_final' => 'decimal:2',
        'aprobado' => 'integer',
        'id_usuario_asigno' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(EmpleadoRrhh::class, 'id_empleado', 'id_empleado');
    }

    public function capacitacion()
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function usuarioAsigno()
    {
        return $this->belongsTo(User::class, 'id_usuario_asigno', 'id');
    }

    public function modulosAvance()
    {
        return $this->hasMany(EmpleadoModuloAvance::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }

    public function intentosEvaluacion()
    {
        return $this->hasMany(EvaluacionIntento::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }

    public function intentosEjercicio()
    {
        return $this->hasMany(EjercicioIntento::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }

    public function avisosCorreo()
    {
        return $this->hasMany(AvisoCorreo::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }

    public function historial()
    {
        return $this->hasMany(HistorialCapacitacionEmpleado::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }
}
