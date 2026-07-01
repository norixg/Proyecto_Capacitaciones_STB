<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisoCorreo extends Model
{
    protected $table = 'aviso_correo';

    protected $primaryKey = 'id_aviso_correo';

    public $timestamps = false;

    protected $dateFormat = 'Ymd H:i:s';

    protected $fillable = [
        'id_empleado_capacitacion',
        'id_configuracion_aviso',
        'tipo_aviso',
        'destinatario_tipo',
        'destinatario_email',
        'asunto',
        'mensaje',
        'fecha_programada',
        'fecha_enviada',
        'estado',
        'intentos_envio',
        'error_envio',
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_enviada' => 'datetime',
        'intentos_envio' => 'integer',
    ];

    public function empleadoCapacitacion()
    {
        return $this->belongsTo(
            EmpleadoCapacitacion::class,
            'id_empleado_capacitacion',
            'id_empleado_capacitacion'
        );
    }

    public function configuracionAviso()
    {
        return $this->belongsTo(
            ConfiguracionAviso::class,
            'id_configuracion_aviso',
            'id_configuracion_aviso'
        );
    }
}