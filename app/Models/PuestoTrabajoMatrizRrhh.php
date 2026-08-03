<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuestoTrabajoMatrizRrhh extends Model
{
    protected $connection = 'rrhh';
    protected $table = 'puesto_trabajo_matriz';
    protected $primaryKey = 'id_puesto_trabajo_matriz';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'id_puesto_trabajo_matriz' => 'integer',
        'id_departamento' => 'integer',
    ];

    public function departamento()
    {
        return $this->belongsTo(DepartamentoRrhh::class, 'id_departamento', 'id_departamento');
    }
}
