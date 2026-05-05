<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Validar firma de empresa</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6 text-sm text-gray-700">
                    <div><strong>Empresa:</strong> {{ $convenio->empresa->nombre_razon_social }}</div>
                    <div><strong>Estado:</strong> {{ \App\Models\Convenio::estadoLabel($convenio->estado) }}</div>
                </div>

                @if($firmadoEmpresa)
                    <div class="mb-6">
                        <a href="{{ route('convenios.documentos.descargar', [$convenio->id, $firmadoEmpresa->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-black">
                            Descargar PDF firmado por empresa
                        </a>
                    </div>

                    <form method="POST" action="{{ route('convenios.validar_firma.store', $convenio->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Decision</label>
                            <select name="decision" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="ok">Firma correcta, enviar a direccion</option>
                                <option value="error">Firma/documento incorrecto</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Motivo si es incorrecto</label>
                            <textarea name="motivo_error" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar decision</button>
                        </div>
                    </form>
                @else
                    <div class="rounded-md bg-yellow-50 p-3 text-sm text-yellow-800">
                        No hay PDF firmado por empresa valido.
                    </div>
                @endif

                <div class="mt-6">
                    <a href="{{ route('convenios.show', $convenio->id) }}" class="text-sm text-gray-600 hover:underline">Volver al convenio</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
