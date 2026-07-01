<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Editar recurso
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $recurso->titulo }}
                </p>
            </div>

            <a href="{{ route('capacitacion_modulos.recursos.index', $modulo->id_capacitacion_modulo) }}"
               class="px-4 py-2 bg-gray-600 text-white rounded text-sm">
                Volver a recursos
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                    <strong>Revisa los siguientes errores:</strong>

                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Editar recurso
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Modifica los datos del recurso seleccionado.
                </p>

                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                    <strong>Módulo:</strong> {{ $modulo->titulo }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST"
                      action="{{ route('capacitacion_recursos.update', $recurso->id_capacitacion_recurso) }}"
                      enctype="multipart/form-data"
                      class="space-y-5">
                    @csrf
                    @method('PUT')

                    <input type="hidden"
                        name="volver_modulo"
                        value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tipo de recurso</label>

                            <select name="tipo_recurso"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="imagen" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'imagen' ? 'selected' : '' }}>Imagen</option>
                                <option value="pdf" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="word" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'word' ? 'selected' : '' }}>Word</option>
                                <option value="powerpoint" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'powerpoint' ? 'selected' : '' }}>PowerPoint</option>
                                <option value="excel" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'excel' ? 'selected' : '' }}>Excel</option>
                                <option value="video" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'video' ? 'selected' : '' }}>Video</option>
                                <option value="audio" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'audio' ? 'selected' : '' }}>Audio</option>
                                <option value="enlace" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'enlace' ? 'selected' : '' }}>Enlace</option>
                                <option value="comprimido" {{ old('tipo_recurso', $recurso->tipo_recurso) === 'comprimido' ? 'selected' : '' }}>Archivo comprimido</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Ubicar en sección/subsección</label>

                            <select name="id_capacitacion_modulo_seccion"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Contenido general del módulo</option>

                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                            {{ old('id_capacitacion_modulo_seccion', $recurso->id_capacitacion_modulo_seccion) == $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                        {{ (int) $seccion->nivel === 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-xs text-gray-500 mt-1">
                                Aquí decides en qué parte del módulo aparecerá este recurso.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Orden</label>

                            <input type="number"
                                   name="orden"
                                   min="1"
                                   value="{{ old('orden', $recurso->orden) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                   required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Título <span class="text-xs font-semibold text-slate-400">(opcional)</span></label>

                        <input type="text"
                            name="titulo"
                            value="{{ old('titulo', $recurso->titulo) }}"
                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción</label>

                        <textarea name="descripcion"
                                  rows="3"
                                  class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('descripcion', $recurso->descripcion) }}</textarea>
                    </div>

<input type="hidden" name="contenido_texto" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">URL externa</label>

                            <input type="url"
                                   name="url_recurso"
                                   value="{{ old('url_recurso', $recurso->url_recurso) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                   placeholder="https://...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Reemplazar archivo</label>

                            <input type="file"
                                   name="archivo_recurso"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                            @if($recurso->ruta_archivo)
                                <p class="text-xs text-gray-500 mt-1">
                                    Archivo actual: {{ basename($recurso->ruta_archivo) }}
                                </p>
                            @else
                                <p class="text-xs text-gray-500 mt-1">
                                    Este recurso aún no tiene archivo cargado.
                                </p>
                            @endif
                        </div>
                    </div>

                    <input type="hidden" name="ruta_archivo" value="{{ old('ruta_archivo', $recurso->ruta_archivo) }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Obligatorio</label>

                            <select name="obligatorio"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="1" {{ old('obligatorio', $recurso->obligatorio) == 1 ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('obligatorio', $recurso->obligatorio) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Permite descarga</label>

                            <select name="permite_descarga"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="1" {{ old('permite_descarga', $recurso->permite_descarga) == 1 ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('permite_descarga', $recurso->permite_descarga) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Estado</label>

                            <select name="estado"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="1" {{ old('estado', $recurso->estado) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado', $recurso->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-500 text-white rounded">
                            Guardar cambios
                        </button>

                        <a href="{{ route('capacitacion_modulos.recursos.index', [
                                'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
                                'volver_modulo' => request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0),
                                'id_capacitacion_modulo_seccion' => request('id_capacitacion_modulo_seccion') ?: $recurso->id_capacitacion_modulo_seccion,
                                'open' => 'recurso-' . $recurso->id_capacitacion_recurso,
                            ]) }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>