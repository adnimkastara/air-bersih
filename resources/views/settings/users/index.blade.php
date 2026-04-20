@extends('layouts.admin')

@section('title', 'Pengaturan • Manajemen User')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Manajemen User',
        'subtitle' => $actor->isKecamatanLevel() ? 'Kelola akun admin kecamatan, admin desa, dan petugas lapangan.' : 'Kelola akun petugas lapangan untuk desa Anda.',
        'actions' => '<a href="'.route('settings.users.create').'" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah User</a>'
    ])
    @include('layouts.partials.alerts')

    <div class="card">
        <form method="GET" class="toolbar">
            <div>
                <label>Cari user</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama atau email">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button class="btn btn-primary" type="submit">Cari</button>
                <a class="btn btn-outline" href="{{ route('settings.users.index') }}">Reset</a>
            </div>
        </form>
    </div>

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Desa</th>
                    <th>Kecamatan</th>
                    <th>Subtype</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $managedUser)
                <tr>
                    <td>{{ $managedUser->name }}</td>
                    <td>{{ $managedUser->email }}</td>
                    <td>{{ str($managedUser->role?->name)->replace('_', ' ')->title() }}</td>
                    <td>{{ $managedUser->desa?->name ?? '-' }}</td>
                    <td>{{ $managedUser->kecamatan?->name ?? '-' }}</td>
                    <td>{{ $managedUser->petugas_subtype ? str($managedUser->petugas_subtype)->replace('_', ' ')->title() : '-' }}</td>
                    <td>
                        <span class="badge {{ $managedUser->is_active ? 'badge-success' : 'badge-danger' }}">{{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a class="btn btn-outline btn-sm" href="{{ route('settings.users.edit', $managedUser) }}">Edit</a>
                            <form method="POST" action="{{ route('settings.users.destroy', $managedUser) }}" onsubmit="return confirm('Hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">@include('layouts.partials.empty-state', ['message' => 'Belum ada user yang sesuai filter.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $users->links() }}</div>
@endsection
