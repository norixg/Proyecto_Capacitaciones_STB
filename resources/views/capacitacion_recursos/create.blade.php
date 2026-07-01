<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                    Recursos del módulo
                </p>

                <h2 class="esf-seguimiento-title">
                    Crear recurso
                </h2>

                <p class="esf-seguimiento-subtitle">
                    Módulo: {{ $modulo->titulo }}
                </p>
            </div>

            <a href="{{ route('capacitacion_modulos.recursos.index', $modulo->id_capacitacion_modulo) }}"
            class="esf-btn esf-btn-soft">
                Volver a recursos
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="esf-admin-modal-card w-full max-w-4xl mx-auto p-6 sm:p-8">
                <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                    Nuevo recurso
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Completa los datos del recurso que se mostrará dentro del módulo.
                </p>

                <form method="POST"
                      action="{{ route('capacitacion_modulos.recursos.store', $modulo->id_capacitacion_modulo) }}"
                      enctype="multipart/form-data"
                      class="space-y-5">
                    @csrf

                    <input type="hidden" name="origen" value="builder">

                    <input type="hidden"
                        name="volver_modulo"
                        value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">

                    <div class="esf-admin-form-grid esf-admin-form-grid-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tipo de recurso</label>

                            <select name="tipo_recurso"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="imagen" {{ old('tipo_recurso') === 'imagen' ? 'selected' : '' }}>Imagen</option>
                                <option value="pdf" {{ old('tipo_recurso') === 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="word" {{ old('tipo_recurso') === 'word' ? 'selected' : '' }}>Word</option>
                                <option value="powerpoint" {{ old('tipo_recurso') === 'powerpoint' ? 'selected' : '' }}>PowerPoint</option>
                                <option value="excel" {{ old('tipo_recurso') === 'excel' ? 'selected' : '' }}>Excel</option>
                                <option value="video" {{ old('tipo_recurso') === 'video' ? 'selected' : '' }}>Video</option>
                                <option value="audio" {{ old('tipo_recurso') === 'audio' ? 'selected' : '' }}>Audio</option>
                                <option value="enlace" {{ old('tipo_recurso') === 'enlace' ? 'selected' : '' }}>Enlace</option>
                                <option value="comprimido" {{ old('tipo_recurso') === 'comprimido' ? 'selected' : '' }}>Archivo comprimido</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Ubicar en sección/subsección</label>

                            <select name="id_capacitacion_modulo_seccion"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Contenido general del módulo</option>

                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                            {{ old('id_capacitacion_modulo_seccion', request('id_capacitacion_modulo_seccion')) == $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
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
                                   value="{{ old('orden', $siguienteOrden ?? 1) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                   required>
                        </div>
                    </div>

                    <div class="esf-admin-form-full">
                        <label class="block text-sm font-medium mb-1">Título <span class="text-xs font-semibold text-slate-400">(opcional)</span></label>

                        <input type="text"
                            name="titulo"
                            value="{{ old('titulo') }}"
                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div class="esf-admin-form-full">
                        <label class="block text-sm font-medium mb-1">Descripción</label>

                        <textarea name="descripcion"
                                  rows="3"
                                  class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('descripcion') }}</textarea>
                    </div>
<input type="hidden" name="contenido_texto" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">URL externa</label>

                            <input type="url"
                                   name="url_recurso"
                                   value="{{ old('url_recurso') }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                   placeholder="https://...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Archivo del recurso</label>

                            <input type="file"
                                   name="archivo_recurso"
                                   class="esf-admin-file-input">

                            <p class="text-xs text-gray-500 mt-1">
                                Puedes subir PDF, Word, PowerPoint, Excel, imagen, video, audio o archivo comprimido.
                            </p>
                        </div>
                    </div>

                    <input type="hidden" name="ruta_archivo" value="{{ old('ruta_archivo') }}">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Obligatorio</label>

                            <select name="obligatorio"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="1" {{ old('obligatorio', '1') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('obligatorio') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Permite descarga</label>

                            <select name="permite_descarga"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="1" {{ old('permite_descarga', '1') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('permite_descarga') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Estado</label>

                            <select name="estado"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    </div>

                    <div class="esf-admin-actions-footer">
                        <button type="submit" class="esf-btn esf-btn-primary">
                            Guardar recurso
                        </button>

                        <a href="{{ route('capacitacion_modulos.recursos.index', $modulo->id_capacitacion_modulo) }}"
                           class="esf-btn esf-btn-soft">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>