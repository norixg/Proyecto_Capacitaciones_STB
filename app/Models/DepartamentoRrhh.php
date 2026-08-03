<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartamentoRrhh extends Model
{
    protected $connection = 'rrhh';
    protected $table = 'departamento';
    protected $primaryKey = 'id_departamento';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'id_departamento' => 'integer',
    ];
}
