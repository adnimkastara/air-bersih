# Air Bersih - Technical Documentation

## Architecture Overview

### Technology Stack
- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Database**: MySQL 5.7+
- **Frontend**: Blade Templates + Vite
- **Session**: File Driver
- **ORM**: Eloquent

### Project Structure
```
air-bersih/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── AdminController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── KecamatanController.php
│   │   │   ├── DesaController.php
│   │   │   ├── PelangganController.php
│   │   │   ├── MeterRecordController.php
│   │   │   ├── TagihanController.php
│   │   │   └── PembayaranController.php
│   │   └── Middleware/
│   └── Models/
│       ├── User.php
│       ├── Role.php
│       ├── Kecamatan.php
│       ├── Desa.php
│       ├── Pelanggan.php
│       ├── MeterRecord.php
│       ├── Tagihan.php
│       ├── Pembayaran.php
│       └── ActivityLog.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── auth/
│   │   ├── dashboard.blade.php
│   │   ├── kecamatan/
│   │   ├── desa/
│   │   ├── pelanggan/
│   │   ├── meter_records/
│   │   ├── tagihan/
│   │   └── pembayaran/
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
└── storage/
```

---

## Database Schema Details

### Users & Roles

#### `users` Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    role_id BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

#### `roles` Table
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Valid Roles**:
- `root` - Super admin
- `admin` - System admin
- `admin_kecamatan` - Kecamatan admin
- `admin_desa` - Desa admin
- `petugas_lapangan` - Field officer
- `user` - Regular user

---

### Master Data

#### `kecamatans` Table (Read-Only)
```sql
CREATE TABLE kecamatans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### `desas` Table (Full CRUD)
```sql
CREATE TABLE desas (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kecamatan_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY (kecamatan_id, name),
    FOREIGN KEY (kecamatan_id) REFERENCES kecamatans(id)
);
```

---

### Business Data

#### `pelanggans` Table (Customers)
```sql
CREATE TABLE pelanggans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULLABLE,
    phone VARCHAR(20) NULLABLE,
    address TEXT NULLABLE,
    kecamatan_id BIGINT UNSIGNED NULLABLE,
    desa_id BIGINT UNSIGNED NULLABLE,
    assigned_petugas_id BIGINT UNSIGNED NULLABLE,
    latitude DECIMAL(10, 7) NULLABLE,
    longitude DECIMAL(10, 7) NULLABLE,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (kecamatan_id) REFERENCES kecamatans(id),
    FOREIGN KEY (desa_id) REFERENCES desas(id),
    FOREIGN KEY (assigned_petugas_id) REFERENCES users(id)
);
```

**Key Fields**:
- `name`: Customer identifier
- `status`: Active/Inactive status
- `assigned_petugas_id`: Assigned field officer for meter reading
- Coordinates: For future mapping features

#### `meter_records` Table (Meter Readings)
```sql
CREATE TABLE meter_records (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pelanggan_id BIGINT UNSIGNED NOT NULL,
    petugas_id BIGINT UNSIGNED NULLABLE,
    meter_previous_month BIGINT UNSIGNED NOT NULL,
    meter_current_month BIGINT UNSIGNED NOT NULL,
    recorded_at DATE NOT NULL,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggans(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);
```

**Key Calculation**:
```
Consumption = meter_current_month - meter_previous_month
```

**Validation**:
```
meter_current_month >= meter_previous_month (validated in controller)
```

#### `tagihans` Table (Invoices)
```sql
CREATE TABLE tagihans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pelanggan_id BIGINT UNSIGNED NOT NULL,
    meter_record_id BIGINT UNSIGNED NULLABLE,
    amount DECIMAL(12, 2) DEFAULT 0,
    status ENUM('draft', 'terbit', 'lunas', 'menunggak') DEFAULT 'draft',
    due_date DATE NULLABLE,
    period VARCHAR(7) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggans(id),
    FOREIGN KEY (meter_record_id) REFERENCES meter_records(id)
);
```

**Status Definitions**:
- `draft`: Created but not published
- `terbit`: Published/issued to customer
- `lunas`: Fully paid
- `menunggak`: Overdue/unpaid

**Amount Calculation**:
```
amount = consumption × 1,000
where consumption = meter_current_month - meter_previous_month
```

**Period Format**: `YYYY-MM` (e.g., "2026-04")

#### `pembayarans` Table (Payments)
```sql
CREATE TABLE pembayarans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tagihan_id BIGINT UNSIGNED NOT NULL,
    petugas_id BIGINT UNSIGNED NULLABLE,
    amount DECIMAL(12, 2) NOT NULL,
    paid_at DATE NOT NULL,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (tagihan_id) REFERENCES tagihans(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);
