# PRD: User Management & Roles
## Feature Add-on untuk POS Toko Kosmetik & Aksesoris

---

**Versi:** 1.0
**Tanggal:** 25 April 2026
**Status:** Draft
**Scope:** Authentication, User Management, Role-Based Access Control

---

## 1. Overview

Sistem authentication dan role-based access control untuk POS, dengan 2 role utama: **Admin** dan **Kasir**. Bertujuan untuk membatasi akses sesuai tanggung jawab dan menjaga keamanan data bisnis.

### 1.1 Tujuan
- Mengamankan akses ke data bisnis sensitif (HPP, profit, settings)
- Memberikan akses sesuai role/tanggung jawab user
- Tracking aktivitas user untuk audit dan akuntabilitas
- Mendukung multi-user (owner + beberapa kasir)

### 1.2 Scope
✅ **In-Scope:**
- Login/Logout authentication
- 2 role: Admin & Kasir
- User management (CRUD users) - admin only
- Role-based access control (RBAC)
- Activity log / audit trail
- Password management
- Session management

❌ **Out-of-Scope:**
- Multi-tenant (multiple toko)
- Custom roles (cuma Admin & Kasir aja)
- Permission per-permission (granular)
- 2FA / OTP
- SSO (Google/Facebook login)

---

## 2. User Roles

### 2.1 Admin (Owner/Manager)

**Profil:**
- Pemilik toko atau manager
- Punya full access ke seluruh sistem
- Bertanggung jawab atas konfigurasi & laporan bisnis

**Akses:**
- Semua fitur yang Kasir punya, PLUS:
- Kelola produk (CRUD)
- Kelola supplier
- Pembelian stok
- Stock adjustment
- Lihat semua laporan (penjualan, profit, HPP)
- Kelola customer (edit/delete)
- Kelola user (tambah/hapus kasir)
- Ubah settings (poin, diskon, struk, dll)
- Lihat HPP & margin
- Export data
- Lihat audit logs

### 2.2 Kasir (Karyawan)

**Profil:**
- Karyawan yang menjalankan operasional kasir
- Akses terbatas untuk fokus ke layanan customer
- Tidak bisa lihat data sensitif bisnis

**Akses:**
✅ **Bisa:**
- Login ke kasir/POS
- Lakukan transaksi penjualan
- Scan barcode & tambah produk ke cart
- Apply diskon poin & member
- Print struk
- Lihat & cari produk (read-only, tanpa HPP)
- Lihat & cari customer (read-only)
- Tambah customer baru (quick register)
- Lihat stok produk (read-only)
- Lihat history transaksi yang dia buat (hari ini saja)

❌ **TIDAK Bisa:**
- Edit/hapus produk
- Lihat HPP & margin
- Pembelian stok
- Stock adjustment
- Ubah settings
- Lihat laporan profit
- Kelola user
- Hapus/edit transaksi
- Lihat transaksi kasir lain

---

## 3. Permission Matrix

| Module | Action | Admin | Kasir |
|--------|--------|:-----:|:-----:|
| **Auth** | Login | ✅ | ✅ |
| **Auth** | Change own password | ✅ | ✅ |
| **Dashboard** | View dashboard | ✅ | ⚠️ Limited |
| **Dashboard** | View profit/HPP stats | ✅ | ❌ |
| **POS/Kasir** | Lakukan transaksi | ✅ | ✅ |
| **POS/Kasir** | Apply diskon manual | ✅ | ⚠️ Max 10% |
| **POS/Kasir** | Void transaksi | ✅ | ❌ |
| **Products** | View list | ✅ | ✅ |
| **Products** | View HPP/margin | ✅ | ❌ |
| **Products** | Create/Edit/Delete | ✅ | ❌ |
| **Customers** | View list | ✅ | ✅ |
| **Customers** | Quick register | ✅ | ✅ |
| **Customers** | Edit/Delete | ✅ | ❌ |
| **Customers** | View detail & poin | ✅ | ✅ |
| **Purchases** | View list | ✅ | ❌ |
| **Purchases** | Create | ✅ | ❌ |
| **Purchases** | Edit/Delete | ✅ | ❌ |
| **Stock Adjustment** | All actions | ✅ | ❌ |
| **Suppliers** | All actions | ✅ | ❌ |
| **Transactions** | View all | ✅ | ⚠️ Own + today only |
| **Transactions** | Edit/Delete | ✅ | ❌ |
| **Transactions** | Print struk ulang | ✅ | ⚠️ Own only |
| **Reports** | Sales report | ✅ | ❌ |
| **Reports** | Profit report | ✅ | ❌ |
| **Reports** | Inventory report | ✅ | ❌ |
| **Reports** | Member report | ✅ | ❌ |
| **Reports** | Export | ✅ | ❌ |
| **Settings** | View | ✅ | ❌ |
| **Settings** | Edit | ✅ | ❌ |
| **Users** | View list | ✅ | ❌ |
| **Users** | Create/Edit/Delete | ✅ | ❌ |
| **Activity Log** | View | ✅ | ❌ |

