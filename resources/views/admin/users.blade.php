<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body { margin:0; min-height:100vh; font-family:'Instrument Sans',sans-serif; background:#f8fafc; }
        .container { width:min(1100px,95vw); margin:40px auto; padding:24px; background:#fff; border:1px solid #e2e8f0; border-radius:18px; box-shadow:0 18px 55px rgba(15,23,42,.08); }
        h1 { margin-top:0; color:#0f172a; }
        .message { padding:16px; margin-bottom:20px; border-radius:14px; background:#ecfccb; color:#365314; border:1px solid #d9f99d; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:16px 12px; border-bottom:1px solid #e2e8f0; text-align:left; }
        th { background:#f8fafc; color:#0f172a; }
        select, button { padding:10px 14px; border-radius:10px; border:1px solid #cbd5e1; background:#fff; }
        button { cursor:pointer; background:#0f172a; color:#fff; border:1px solid transparent; }
        .actions { display:flex; gap:8px; align-items:center; }
        .button { display:inline-flex; align-items:center; justify-content:center; padding:14px 20px; border-radius:12px; background:#0f172a; color:#fff; text-decoration:none; font-weight:600; margin-top:20px; }
        .role-label { text-transform:capitalize; }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Management</h1>

        @if(session('status'))
            <div class="message">{{ session('status') }}</div>
        @endif

        <p>Manage user roles for the application. Select a new role and submit to update a user's assignment.</p>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $managedUser)
                    <tr>
                        <td>{{ $managedUser->name }}</td>
                        <td>{{ $managedUser->email }}</td>
                        <td class="role-label">{{ $managedUser->role?->name ?? 'None' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.updateRole', ['user' => $managedUser->id]) }}">
                                @csrf
                                @method('PUT')

                                <select name="role" aria-label="Role for {{ $managedUser->name }}">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ $managedUser->role?->name === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('admin') }}" class="button">Back to Admin</a>
        <a href="{{ route('dashboard') }}" class="button" style="margin-left:12px;background:#475569;">Back to Dashboard</a>
    </div>
</body>
</html>
