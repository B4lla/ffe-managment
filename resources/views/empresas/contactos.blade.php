<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Empresas contactadas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <a href="{{ route('empresas.index') }}" class="text-sm text-indigo-700 hover:text-indigo-900">Volver a empresas</a>
                    <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ $empresa->nombre_razon_social }}</h3>
                    <p class="text-sm text-gray-600">DNI/CIF: {{ $empresa->dni_cif }}</p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-md border border-gray-200 p-4 mb-6">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Registrar nuevo contacto</h4>

                    <form method="POST" action="{{ route('empresas.contactos.store', $empresa->id) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="resultado" class="block text-sm font-medium text-gray-700">Resultado</label>
                            <input
                                id="resultado"
                                name="resultado"
                                type="text"
                                value="{{ old('resultado') }}"
                                placeholder="Ejemplo: Llamada atendida / Pendiente respuesta"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >
                        </div>

                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea
                                id="observaciones"
                                name="observaciones"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            >{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Guardar contacto
                            </button>
                        </div>
                    </form>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Historial de contactos</h4>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Usuario</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Resultado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($contactos as $contacto)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ optional($contacto->fecha_contacto)->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $contacto->profesor->nombre ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $contacto->resultado ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $contacto->observaciones ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                            No hay contactos registrados para esta empresa.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $contactos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
