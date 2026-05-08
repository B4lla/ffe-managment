<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Roles</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('status'))
                    <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
                @endif

                <div class="flex justify-end mb-4">
                    <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-black rounded-md">Crear rol</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Descripción</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($roles as $rol)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $rol->nombre }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $rol->descripcion ?? '-' }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('roles.edit', $rol->id) }}" class="text-indigo-600 hover:underline mr-3">Editar</a>
                                        <form method="POST" action="{{ route('roles.destroy', $rol->id) }}" class="inline" onsubmit="return confirm('Eliminar rol?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
