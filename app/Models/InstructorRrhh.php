<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorRrhh extends Model
{
    protected $connection = 'rrhh';
    protected $table = 'instructor';
    protected $primaryKey = 'id_instructor';
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'id_instructor' => 'integer',
    ];
}
