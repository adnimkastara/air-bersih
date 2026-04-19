@extends('layouts.admin')

@section('title', 'Edit Desa')

@section('content')
    @include('layouts.partials.page-header', ['title' => 'Edit Desa', 'subtitle' => 'Perbarui data desa.'])
    @include('layouts.partials.alerts')

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('desa.update', $desa) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:14px;">
                <label>Pilih Kecamatan</label>
                <select name="kecamatan_id" required>
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id', $desa->kecamatan_id) == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label>Nama Desa</label>
                <input type="text" name="name" value="{{ old('name', $desa->name) }}" required>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('desa.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
