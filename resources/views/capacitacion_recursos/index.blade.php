<x-app-layout>

    @php
        $volverAlModuloDesdeRecursos = (int) request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) === 1;
        $idSeccionRetornoRecursos = request('id_capacitacion_modulo_seccion');

        $urlRegresoRecursos = $volverAlModuloDesdeRecursos
            ? route('capacitacion_modulos.edit', [
                'id' => $modulo->id_capacitacion_modulo,
                'origen' => 'builder',
            ]) . ($idSeccionRetornoRecursos ? '#seccion-modulo-' . $idSeccionRetornoRecursos : '')
            : route('capacitaciones.builder', $modulo->capacitacion?->id_capacitacion);
        $parametrosCrearRecurso = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ];

        if ($volverAlModuloDesdeRecursos && $idSeccionRetornoRecursos) {
            $parametrosCrearRecurso['volver_modulo'] = 1;
            $parametrosCrearRecurso['id_capacitacion_modulo_seccion'] = $idSeccionRetornoRecursos;
        }
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
