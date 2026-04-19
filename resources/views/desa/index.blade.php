<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Desa</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;}th,td{padding:12px;border-bottom:1px solid #e2e8f0;}th{background:#f1f5f9;text-align:left;}a,button{padding:10px 14px;border-radius:8px;text-decoration:none;border:none;cursor:pointer;}a{background:#0f172a;color:#fff;}button{background:#ef4444;color:#fff;} .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}</style>
</head>
<body>
    <div class="top">
        <h1>Daftar Desa</h1>
        <a href="{{ route('desa.create') }}">Tambah Desa</a>
    </div>

    @if(session('status'))
        <div style="margin-bottom:16px;padding:14px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">{{ session('status') }}</div>
    @endif

    <table>
        <thead>
            <tr><th>Nama Desa</th><th>Kecamatan</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @foreach($desas as $desa)
                <tr>
                    <td>{{ $desa->name }}</td>
                    <td>{{ $desa->kecamatan->name }}</td>
                    <td>
                        <a href="{{ route('desa.edit', $desa) }}" style="margin-right:8px;background:#2563eb;">Edit</a>
                        <form action="{{ route('desa.destroy', $desa) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
</body>
</html>
