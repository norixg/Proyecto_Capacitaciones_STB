<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'rol',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'id_rol' => 'integer',
        'estado' => 'integer',
    ];

    public function userRoles()
    {
        return $this->hasMany(UserRol::class, 'id_rol', 'id_rol');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_rol',
            'id_rol',
            'id_user',
            'id_rol',
            'id'
        );
    }
}
