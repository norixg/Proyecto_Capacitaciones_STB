<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRol extends Model
{
    use HasFactory;

    protected $table = 'user_rol';
    protected $primaryKey = 'id_user_rol';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_rol',
        'fecha_asignacion',
    ];

    protected $casts = [
        'id_user_rol' => 'integer',
        'id_user' => 'integer',
        'id_rol' => 'integer',
        'fecha_asignacion' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}
