@extends('layouts.admin')

@section('title', 'Daftar Desa')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Daftar Desa',
        'subtitle' => 'Kelola data desa berdasarkan kecamatan.',
        'actions' => '<a class="btn btn-primary" href="'.route('desa.create').'"><i class="bi bi-plus-lg"></i> Tambah Desa</a>'
    ])

    @include('layouts.partials.alerts')

    <div class="card table-wrap">
        <table>
            <thead>
            <tr>
                <th>Nama Desa</th>
                <th>Kecamatan</th>
                <th style="width:170px;">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($desas as $desa)
                <tr>
                    <td>{{ $desa->name }}</td>
                    <td>{{ $desa->kecamatan->name }}</td>
                    <td>
                        <div style="display:flex;gap:8px;">
                            <a href="{{ route('desa.edit', $desa) }}" class="btn btn-outline btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('desa.destroy', $desa) }}" method="POST" onsubmit="return confirm('Hapus desa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">@include('layouts.partials.empty-state', ['message' => 'Belum ada data desa.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
