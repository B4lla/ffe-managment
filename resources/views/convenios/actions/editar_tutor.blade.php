<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar tutor asignado</h2>
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

                <form method="POST" action="{{ route('convenios.editar_tutor.update', $convenio->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Departamento</label>
                            <select id="departamento_select" name="departamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Selecciona departamento</option>
                                @foreach($departamentos as $departamento)
                                    <option value="{{ $departamento->id }}" @selected((string) old('departamento_id', $departamentoActual) === (string) $departamento->id)>{{ $departamento->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre del tutor</label>
                            <select id="tutor_select" name="tutor_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Selecciona primero un departamento</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telefono tutor</label>
                            <input name="tutor_telefono" type="text" value="{{ old('tutor_telefono', $convenio->resp_ies_telefono) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email tutor</label>
                            <input name="tutor_email" type="email" value="{{ old('tutor_email', $convenio->resp_ies_email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('convenios.show', $convenio->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar tutor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const tutoresByDept = @json($tutoresByDept ?? []);
        const selectedTutor = String(@json((string) old('tutor_id', $convenio->profesor_id)));

        document.addEventListener('DOMContentLoaded', function () {
            const depSelect = document.getElementById('departamento_select');
            const tutorSelect = document.getElementById('tutor_select');

            function loadTutors() {
                const deptId = depSelect?.value || '';
                const tutors = tutoresByDept[deptId] || [];

                tutorSelect.innerHTML = '';

                if (!deptId) {
                    tutorSelect.appendChild(new Option('Selecciona primero un departamento', ''));
                    tutorSelect.disabled = true;
                    return;
                }

                if (tutors.length === 0) {
                    tutorSelect.appendChild(new Option('No hay tutores en este departamento', ''));
                    tutorSelect.disabled = true;
                    return;
                }

                tutorSelect.appendChild(new Option('Selecciona tutor', ''));
                tutors.forEach(t => {
                    const opt = new Option(t.name, t.id);
                    if (String(t.id) === selectedTutor) {
                        opt.selected = true;
                    }
                    tutorSelect.appendChild(opt);
                });
                tutorSelect.disabled = false;
            }

            depSelect?.addEventListener('change', loadTutors);
            loadTutors();
        });
    </script>
</x-app-layout>
