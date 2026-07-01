<?php

use App\Services\AvisoCorreoService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('avisos:procesar', function () {
    $servicio = app(AvisoCorreoService::class);

    $generados = $servicio->generarAvisos();
    $enviados = $servicio->enviarPendientes();

    $this->info('Avisos generados: ' . $generados['total']);
    $this->info('Por vencer: ' . $generados['por_vencer']);
    $this->info('Retrasados: ' . $generados['vencida']);
    $this->info('Finalizados: ' . $generados['terminada']);
    $this->info('Procesados: ' . $enviados['procesados']);
    $this->info('Enviados: ' . $enviados['enviados']);
    $this->info('Errores: ' . $enviados['errores']);
})->purpose('Generar y enviar automáticamente avisos pendientes de capacitaciones');

Schedule::command('avisos:procesar')
    ->dailyAt('09:00')
    ->withoutOverlapping();