**Legend:**
- ✅ Full access
- ⚠️ Limited access (with conditions)
- ❌ No access

---

## 4. Core Features

### 4.1 Authentication

#### Login
- Username & password based authentication
- Session-based (untuk web)
- Remember me option (max 7 hari)
- Redirect ke dashboard setelah login berhasil
- Validate `is_active` status (akun nonaktif tidak bisa login)
- Log setiap login attempt (success/failed)

#### Logout
- Manual logout
- Auto logout setelah session expired (8 jam idle)
- Force logout saat password diubah admin

#### Password Management
- User bisa ganti password sendiri (Profile page)
- Admin bisa reset password user lain
- Password hashing pakai bcrypt
- Password policy:
  - Minimal 8 karakter
  - Wajib ada huruf besar, huruf kecil, dan angka
  - Tidak boleh sama dengan username

#### Login Protection
- Max 5x salah password → akun dikunci 15 menit
- Setelah 3x gagal → tampilkan captcha
- Log semua failed login (untuk monitoring)

---

### 4.2 User Management (Admin Only)

#### Description
Halaman untuk admin mengelola user (tambah/edit/hapus kasir, dan tambahan admin lain jika perlu).

#### Features

**List Users:**
- Tabel semua user (admin + kasir)
- Filter: by role, by status (active/inactive)
- Search: by nama atau username
- Action per user: Edit, Reset Password, Activate/Deactivate, Delete

**Create User:**
- Form: nama, username, email (opsional), no. HP, role, password
- Validasi: username unique, password sesuai policy
- Auto-generate password option (dengan tampilkan ke admin sekali)

**Edit User:**
- Update info user (kecuali username, password)
- Ganti role (admin ↔ kasir)
- Toggle is_active

**Delete User:**
- Soft delete (preserve data history)
- Tidak bisa delete user yang sedang login
- Tidak bisa delete admin terakhir (harus selalu ada minimal 1 admin)
- Konfirmasi sebelum delete

**Reset Password:**
- Admin generate password baru
- Tampilkan password sekali (admin kasih ke user)
- User wajib ganti password saat login pertama setelah reset

#### Validations
- Username: unique, alphanumeric + dot/underscore, 3-20 karakter
- Email: format valid (jika diisi), unique
- Password: min 8 char, ada huruf besar/kecil/angka
- Role: enum 'admin' atau 'kasir'
- Phone: format Indonesia (08xx atau +628xx), opsional

---

### 4.3 Role-Based Access Control (RBAC)

#### Implementation Layers

**1. Route Level (Middleware)**
- Middleware `auth` - hanya user logged in
- Middleware `admin` - hanya admin
- Group routes berdasarkan role

**2. Controller Level**
- Method-level permission check
- Filter data berdasarkan user role (e.g., kasir cuma lihat transaksinya sendiri)

**3. View Level (Blade Directive)**
- Show/hide menu items
- Show/hide buttons (edit, delete, dll)
- Show/hide sensitive data (HPP, margin)

**4. Model Level**
- Global scope untuk filter data otomatis
- Hidden attributes untuk role tertentu (e.g., HPP)

#### Helper Functions

```php
// User model helpers
$user->isAdmin();    // bool
$user->isKasir();    // bool
$user->hasRole('admin'); // bool
$user->canAccess('products.create'); // bool

// Blade directives
@admin
    <button>Tombol Admin Only</button>
@endadmin

@kasir
    <p>Hanya muncul untuk kasir</p>
@endkasir

@role('admin')
    {{-- konten --}}
@endrole
```

---

### 4.4 Activity Log (Audit Trail)

#### Description
Mencatat semua aksi penting yang dilakukan user untuk akuntabilitas dan investigasi masalah.

