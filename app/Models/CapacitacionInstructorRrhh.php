<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapacitacionInstructorRrhh extends Model
{
    protected $connection = 'rrhh';
    protected $table = 'capacitacion_instructor';
    protected $primaryKey = 'id_capacitacion_instructor';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'id_capacitacion_instructor' => 'integer',
        'id_capacitacion' => 'integer',
        'id_instructor' => 'integer',
    ];
}
