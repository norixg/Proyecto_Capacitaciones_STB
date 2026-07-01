<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Crear pregunta para: {{ $evaluacion->titulo }}
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

                    <form method="POST" action="{{ route('evaluaciones.preguntas.store', $evaluacion->id_evaluacion) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block mb-1">Pregunta</label>
                            <textarea name="pregunta" rows="4"
                                class="w-full border rounded px-3 py-2 text-black @error('pregunta') border-red-500 @enderror">{{ old('pregunta') }}</textarea>
                            @error('pregunta') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Mínimo 3 caracteres.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block mb-1">Tipo de pregunta</label>
                                <select name="tipo_pregunta"
                                    class="w-full border rounded px-3 py-2 text-black @error('tipo_pregunta') border-red-500 @enderror">
                                    <option value="">Seleccione</option>
                                    <option value="opcion_unica" {{ old('tipo_pregunta') == 'opcion_unica' ? 'selected' : '' }}>Opción única</option>
                                    <option value="checklist_guiado" {{ old('tipo_pregunta') == 'checklist_guiado' ? 'selected' : '' }}>Opción múltiple</option>
                                    <option value="verdadero_falso" {{ old('tipo_pregunta') == 'verdadero_falso' ? 'selected' : '' }}>Verdadero/Falso</option>
                                </select>
                                @error('tipo_pregunta') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Puntaje</label>
                                <input type="number" step="0.01" name="puntaje" value="{{ old('puntaje', '1') }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('puntaje') border-red-500 @enderror">
                                @error('puntaje') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1">Orden</label>
                                <input type="number" name="orden" value="{{ old('orden') }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('orden') border-red-500 @enderror">
                                @error('orden') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block mb-1">Estado</label>
                                <select name="activa"
                                    class="w-full border rounded px-3 py-2 text-black @error('activa') border-red-500 @enderror">
                                    <option value="1" {{ old('activa', '1') == '1' ? 'selected' : '' }}>Activa</option>
                                    <option value="0" {{ old('activa') == '0' ? 'selected' : '' }}>Inactiva</option>
                                </select>
                                @error('activa') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Guardar
                            </button>

                            <a href="{{ route('evaluaciones.preguntas.index', $evaluacion->id_evaluacion) }}" class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>