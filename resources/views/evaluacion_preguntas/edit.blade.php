<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar pregunta
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div class="mb-6 rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                            <strong>Revisa los siguientes errores:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('evaluacion_preguntas.update', $pregunta->id_evaluacion_pregunta) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="origen" value="{{ request('origen') }}">

                        <div class="mb-4">
                            <label class="block mb-1">Pregunta</label>
                            <textarea name="pregunta" rows="4"
                                class="w-full border rounded px-3 py-2 text-black @error('pregunta') border-red-500 @enderror">{{ old('pregunta', $pregunta->pregunta) }}</textarea>
                            @error('pregunta') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Mínimo 3 caracteres.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block mb-1">Tipo de pregunta</label>
                                <select name="tipo_pregunta"
                                    class="w-full border rounded px-3 py-2 text-black @error('tipo_pregunta') border-red-500 @enderror">
                                    <option value="opcion_unica" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) == 'opcion_unica' ? 'selected' : '' }}>Opción única</option>
                                    <option value="checklist_guiado" {{ in_array(old('tipo_pregunta', $pregunta->tipo_pregunta), ['checklist_guiado', 'opcion_multiple', 'multiple'], true) ? 'selected' : '' }}>Opción múltiple</option>
                                    <option value="verdadero_falso" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) == 'verdadero_falso' ? 'selected' : '' }}>Verdadero/Falso</option>
                                    <option value="completar" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) == 'completar' ? 'selected' : '' }}>Completar en frase</option>
                                    <option value="respuesta_corta" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) == 'respuesta_corta' ? 'selected' : '' }}>Respuesta breve</option>
                                </select>
                                @error('tipo_pregunta') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Puntaje</label>
                                <input type="number" step="0.01" name="puntaje" value="{{ old('puntaje', $pregunta->puntaje) }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('puntaje') border-red-500 @enderror">
                                @error('puntaje') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Orden</label>
                                <input type="number" name="orden" value="{{ old('orden', $pregunta->orden) }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('orden') border-red-500 @enderror">
                                @error('orden') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block mb-1">Estado</label>
                                <select name="activa"
                                    class="w-full border rounded px-3 py-2 text-black @error('activa') border-red-500 @enderror">
                                    <option value="1" {{ old('activa', $pregunta->activa) == '1' ? 'selected' : '' }}>Activa</option>
                                    <option value="0" {{ old('activa', $pregunta->activa) == '0' ? 'selected' : '' }}>Inactiva</option>
                                </select>
                                @error('activa') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Respuesta correcta texto</label>
                                <input type="text" name="respuesta_correcta_texto"
                                    value="{{ old('respuesta_correcta_texto', $pregunta->respuesta_correcta_texto) }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('respuesta_correcta_texto') border-red-500 @enderror">
                                @error('respuesta_correcta_texto') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                                <p class="text-xs text-gray-500 mt-1">
                                    Úsalo solo para preguntas tipo completar en frase.
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Configuración JSON</label>
                                <textarea name="configuracion_json" rows="4"
                                    class="w-full border rounded px-3 py-2 text-black @error('configuracion_json') border-red-500 @enderror">{{ old('configuracion_json', $pregunta->configuracion_json) }}</textarea>
                                @error('configuracion_json') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                                <p class="text-xs text-gray-500 mt-1">
                                    Úsalo solo si necesitas ajustar configuración avanzada.
                                </p>
                            </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Actualizar
                            </button>

                            @if(request('origen') === 'builder')
                                <a href="{{ route('capacitaciones.builder', $pregunta->evaluacion->capacitacionModulo->id_capacitacion) }}"
                                class="px-4 py-2 bg-gray-600 text-white rounded">
                                    Cancelar
                                </a>
                            @else
                                <a href="{{ route('evaluaciones.preguntas.index', $pregunta->id_evaluacion) }}"
                                class="px-4 py-2 bg-gray-600 text-white rounded">
                                    Cancelar
                                </a>
                            @endif
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>