```

**Status Update Logic**:
After payment recorded, tagihan status updated as:
```php
if (total_paid >= tagihan_amount) {
    status = 'lunas';  // Fully paid
} elseif (paid_date > due_date) {
    status = 'menunggak';  // Overdue
} else {
    status = 'terbit';  // Issued
}
```

---

### Activity Logging

#### `activity_logs` Table
```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULLABLE,
    action VARCHAR(255) NOT NULL,
    subject_type VARCHAR(255) NULLABLE,
    subject_id BIGINT UNSIGNED NULLABLE,
    description TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Logged Actions**:
- `pelanggan.created`
- `pelanggan.updated`
- `pelanggan.deleted`
- `desa.created`
- `desa.updated`
- `desa.deleted`
- `meter.recorded`
- `tagihan.generated`
- `tagihan.published`
- `pembayaran.recorded`

---

## Model Relationships

### User Model
```php
class User extends Model {
    public function role(): BelongsTo {
        return $this->belongsTo(Role::class);
    }
    
    public function meterRecords(): HasMany {
        return $this->hasMany(MeterRecord::class, 'petugas_id');
    }
    
    public function pembayarans(): HasMany {
        return $this->hasMany(Pembayaran::class, 'petugas_id');
    }
    
    public function activityLogs(): HasMany {
        return $this->hasMany(ActivityLog::class);
    }
}
```

### Pelanggan Model
```php
class Pelanggan extends Model {
    public function kecamatan(): BelongsTo {
        return $this->belongsTo(Kecamatan::class);
    }
    
    public function desa(): BelongsTo {
        return $this->belongsTo(Desa::class);
    }
    
    public function assignedPetugas(): BelongsTo {
        return $this->belongsTo(User::class, 'assigned_petugas_id');
    }
    
    public function meterRecords(): HasMany {
        return $this->hasMany(MeterRecord::class);
    }
    
    public function tagihans(): HasMany {
        return $this->hasMany(Tagihan::class);
    }
}
```

### MeterRecord Model
```php
class MeterRecord extends Model {
    public function pelanggan(): BelongsTo {
        return $this->belongsTo(Pelanggan::class);
    }
    
    public function petugas(): BelongsTo {
        return $this->belongsTo(User::class, 'petugas_id');
    }
    
    public function getConsumptionAttribute(): int {
        return $this->meter_current_month - $this->meter_previous_month;
    }
}
```

### Tagihan Model
```php
class Tagihan extends Model {
    public function pelanggan(): BelongsTo {
        return $this->belongsTo(Pelanggan::class);
    }
    
    public function meterRecord(): BelongsTo {
        return $this->belongsTo(MeterRecord::class);
    }
    
    public function pembayarans(): HasMany {
        return $this->hasMany(Pembayaran::class);
    }
}
```

---

## Key Controller Methods

### MeterRecordController
```php
public function store(Request $request) {
    $validated = $request->validate([
        'pelanggan_id' => 'required|exists:pelanggans,id',
        'meter_previous_month' => 'required|numeric|min:0',
        'meter_current_month' => 'required|numeric|min:0',
        'recorded_at' => 'required|date',
        'notes' => 'nullable|string',
    ]);
    
    // Anomaly detection
    if ($validated['meter_current_month'] < $validated['meter_previous_month']) {
        return back()->withErrors([
            'meter_current_month' => 'Meter bulan ini tidak boleh kurang dari meter bulan lalu'
        ]);
    }
    
    $meter = MeterRecord::create($validated);
    $this->logActivity($request, 'meter.recorded', 'MeterRecord', $meter->id);
    
    return redirect()->route('meter-records.index');
}
```

