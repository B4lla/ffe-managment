<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tareas pendientes</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
                @endif

                @if($tareas->isEmpty())
                    <div class="text-sm text-gray-600">No tienes tareas pendientes.</div>
                @else
                    <ul class="space-y-4">
                        @foreach($tareas as $tarea)
                            <li class="border p-4 rounded-md flex flex-col gap-4 md:flex-row md:justify-between md:items-start">
                                <div class="space-y-1">
                                    <div class="font-semibold">{{ $tarea->tipo_tarea }}</div>
                                    <div class="text-sm text-gray-600">{{ $tarea->descripcion }}</div>
                                    <div class="text-xs text-gray-400">Creada: {{ optional($tarea->created_at)->format('d/m/Y H:i') }}</div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if(!empty($tarea->action_url))
                                        <a href="{{ $tarea->action_url }}" class="inline-flex items-center px-3 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                                            {{ $tarea->action_label ?? 'Abrir' }}
                                        </a>
                                    @endif

                                    @if($tarea->convenio_id)
                                        <a href="{{ route('convenios.show', $tarea->convenio_id) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                            Ver convenio
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('tareas_pendientes.completar', $tarea->id) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Marcar completada</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
