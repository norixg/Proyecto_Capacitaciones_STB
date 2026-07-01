<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionAviso extends Model
{
    protected $table = 'configuracion_aviso';

    protected $primaryKey = 'id_configuracion_aviso';

    public $timestamps = false;

    protected $fillable = [
        'tipo_aviso',
        'dias_anticipacion',
        'enviar_a_empleado',
        'enviar_a_admin',
        'activo',
    ];

    public function avisosCorreo()
    {
        return $this->hasMany(
            AvisoCorreo::class,
            'id_configuracion_aviso',
            'id_configuracion_aviso'
        );
    }
}