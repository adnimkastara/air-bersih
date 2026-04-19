<table border="1">
    <thead>
    <tr>
        <th colspan="20">Laporan {{ str($report)->replace('_', ' ')->title() }}</th>
    </tr>
    <tr>
        <th colspan="20">Filter tanggal: {{ $filters['date_from'] ?? '-' }} s/d {{ $filters['date_to'] ?? '-' }}, Desa: {{ $filters['desa_id'] ?? 'Semua' }}</th>
    </tr>
    <tr>
        @foreach(array_keys($rows[0] ?? ['data' => 'Tidak ada data']) as $column)
            <th>{{ str($column)->replace('_', ' ')->title() }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @forelse($rows as $row)
        <tr>
            @foreach($row as $value)
                <td>{{ $value }}</td>
            @endforeach
        </tr>
    @empty
        <tr>
            <td>Tidak ada data</td>
        </tr>
    @endforelse
    </tbody>
</table>
