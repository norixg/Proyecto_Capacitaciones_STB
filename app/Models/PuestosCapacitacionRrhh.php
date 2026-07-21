<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuestosCapacitacionRrhh extends Model
{
    protected $connection = 'rrhh';
    protected $table = 'puestos_capacitacion';
    protected $primaryKey = 'id_puestos_capacitacion';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'id_puestos_capacitacion' => 'integer',
        'id_puesto_trabajo_matriz' => 'integer',
        'id_capacitacion' => 'integer',
    ];
}
