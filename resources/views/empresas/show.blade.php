<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Empresa</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('status'))
                    <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
                @endif

                <div class="mb-4 flex gap-2">
                    @if($puede_crear ?? false)
                        <a href="{{ route('empresas.edit', $empresa->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-black rounded-md">Editar</a>
                    @endif
                    <a href="{{ route('empresas.contactos.index', $empresa->id) }}" class="inline-flex items-center px-3 py-1 bg-gray-200 text-black rounded-md">Contactos</a>
                    <a href="{{ route('empresas.index') }}" class="inline-flex items-center px-3 py-1 bg-gray-100 text-black rounded-md">Volver</a>
                </div>

                <h3 class="text-lg font-medium text-gray-900">Detalle</h3>
                <div class="mt-4 text-sm text-gray-800">
                    <div><strong>Nombre:</strong> {{ $empresa->nombre_razon_social }}</div>
                    <div><strong>DNI/CIF:</strong> {{ $empresa->dni_cif }}</div>
                    <div><strong>Email:</strong> {{ $empresa->email ?? '-' }}</div>
                    <div><strong>Telefono:</strong> {{ $empresa->telefono1 ?? $empresa->telefono2 ?? '-' }}</div>
                    <div><strong>Ubicacion:</strong> {{ trim(($empresa->municipio ?? '').' '.($empresa->provincia ?? '')) ?: '-' }}</div>
                </div>

                <div class="mt-8 border-t pt-6">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-medium text-gray-900">Tareas pendientes</h3>
                        @if($esEmpresaExterna ?? false)
                            <a href="{{ route('tareas_pendientes.index') }}" class="text-sm text-indigo-700 hover:underline">Ver todas</a>
                        @endif
                    </div>

                    @if(($tareasPendientes ?? collect())->isEmpty())
                        <div class="mt-4 text-sm text-gray-600">No hay tareas pendientes para esta empresa.</div>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach($tareasPendientes as $tarea)
                                <div class="rounded-md border border-gray-200 p-4">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div class="space-y-1">
                                            <div class="font-semibold text-gray-900">{{ $tarea->tipo_tarea }}</div>
                                            <div class="text-sm text-gray-600">{{ $tarea->descripcion }}</div>
                                            <div class="text-xs text-gray-400">
                                                Creada: {{ optional($tarea->created_at)->format('d/m/Y H:i') }}
                                                @unless($esEmpresaExterna ?? false)
                                                    - Usuario: {{ $tarea->usuario->nombre ?? '-' }}
                                                @endunless
                                            </div>
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
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
