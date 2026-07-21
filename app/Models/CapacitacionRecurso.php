<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\CapacitacionModulo;
use App\Models\CapacitacionModuloSeccion;


class CapacitacionRecurso extends Model
{
    use HasFactory;

    protected $table = 'capacitacion_recurso';
    protected $primaryKey = 'id_capacitacion_recurso';
    public $timestamps = false;

    protected $fillable = [
        'id_capacitacion_modulo',
        'id_capacitacion_modulo_seccion',
        'tipo_recurso',
        'titulo',
        'descripcion',
        'url_recurso',
        'ruta_archivo',
        'obligatorio',
        'orden',
        'estado',
        'contenido_texto',
        'permite_descarga',
    ];

    protected $casts = [
        'id_capacitacion_recurso' => 'integer',
        'id_capacitacion_modulo' => 'integer',
        'id_capacitacion_modulo_seccion' => 'integer',
        'obligatorio' => 'integer',
        'orden' => 'integer',
        'estado' => 'integer',
    ];

    protected function urlRecurso(): Attribute
    {
        $normalizar = static function ($valor): ?string {
            $url = trim((string) $valor);

            if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                return null;
            }

            return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
                ? $url
                : null;
        };

        return Attribute::make(get: $normalizar, set: $normalizar);
    }

    protected function rutaArchivo(): Attribute
    {
        $normalizar = static function ($valor): ?string {
            $ruta = ltrim(str_replace('\\', '/', trim((string) $valor)), '/');

            if ($ruta === '' || str_contains($ruta, '..') || ! str_starts_with($ruta, 'capacitaciones/recursos/')) {
                return null;
            }

            return $ruta;
        };

        return Attribute::make(get: $normalizar, set: $normalizar);
    }

    public function modulo()
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function capacitacionModulo(): BelongsTo
    {
        return $this->belongsTo(CapacitacionModulo::class, 'id_capacitacion_modulo', 'id_capacitacion_modulo');
    }

    public function seccion()
    {
        return $this->belongsTo(
            CapacitacionModuloSeccion::class,
            'id_capacitacion_modulo_seccion',
            'id_capacitacion_modulo_seccion'
        );
    }
}
