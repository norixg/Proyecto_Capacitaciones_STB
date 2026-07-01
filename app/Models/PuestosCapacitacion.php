<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PuestosCapacitacion extends Model
{
    use HasFactory;

    protected $table = 'puestos_capacitacion';
    protected $primaryKey = 'id_puestos_capacitacion';
    public $timestamps = false;

    protected $fillable = [
        'id_puesto_trabajo_matriz',
        'id_capacitacion',
        'obligatoria',
        'dias_para_vencer',
        'fecha_asignacion',
        'estado',
    ];

    protected $casts = [
        'id_puestos_capacitacion' => 'integer',
        'id_puesto_trabajo_matriz' => 'integer',
        'id_capacitacion' => 'integer',
        'obligatoria' => 'integer',
        'dias_para_vencer' => 'integer',
        'fecha_asignacion' => 'date',
        'estado' => 'integer',
    ];

    public function puestoTrabajo()
    {
        return $this->belongsTo(PuestoTrabajoMatriz::class, 'id_puesto_trabajo_matriz', 'id_puesto_trabajo_matriz');
    }

    public function capacitacion()
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }
}
