<table>
    <tr>
        <td colspan="{{ 3 + count($report['columns']) }}"><strong>{{ $report['title'] }}</strong></td>
    </tr>
    @if(!empty($report['subtitle']))
        <tr>
            <td colspan="{{ 3 + count($report['columns']) }}">{{ $report['subtitle'] }}</td>
        </tr>
    @endif
    <tr></tr>
    <tr>
        <th><strong>EMPRESA</strong></th>
        <th><strong>Tutor Centro</strong></th>
        @foreach($report['columns'] as $column)
            <th><strong>{{ $column['label'] }}</strong></th>
        @endforeach
        <th><strong>Observaciones</strong></th>
    </tr>
    @foreach($report['rows'] as $row)
        <tr>
            <td>{{ $row['empresa'] }}</td>
            <td>{{ $row['tutor_centro'] }}</td>
            @foreach($report['columns'] as $column)
                <td>{{ $row['cells'][$column['id']] ?? '' }}</td>
            @endforeach
            <td>{{ $row['observaciones'] }}</td>
        </tr>
    @endforeach
</table>