#### Yang Di-log

| Module | Action | Detail |
|--------|--------|--------|
| **Auth** | Login | success/failed, IP, user agent |
| **Auth** | Logout | manual/auto |
| **Auth** | Password change | self atau di-reset admin |
| **Transaction** | Create | transaction number, total |
| **Transaction** | Void | reason |
| **Product** | Create | nama produk |
| **Product** | Update | field yang berubah, old vs new |
| **Product** | Delete | nama produk |
| **Purchase** | Create | supplier, total |
| **Stock** | Adjustment | produk, qty, alasan |
| **Customer** | Edit | field yang berubah |
| **Customer** | Delete | nama customer |
| **Settings** | Change | setting key, old vs new value |
| **User** | Create | nama, role |
| **User** | Update | field yang berubah |
| **User** | Delete | nama user |
| **User** | Reset password | target user |

#### Data yang Disimpan

| Field | Description |
|-------|-------------|
| user_id | Siapa yang melakukan |
| action | Jenis aksi (created, updated, deleted, login, etc) |
| module | Modul/entity (Product, Transaction, etc) |
| description | Deskripsi human-readable |
| old_values | Data sebelum perubahan (JSON) |
| new_values | Data setelah perubahan (JSON) |
| ip_address | IP address user |
| user_agent | Browser/device info |
| created_at | Kapan kejadiannya |

#### View & Filter
- Halaman dedicated untuk audit log (admin only)
- Filter: by user, by module, by action, by date range
- Search: keyword di description
- Export ke CSV/Excel
- Auto-cleanup log lama (>1 tahun, configurable)

---

## 5. User Flows

### 5.1 Flow: Login

```
1. User buka aplikasi
2. Tampil halaman login
3. User input username & password
4. Klik tombol "Login"
5. Sistem validasi:
   - Username & password match?
   - Akun aktif?
   - Tidak terkunci karena failed attempts?
6. Berhasil → redirect ke dashboard sesuai role
   Gagal → tampil error, increment failed counter
7. Log activity: login (success/failed)
```

### 5.2 Flow: Admin Tambah Kasir Baru

```
1. Admin login → dashboard
2. Klik menu "User Management"
3. Klik tombol "Tambah User"
4. Isi form:
   - Nama: Andi Saputra
   - Username: andi.kasir
   - Email: andi@toko.com (opsional)
   - No. HP: 0812-3456-7890
   - Role: Kasir
   - Password: [generate or input]
   - Status: Active
5. Klik "Simpan"
6. Sistem validasi & create user
7. Tampil notifikasi sukses + password (jika auto-generate)
8. Admin kasih credential ke kasir baru
9. Log activity: User created
```

### 5.3 Flow: Kasir Login & Transaksi

```
1. Kasir login dengan username/password
2. Sistem validasi → redirect ke Kasir Dashboard
3. Kasir lihat dashboard (limited stats - tanpa profit)
4. Klik "Mulai Kasir" → halaman POS
5. Lakukan transaksi:
   - Scan barcode produk
   - (Opsional) Input no. HP customer untuk member
   - Apply diskon poin/member
   - Pilih metode bayar
   - Print struk
6. Sistem catat transaction.cashier_id = kasir.id
7. Log activity: Transaction created
```

### 5.4 Flow: Admin Reset Password Kasir

```
1. Admin → User Management
2. Cari kasir yang lupa password
3. Klik action "Reset Password"
4. Konfirmasi: "Reset password Andi?"
5. Sistem generate password baru
6. Tampilkan password sekali (sekali aja!)
7. Admin copy & kasih ke kasir
8. Saat kasir login dengan password baru:
   - Force redirect ke "Change Password" page
   - Wajib ganti password sebelum lanjut
9. Log activity: Password reset (by admin) & changed
```

### 5.5 Flow: Force Logout (Akun Dideaktivasi)

```
1. Admin set is_active = false untuk user X
2. Sistem invalidate semua session user X
3. User X otomatis logout dari semua device
4. Saat user X coba login lagi:
   - Tampil error: "Akun Anda dinonaktifkan. Hubungi admin."
5. Log activity: User deactivated, force logout
```

---

## 6. Data Model

### 6.1 User Table

