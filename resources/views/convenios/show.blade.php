<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Convenio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @php
                    $user = auth()->user();
                    $role = strtolower(trim((string) optional($user?->rol)->nombre ?? ''));
                    $isAssignedTutor = $user && isset($convenio->profesor_id) && $user->id === $convenio->profesor_id;
                    $canManageConvenio = in_array($role, ['administrador', 'coordinador ffe', 'profesor tutor', 'secretaria', 'direccion', 'empresa'], true);
                @endphp

                <div class="mb-4 flex gap-2 flex-wrap">
                    @if($canManageConvenio && in_array($convenio->estado, ['pendiente_datos', 'nuevo_solicitado'], true) && in_array($role, ['administrador', 'coordinador ffe', 'profesor tutor'], true))
                        <a href="{{ route('convenios.datos', $convenio->id) }}" class="inline-flex items-center px-3 py-1 bg-green-600 text-black rounded-md">Meter datos iniciales</a>
                    @endif

                    @if(in_array($role, ['secretaria', 'administrador'], true) && in_array($convenio->estado, ['pendiente_secretaria', 'nuevo_solicitado'], true))
                        <a href="{{ route('convenios.generar_pdf', $convenio->id) }}" class="inline-flex items-center px-3 py-1 bg-yellow-600 text-black rounded-md">Generar/Subir PDF inicial</a>
                    @endif

                    @if($role === 'empresa externa' && in_array($convenio->estado, ['pendiente_firma_empresa', 'firmado_centro'], true))
                        <a href="{{ route('convenios.firmar_empresa', $convenio->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 text-black rounded-md">Descargar y firmar (Empresa)</a>
                    @endif

                    @if($isAssignedTutor && in_array($convenio->estado, ['pendiente_validacion_tutor', 'firmado_empresa'], true))
                        <a href="{{ route('convenios.validar_firma', $convenio->id) }}" class="inline-flex items-center px-3 py-1 bg-purple-600 text-black rounded-md">Validar firma de empresa</a>
                    @endif

                    @if($role === 'direccion' && in_array($convenio->estado, ['pendiente_firma_direccion', 'validado_tutor'], true))
                        <a href="{{ route('convenios.firmar_centro', $convenio->id) }}" class="inline-flex items-center px-3 py-1 bg-red-600 text-black rounded-md">Firmar por el centro</a>
                    @endif

                    @if($role === 'empresa externa' && ($convenio->estado === 'en_vigor'))
                        <a href="{{ route('convenios.descargar_firmado', $convenio->id) }}" class="inline-flex items-center px-3 py-1 bg-gray-600 text-black rounded-md">Descargar convenio firmado</a>
                    @endif
                </div>

                @if(! $canManageConvenio)
                    <div class="mb-4 rounded-md bg-gray-50 p-3 text-sm text-gray-600">
                        No hay acciones disponibles para tu rol en este convenio.
                    </div>
                @endif

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Detalle del convenio</h3>
                    <a href="{{ route('convenios.index') }}" class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Volver</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Empresa</h4>
                        <div class="mt-2 text-sm text-gray-800">
                            <div><strong>Nombre:</strong> {{ $convenio->empresa->nombre_razon_social ?? '-' }}</div>
                            <div><strong>DNI/CIF:</strong> {{ $convenio->empresa->dni_cif ?? '-' }}</div>
                            <div><strong>Categoria:</strong> {{ $convenio->empresa->categoria ?? '-' }}</div>
                            <div><strong>Tipo:</strong> {{ $convenio->empresa->tipo ?? '-' }}</div>
                            <div><strong>Email:</strong> {{ $convenio->empresa->email ?? '-' }}</div>
                            <div><strong>Telefono:</strong> {{ $convenio->empresa->telefono1 ?? $convenio->empresa->telefono2 ?? '-' }}</div>
                            <div><strong>Ubicacion:</strong> {{ trim(($convenio->empresa->municipio ?? '').' '.($convenio->empresa->provincia ?? '')) ?: '-' }}</div>
                            <div><strong>Alta empresa:</strong> {{ optional($convenio->empresa->created_at)->format('d/m/Y') ?? '-' }}</div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-600">Convenio</h4>
                        <div class="mt-2 text-sm text-gray-800">
                            <div><strong>ID:</strong> {{ $convenio->id }}</div>
                            <div><strong>Fecha inicio:</strong> {{ optional($convenio->fecha_inicio)->format('d/m/Y') ?? '-' }}</div>
                            <div><strong>Fecha fin:</strong> {{ optional($convenio->fecha_fin)->format('d/m/Y') ?? '-' }}</div>
                            <div><strong>Estado:</strong> {{ $convenio->estado ?? '-' }}</div>
                            <div class="mt-3"><strong>Observaciones:</strong>
                                <div class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $convenio->observaciones ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
