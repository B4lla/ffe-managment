<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Usuario') }}
        </h2>
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

                <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-5">
                    @csrf

                    @php
                        $empresaExternaRoleId = $roles->firstWhere('nombre', 'Empresa externa')?->id;
                        $selectedRoleId = old('rol_id', $puede_gestionar_todos ? null : $empresaExternaRoleId);
                        $showEmpresaField = $empresaExternaRoleId && (string) $selectedRoleId === (string) $empresaExternaRoleId;
                        $cancelRoute = $puede_gestionar_todos ? route('usuarios.index') : route('empresas.index');
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dni_cif" class="block text-sm font-medium text-gray-700">DNI/CIF</label>
                            <input id="dni_cif" name="dni_cif" type="text" value="{{ old('dni_cif') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label for="rol_id" class="block text-sm font-medium text-gray-700">Rol</label>
                            <select id="rol_id" name="rol_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Selecciona rol</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected(old('rol_id') == $rol->id)>{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="departamento_id" class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select id="departamento_id" name="departamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" @disabled($showEmpresaField)>
                            <option value="">Selecciona departamento</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id }}" @selected(old('departamento_id') == $departamento->id)>{{ $departamento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="empresa-field" @class(['hidden' => ! $showEmpresaField])>
                        <label for="empresa_id" class="block text-sm font-medium text-gray-700">Empresa asociada</label>
                        <select id="empresa_id" name="empresa_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Selecciona empresa</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id') == $empresa->id)>{{ $empresa->nombre_razon_social }}</option>
                            @endforeach
                        </select>
                        @error('empresa_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <input id="password" name="password" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="activo" name="activo" type="checkbox" value="1" @checked(old('activo', '1') == '1') class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <label for="activo" class="text-sm text-gray-700">Usuario activo</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <a href="{{ $cancelRoute }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-black rounded-md hover:bg-indigo-700">
                            Guardar usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('rol_id');
            const empresaField = document.getElementById('empresa-field');
            const empresaSelect = document.getElementById('empresa_id');
            const departamentoSelect = document.getElementById('departamento_id');
            const empresaExternaRoleId = @json($empresaExternaRoleId);

            if (! roleSelect || ! empresaField || ! empresaSelect || ! departamentoSelect || ! empresaExternaRoleId) {
                return;
            }

            const syncFields = () => {
                const isEmpresaExterna = roleSelect.value === String(empresaExternaRoleId);

                empresaField.classList.toggle('hidden', ! isEmpresaExterna);
                departamentoSelect.disabled = isEmpresaExterna;

                if (isEmpresaExterna) {
                    departamentoSelect.value = '';
                } else {
                    empresaSelect.value = '';
                }
            };

            roleSelect.addEventListener('change', syncFields);
            syncFields();
        });
    </script>
</x-app-layout>
