@extends('layouts.admin')

@section('title', 'Ganti Password')

@section('content')
    <div class="card" style="max-width:500px;">
        <h2 style="margin-top:0;margin-bottom:18px;font-size:1.4rem;font-weight:800;">Ganti Password</h2>
        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

        <form method="POST" action="{{ route('profile.password.update') }}" class="grid-2">
            @csrf
            @method('PUT')
            <div class="full">
                <label>Password Saat Ini</label>
                <input type="password" name="current_password" required>
            </div>
            <div>
                <label>Password Baru</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required>
            </div>
            <div class="full">
                <button class="btn btn-primary" type="submit">Simpan Password</button>
            </div>
        </form>
    </div>
@endsection
