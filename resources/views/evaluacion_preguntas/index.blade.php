<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Preguntas de la evaluación: {{ $evaluacion->titulo }}
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
                <strong>Módulo:</strong> {{ $evaluacion->capacitacionModulo?->titulo }}
            </div>

            <div class="mb-4 rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                Antes de usar una evaluación en el lado del usuario, cada pregunta debe quedar bien configurada según su tipo y con opciones válidas.
            </div>

            <div class="mb-4 flex flex-wrap gap-3">
                <a href="{{ route('evaluaciones.preguntas.create', $evaluacion->id_evaluacion) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded">
                    Nueva pregunta
                </a>

                <a href="{{ route('capacitacion_modulos.evaluaciones.index', $evaluacion->id_capacitacion_modulo) }}"
                   class="px-4 py-2 bg-gray-600 text-white rounded">
                    Volver a evaluaciones
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead class="bg-gray-100 text-black">
                            <tr>
                                <th class="px-4 py-2 border">Orden</th>
                                <th class="px-4 py-2 border">Pregunta</th>
                                <th class="px-4 py-2 border">Tipo</th>
                                <th class="px-4 py-2 border">Puntaje</th>
                                <th class="px-4 py-2 border">Estado</th>
                                <th class="px-4 py-2 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($preguntas as $pregunta)
                                <tr class="text-center">
                                    <td class="px-4 py-2 border">{{ $pregunta->orden }}</td>
                                    <td class="px-4 py-2 border">{{ $pregunta->pregunta }}</td>
                                    <td class="px-4 py-2 border">{{ $pregunta->tipo_pregunta }}</td>
                                    <td class="px-4 py-2 border">{{ $pregunta->puntaje }}</td>
                                    <td class="px-4 py-2 border">
                                        @if((int) $pregunta->activa === 1)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Activa</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('evaluacion_preguntas.edit', $pregunta->id_evaluacion_pregunta) }}"
                                               class="px-3 py-1 bg-yellow-500 text-white rounded">
                                                Editar
                                            </a>

                                            <a href="{{ route('evaluacion_preguntas.opciones.index', $pregunta->id_evaluacion_pregunta) }}"
                                                class="px-3 py-1 bg-purple-600 text-white rounded">
                                                Opciones
                                            </a>

                                            <form method="POST" action="{{ route('evaluacion_preguntas.toggleEstado', $pregunta->id_evaluacion_pregunta) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        onclick="return confirm('¿Seguro que deseas {{ (int) $pregunta->activa === 1 ? 'inactivar' : 'activar' }} esta pregunta?')"
                                                        class="px-3 py-1 {{ (int) $pregunta->activa === 1 ? 'bg-red-600' : 'bg-green-600' }} text-white rounded">
                                                    {{ (int) $pregunta->activa === 1 ? 'Inactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 border text-center">
                                        Esta evaluación todavía no tiene preguntas registradas.
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