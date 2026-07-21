<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoRrhh extends Model
{
    protected $connection = 'rrhh';
    protected $table = 'empleado';
    protected $primaryKey = 'id_empleado';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'id_empleado' => 'integer',
        'estado' => 'integer',
    ];
}
