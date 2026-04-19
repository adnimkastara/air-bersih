@extends('layouts.admin')

@section('title', 'Edit Kecamatan')

@section('content')
    @include('layouts.partials.page-header', ['title' => 'Edit Kecamatan'])
    @include('layouts.partials.alerts')

    <div class="card" style="max-width:700px;">
        <form method="POST" action="{{ route('kecamatan.update', $kecamatan) }}">
            @csrf
            @method('PUT')
            <div style="margin-bottom:14px;">
                <label>Nama Kecamatan</label>
                <input type="text" name="name" value="{{ old('name', $kecamatan->name) }}" required>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('kecamatan.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
