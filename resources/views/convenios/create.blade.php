<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Convenio</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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

                <form method="POST" action="{{ route('convenios.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <h3 class="text-lg font-medium">Responsable de la gestión</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input name="responsable_nombre" type="text" value="{{ old('responsable_nombre') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input name="responsable_telefono" type="text" value="{{ old('responsable_telefono') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input name="responsable_email" type="email" value="{{ old('responsable_email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Responsable del IES LUIS BRAILLE</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Departamento</label>
                                <select id="departamento_select" name="departamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Selecciona departamento</option>
                                    @foreach($departamentos ?? [] as $departamento)
                                        <option value="{{ $departamento->id }}" @selected(old('departamento_id') == $departamento->id)>{{ $departamento->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre Tutor</label>
                                <select id="tutor_select" name="tutor_id" disabled class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Selecciona tutor</option>
                                </select>
                            </div>

                            <div></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input name="tutor_telefono" type="text" value="{{ old('tutor_telefono') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input name="tutor_email" type="email" value="{{ old('tutor_email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Datos de la empresa</h3>
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700">Empresa existente (opcional)</label>
                            <select id="empresa_select" name="empresa_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Crear nueva empresa</option>
                                @foreach($empresas ?? [] as $empresaOption)
                                    <option value="{{ $empresaOption->id }}" @selected(old('empresa_id') == $empresaOption->id)>{{ $empresaOption->nombre_razon_social }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre / Razón Social</label>
                                <input name="empresa_nombre" type="text" value="{{ old('empresa_nombre') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">DNI / CIF</label>
                                <input name="empresa_dni_cif" type="text" value="{{ old('empresa_dni_cif') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Actividad</label>
                                <input name="empresa_actividad" type="text" value="{{ old('empresa_actividad') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Categoría</label>
                                <select name="categoria" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Sin categoría</option>
                                        @foreach($categorias ?? [] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('categoria') == $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                <select name="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Sin tipo</option>
                                        @foreach($tipos ?? [] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('tipo') == $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Domicilio Social</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Provincia</label>
                                <input name="domicilio_provincia" type="text" value="{{ old('domicilio_provincia') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Municipio</label>
                                <input name="domicilio_municipio" type="text" value="{{ old('domicilio_municipio') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Dirección</label>
                                <input name="domicilio_direccion" type="text" value="{{ old('domicilio_direccion') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Código Postal</label>
                                <input name="domicilio_codigo_postal" type="text" value="{{ old('domicilio_codigo_postal') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Direcciones Centro de Trabajo</h3>
                        <div id="direcciones_container" class="space-y-4 mt-3"></div>
                        <div class="mt-3">
                            <button type="button" id="add_direccion" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Añadir dirección</button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Contacto</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono 1</label>
                                <input name="contacto_telefono1" type="text" value="{{ old('contacto_telefono1') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono 2</label>
                                <input name="contacto_telefono2" type="text" value="{{ old('contacto_telefono2') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input name="contacto_email" type="email" value="{{ old('contacto_email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Representante</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIF</label>
                                <input name="representante_nif" type="text" value="{{ old('representante_nif') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                <input name="representante_nombre" type="text" value="{{ old('representante_nombre') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Apellido 1</label>
                                <input name="representante_apellido1" type="text" value="{{ old('representante_apellido1') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Apellido 2</label>
                                <input name="representante_apellido2" type="text" value="{{ old('representante_apellido2') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Tutores (puede haber más de uno)</h3>
                        <div id="tutores_container" class="space-y-4 mt-3"></div>
                        <div class="mt-3">
                            <button type="button" id="add_tutor" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Añadir tutor</button>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Horario: seleccione uno o varios horarios por defecto (16 opciones disponibles).</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium">Relación académica y observaciones</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ciclos relacionados</label>
                                <select name="ciclo_ids[]" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm min-h-40">
                                    @foreach($ciclos ?? [] as $ciclo)
                                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}{{ $ciclo->grado ? ' - '.$ciclo->grado : '' }}{{ $ciclo->departamento_nombre ? ' (' . $ciclo->departamento_nombre . ')' : '' }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Mantén pulsado Ctrl/Command para seleccionar varios.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                                <textarea name="observaciones" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('observaciones') }}</textarea>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha de firma</label>
                                <input name="fecha_firma" type="date" value="{{ old('fecha_firma') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado inicial</label>
                                <input type="text" value="borrador" disabled class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <a href="{{ route('convenios.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-black rounded-md hover:bg-indigo-700">Crear convenio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="direccion_template">
        <div class="border rounded-md p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Provincia</label>
                    <input name="direcciones[][provincia]" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Municipio</label>
                    <input name="direcciones[][municipio]" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input name="direcciones[][direccion]" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código Postal</label>
                    <input name="direcciones[][codigo_postal]" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>
            <div class="mt-3 text-right">
                <button type="button" class="remove_direccion inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-md">Eliminar</button>
            </div>
        </div>
    </template>

    <template id="tutor_template">
        <div class="border rounded-md p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
                    <input name="tutores[][nombre_completo]" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">DNI</label>
                    <input name="tutores[][dni]" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Horario (múltiple)</label>
                    <select name="tutores[][horarios][]" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @for($i = 1; $i <= 16; $i++)
                            <option value="{{ $i }}">Horario {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="mt-3 text-right">
                <button type="button" class="remove_tutor inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-md">Eliminar</button>
            </div>
        </div>
    </template>

    <script>
        const tutoresByDept = <?php echo json_encode($tutoresByDept ?? []); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const empresaSelect = document.getElementById('empresa_select');
            const empresaFields = ['empresa_nombre','empresa_dni_cif','empresa_actividad','domicilio_provincia','domicilio_municipio','domicilio_direccion','domicilio_codigo_postal','contacto_telefono1','contacto_telefono2','contacto_email'];

            function toggleEmpresaFields() {
                const useExisting = empresaSelect && empresaSelect.value !== '';
                empresaFields.forEach(name => {
                    const el = document.querySelector('[name="'+name+'"]');
                    if (!el) return;
                    el.disabled = useExisting;
                });
            }

            if (empresaSelect) {
                empresaSelect.addEventListener('change', toggleEmpresaFields);
                // init state
                toggleEmpresaFields();
            }
            const depSelect = document.getElementById('departamento_select');
            const tutorSelect = document.getElementById('tutor_select');

            depSelect?.addEventListener('change', function () {
                const val = this.value;
                tutorSelect.innerHTML = '<option value="">Selecciona tutor</option>';
                if (val && tutoresByDept[val]) {
                    tutoresByDept[val].forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t.id;
                        opt.textContent = t.name;
                        tutorSelect.appendChild(opt);
                    });
                    tutorSelect.disabled = false;
                } else {
                    tutorSelect.disabled = true;
                }
            });

            const addDireccion = document.getElementById('add_direccion');
            const direccionesContainer = document.getElementById('direcciones_container');
            const direccionTpl = document.getElementById('direccion_template');

            addDireccion?.addEventListener('click', function () {
                const node = direccionTpl.content.cloneNode(true);
                direccionesContainer.appendChild(node);
            });

            direccionesContainer.addEventListener('click', function (e) {
                if (e.target.matches('.remove_direccion')) {
                    e.target.closest('div.border')?.remove();
                }
            });

            const addTutor = document.getElementById('add_tutor');
            const tutoresContainer = document.getElementById('tutores_container');
            const tutorTpl = document.getElementById('tutor_template');

            addTutor?.addEventListener('click', function () {
                const node = tutorTpl.content.cloneNode(true);
                tutoresContainer.appendChild(node);
            });

            tutoresContainer.addEventListener('click', function (e) {
                if (e.target.matches('.remove_tutor')) {
                    e.target.closest('div.border')?.remove();
                }
            });
        });
    </script>
</x-app-layout>