```sql
CREATE TABLE users (
    id VARCHAR PRIMARY KEY,
    name VARCHAR NOT NULL,
    username VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR UNIQUE NULL,
    password VARCHAR NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir',
    phone VARCHAR NULL,
    avatar VARCHAR NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    must_change_password BOOLEAN NOT NULL DEFAULT false,
    last_login_at TIMESTAMP NULL,
    failed_login_attempts INTEGER DEFAULT 0,
    locked_until TIMESTAMP NULL,
    remember_token VARCHAR NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
);
```

### 6.2 Activity Log Table

```sql
CREATE TABLE activity_logs (
    id VARCHAR PRIMARY KEY,
    user_id VARCHAR NOT NULL,
    action VARCHAR NOT NULL,
    module VARCHAR NOT NULL,
    description TEXT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    reference_type VARCHAR NULL,
    reference_id VARCHAR NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NOT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_module (module),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    INDEX idx_reference (reference_type, reference_id)
);
```

### 6.3 Login Attempts Table (untuk security)

```sql
CREATE TABLE login_attempts (
    id VARCHAR PRIMARY KEY,
    username VARCHAR NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    success BOOLEAN NOT NULL,
    created_at TIMESTAMP NOT NULL,

    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_created (created_at)
);
```

### 6.4 Update Existing Tables

Tambah `cashier_id` / `created_by` ke tabel-tabel transaksional:

```sql
-- transactions
ALTER TABLE transactions
    ADD COLUMN cashier_id VARCHAR NOT NULL AFTER channel,
    ADD FOREIGN KEY (cashier_id) REFERENCES users(id),
    ADD INDEX idx_cashier (cashier_id);

-- purchases
ALTER TABLE purchases
    ADD COLUMN created_by VARCHAR NOT NULL,
    ADD FOREIGN KEY (created_by) REFERENCES users(id);

-- stock_movements
ALTER TABLE stock_movements
    ADD COLUMN created_by VARCHAR NULL,
    ADD FOREIGN KEY (created_by) REFERENCES users(id);

-- settings (audit who changed what)
ALTER TABLE settings
    ADD COLUMN updated_by VARCHAR NULL,
    ADD FOREIGN KEY (updated_by) REFERENCES users(id);
```

---

## 7. Implementation Details

### 7.1 Middleware Setup

```php
// app/Http/Middleware/AdminOnly.php
namespace App\Http\Middleware;

class AdminOnly
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang bisa mengakses halaman ini.');
        }

        return $next($request);
    }
}

// Register di app/Http/Kernel.php
protected $routeMiddleware = [
    // ...
    'admin' => \App\Http\Middleware\AdminOnly::class,
];
```

### 7.2 User Model

```php
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'password',
        'role', 'phone', 'is_active', 'must_change_password'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // Lock check
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    // Relations
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
```

### 7.3 Routes Protection

```php
// routes/web.php

// Public
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Authenticated (Admin + Kasir)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/password', [ProfileController::class, 'changePassword']);

    // POS - both can access
    Route::resource('cashier', CashierController::class);

    // Products - read only for kasir
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Customers - both can view & quick register
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::post('/customers/quick-register', [CustomerController::class, 'quickRegister']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    // ADMIN ONLY
    Route::middleware('admin')->group(function () {
        // Products full CRUD
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::post('/products/{id}/adjust-stock', [ProductController::class, 'adjustStock']);

        // Purchases & Suppliers
        Route::resource('purchases', PurchaseController::class);
        Route::resource('suppliers', SupplierController::class);

        // Customers - full management
        Route::resource('customers', CustomerController::class)->except(['index', 'show']);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/sales', [ReportController::class, 'sales']);
            Route::get('/profit', [ReportController::class, 'profit']);
            Route::get('/inventory', [ReportController::class, 'inventory']);
            Route::get('/members', [ReportController::class, 'members']);
        });

        // Settings
        Route::resource('settings', SettingController::class);

        // User Management
        Route::resource('users', UserController::class);
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
        Route::post('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);

        // Activity Log
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    });
});
```

### 7.4 Filter Data per Role

```php
// TransactionController
public function index()
{
    $query = Transaction::query();

    // Kasir: cuma lihat transaksinya sendiri hari ini
    if (auth()->user()->isKasir()) {
        $query->where('cashier_id', auth()->id())
              ->whereDate('created_at', today());
    }

    $transactions = $query->latest()->paginate(20);

    return view('transactions.index', compact('transactions'));
}
```

