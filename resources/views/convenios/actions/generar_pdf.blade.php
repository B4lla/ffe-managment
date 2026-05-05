<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Generar/Subir PDF inicial</h2>
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
                    <div><strong>Estado actual:</strong> {{ \App\Models\Convenio::estadoLabel($convenio->estado) }}</div>
                </div>

                <form method="POST" action="{{ route('convenios.generar_pdf.store', $convenio->id) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">PDF del convenio</label>
                        <input name="pdf" type="file" accept="application/pdf,.pdf" required class="mt-1 block w-full text-sm">
                    </div>

                    <label class="flex items-start gap-3 text-sm text-gray-700">
                        <input name="activar_directamente" value="1" type="checkbox" class="mt-1 rounded border-gray-300">
                        <span>Este PDF ya esta firmado por el centro y deja el convenio en vigor.</span>
                    </label>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha de firma si queda en vigor</label>
                        <input name="fecha_firma" type="date" value="{{ old('fecha_firma', optional($convenio->fecha_firma)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('convenios.show', $convenio->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Subir PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