### TagihanController - Generate Logic
```php
public function generate(Request $request) {
    $meterRecords = MeterRecord::whereMonth('recorded_at', now()->subMonth()->month)
        ->with('pelanggan')
        ->get();
    
    foreach ($meterRecords as $meter) {
        // Calculate consumption
        $consumption = $meter->meter_current_month - $meter->meter_previous_month;
        
        // Skip invalid consumption
        if ($consumption <= 0) continue;
        
        // Skip if tagihan already exists
        if (Tagihan::where('meter_record_id', $meter->id)->exists()) continue;
        
        // Create tagihan
        $tagihan = Tagihan::create([
            'pelanggan_id' => $meter->pelanggan_id,
            'meter_record_id' => $meter->id,
            'amount' => $consumption * 1000,  // Price per unit: 1000
            'status' => 'draft',
            'due_date' => $meter->recorded_at->endOfMonth(),
            'period' => $meter->recorded_at->format('Y-m'),
        ]);
        
        $this->logActivity($request, 'tagihan.generated', 'Tagihan', $tagihan->id);
    }
    
    return redirect()->route('tagihan.index');
}
```

### PembayaranController - Status Update Logic
```php
public function store(Request $request) {
    $validated = $request->validate([
        'tagihan_id' => 'required|exists:tagihans,id',
        'amount' => 'required|numeric|min:0.01',
        'paid_at' => 'required|date',
        'notes' => 'nullable|string',
    ]);
    
    // Create payment
    $payment = Pembayaran::create([
        ...$validated,
        'petugas_id' => $request->user()->id,  // Auto-assign current user
    ]);
    
    // Update tagihan status
    $tagihan = Tagihan::find($validated['tagihan_id']);
    $totalPaid = $tagihan->pembayarans()->sum('amount');
    
    if ($totalPaid >= $tagihan->amount) {
        $tagihan->status = 'lunas';  // Fully paid
    } elseif ($validated['paid_at'] > $tagihan->due_date) {
        $tagihan->status = 'menunggak';  // Overdue
    } else {
        $tagihan->status = 'terbit';  // Issued
    }
    
    $tagihan->save();
    
    $this->logActivity($request, 'pembayaran.recorded', 'Pembayaran', $payment->id);
    
    return redirect()->route('pembayaran.index');
}
```

---

## Activity Logging System

### Base Controller Method
```php
protected function logActivity(
    Request $request,
    string $action,
    ?string $subjectType = null,
    ?int $subjectId = null,
    ?string $description = null
) {
    ActivityLog::create([
        'user_id' => $request->user()?->id,
        'action' => $action,
        'subject_type' => $subjectType,
        'subject_id' => $subjectId,
        'description' => $description ?? "Aksi: $action",
    ]);
}
```

### Usage Example
```php
$this->logActivity(
    $request,
    'pelanggan.created',
    'Pelanggan',
    $pelanggan->id,
    "Pelanggan baru: {$pelanggan->name}"
);
```

---

## Validation Rules

### Meter Record Validation
```php
$validated = $request->validate([
    'pelanggan_id' => 'required|exists:pelanggans,id',
    'meter_previous_month' => 'required|numeric|min:0',
    'meter_current_month' => 'required|numeric|min:0',
    'recorded_at' => 'required|date',
    'notes' => 'nullable|string|max:500',
]);

// Custom validation
if ($validated['meter_current_month'] < $validated['meter_previous_month']) {
    // Anomaly detected
}
```

### Pelanggan Validation
```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'nullable|email|unique:pelanggans,email',
    'phone' => 'nullable|string|max:20',
    'address' => 'nullable|string|max:500',
    'kecamatan_id' => 'required|exists:kecamatans,id',
    'desa_id' => 'required|exists:desas,id',
    'assigned_petugas_id' => 'nullable|exists:users,id',
    'latitude' => 'nullable|numeric|between:-90,90',
    'longitude' => 'nullable|numeric|between:-180,180',
    'status' => 'required|in:aktif,nonaktif',
]);
```

---

## Routes Configuration

### Public Routes
```php
Route::get('/', function () { /* ... */ });
Route::get('/preview', function () { /* ... */ });
```

### Auth Routes
```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);
});
```

