<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class DepartamentosCapacitacion extends Model
{
    use HasFactory;

    protected $table = 'departamentos_capacitacion';
    protected $primaryKey = 'id_departamentos_capacitacion';
    public $timestamps = false;

    protected $fillable = [
        'id_departamento',
        'id_capacitacion',
        'obligatoria',
        'dias_para_vencer',
        'fecha_asignacion',
        'estado',
    ];

    protected $casts = [
        'id_departamentos_capacitacion' => 'integer',
        'id_departamento' => 'integer',
        'id_capacitacion' => 'integer',
        'obligatoria' => 'integer',
        'dias_para_vencer' => 'integer',
        'fecha_asignacion' => 'date',
        'estado' => 'integer',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function capacitacion()
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }
}
