<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Módulos de la capacitación: {{ $capacitacion->capacitacion }}
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

            <div class="mb-4 flex flex-wrap gap-3">
                <a href="{{ route('capacitaciones.modulos.create', $capacitacion->id_capacitacion) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded">
                    Nuevo módulo
                </a>

                <a href="{{ route('capacitaciones.index') }}"
                   class="px-4 py-2 bg-gray-600 text-white rounded">
                    Volver a capacitaciones
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead class="bg-gray-100 text-black">
                            <tr>
                                <th class="px-4 py-2 border">Orden</th>
                                <th class="px-4 py-2 border">Título</th>
                                <th class="px-4 py-2 border">Duración</th>
                                <th class="px-4 py-2 border">Requiere evaluación</th>
                                <th class="px-4 py-2 border">% Aprobación</th>
                                <th class="px-4 py-2 border">Estado</th>
                                <th class="px-4 py-2 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modulos as $modulo)
                                <tr class="text-center">
                                    <td class="px-4 py-2 border">{{ $modulo->orden }}</td>
                                    <td class="px-4 py-2 border">{{ $modulo->titulo }}</td>
                                    <td class="px-4 py-2 border">{{ $modulo->duracion_horas ?: '-' }}</td>
                                    <td class="px-4 py-2 border">{{ (int) $modulo->requiere_evaluacion === 1 ? 'Sí' : 'No' }}</td>
                                    <td class="px-4 py-2 border">{{ $modulo->porcentaje_aprobacion ?: '-' }}</td>
                                    <td class="px-4 py-2 border">
                                        @if((int) $modulo->estado === 1)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Activo</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('capacitacion_modulos.edit', $modulo->id_capacitacion_modulo) }}"
                                               class="px-3 py-1 bg-yellow-500 text-white rounded">
                                                Editar
                                            </a>

                                            <a href="{{ route('capacitacion_modulos.recursos.index', $modulo->id_capacitacion_modulo) }}"
                                                class="px-3 py-1 bg-indigo-600 text-white rounded">
                                                Recursos
                                            </a>

                                            <a href="{{ route('capacitacion_modulos.evaluaciones.index', $modulo->id_capacitacion_modulo) }}"
                                                class="px-3 py-1 bg-purple-600 text-white rounded">
                                                Evaluaciones
                                            </a>

                                            <form method="POST"
                                                action="{{ route('capacitacion_modulos.destroy', $modulo->id_capacitacion_modulo) }}"
                                                onsubmit="return confirm('¿Eliminar este módulo y todo su contenido interno? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="px-3 py-1 bg-red-700 text-white rounded">
                                                    Eliminar
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('capacitacion_modulos.toggleEstado', $modulo->id_capacitacion_modulo) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        onclick="return confirm('¿Seguro que deseas {{ (int) $modulo->estado === 1 ? 'inactivar' : 'activar' }} este módulo?')"
                                                        class="px-3 py-1 {{ (int) $modulo->estado === 1 ? 'bg-red-600' : 'bg-green-600' }} text-white rounded">
                                                    {{ (int) $modulo->estado === 1 ? 'Inactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 border text-center">
                                        Esta capacitación todavía no tiene módulos registrados.
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