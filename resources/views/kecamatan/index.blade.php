@extends('layouts.admin')

@section('title', 'Daftar Kecamatan')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Daftar Kecamatan',
        'subtitle' => 'Master data kecamatan untuk administrasi wilayah.',
        'actions' => Route::has('kecamatan.create') ? '<a class="btn btn-primary" href="'.route('kecamatan.create').'"><i class="bi bi-plus-lg"></i> Tambah Kecamatan</a>' : null
    ])

    @include('layouts.partials.alerts')

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Nama Kecamatan</th><th style="width:140px;">Aksi</th></tr></thead>
            <tbody>
            @forelse($kecamatans as $kecamatan)
                <tr>
                    <td>{{ $kecamatan->name }}</td>
                    <td>@if(Route::has('kecamatan.edit'))<a href="{{ route('kecamatan.edit', $kecamatan) }}" class="btn btn-outline btn-sm">Edit</a>@else-@endif</td>
                </tr>
            @empty
                <tr><td colspan="2">@include('layouts.partials.empty-state', ['message' => 'Belum ada data kecamatan.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
