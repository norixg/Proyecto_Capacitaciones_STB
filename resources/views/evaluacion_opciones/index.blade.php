<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Opciones de la pregunta
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 rounded border border-green-300 bg-green-100 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                <strong>Pregunta:</strong> {{ $pregunta->pregunta }}
            </div>

            <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                <strong>Tipo:</strong> {{ $pregunta->tipo_pregunta }}
            </div>

            @if($pregunta->tipo_pregunta === 'opcion_unica')
                <div class="mb-4 rounded border border-yellow-300 bg-yellow-100 px-4 py-3 text-yellow-800">
                    Esta pregunta es de opción única: solo debe tener una opción correcta.
                </div>
            @endif

            @if($pregunta->tipo_pregunta === 'multiple')
                <div class="mb-4 rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                    Esta pregunta es múltiple: puede tener varias opciones correctas, pero al menos una debe ser correcta.
                </div>
            @endif

            @if($pregunta->tipo_pregunta === 'verdadero_falso')
                <div class="mb-4 rounded border border-purple-300 bg-purple-100 px-4 py-3 text-purple-800">
                    Esta pregunta es verdadero/falso: debe tener exactamente 2 opciones y solo 1 correcta.
                </div>
            @endif

            <div class="mb-4 flex flex-wrap gap-3">
                <a href="{{ route('evaluacion_preguntas.opciones.create', $pregunta->id_evaluacion_pregunta) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded">
                    Nueva opción
                </a>

                <a href="{{ route('evaluaciones.preguntas.index', $pregunta->id_evaluacion) }}"
                   class="px-4 py-2 bg-gray-600 text-white rounded">
                    Volver a preguntas
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead class="bg-gray-100 text-black">
                            <tr>
                                <th class="px-4 py-2 border">Orden</th>
                                <th class="px-4 py-2 border">Opción</th>
                                <th class="px-4 py-2 border">¿Correcta?</th>
                                <th class="px-4 py-2 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($opciones as $opcion)
                                <tr class="text-center">
                                    <td class="px-4 py-2 border">{{ $opcion->orden }}</td>
                                    <td class="px-4 py-2 border">{{ $opcion->opcion }}</td>
                                    <td class="px-4 py-2 border">{{ (int) $opcion->es_correcta === 1 ? 'Sí' : 'No' }}</td>
                                    <td class="px-4 py-2 border">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('evaluacion_opciones.edit', $opcion->id_evaluacion_opcion) }}"
                                               class="px-3 py-1 bg-yellow-500 text-white rounded">
                                                Editar
                                            </a>

                                            <form method="POST" action="{{ route('evaluacion_opciones.destroy', $opcion->id_evaluacion_opcion) }}">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        onclick="return confirm('¿Seguro que deseas eliminar esta opción?')"
                                                        class="px-3 py-1 bg-red-600 text-white rounded">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 border text-center">
                                        Esta pregunta todavía no tiene opciones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>