### 7.5 Hide HPP for Kasir

```php
// Product Model - Global Scope
class Product extends Model
{
    protected $hidden = [];

    protected static function booted()
    {
        static::retrieved(function ($product) {
            // Sembunyikan HPP untuk kasir
            if (auth()->check() && auth()->user()->isKasir()) {
                $product->makeHidden('cost_price');
            }
        });
    }
}
```

### 7.6 Activity Logger Helper

```php
class ActivityLogger
{
    public static function log(string $action, string $module, array $data = [])
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'description' => $data['description'] ?? null,
            'old_values' => $data['old'] ?? null,
            'new_values' => $data['new'] ?? null,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

// Usage
ActivityLogger::log('created', 'Transaction', [
    'description' => 'Created transaction TRX-001',
    'reference_type' => 'transaction',
    'reference_id' => $transaction->id,
    'new' => $transaction->toArray(),
]);
```

---

## 8. UI/UX Requirements

### 8.1 Login Page
- Centered card layout
- Logo toko di atas
- Form: username, password
- Remember me checkbox
- Error message yang jelas (tanpa expose detail security)
- Link "Lupa password?" → contact admin

### 8.2 Dashboard - Conditional Display

**Admin Dashboard:**
- Total penjualan (revenue)
- Total profit
- Stats lengkap
- Quick actions: Pembelian, Reports, Settings

**Kasir Dashboard:**
- Transaksi saya hari ini
- Total penjualan saya
- New members hari ini
- Quick actions: Mulai Kasir, Lihat Customer

### 8.3 Sidebar Navigation - Per Role

**Admin:**
```
📊 Dashboard
🛒 Kasir/POS
📦 Produk
📥 Pembelian
🏢 Supplier
👥 Customer
💼 Transaksi
📈 Laporan
⚙️ Settings
👤 User Management
📋 Activity Log
```

**Kasir:**
```
📊 Dashboard
🛒 Kasir/POS
📦 Produk (view only)
👥 Customer
💼 Transaksi Saya
```

### 8.4 User Management Page (Admin Only)
- List view dengan search & filter
- Action buttons: Edit, Reset Password, Toggle Active, Delete
- Form modal untuk add/edit
- Konfirmasi untuk action sensitif (delete, deactivate)

### 8.5 Profile Page
- Info user: nama, username, email, role, member since
- Edit profile (nama, email, phone)
- Change password section
- Recent activity (login history)

### 8.6 Activity Log Page (Admin Only)
- Tabel dengan filter: user, module, action, date
- Detail modal: tampilkan old_values vs new_values
- Export to CSV/Excel
- Pagination

---

## 9. Security Best Practices

### 9.1 Password Policy
```php
// Validation rules
'password' => [
    'required',
    'string',
    'min:8',
    'regex:/[a-z]/',      // huruf kecil
    'regex:/[A-Z]/',      // huruf besar
    'regex:/[0-9]/',      // angka
    'confirmed',
],
```

### 9.2 Login Protection
- Max 5 failed attempts → lock akun 15 menit
- Setelah 3 failed → tampil captcha
- Log semua login attempts (table login_attempts)
- Reset failed counter saat login berhasil

### 9.3 Session Management
- Idle timeout: 8 jam
- Remember me: 7 hari max
- Force logout saat password diubah
- Force logout saat akun dinonaktifkan
- Single session per user (opsional)

### 9.4 CSRF Protection
- Semua form POST/PUT/DELETE pakai CSRF token
- Laravel built-in middleware

### 9.5 SQL Injection Prevention
- Pakai Eloquent ORM atau prepared statements
- Validate semua input

### 9.6 XSS Prevention
- Escape output di Blade (`{{ }}` auto-escape)
- Validate & sanitize input

---

## 10. First Time Setup

### 10.1 Database Seeder

```php
class UserSeeder extends Seeder
{
    public function run()
    {
        // Default admin
        User::create([
            'name' => 'Owner',
            'username' => 'owner',
            'email' => 'owner@toko.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'must_change_password' => true, // wajib ganti password saat login pertama
        ]);
    }
}
```

### 10.2 Setup Wizard (Alternatif)

Saat install pertama kali, tampilkan setup wizard:

```
First Time Setup:
─────────────────────────────
Selamat datang! Mari setup akun admin pertama.

Step 1/3: Info Toko
- Nama Toko
- Alamat
- Kontak

Step 2/3: Akun Admin
- Nama Owner
- Username
- Password
- Konfirmasi Password

Step 3/3: Konfirmasi
- Review data
- [Mulai Setup]
```

