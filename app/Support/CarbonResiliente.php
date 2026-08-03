<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\Month;
use Carbon\WeekDay;
use DateTimeInterface;
use DateTimeZone;

/**
 * En el entorno local (macOS), el driver ODBC de SQL Server a veces devuelve
 * columnas date/datetime a medianoche como "Mon dd yyyy hh:mm:ss:AM" (con
 * milisegundos vacíos y dos puntos en vez de espacio antes de AM/PM), un
 * formato que Carbon no reconoce y que revienta cualquier cast de fecha.
 * Este mismo valor, leído desde el contenedor Docker, llega limpio como
 * "yyyy-mm-dd". Como es un problema del driver y no de los datos, se corrige
 * aquí una sola vez en vez de en cada modelo/consulta que toque una fecha.
 */
class CarbonResiliente extends CarbonImmutable
{
    public static function rawParse(
        DateTimeInterface|WeekDay|Month|string|int|float|null $time,
        DateTimeZone|string|int|null $timezone = null,
    ): static {
        if (is_string($time)) {
            $time = preg_replace(
                '/(\d{1,2}:\d{2}:\d{2})(?::\d{1,3})?:(AM|PM)\b/i',
                '$1 $2',
                $time
            );
        }

        return parent::rawParse($time, $timezone);
    }
}
