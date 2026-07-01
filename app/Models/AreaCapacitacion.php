<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AreaCapacitacion extends Model
{
     use HasFactory;

    protected $table = 'area_capacitacion';
    protected $primaryKey = 'id_area_capacitacion';
    public $timestamps = false;

    protected $fillable = [
        'area_capacitacion',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'id_area_capacitacion' => 'integer',
        'estado' => 'integer',
    ];

    public function capacitacionesArea()
    {
        return $this->hasMany(CapacitacionArea::class, 'id_area_capacitacion', 'id_area_capacitacion');
    }
}
