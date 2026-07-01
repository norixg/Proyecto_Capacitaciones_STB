<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar opción
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

                    <form method="POST" action="{{ route('evaluacion_opciones.update', $opcion->id_evaluacion_opcion) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="origen" value="{{ request('origen') }}">

                        <div class="mb-4">
                            <label class="block mb-1">Texto de la opción</label>
                            <textarea name="opcion" rows="3"
                                class="w-full border rounded px-3 py-2 text-black @error('opcion') border-red-500 @enderror">{{ old('opcion', $opcion->opcion) }}</textarea>
                            @error('opcion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block mb-1">¿Es correcta?</label>
                                <select name="es_correcta"
                                    class="w-full border rounded px-3 py-2 text-black @error('es_correcta') border-red-500 @enderror">
                                    <option value="0" {{ old('es_correcta', $opcion->es_correcta) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('es_correcta', $opcion->es_correcta) == '1' ? 'selected' : '' }}>Sí</option>
                                </select>
                                @error('es_correcta') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block mb-1">Orden</label>
                                <input type="number" name="orden" value="{{ old('orden', $opcion->orden) }}"
                                    class="w-full border rounded px-3 py-2 text-black @error('orden') border-red-500 @enderror">
                                @error('orden') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Actualizar
                            </button>

                            @if(request('origen') === 'builder')
                                <a href="{{ route('capacitaciones.builder', $opcion->pregunta->evaluacion->capacitacionModulo->id_capacitacion) }}"
                                class="px-4 py-2 bg-gray-600 text-white rounded">
                                    Cancelar
                                </a>
                            @else
                                <a href="{{ route('evaluacion_preguntas.opciones.index', $opcion->id_evaluacion_pregunta) }}"
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