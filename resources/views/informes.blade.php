<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Informes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('informes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label for="report_type" class="block text-sm font-medium text-gray-700">Tipo de informe</label>
                        <select id="report_type" name="report_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($reportTypes as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['report_type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="departamento_id" class="block text-sm font-medium text-gray-700">Familia / Departamento</label>
                        <select id="departamento_id" name="departamento_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Selecciona una familia</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id }}" @selected(($filters['departamento_id'] ?? null) == $departamento->id)>{{ $departamento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="cycles_filter">
                        <label for="ciclo_ids" class="block text-sm font-medium text-gray-700">Ciclos</label>
                        <select id="ciclo_ids" name="ciclo_ids[]" multiple class="mt-1 block w-full min-h-32 border-gray-300 rounded-md shadow-sm">
                            @foreach($filters['available_cycles'] ?? [] as $cycle)
                                <option value="{{ $cycle['id'] }}" @selected(in_array($cycle['id'], $filters['ciclo_ids'] ?? [], true))>{{ $cycle['label'] }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Usa Ctrl/Command para seleccionar varios.</p>
                    </div>

                    <div id="course_filter">
                        <label for="curso_anio" class="block text-sm font-medium text-gray-700">Curso</label>
                        <select id="curso_anio" name="curso_anio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Selecciona curso</option>
                            @foreach($filters['available_course_years'] ?? [] as $year)
                                <option value="{{ $year }}" @selected(($filters['curso_anio'] ?? null) == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Generar informe
                        </button>
                        <a href="{{ route('informes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                            Limpiar
                        </a>
                    </div>
                </form>

                @if($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6 rounded-md bg-gray-50 p-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $report['title'] }}</h3>
                    @if(!empty($report['subtitle']))
                        <p class="text-sm text-gray-600 mt-1">{{ $report['subtitle'] }}</p>
                    @endif
                    @if(!empty($report['message']))
                        <p class="text-sm text-gray-600 mt-3">{{ $report['message'] }}</p>
                    @endif
                </div>

                @if($report['rows'] !== [])
                    <div class="flex justify-end mb-4">
                        <a
                            href="{{ route('informes.export', request()->query()) }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                        >
                            Exportar Excel
                        </a>
                    </div>

                    @include('informes.partials.table', ['report' => $report])
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reportType = document.getElementById('report_type');
            const cyclesFilter = document.getElementById('cycles_filter');
            const courseFilter = document.getElementById('course_filter');

            function toggleReportFilters() {
                const value = reportType ? reportType.value : '';
                if (cyclesFilter) {
                    cyclesFilter.style.display = value === 'ciclos_familia' ? 'block' : 'none';
                }
                if (courseFilter) {
                    courseFilter.style.display = value === 'curso_familia' ? 'block' : 'none';
                }
            }

            reportType?.addEventListener('change', toggleReportFilters);
            toggleReportFilters();
        });
    </script>
</x-app-layout>
