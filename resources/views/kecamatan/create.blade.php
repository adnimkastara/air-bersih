@extends('layouts.admin')

@section('title', 'Tambah Kecamatan')

@section('content')
    @include('layouts.partials.page-header', ['title' => 'Tambah Kecamatan'])
    @include('layouts.partials.alerts')

    <div class="card" style="max-width:700px;">
        <form method="POST" action="{{ route('kecamatan.store') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label>Nama Kecamatan</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('kecamatan.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
