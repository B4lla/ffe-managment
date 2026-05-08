<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Usuario</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                <form method="POST" action="{{ route('usuarios.update', $user->id) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input name="nombre" type="text" value="{{ old('nombre', $user->nombre) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">DNI/CIF</label>
                            <input name="dni_cif" type="text" value="{{ old('dni_cif', $user->dni_cif) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rol</label>
                            <select id="rol_id" name="rol_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Selecciona rol</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected(old('rol_id', $user->rol_id) == $rol->id)>{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select id="departamento_id" name="departamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Selecciona departamento</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id }}" @selected(old('departamento_id', $user->departamento_id) == $departamento->id)>{{ $departamento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="empresa-field" class="mt-4 {{ (old('rol_id', $user->rol_id) == (int) ($roles->firstWhere('nombre', 'Empresa externa')?->id ?? 0)) ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700">Empresa asociada</label>
                        <select id="empresa_id" name="empresa_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Selecciona empresa</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id', $user->empresa_id) == $empresa->id)>{{ $empresa->nombre_razon_social }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contraseña (dejar en blanco para no cambiar)</label>
                            <input name="password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                            <input name="password_confirmation" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="activo" name="activo" type="checkbox" value="1" @checked(old('activo', $user->activo) == '1') class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <label for="activo" class="text-sm text-gray-700">Usuario activo</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <a href="{{ route('usuarios.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-black rounded-md hover:bg-indigo-700">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('rol_id');
            const empresaField = document.getElementById('empresa-field');
            const empresaExternaRoleId = @json($roles->firstWhere('nombre', 'Empresa externa')?->id ?? null);

            if (! roleSelect || ! empresaField || ! empresaExternaRoleId) {
                return;
            }

            const syncFields = () => {
                const isEmpresaExterna = roleSelect.value === String(empresaExternaRoleId);
                empresaField.classList.toggle('hidden', ! isEmpresaExterna);
            };

            roleSelect.addEventListener('change', syncFields);
            syncFields();
        });
    </script>
</x-app-layout>
