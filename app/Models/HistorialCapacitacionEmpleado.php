<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistorialCapacitacionEmpleado extends Model
{
    use HasFactory;

    protected $dateFormat = 'Ymd H:i:s';
    protected $table = 'historial_capacitacion_empleado';
    protected $primaryKey = 'id_historial_capacitacion_empleado';
    public $timestamps = false;

    protected $fillable = [
        'id_empleado_capacitacion',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'fecha_movimiento',
        'id_user',
    ];

    protected $casts = [
        'id_historial_capacitacion_empleado' => 'integer',
        'id_empleado_capacitacion' => 'integer',
        'fecha_movimiento' => 'datetime',
        'id_user' => 'integer',
    ];

    public function empleadoCapacitacion()
    {
        return $this->belongsTo(EmpleadoCapacitacion::class, 'id_empleado_capacitacion', 'id_empleado_capacitacion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
