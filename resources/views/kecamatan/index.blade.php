<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kecamatan</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;}th,td{padding:12px;border-bottom:1px solid #e2e8f0;}th{background:#f1f5f9;text-align:left;}a{padding:10px 14px;border-radius:8px;text-decoration:none;color:#2563eb;}</style>
</head>
<body>
    <h1>Daftar Kecamatan</h1>

    <table>
        <thead>
            <tr><th>Nama Kecamatan</th></tr>
        </thead>
        <tbody>
            @foreach($kecamatans as $kecamatan)
                <tr>
                    <td>{{ $kecamatan->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
</body>
</html>