---

## 11. Edge Cases & Validations

| Case | Behavior |
|------|----------|
| Delete admin terakhir | Block, harus ada minimal 1 admin |
| Kasir coba akses URL admin | Redirect ke 403 page |
| User login saat akun di-disable | Tampil error, log attempt |
| User edit profile sendiri | OK, kecuali username & role |
| Admin edit role dirinya sendiri | Block, hanya admin lain yang bisa |
| Delete user yang punya transaksi | Soft delete (preserve history) |
| Kasir input password salah 5x | Lock 15 menit, notif admin |
| Session timeout saat ada draft transaksi | Save draft otomatis, prompt re-login |

---

## 12. Reports & Analytics

### Activity Reports (Admin Only)
- Login summary (success rate, peak hours)
- Most active users
- Action distribution per module
- Failed login monitoring

### Cashier Performance
- Transaksi per kasir (count, total revenue)
- Average transaction value per kasir
- Customer acquired per kasir (new member registration)

---

## 13. Development Checklist

### Phase 1: Authentication Foundation (2-3 hari)
- [ ] Migration: users, login_attempts, activity_logs
- [ ] Model: User, ActivityLog
- [ ] Login controller & view
- [ ] Logout functionality
- [ ] Session config
- [ ] Password hashing & validation

### Phase 2: User Management (2-3 hari)
- [ ] User CRUD (admin only)
- [ ] User list with search & filter
- [ ] Add/Edit user form
- [ ] Reset password feature
- [ ] Toggle active status
- [ ] Soft delete user

### Phase 3: Role-Based Access (2 hari)
- [ ] Middleware AdminOnly
- [ ] Routes grouping per role
- [ ] Blade directives (@admin, @kasir, @role)
- [ ] Filter data per role (transactions, products)
- [ ] Hide HPP for kasir

### Phase 4: Activity Logging (1-2 hari)
- [ ] ActivityLogger helper class
- [ ] Auto-log model events (created/updated/deleted)
- [ ] Activity log viewer page
- [ ] Filter & search logs
- [ ] Export logs

### Phase 5: Security Features (1-2 hari)
- [ ] Login protection (max attempts, lockout)
- [ ] Password policy enforcement
- [ ] Session management
- [ ] Force logout on deactivation
- [ ] Must change password flow

### Phase 6: UI/UX Polish (1-2 hari)
- [ ] Login page design
- [ ] Conditional dashboard
- [ ] Sidebar per role
- [ ] Profile page
- [ ] User management UI
- [ ] Activity log UI

### Phase 7: Testing (1-2 hari)
- [ ] Unit tests (User model, helpers)
- [ ] Integration tests (login, RBAC)
- [ ] Permission tests (admin vs kasir)
- [ ] Security tests (lockout, session)

**Total Estimasi: 10-16 hari kerja**

---

## 14. Dependencies & Integration

### Modules yang Bergantung pada Fitur Ini
- **Transaction** - butuh `cashier_id` untuk track siapa yang melakukan transaksi
- **Purchase** - butuh `created_by`
- **Stock Movement** - butuh `created_by`
- **Settings** - butuh `updated_by`
- **Customer** - kasir bisa quick register tapi tidak bisa edit/delete

### Affects Existing Features
- Semua halaman butuh authentication check
- Menu navigation berubah per role
- Data filtering berubah per role
- Audit log perlu di-integrate ke semua action penting

---

## 15. Future Enhancements (Out of Scope)

- 🔐 **2FA / OTP** untuk keamanan ekstra
- 🌐 **SSO** (Login dengan Google/Facebook)
- 👥 **Custom Roles** (selain Admin & Kasir)
- 🎯 **Granular Permissions** (per-permission, bukan per-role)
- 📱 **Mobile App** dengan biometric login
- 🔔 **Email Notifications** untuk security events
- 📊 **Advanced Analytics** untuk user behavior
- 🏢 **Multi-tenant** (multiple toko)
- 🔄 **Password Recovery** via email/SMS
- ⏰ **Scheduled Account Lockout** (jam kerja)

---

*Dokumen ini fokus pada User Management & Roles. Fitur lain (Membership, Receipt, dll) didokumentasikan terpisah.*
