<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Empleado;

class PuestoTrabajoMatriz extends Model
{
    use HasFactory;

    protected $table = 'puesto_trabajo_matriz';
    protected $primaryKey = 'id_puesto_trabajo_matriz';
    public $timestamps = false;

    protected $fillable = [
        'puesto_trabajo_matriz',
        'id_departamento',
        'descripcion_general',
        'objetivo_puesto',
        'num_empleados',
        'estado',
    ];

    protected $casts = [
        'id_puesto_trabajo_matriz' => 'integer',
        'id_departamento' => 'integer',
        'num_empleados' => 'integer',
        'estado' => 'integer',
    ];

    public function departamento(){
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');

    }

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_puesto_trabajo_matriz', 'id_puesto_trabajo_matriz');
    }

    public function puestosCapacitacion()
    {
        return $this->hasMany(PuestosCapacitacion::class, 'id_puesto_trabajo_matriz', 'id_puesto_trabajo_matriz');
    }
}
