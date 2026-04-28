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
                            <li class="border p-4 rounded-md flex justify-between items-start">
                                <div>
                                    <div class="font-semibold">{{ $tarea->tipo_tarea }}</div>
                                    <div class="text-sm text-gray-600">{{ $tarea->descripcion }}</div>
                                    <div class="text-xs text-gray-400">Creada: {{ optional($tarea->created_at)->format('d/m/Y H:i') }}</div>
                                </div>
                                <form method="POST" action="{{ route('tareas_pendientes.completar', $tarea->id) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Marcar completada</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
