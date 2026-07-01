<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Instructor extends Model
{
    use HasFactory;

    protected $table = 'instructor';
    protected $primaryKey = 'id_instructor';
    public $timestamps = false;

    protected $fillable = [
        'instructor',
        'correo',
        'telefono',
        'interno',
        'estado',
        'id_empleado',
    ];

    protected $casts = [
        'id_instructor' => 'integer',
        'interno' => 'integer',
        'estado' => 'integer',
        'id_empleado' => 'integer',
    ];

    public function capacitaciones()
    {
        return $this->hasMany(Capacitacion::class, 'id_instructor', 'id_instructor');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}