<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="flex justify-end mb-4">
                    @if($puede_gestionar)
                        <a href="{{ route('usuarios.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-black border-2 border-black rounded-md hover:bg-indigo-700">
                            Crear usuario
                        </a>
                    @endif
                </div>

                <form method="GET" action="{{ route('usuarios.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div>
                        <label for="rol_id" class="block text-sm font-medium text-gray-700">Rol</label>
                        <select id="rol_id" name="rol_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(($filtros['rol_id'] ?? '') == $rol->id)>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="departamento_id" class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select id="departamento_id" name="departamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id }}" @selected(($filtros['departamento_id'] ?? '') == $departamento->id)>
                                    {{ $departamento->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="activo" class="block text-sm font-medium text-gray-700">Estado</label>
                        <select id="activo" name="activo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            <option value="1" @selected(($filtros['activo'] ?? '') === '1')>Activos</option>
                            <option value="0" @selected(($filtros['activo'] ?? '') === '0')>Inactivos</option>
                        </select>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email exacto</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ $filtros['email'] ?? '' }}"
                            placeholder="usuario@dominio.com"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        >
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Filtrar
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                            Limpiar
                        </a>
                    </div>
                </form>

                <div class="mb-4 text-sm text-gray-600">
                    Mostrando {{ $usuarios->count() }} de {{ $usuarios->total() }} usuarios
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">DNI/CIF</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rol</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Departamento</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Alta</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $usuario->nombre }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $usuario->email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $usuario->dni_cif ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ optional($usuario->rol)->nombre ?? 'Sin rol' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ optional($usuario->departamento)->nombre ?? 'Sin departamento' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($usuario->activo)
                                            <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-medium">Activo</span>
                                        @else
                                            <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-medium">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ optional($usuario->created_at)->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No hay usuarios para los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
