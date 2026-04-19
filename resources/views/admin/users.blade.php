@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="card">
        <h2>User Management</h2>
        @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

        <h3>Tambah User</h3>
        <form method="POST" action="{{ route('users.store') }}" class="grid-2">
            @csrf
            <div><label>Nama</label><input name="name" required></div>
            <div><label>Email</label><input type="email" name="email" required></div>
            @if($actor->isRoot())
                <div>
                    <label>Desa</label>
                    <select name="desa_id" required>
                        <option value="">-- Pilih Desa --</option>
                        @foreach($desas as $desa)
                            <option value="{{ $desa->id }}">{{ $desa->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if($actor->isAdminDesa())
                <div>
                    <label>Jenis Petugas</label>
                    <select name="petugas_subtype" required>
                        <option value="pencatat_meter">Pencatat Meter</option>
                        <option value="penagih_iuran">Penagih Iuran</option>
                    </select>
                </div>
            @endif
            <div><label>Password</label><input type="password" name="password" required></div>
            <div><label>Konfirmasi Password</label><input type="password" name="password_confirmation" required></div>
            <div class="full"><button class="btn btn-primary" type="submit">Simpan User</button></div>
        </form>
    </div>

    <div class="card">
        <h3>Daftar User</h3>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Nama</th><th>Email</th><th>Role</th><th>Desa</th><th>Subtype</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @foreach($users as $managedUser)
                    <tr>
                        <td>{{ $managedUser->name }}</td>
                        <td>{{ $managedUser->email }}</td>
                        <td>{{ $managedUser->role?->name }}</td>
                        <td>{{ $managedUser->desa?->name ?? '-' }}</td>
                        <td>{{ $managedUser->petugas_subtype ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('users.update', $managedUser) }}" style="display:grid;gap:8px;margin-bottom:8px;">
                                @csrf @method('PUT')
                                <input name="name" value="{{ $managedUser->name }}" required>
                                <input type="email" name="email" value="{{ $managedUser->email }}" required>
                                @if($actor->isRoot() && $managedUser->hasRole('admin_desa'))
                                    <select name="desa_id" required>
                                        @foreach($desas as $desa)
                                            <option value="{{ $desa->id }}" @selected($managedUser->desa_id === $desa->id)>{{ $desa->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @if($managedUser->hasRole('petugas_lapangan'))
                                    <select name="petugas_subtype">
                                        <option value="pencatat_meter" @selected($managedUser->petugas_subtype === 'pencatat_meter')>Pencatat Meter</option>
                                        <option value="penagih_iuran" @selected($managedUser->petugas_subtype === 'penagih_iuran')>Penagih Iuran</option>
                                    </select>
                                @endif
                                <button class="btn btn-outline btn-sm" type="submit">Update</button>
                            </form>
                            <form method="POST" action="{{ route('users.reset-password', $managedUser) }}" style="display:grid;gap:8px;">
                                @csrf @method('PUT')
                                <input type="password" name="password" placeholder="Password baru" required>
                                <input type="password" name="password_confirmation" placeholder="Konfirmasi" required>
                                <button class="btn btn-danger btn-sm" type="submit">Reset Password</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
