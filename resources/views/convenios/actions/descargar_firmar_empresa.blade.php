<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Descargar y firmar empresa</h2>
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

                @if($provisional)
                    <div class="mb-6 rounded-md border border-gray-200 p-4">
                        <h3 class="text-base font-semibold text-gray-900">1. Descargar convenio inicial</h3>
                        <a href="{{ route('convenios.documentos.descargar', [$convenio->id, $provisional->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-black">
                            Descargar PDF inicial
                        </a>
                    </div>

                    <form method="POST" action="{{ route('convenios.firmar_empresa.store', $convenio->id) }}" enctype="multipart/form-data" class="space-y-4 rounded-md border border-indigo-200 bg-indigo-50 p-4">
                        @csrf
                        <h3 class="text-base font-semibold text-gray-900">2. Subir convenio firmado por la empresa</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PDF firmado por la empresa</label>
                            <input name="pdf" type="file" accept="application/pdf,.pdf" required class="mt-1 block w-full rounded-md border border-gray-300 bg-white p-2 text-sm">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Confirmar firma y subir PDF firmado</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('convenios.reportar_error_empresa', $convenio->id) }}" class="space-y-4 rounded-md border border-red-200 bg-red-50 p-4 mt-5">
                        @csrf
                        <input type="hidden" name="tipo_documento" value="provisional">
                        <h3 class="text-base font-semibold text-gray-900">Indicar error en vez de firmar</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Indicar error en el PDF inicial</label>
                            <textarea name="motivo_error" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Marcar como erroneo</button>
                        </div>
                    </form>
                @else
                    <div class="rounded-md bg-yellow-50 p-3 text-sm text-yellow-800">
                        No hay PDF inicial valido para descargar.
                    </div>
                @endif

                <div class="mt-6">
                    <a href="{{ route('convenios.show', $convenio->id) }}" class="text-sm text-gray-600 hover:underline">Volver al convenio</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
