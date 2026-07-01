<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CapacitacionArea extends Model
{
    use HasFactory;

    protected $table = 'capacitacion_area';
    protected $primaryKey = 'id_capacitacion_area';
    public $timestamps = false;

    protected $fillable = [
        'id_capacitacion',
        'id_area_capacitacion',
    ];

    protected $casts = [
        'id_capacitacion_area' => 'integer',
        'id_capacitacion' => 'integer',
        'id_area_capacitacion' => 'integer',
    ];

    public function capacitacion()
    {
        return $this->belongsTo(Capacitacion::class, 'id_capacitacion', 'id_capacitacion');
    }

    public function areaCapacitacion()
    {
        return $this->belongsTo(AreaCapacitacion::class, 'id_area_capacitacion', 'id_area_capacitacion');
    }
}
