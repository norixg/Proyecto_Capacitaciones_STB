<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorUser extends Model
{
    protected $table = 'instructor_user';
    protected $primaryKey = 'id_instructor_user';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_instructor',
        'fecha_asignacion',
    ];

    protected $casts = [
        'id_instructor_user' => 'integer',
        'id_user' => 'integer',
        'id_instructor' => 'integer',
        'fecha_asignacion' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function instructor()
    {
        return $this->belongsTo(InstructorRrhh::class, 'id_instructor', 'id_instructor');
    }
}
