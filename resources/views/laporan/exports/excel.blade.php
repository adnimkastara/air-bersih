@php
    $dateFrom = data_get($filters, 'date_from');
    $dateTo = data_get($filters, 'date_to');
    $desaId = data_get($filters, 'desa_id');
    $reportType = data_get($filters, 'report', $report ?? null);
    $desaFilterLabel = $desaId ? 'Desa ID '.$desaId : 'Semua Desa';

    if ($reportType === 'setoran_kecamatan' && ! $desaId) {
        $desaFilterLabel = 'Semua Desa (Laporan Kecamatan)';
    }
@endphp
<table border="1">
    <thead>
    <tr>
        <th colspan="20">Laporan {{ str($report)->replace('_', ' ')->title() }}</th>
    </tr>
    <tr>
        <th colspan="20">Filter tanggal: {{ $dateFrom ?? '-' }} s/d {{ $dateTo ?? '-' }}, Desa: {{ $desaFilterLabel }}</th>
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
