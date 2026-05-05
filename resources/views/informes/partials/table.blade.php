@if($report['rows'] !== [])
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Empresa</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tutor Centro</th>
                    @foreach($report['columns'] as $column)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ $column['label'] }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Observaciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($report['rows'] as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['empresa'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['tutor_centro'] }}</td>
                        @foreach($report['columns'] as $column)
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row['cells'][$column['id']] ?? '' }}</td>
                        @endforeach
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['observaciones'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
