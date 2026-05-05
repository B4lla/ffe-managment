<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Firmar por el centro</h2>
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
                            Descargar PDF a firmar
                        </a>
                    </div>

                    <form method="POST" action="{{ route('convenios.firmar_centro.store', $convenio->id) }}" enctype="multipart/form-data" class="space-y-4 border-t pt-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PDF firmado por el centro</label>
                            <input name="pdf" type="file" accept="application/pdf,.pdf" required class="mt-1 block w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha firma</label>
                            <input name="fecha_firma" type="date" value="{{ old('fecha_firma', optional($convenio->fecha_firma)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Subir firma centro</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('convenios.firmar_centro.rechazar', $convenio->id) }}" class="space-y-4 border-t pt-5 mt-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Motivo de rechazo</label>
                            <textarea name="motivo_error" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Marcar incorrecto</button>
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