### Protected Routes
```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Kecamatan (read-only)
    Route::get('/kecamatan', [KecamatanController::class, 'index']);
    
    // Desa (full CRUD)
    Route::resource('desa', DesaController::class, [
        'except' => ['show']
    ]);
    
    // Other resources...
    Route::resource('pelanggan', PelangganController::class, ['except' => ['show']]);
    Route::resource('meter-records', MeterRecordController::class, ['only' => ['index', 'create', 'store']]);
    
    // Custom routes
    Route::post('/tagihan/generate', [TagihanController::class, 'generate'])->name('tagihan.generate');
    Route::post('/tagihan/{id}/publish', [TagihanController::class, 'publish'])->name('tagihan.publish');
    Route::get('/pembayaran/{id}/receipt', [PembayaranController::class, 'receipt'])->name('pembayaran.receipt');
});
```

---

## Best Practices

### 1. Eager Loading
Always use eager loading to prevent N+1 queries:
```php
$meters = MeterRecord::with('pelanggan', 'petugas')->get();
```

### 2. Query Optimization
Use selective columns:
```php
$users = User::select('id', 'name', 'email')
    ->with('role:id,name')
    ->get();
```

### 3. Validation in Controller
Centralize validation in controller before model operations:
```php
$validated = $request->validate([...]);
$model = Model::create($validated);
```

### 4. Activity Logging
Log all important operations for audit trail:
```php
$this->logActivity($request, 'action', 'Model', $id, 'Description');
```

### 5. Transaction Safety
Use transactions for multi-step operations:
```php
DB::transaction(function () {
    // Multiple operations
});
```

---

## Performance Optimization

### Caching Strategy
```php
// Cache dashboard stats
$stats = Cache::remember('dashboard.stats', 3600, function () {
    return [
        'pelanggan' => Pelanggan::count(),
        'tagihan' => Tagihan::count(),
        'pembayaran' => Pembayaran::count(),
    ];
});
```

### Pagination for Large Datasets
```php
// Add pagination
$meters = MeterRecord::with('pelanggan', 'petugas')
    ->paginate(25);
```

### Database Indexing
Critical indexes to add:
```sql
ALTER TABLE meter_records ADD INDEX (pelanggan_id);
ALTER TABLE meter_records ADD INDEX (recorded_at);
ALTER TABLE tagihans ADD INDEX (status);
ALTER TABLE tagihans ADD INDEX (pelanggan_id);
ALTER TABLE pembayarans ADD INDEX (tagihan_id);
ALTER TABLE activity_logs ADD INDEX (created_at);
```

---

## Error Handling

### HTTP Exception Responses
```php
public function authorize(Request $request) {
    if (!$request->user()->isAdmin()) {
        abort(403, 'Tidak diizinkan');
    }
}
```

### Validation Error Response
```php
if ($meter->meter_current_month < $meter->meter_previous_month) {
    return back()->withErrors([
        'meter_current_month' => 'Meter bulan ini tidak boleh kurang dari meter bulan lalu'
    ])->withInput();
}
```

---

## Deployment Checklist

- [ ] Environment variables configured (.env)
- [ ] Database migrated: `php artisan migrate --force`
- [ ] Database seeded: `php artisan db:seed --force`
- [ ] Cache cleared: `php artisan cache:clear`
- [ ] Config cached: `php artisan config:cache`
- [ ] Routes cached: `php artisan route:cache`
- [ ] Assets compiled: `npm run build`
- [ ] File permissions set: `storage/` and `bootstrap/cache/` writable
- [ ] Backup database before deployment
- [ ] Test all critical flows post-deployment

---

## Development Commands

```bash
# Database operations
php artisan migrate:fresh --seed        # Fresh migration with seeding
php artisan migrate:rollback            # Rollback last migration
php artisan migrate:refresh             # Refresh all migrations

# Cache operations
php artisan cache:clear                 # Clear all cache
php artisan config:cache                # Cache config
php artisan route:cache                 # Cache routes

# Development server
php artisan serve                       # Start development server
php artisan tinker                      # Interactive shell

# Database
php artisan db:seed --class=DatabaseSeeder  # Seed database
php artisan db:show                     # Show database info

# Testing (future)
php artisan test                        # Run tests
php artisan test --filter=TestName      # Run specific test
```

---

**Last Updated**: April 2026
**Version**: MVP 1.0
**Status**: Production Ready
