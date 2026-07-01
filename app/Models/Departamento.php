<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamento';
    protected $primaryKey = 'id_departamento';
    public $timestamps = false;

    protected $fillable = [
        'departamento',
        'estado',

    ];

    protected $casts = [
        'id_departamento' => 'integer',
        'estado' => 'integer',
    ];

    public function puestosTrabajo(){
        return $this->hasMany(PuestoTrabajoMatriz::class, 'id_departamento', 'id_departamento');
    }
    public function departamentosCapacitacion()
    {
        return $this->hasMany(DepartamentosCapacitacion::class, 'id_departamento', 'id_departamento');
    }
}
