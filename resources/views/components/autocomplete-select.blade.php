@props([
    'name',
    'options',
    'selected' => '',
    'placeholder' => 'Escriba para buscar',
    'inputId' => null,
])

@php
    $inputId ??= $name;
@endphp

<div
    class="relative"
    x-data="selectAutocomplete(@js($options), @js($selected), @js($name))"
    @click.outside="abierto = false"
>
    <input type="hidden" name="{{ $name }}" :value="idSeleccionado">

    <div class="relative">
        <input
            id="{{ $inputId }}"
            x-ref="entrada"
            type="text"
            x-model="consulta"
            autocomplete="off"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 pr-12 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 {{ $errors->has($name) ? 'border-red-500' : '' }}"
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="abierto"
            @focus="abierto = true"
            @input="escribir()"
            @keydown.down.prevent="mover(1)"
            @keydown.up.prevent="mover(-1)"
            @keydown.enter.prevent="seleccionarActivo()"
            @keydown.escape="abierto = false"
        >

        <button
            x-show="consulta"
            type="button"
            class="absolute inset-y-0 right-0 px-4 text-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200"
            aria-label="Limpiar selección"
            @click="limpiar()"
        >&times;</button>
    </div>

    <div
        x-cloak
        x-show="abierto"
        class="absolute z-50 mt-1 max-h-72 w-full overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
        role="listbox"
    >
        <template x-for="(opcion, indice) in coincidencias" :key="opcion.id">
            <button
                type="button"
                class="block w-full px-4 py-3 text-left text-sm text-slate-900 dark:text-slate-100"
                :class="indice === indiceActivo ? 'bg-blue-100 dark:bg-blue-900/50' : 'hover:bg-slate-100 dark:hover:bg-slate-800'"
                role="option"
                :aria-selected="idSeleccionado === String(opcion.id)"
                @mouseenter="indiceActivo = indice"
                @mousedown.prevent="seleccionar(opcion)"
            >
                <span x-text="opcion.etiqueta"></span>
            </button>
        </template>

        <p x-show="coincidencias.length === 0" class="px-4 py-3 text-sm text-slate-500">
            No se encontraron coincidencias.
        </p>
    </div>
</div>
