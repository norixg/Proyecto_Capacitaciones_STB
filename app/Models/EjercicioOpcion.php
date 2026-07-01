<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\EjercicioPregunta;

class EjercicioOpcion extends Model
{
    use HasFactory;

    protected $table = 'ejercicio_opcion';
    protected $primaryKey = 'id_ejercicio_opcion';
    public $timestamps = false;

    protected $fillable = [
        'id_ejercicio_pregunta',
        'opcion',
        'lado',
        'clave_relacion',
        'es_correcta',
        'orden',
    ];

    protected $casts = [
        'id_ejercicio_opcion' => 'integer',
        'id_ejercicio_pregunta' => 'integer',
        'es_correcta' => 'integer',
        'orden' => 'integer',
    ];

    public function pregunta()
    {
        return $this->belongsTo(EjercicioPregunta::class, 'id_ejercicio_pregunta', 'id_ejercicio_pregunta');
    }
}