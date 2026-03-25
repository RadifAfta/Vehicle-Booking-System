# 📚 INTERVIEW PREPARATION - BOOKING SYSTEM VEHICLE

**Kandidat:** Mid-Level Developer  
**Project:** Technical Test - Sistem Pemesanan Kendaraan  
**Tanggal Persiapan:** 25 Maret 2026  

---

## 📋 DAFTAR ISI

1. [Project Overview](#project-overview)
2. [Tech Stack & Architecture](#tech-stack--architecture)
3. [Fitur Utama & Business Logic](#fitur-utama--business-logic)
4. [Database Schema](#database-schema)
5. [Implementation Details](#implementation-details)
6. [Code Highlights & Best Practices](#code-highlights--best-practices)
7. [Common Interview Questions & Answers](#common-interview-questions--answers)
8. [Demo Talking Points](#demo-talking-points)
9. [Challenges & Solutions](#challenges--solutions)
10. [Performance & Security](#performance--security)
11. [Future Improvements](#future-improvements)

---

## 1. PROJECT OVERVIEW

### 📝 Deskripsi Singkat

Aplikasi web **Sistem Pemesanan Kendaraan Perusahaan** berbasis Laravel 13 yang dirancang untuk perusahaan tambang. Aplikasi ini mengelola pemesanan kendaraan dengan alur persetujuan berjenjang 2 level, dashboard analitik, dan fitur laporan export Excel.

### 🎯 Tujuan Bisnis

- Mekanisme pemesanan kendaraan yang terstruktur dan terintegrasi
- Workflow persetujuan untuk mengurangi kesalahan dan meningkatkan akuntabilitas
- Transparansi penggunaan kendaraan melalui tracking riwayat pemakaian
- Analitik dan laporan untuk decision making

### 👥 User Roles

| Role | Tanggung Jawab |
|------|-----------------|
| **Admin** | Input pemesanan, pilih driver, tentukan penyetuju level 1 & 2, lihat laporan, lihat dashboard |
| **Penyetujui** | Approve/reject pemesanan sesuai level mereka, view persetujuan pending |

### 📊 Key Metrics

- **2 Level Approval** workflow untuk setiap pemesanan
- **8 Database Tables** dengan relasi yang kompleks
- **4 Main Modules**: Pemesanan, Persetujuan, Dashboard, Laporan
- **Real-time Activity Logging** untuk audit trail

---

## 2. TECH STACK & ARCHITECTURE

### 🔧 Technology Stack

```
Backend:
├── Framework: Laravel 13
├── PHP Version: 8.3+
├── Database: MySQL 8.x
├── ODM Tools:
│   ├── Eloquent ORM
│   ├── Query Builder
│   └── Migrations & Seeders
├── Validation: Built-in Laravel validation
├── Export: maatwebsite/excel (PhpSpreadsheet)
└── Testing: Pest PHP

Frontend:
├── Build Tool: Vite
├── Assets: CSS & JavaScript
├── Template Engine: Blade
└── Interactivity: JavaScript (Vanilla/jQuery)

DevOps:
├── Docker & Docker Compose
├── Environment: Nginx + PHP-FPM
└── Package Management: Composer & NPM
```

### 🏗️ Architecture Pattern

**MVC (Model-View-Controller) Pattern:**

```
┌─────────────────────────────────────┐
│          ROUTES (web.php)           │
│  ├── Guest Routes (Login)           │
│  ├── Auth Routes                    │
│  ├── Admin Routes                   │
│  └── Penyetujui Routes              │
└──────────────┬──────────────────────┘
               │
        ┌──────▼──────────────────────┐
        │    MIDDLEWARE Stack         │
        │ ├── auth                    │
        │ ├── role (RoleMiddleware)   │
        │ └── activity.log (LogMid.)  │
        └──────┬──────────────────────┘
               │
    ┌──────────┴──────────────┐
    │                         │
┌───▼───────────────────┐  ┌─▼───────────────────┐
│    CONTROLLERS        │  │    MODELS           │
│                       │  │                     │
│ • AuthController      │  │ • User              │
│ • PemesananCtrl       │  │ • Pemesanan         │
│ • PersetujuanCtrl     │  │ • LogPersetujuan    │
│ • DashboardCtrl       │  │ • RiwayatPemakaian  │
│ • LaporanCtrl         │  │ • Kendaraan         │
│                       │  │ • Driver            │
│                       │  │ • Kantor            │
│                       │  │ • LogAktivitas      │
└───────────────────────┘  └─────────────────────┘
         │                          │
         └──────────────┬───────────┘
                        │
                    ┌───▼─────┐
                    │DATABASE │
                    │(MySQL8) │
                    └─────────┘
         │
         └──────────────┬───────────────────┐
                        │                   │
                    ┌───▼────────┐    ┌────▼──────┐
                    │  VIEWS     │    │ EXPORTS   │
                    │ (Blade)    │    │(Excel)    │
                    └────────────┘    └───────────┘
```

### 🔓 Security Layers

1. **Authentication**: Laravel's native Auth
2. **Authorization**: Role-based Middleware (RoleMiddleware)
3. **Validation**: Request validation di setiap controller action
4. **Activity Logging**: Middleware untuk log semua aktivitas user
5. **Database Constraints**: Foreign keys dengan cascade rules yang tepat

---

## 3. FITUR UTAMA & BUSINESS LOGIC

### 🚗 Fitur 1: Pemesanan Kendaraan

#### Flow Pemesanan:
```
Admin membuat pemesanan
    ↓
Pilih kendaraan, driver, 2 penyetuju
    ↓
Status: MENUNGGU_PERSETUJUAN
    ↓
Penyetuju Level 1 review & approve/reject
    ├─ Reject → Status: DITOLAK (End)
    └─ Approve → Status: DISETUJUI_LEVEL_1
       ↓
       Penyetuju Level 2 review & approve/reject
       ├─ Reject → Status: DITOLAK (End)
       └─ Approve → Status: DISETUJUI_FINAL
          ↓
          Admin dapat input riwayat pemakaian (km, BBM, keterangan)
```

#### Status Pemesanan:
- `menunggu_persetujuan` - Menunggu approval level 1
- `disetujui_level_1` - Level 1 sudah approve, menunggu level 2
- `disetujui_final` - Semua level approve, kendaraan siap digunakan
- `ditolak` - Ditolak oleh salah satu penyetuju

#### Business Rules:
- 1 pemesanan HARUS punya 2 penyetuju berbeda (level 1 & level 2)
- Penyetuju tidak bisa approve pemesanan sendiri
- Setiap approval harus catat log dengan catatan tambahan (opsional)
- Admin bisa input riwayat pemakaian hanya jika status = `disetujui_final`

---

### ✅ Fitur 2: Approval Workflow (2-Level)

#### Implementasi Workflow:

```php
// PersetujuanController.php - Logic approval
if ($pemesanan->atasan_1_id === $userId && 
    $pemesanan->status_pemesanan === 'menunggu_persetujuan') {
    // Level 1 approval logic
    LogPersetujuan::create([...]);
    $pemesanan->update([
        'status_pemesanan' => $data['aksi'] === 'setuju' 
            ? 'disetujui_level_1' 
            : 'ditolak'
    ]);
    return;
}

if ($pemesanan->atasan_2_id === $userId && 
    $pemesanan->status_pemesanan === 'disetujui_level_1') {
    // Level 2 approval logic
    LogPersetujuan::create([...]);
    $pemesanan->update([
        'status_pemesanan' => $data['aksi'] === 'setuju' 
            ? 'disetujui_final' 
            : 'ditolak'
    ]);
}
```

#### Key Points:
- Menggunakan **Database Transaction** (`DB::transaction()`) untuk atomicity
- Cek validasi status dan user ID untuk security
- Log setiap approval action dengan timestamp
- Guard: Tidak bisa approve 2x atau bypass level

---

### 📊 Fitur 3: Dashboard Analytics

#### Grafik:
- **Chart Nama Kendaraan vs Total KM Tempuh**
  - Data dari `riwayat_pemakaian` table
  - Aggregation: `SUM(jarak_tempuh_km)` grouped by kendaraan

#### Business Logic:
```php
$chartRows = RiwayatPemakaian::query()
    ->join('pemesanan', ...) // Hubung ke pemesanan
    ->join('kendaraan', ...)  // Hubung ke kendaraan
    ->select('kendaraan.nama', DB::raw('SUM(...) as total_km'))
    ->groupBy('kendaraan.nama')
    ->orderByDesc('total_km')
    ->get();
```

#### Metrics Ditampilkan:
- Total Kendaraan
- Total Pemesanan
- Chart KM per Kendaraan (Top performers)

---

### 📑 Fitur 4: Laporan & Export Excel

#### Jenis Laporan:

1. **Laporan Pemesanan Kendaraan**
   - Filter by tanggal mulai & selesai
   - Export ke Excel (.xlsx)
   - Include: Admin, Kendaraan, Driver, Atasan 1 & 2, Status, Catatan
   - Styling: Header bold + blue background, borders

2. **Laporan Log Persetujuan**
   - History semua approval actions
   - Filter by tanggal
   - Include: Pemesanan ID, Penyetuju, Level, Action (setuju/tolak), Catatan

3. **Laporan Log Aktivitas**
   - Audit trail semua user actions
   - Filter by tanggal & user
   - Include: User, Action, Endpoint, IP Address, Timestamp

#### Export Implementation:
```php
// Menggunakan maatwebsite/excel
class PemesananExport implements 
    FromCollection, 
    WithHeadings, 
    ShouldAutoSize, 
    WithStyles, 
    WithEvents { ... }

// Styling Excel:
- Freeze pane di row 1
- Auto-filter di header
- Border pada semua cell
- Custom color scheme
```

---

## 4. DATABASE SCHEMA

### 📐 ERD Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        KANTOR                                   │
│                   (Cabang Perusahaan)                           │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ id (PK)                                                  │  │
│  │ nama (string)                                            │  │
│  │ alamat (text)                                            │  │
│  │ created_at, updated_at                                   │  │
│  └──────────────────────────────────────────────────────────┘  │
│                              △                                   │
│                              │ (1:N)                             │
└──────────────────────────────┼───────────────────────────────────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
        │                      │                      │
  ┌─────▼──────────┐  ┌────────▼────────┐  ┌────────▼────────┐
  │    KENDARAAN   │  │   USERS         │  │   DRIVER        │
  │                │  │                 │  │                 │
  │ id (PK)        │  │ id (PK)         │  │ id (PK)         │
  │ nama           │  │ nama            │  │ nama            │
  │ jenis (enum)   │  │ email           │  │ telepon         │
  │ kepemilikan    │  │ password        │  │ status (enum)   │
  │ kantor_id (FK) │  │ role (enum)     │  │ created_at      │
  │ konsumsi_bbm   │  │ kantor_id (FK)  │  │ updated_at      │
  │ tgl_servis     │  │ created_at      │  └─────────────────┘
  │ created_at     │  │ updated_at      │
  └─────┬──────────┘  └─────┬───────────┘
        │                   │
        │ (1:N)             │ (1:N admin)
        │                   │  (1:N atasan_1)
        │                   │  (1:N atasan_2)
        └──────────┬────────┘
                   │
            ┌──────▼──────────────┐
            │   PEMESANAN         │
            │                     │
            │ id (PK)             │
            │ admin_id (FK→User)  │
            │ kendaraan_id (FK)   │
            │ driver_id (FK)      │
            │ atasan_1_id (FK)    │
            │ atasan_2_id (FK)    │
            │ tanggal_mulai       │
            │ tanggal_selesai     │
            │ status_pemesanan    │
            │ catatan             │
            │ created_at          │
            └──────┬──────────────┘
                   │
        ┌──────────┼──────────────┐
        │                         │
   (1:N)│                    (1:N)│
        │                         │
   ┌────▼─────────────────┐  ┌───▼──────────────────┐
   │ LOG_PERSETUJUAN      │  │ RIWAYAT_PEMAKAIAN    │
   │                      │  │                      │
   │ id (PK)              │  │ id (PK)              │
   │ pemesanan_id (FK)    │  │ pemesanan_id (FK)    │
   │ penyetujui_id (FK)   │  │ jarak_tempuh_km      │
   │ level (tinyint)      │  │ bbm_terpakai_liter   │
   │ aksi (enum)          │  │ keterangan           │
   │ catatan_tambahan     │  │ created_at           │
   │ created_at           │  │ updated_at           │
   └──────────────────────┘  └──────────────────────┘

┌────────────────────────────────────────┐
│      LOG_AKTIVITAS (Audit Trail)       │
│                                        │
│ id (PK)                                │
│ user_id (FK→User)                      │
│ action (string) - e.g. "create", "update"  │
│ endpoint (string) - e.g. "/pemesanan" │
│ ip_address (string)                    │
│ user_agent (text)                      │
│ created_at                             │
└────────────────────────────────────────┘
```

### 🔑 Foreign Key Relationships

| Dari | Ke | Tipe | On Delete | On Update |
|------|----|----|-----------|-----------|
| pemesanan.admin_id | users.id | N:1 | RESTRICT | CASCADE |
| pemesanan.kendaraan_id | kendaraan.id | N:1 | RESTRICT | CASCADE |
| pemesanan.driver_id | driver.id | N:1 | RESTRICT | CASCADE |
| pemesanan.atasan_1_id | users.id | N:1 | RESTRICT | CASCADE |
| pemesanan.atasan_2_id | users.id | N:1 | RESTRICT | CASCADE |
| kendaraan.kantor_id | kantor.id | N:1 | RESTRICT | CASCADE |
| log_persetujuan.pemesanan_id | pemesanan.id | N:1 | CASCADE | CASCADE |
| log_persetujuan.penyetujui_id | users.id | N:1 | RESTRICT | CASCADE |
| riwayat_pemakaian.pemesanan_id | pemesanan.id | N:1 | CASCADE | CASCADE |
| log_aktivitas.user_id | users.id | N:1 | RESTRICT | CASCADE |

### 📊 Data Types & Constraints

```sql
-- Enum fields yang digunakan:
kendaraan.jenis: ENUM('angkutan_orang', 'angkutan_barang')
kendaraan.kepemilikan: ENUM('milik_perusahaan', 'sewa')
driver.status: ENUM('tersedia', 'sibuk') DEFAULT 'tersedia'
pemesanan.status_pemesanan: ENUM('menunggu_persetujuan', 'disetujui_level_1', 'disetujui_final', 'ditolak')
users.role: ENUM('admin', 'penyetujui')
log_persetujuan.aksi: ENUM('setuju', 'tolak')
```

---

## 5. IMPLEMENTATION DETAILS

### 🔐 Authentication & Authorization

#### AuthController.php
```php
// Login dengan email & password
// Validasi kredensial
// Session-based authentication (Laravel default)
// Password hashing: bcrypt (otomatis)

// Roles:
// - admin: Akses penuh (pemesanan, laporan, dashboard)
// - penyetujui: Hanya view persetujuan pending
```

#### Middleware: RoleMiddleware.php
```php
// Guard route based on user role
// Contoh: Route::middleware('role:admin')->group(...)
// Throw 403 jika user tidak punya role yang diperlukan
```

#### Middleware: ActivityLogMiddleware.php
```php
// Log setiap request user:
// - user_id
// - action (extracted from route)
// - endpoint (url path)
// - ip_address
// - user_agent
// Dijalankan setelah auth middleware
```

---

### 🚀 Models & Relationships

#### 1. Pemesanan Model

**Key Relationships:**
```php
class Pemesanan extends Model {
    // Many-to-One ke User
    public function admin(): BelongsTo { ... }
    public function atasan1(): BelongsTo { ... }
    public function atasan2(): BelongsTo { ... }
    
    // Many-to-One ke Kendaraan & Driver
    public function kendaraan(): BelongsTo { ... }
    public function driver(): BelongsTo { ... }
    
    // One-to-Many
    public function logPersetujuan(): HasMany { ... }
    public function riwayatPemakaian(): HasMany { ... }
}
```

**Mass Assignment:**
```php
protected $fillable = [
    'admin_id', 'kendaraan_id', 'driver_id',
    'atasan_1_id', 'atasan_2_id',
    'tanggal_mulai', 'tanggal_selesai',
    'status_pemesanan', 'catatan',
];
```

**Date Casting:**
```php
protected $casts = [
    'tanggal_mulai' => 'datetime',
    'tanggal_selesai' => 'datetime',
];
```

---

#### 2. LogPersetujuan Model

```php
class LogPersetujuan extends Model {
    // Relasi ke Pemesanan & User (penyetujui)
    public function pemesanan(): BelongsTo { ... }
    public function penyetujui(): BelongsTo { ... }
}
```

**Gunanya:**
- Audit trail untuk setiap approval
- Store: penyetujui, level, action (setuju/tolak), catatan

---

### 🎛️ Controllers & Actions

#### PemesananController.php

**Action 1: index()**
- Load semua pemesanan dengan eager loading relasi
- Display sebagai list dengan detail kendaraan, driver, penyetuju, status

**Action 2: searchKendaraan()**
- API endpoint untuk autocomplete kendaraan
- Search by: nama, jenis, nama kantor
- Pagination: 20 results per page
- Return JSON response

**Action 3: searchDriver()**
- API endpoint untuk autocomplete driver
- Search by: nama, telepon
- Return JSON response

**Action 4: searchPenyetuju()**
- API endpoint untuk autocomplete penyetuju (role='penyetujui')

**Action 5: store()**
- Validate semua input
- Create pemesanan dengan status 'menunggu_persetujuan'
- Log aktivitas

**Action 6: storeRiwayat()**
- Add riwayat pemakaian ke pemesanan
- Only jika status = 'disetujui_final'
- Input: jarak_tempuh_km, bbm_terpakai_liter, keterangan

---

#### PersetujuanController.php

**Action 1: index()**
- Load pemesanan where user adalah atasan_1 atau atasan_2
- Filter berdasarkan status (yang bisa diapprove)

**Action 2: update()**
- **Key Logic:**
  ```php
  DB::transaction(function() {
      // Cek status & user ID
      // Jika level 1 & status menunggu_persetujuan:
      //   - Create log persetujuan
      //   - Update status (setuju→level_1 | tolak→ditolak)
      // Jika level 2 & status level_1:
      //   - Create log persetujuan
      //   - Update status (setuju→final | tolak→ditolak)
  });
  ```
- Validate aksi (setuju/tolak) & catatan_tambahan (opsional)
- Transaction untuk ensure data consistency

---

#### DashboardController.php

```php
public function index(): View {
    // Query dengan join & group by
    $chartRows = RiwayatPemakaian::query()
        ->join('pemesanan', ...)
        ->join('kendaraan', ...)
        ->select('kendaraan.nama', DB::raw('SUM(jarak) as total_km'))
        ->groupBy('kendaraan.nama')
        ->orderByDesc('total_km')
        ->get();
    
    // Return metrics & chart data
}
```

---

#### LaporanController.php

**Actions:**

1. **index()** - Laporan pemesanan dengan filter tanggal
2. **export()** - Export ke Excel dengan date range
3. **logPersetujuan()** - History approval dengan filter tanggal
4. **logAktivitas()** - User activity log dengan filter tanggal & user

---

### 🔌 Exports: PemesananExport.php

**Implementation:**
```php
class PemesananExport implements 
    FromCollection,      // Data dari collection
    WithHeadings,        // Header row
    ShouldAutoSize,      // Auto width columns
    WithStyles,          // Custom styling
    WithEvents           // Events (freeze pane, filter, border)
{
    public function collection(): Collection { ... }
    public function headings(): array { ... }
    public function styles(Worksheet $sheet): array { ... }
    public function registerEvents(): array { ... }
}
```

**Features:**
- Column autosizing
- Header styling: font bold white, background blue (#1F4E78)
- Data alignment: center
- Borders: thin gray
- Freeze pane di row 1
- Auto-filter di header

---

## 6. CODE HIGHLIGHTS & BEST PRACTICES

### ✅ Best Practices yang Diterapkan

#### 1. **Eager Loading (Prevent N+1 Query)**

```php
// ❌ BAD - Akan buat N+1 query
foreach ($pemesanans as $pemesanan) {
    echo $pemesanan->admin->nama;  // Query per loop
}

// ✅ GOOD - Eager loading
$pemesanans = Pemesanan::with(['admin', 'kendaraan', 'driver'])->get();
foreach ($pemesanans as $pemesanan) {
    echo $pemesanan->admin->nama;  // Tidak ada query baru
}
```

**Implementasi di project:**
```php
// PemesananController.index()
Pemesanan::query()
    ->with(['admin', 'kendaraan', 'driver', 'atasan1', 'atasan2', 'riwayatPemakaian'])
    ->latest()
    ->get();
```

#### 2. **Database Transactions untuk Atomicity**

```php
// ✅ GOOD - All or nothing
DB::transaction(function () {
    LogPersetujuan::create([...]);
    $pemesanan->update([...]);
});
```

**Gunanya:**
- Jika create log gagal, update juga tidak jadi
- Maintain data consistency

**Implementasi di project:**
```php
// PersetujuanController.update()
DB::transaction(function () use ($pemesanan, $data, $userId) {
    // Validation checks
    // Create log
    // Update status
});
```

#### 3. **Route Model Binding**

```php
// ✅ GOOD - Laravel automatically inject model
Route::patch('/persetujuan/{pemesanan}', [PersetujuanController::class, 'update']);

// Di controller:
public function update(Request $request, Pemesanan $pemesanan) {
    // $pemesanan is automatically resolved from route parameter
}
```

#### 4. **Form Request Validation (Implicit validation)**

```php
// ✅ GOOD - Validate & throw automatically
$data = $request->validate([
    'aksi' => ['required', Rule::in(['setuju', 'tolak'])],
    'catatan_tambahan' => ['nullable', 'string'],
]);
```

**Rules diterapkan:**
- `required` - Field wajib ada
- `Rule::in()` - Value harus dari list yang diizinkan
- `nullable` - Boleh null
- `string` - Type check
- `date` - Date format validation
- `after_or_equal` - Date comparison

#### 5. **Middleware Stack untuk Cross-Cutting Concerns**

```php
Route::middleware(['auth', 'activity.log'])->group(function () {
    // All routes dalam group:
    // 1. Check authentication
    // 2. Log aktivitas user
});

Route::middleware('role:admin')->group(function () {
    // Check role authorization
});
```

#### 6. **JSON API Response untuk Autocomplete**

```php
// ✅ GOOD - Return structured JSON
return response()->json([
    'results' => $items->map(fn($item) => [
        'id' => $item->id,
        'text' => $item->name,
    ])->values(),
    'pagination' => ['more' => $result->hasMorePages()],
]);
```

#### 7. **Casting untuk Type Safety**

```php
// ✅ GOOD - Automatic type conversion
protected $casts = [
    'tanggal_mulai' => 'datetime',
    'tanggal_selesai' => 'datetime',
];

// Otomatis convert ke Carbon instance
$pemesanan->tanggal_mulai->format('d-m-Y'); // Works!
```

#### 8. **Attribute-based Fillable (PHP 8.1+)**

```php
// ✅ GOOD - Cleaner syntax
#[Fillable(['nama', 'email', 'password', 'role', 'kantor_id'])]
class User extends Authenticatable { ... }

// vs traditional:
protected $fillable = ['nama', 'email', ...];
```

#### 9. **String Interpolation untuk SQL Injection Prevention**

```php
// ✅ GOOD - Parameterized query
->where('nama', 'like', "%{$search}%")

// vs ❌ DANGEROUS:
// ->where('nama', 'like', '%' . $search . '%') // potential injection
```

#### 10. **Closed-over Functions untuk Clean Query Builders**

```php
// ✅ GOOD - Readable nested conditions
$query->where(function ($builder) use ($search) {
    $builder->where('nama', 'like', "%{$search}%")
        ->orWhere('jenis', 'like', "%{$search}%")
        ->orWhereHas('kantor', function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%");
        });
});
```

---

### 🎨 Code Quality Highlights

#### Clean Code Principles

1. **Meaningful Names:**
   - `searchKendaraan()` - jelas apa fungsinya
   - `storeRiwayat()` - jelas menyimpan apa
   - `pemesananList` - jelas ini adalah list

2. **Single Responsibility Principle:**
   - PemesananController: handle pemesanan operations saja
   - PersetujuanController: handle approval saja
   - DashboardController: handle dashboard saja

3. **DRY (Don't Repeat Yourself):**
   - Reusable `searchKendaraan()`, `searchDriver()`, `searchPenyetuju()` - pattern sama
   - Trait usage (HasFactory, Notifiable dari Eloquent)

4. **Small Functions:**
   - Controllers method rata-rata < 50 lines
   - Easy to test & understand

---

## 7. COMMON INTERVIEW QUESTIONS & ANSWERS

### 📌 Architecture & Design Questions

#### Q1: "Jelaskan alur approval workflow di aplikasi Anda"

**Answer:**
Aplikasi ini menggunakan **2-level approval system**:

1. **Admin membuat pemesanan** dengan memilih:
   - Kendaraan
   - Driver
   - Penyetuju Level 1 (atasan_1)
   - Penyetuju Level 2 (atasan_2)
   - Status awal: `menunggu_persetujuan`

2. **Penyetuju Level 1 review:**
   - Approve → Status: `disetujui_level_1`
   - Reject → Status: `ditolak` (berhenti)
   - Create log entry dengan level=1, aksi, catatan

3. **Penyetuju Level 2 review:**
   - Approve → Status: `disetujui_final`
   - Reject → Status: `ditolak`
   - Create log entry dengan level=2, aksi, catatan

4. **Setelah final approval:**
   - Admin bisa input riwayat pemakaian (km, bbm)
   - Data stored di `riwayat_pemakaian` table

**Implementasi:**
- Menggunakan `DB::transaction()` untuk ensure atomicity
- Status validation sebelum update
- User ID validation untuk security
- Tidak bisa bypass level atau approve 2x

---

#### Q2: "Teknologi apa yang Anda gunakan dan mengapa?"

**Answer:**

| Tech | Gunanya | Alasan |
|------|---------|--------|
| **Laravel 13** | Backend framework | MVC pattern, built-in auth, validation, ORM |
| **PHP 8.3** | Programming language | Modern features (attributes, typed properties), performance |
| **MySQL 8.x** | Database | Reliable, support ENUM, good for relationships |
| **Eloquent ORM** | Database abstraction | Clean query builder, eager loading, relationship management |
| **Vite** | Frontend build tool | Fast development, hot reload, asset bundling |
| **maatwebsite/excel** | Excel export | Powerful styling, formula support, easy integration |
| **Docker** | Containerization | Consistent dev/prod environment, easy deployment |

**Mengapa Laravel?**
- Powerful ORM (Eloquent) untuk model relationships
- Built-in middleware untuk auth & authorization
- Validation framework yang comprehensive
- Migration system untuk database versioning
- Testing framework (Pest)

---

#### Q3: "Apa design pattern yang Anda gunakan?"

**Answer:**

1. **MVC Pattern:**
   - **Route** → handles URL routing
   - **Model** → Eloquent models dengan relationship definitions
   - **Controller** → business logic, validation
   - **View** → Blade templates

2. **Repository Pattern (Implicit):**
   - Query logic di Eloquent models
   - Controllers call model methods
   - Easy to swap/test

3. **Transaction Pattern:**
   - Multiple DB operations wrapped in `DB::transaction()`
   - Ensure all-or-nothing consistency
   - Used in approval workflow

4. **Service Layer Pattern (Potential):**
   - Could extract complex logic ke service classes
   - Currently logic ada di controllers

5. **Middleware Pattern:**
   - Request filtering (Auth, RoleMiddleware, ActivityLog)
   - Cross-cutting concerns handling

---

### 🔴 Technical Deep-Dive Questions

#### Q4: "Mengapa harus gunakan transaction di approval workflow?"

**Answer:**

Tanpa transaction, scenario error bisa:
```
1. LogPersetujuan::create() ✓ - success
2. $pemesanan->update() ✗ - database error
Result: Log ada tapi status tidak updated (data inconsistent)
```

Dengan transaction:
```php
DB::transaction(function () {
    LogPersetujuan::create([...]);  // Step 1
    $pemesanan->update([...]);       // Step 2
    // Jika error di step 2, step 1 di-rollback
});
```

**Benefit:**
- Data consistency guaranteed
- No orphaned logs
- Atomic operation - user sees completed state atau original state, never in-between

---

#### Q5: "Bagaimana Anda prevent N+1 query problem?"

**Answer:**

**N+1 Problem:**
```php
// ❌ BAD - Query dalam loop
$pemesanans = Pemesanan::all();
foreach ($pemesanans as $p) {
    echo $p->admin->nama;  // 1 query per pemesanan + 1 query list = N+1
}
```

**Solution - Eager Loading:**
```php
// ✅ GOOD
$pemesanans = Pemesanan::with(['admin', 'kendaraan', 'driver'])->get();
// Total queries: 3 (1 pemesanan + 1 admin + 1 kendaraan, etc)
foreach ($pemesanans as $p) {
    echo $p->admin->nama;  // Dari memory (already loaded)
}
```

**Di project:**
- PemesananController.index() → eager load semua relasi
- PersetujuanController.index() → eager load logPersetujuan
- LaporanController → eager load admin, kendaraan, driver

---

#### Q6: "Jelaskan validation rules yang Anda gunakan di approval"

**Answer:**

```php
$request->validate([
    'aksi' => ['required', Rule::in(['setuju', 'tolak'])],
    'catatan_tambahan' => ['nullable', 'string'],
]);
```

**Rules breakdown:**
- `aksi` REQUIRED - admin harus pilih approve/reject
- `aksi` IN LIST - hanya boleh 'setuju' atau 'tolak' (prevent invalid status)
- `catatan_tambahan` NULLABLE - bisa kosong
- `catatan_tambahan` STRING - harus text, bukan number/array

**Backend validasi:**
- Cek user ID sama dengan pemesanan atasan_1 atau atasan_2
- Cek status pemesanan sesuai expected state
- Cek tidak sudah diapprove level ini

**Gunanya:**
- Security: prevent injection/invalid data
- Consistency: enforce business rules
- UX: clear error messages

---

#### Q7: "Bagaimana Excel export dengan styling bekerja?"

**Answer:**

```php
class PemesananExport implements 
    FromCollection,      // Source data
    WithHeadings,        // Header row
    ShouldAutoSize,      // Column width
    WithStyles,          // Styling
    WithEvents           // Post-processing
{
    public function collection(): Collection {
        // Query & transform data
    }
    
    public function headings(): array {
        return ['ID', 'Admin', 'Kendaraan', ...];
    }
    
    public function styles(Worksheet $sheet): array {
        return [
            1 => [           // Row 1
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
    
    public function registerEvents(): array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');  // Freeze header
                $sheet->setAutoFilter('A1:J1');  // Filter
                // Border styling
            },
        ];
    }
}
```

**Features:**
- Data aggregation dengan Eloquent
- Heading mapping
- Column auto-widening
- Header styling (bold, color, center)
- Freeze pane & auto filter
- Border styling

---

### 🛡️ Security Questions

#### Q8: "Bagaimana Anda handle authorization di routes?"

**Answer:**

1. **Middleware Protection:**
   ```php
   Route::middleware(['auth', 'role:admin'])->group(function () {
       // Only authenticated users with 'admin' role
   });
   ```

2. **RoleMiddleware Implementation:**
   ```php
   class RoleMiddleware {
       public function handle(Request $request, Closure $next, ...$roles) {
           if (!Auth::user() || !in_array(Auth::user()->role, $roles)) {
               abort(403, 'Unauthorized');
           }
           return $next($request);
       }
   }
   ```

3. **Model-level Authorization:**
   ```php
   // Update() method cek user ID
   if ($pemesanan->atasan_1_id !== $userId) {
       return back(); // User tidak punya akses
   }
   ```

4. **Validation:**
   - Status validation sebelum state transition
   - User ID validation untuk setiap action

**Result:** Multi-layer security
- Route middleware (first line)
- Controller logic (second line)
- Model validation (third line)

---

#### Q9: "Bagaimana Anda prevent SQL Injection?"

**Answer:**

1. **Parameter Binding (Built-in Eloquent):**
   ```php
   // ✅ SAFE - Eloquent parameterizes automatically
   ->where('nama', 'like', "%{$search}%")
   
   // vs ❌ DANGEROUS (raw string concatenation)
   // ->whereRaw("nama LIKE '%{$search}%'")
   ```

2. **Query Builder:**
   ```php
   // ✅ SAFE
   $builder->where('nama', 'like', "%{$search}%")
       ->orWhere('jenis', 'like', "%{$search}%")
   
   // All values are parameterized
   ```

3. **Mass Assignment Protection:**
   ```php
   #[Fillable(['admin_id', 'kendaraan_id', ...])]
   class Pemesanan extends Model { ... }
   
   // Hanya field di $fillable bisa diisi dari request
   // Prevent attacker dari set field lain (e.g., status)
   ```

4. **Validation:**
   ```php
   // Validate aksi hanya boleh 'setuju' atau 'tolak'
   'aksi' => ['required', Rule::in(['setuju', 'tolak'])]
   
   // Prevent attacker dari set status langsung
   ```

---

### 🚀 Performance Questions

#### Q10: "Bagaimana Anda optimize database queries?"

**Answer:**

1. **Eager Loading:**
   ```php
   // Load relations in one query
   Pemesanan::with(['admin', 'kendaraan', 'driver'])->get();
   ```

2. **Indexing Strategy:**
   ```sql
   -- Foreign keys automatically indexed
   INDEX on pemesanan(admin_id, kendaraan_id, driver_id)
   INDEX on log_persetujuan(pemesanan_id, penyetujui_id)
   
   -- Status lookups
   INDEX on pemesanan(status_pemesanan)
   ```

3. **Query Optimization:**
   ```php
   // Aggregate di database, bukan application
   RiwayatPemakaian::query()
       ->join('kendaraan', ...)
       ->select('kendaraan.nama', DB::raw('SUM(jarak_tempuh_km) as total_km'))
       ->groupBy('kendaraan.nama')
       ->orderByDesc('total_km')
       ->get();
   ```

4. **Pagination untuk Large Result Sets:**
   ```php
   $query->paginate($perPage, ['*'], 'page', $page);
   ```

5. **Caching (Potential):**
   - Cache dashboard chart data (update per interval)
   - Cache approver lists

---

#### Q11: "Bagaimana handle large-scale pemesanan?"

**Answer:**

**Current Implementation:**
- Can handle thousands pemesanan (MySQL dapat scale)
- Pagination untuk list views
- Indexes pada foreign keys

**Potential Improvements:**
1. **Database:**
   - Partitioning pada pemesanan table (by date range)
   - Archive old records

2. **Caching:**
   ```php
   Cache::remember('dashboard_chart', 1_hour, function () {
       return RiwayatPemakaian::query()->groupBy(...)->get();
   });
   ```

3. **Queue Processing:**
   - Excel export async via queue
   - Heavy reports di background job

4. **Search Optimization:**
   ```php
   // Add full-text search jika perlu
   ->whereFullText(['nama', 'jenis'], $search)
   ```

---

### 💡 Behavioral Questions

#### Q12: "Apa challenge terbesar yang Anda hadapi saat develop project ini?"

**Answer:**

**Challenge 1: 2-Level Approval Workflow Logic**
- Complex state management dengan 4 status
- Need to ensure correct level transitions
- Must validate user ID sesuai level
- **Solution:** Use transaction untuk atomicity, explicit status checks

**Challenge 2: Data Consistency**
- Multiple tables terhubung (pemesanan → log → riwayat)
- Foreign key constraints perlu careful
- **Solution:** Cascade ON DELETE untuk orphaned records, transaction untuk updates

**Challenge 3: Search Performance**
- Large dataset dengan 3 search fields (nama, jenis, kantor)
- Autocomplete needs to be fast
- **Solution:** Pagination (20 per page), index pada search fields

**Challenge 4: Excel Export with Complex Data**
- Multiple aggregations & relatings
- Custom styling requirements
- **Solution:** maatwebsite/excel library dengan custom export class

**Lessons Learned:**
- Transaction untuk consistency
- Eager loading untuk performance
- Validation di multiple layers untuk security

---

#### Q13: "Bagaimana Anda ensure code quality?"

**Answer:**

1. **Following Laravel Best Practices:**
   - Route model binding
   - Eager loading
   - Validation at controller level
   - Middleware untuk cross-cutting concerns

2. **Code Organization:**
   - Separation of concerns (Controllers, Models, Exports)
   - Meaningful naming conventions
   - Single responsibility principle

3. **Database Design:**
   - Proper foreign key relationships
   - Enum fields untuk constrained values
   - Cascade rules untuk data cleanup

4. **Testing (Could implement):**
   ```php
   // Pest framework available
   test('approval workflow transitions correctly', function () {
       $pemesanan = Pemesanan::factory()->create();
       $user = User::factory()->penyetujui()->create();
       
       // Approve level 1
       actingAs($user)->patch("/persetujuan/{$pemesanan}", [
           'aksi' => 'setuju',
       ]);
       
       expect($pemesanan->refresh()->status_pemesanan)
           ->toBe('disetujui_level_1');
   });
   ```

5. **Security Practices:**
   - Authentication & authorization
   - Input validation
   - Mass assignment protection
   - Activity logging untuk audit trail

---

#### Q14: "Bagaimana Anda akan maintain dan scale aplikasi ini?"

**Answer:**

**Maintenance:**
1. Regular database backups
2. Monitor query performance (slow query log)
3. Update dependencies regularly (composer update)
4. Keep Laravel version updated

**Scaling Strategies:**
1. **Database:**
   - Add indexes di frequently queried fields
   - Archiving old records (pemesanan lama)
   - Read replicas untuk report queries

2. **Caching:**
   - Redis untuk session & cache
   - Cache dashboard metrics
   - Cache approver lists

3. **Async Processing:**
   ```php
   // Queue Excel exports
   dispatch(new ExportPemesananJob($startDate, $endDate));
   ```

4. **API Layer:**
   - Create REST API versi (separate dari web)
   - Authentication token-based (JWT)

5. **Monitoring:**
   - Log tracking (built-in)
   - Performance monitoring
   - Error tracking (Sentry)

6. **DevOps:**
   - CI/CD pipeline (GitHub Actions)
   - Automated testing
   - Docker deployment

---

## 8. DEMO TALKING POINTS

### 🎯 Saat Demo ke Interviewer

#### Demo Flow:

1. **Login & Dashboard**
   - Show 2 akun: Admin & Penyetujui
   - Point out chart (Total KM per kendaraan)
   - Explain data aggregation logic

2. **Pemesanan Flow (Admin)**
   - Create pemesanan baru
   - Show autocomplete search untuk kendaraan, driver
   - Explain selection criteria untuk penyetuju
   - Explain status awal: `menunggu_persetujuan`
   - Input riwayat pemakaian (hanya setelah final approval)

3. **Approval Workflow (Penyetujui)**
   - Login sebagai approver level 1
   - Show pending pembukuan
   - Demonstrate approve/reject dengan catatan
   - Show status transition to `disetujui_level_1`
   - Switch to approver level 2
   - Show same workflow untuk level 2
   - Final approval transition to `disetujui_final`

4. **Reports (Admin)**
   - Show laporan pemesanan dengan date filter
   - Export ke Excel - show styling, freeze pane
   - Show log persetujuan (history)
   - Show log aktivitas (user actions)

---

### 💬 Key Points to Emphasize:

1. **2-Level Workflow:**
   - "Untuk ensure accountability, setiap pemesanan harus approve 2 orang"
   - "Workflow logic built-in ke application, tidak perlu manual process"

2. **Data Integrity:**
   - "Transaction ensures atomicity - log & status diupdate bersamaan"
   - "Foreign key constraints mencegah orphaned data"

3. **Performance:**
   - "Eager loading prevent N+1 queries"
   - "Dashboard chart aggregated di database, bukan application"

4. **Security:**
   - "Role-based authorization di middleware & controller level"
   - "Activity logging untuk audit trail"
   - "Mass assignment protection & validation"

5. **Code Quality:**
   - "Following Laravel best practices & conventions"
   - "Clean code principles: meaningful names, SRP"

---

## 9. CHALLENGES & SOLUTIONS

### 🔴 Challenge 1: Approval State Management

**Problem:** Complex state transitions dengan multiple users

**Scenario:**
```
Pemesanan created → Level 1 reject → Status ditolak
admin try create log → should check status first
```

**Solution:**
```php
if ($pemesanan->status_pemesanan === 'ditolak' || 
    $pemesanan->status_pemesanan === 'disetujui_final') {
    return; // Don't process
}
```

---

### 🔴 Challenge 2: N+1 Query Problem

**Problem:** Multiple queries untuk load related data

**Scenario:**
```php
foreach (Pemesanan::all() as $p) {
    echo $p->admin->nama;      // Query 1 per pemesanan
    echo $p->driver->nama;     // Query 2 per pemesanan
    echo $p->kendaraan->nama;  // Query 3 per pemesanan
}
// Total: 1 + (N * 3) queries
```

**Solution:**
```php
Pemesanan::with(['admin', 'driver', 'kendaraan'])->get()
// Total: 4 queries (1 pemesanan + 3 rel)
```

---

### 🔴 Challenge 3: Data Consistency pada Update

**Problem:** Jika ada error di tengah proses, data bisa inconsistent

**Scenario:**
```
Create log persetujuan → OK
Update status pemesanan → FAIL (DB connection lost)
Result: Log ada, status tidak update
```

**Solution:**
```php
DB::transaction(function () {
    LogPersetujuan::create([...]);
    $pemesanan->update([...]);
    // Either both succeed or both rollback
});
```

---

### 🔴 Challenge 4: Preventing Unauthorized Approval

**Problem:** User bisa coba approve pemesanan yang bukan untuk dia

**Scenario:**
```php
// Hacker post to approve dengan user_id=2 padahal atasan_1_id=1
PATCH /persetujuan/5
```

**Solution:**
```php
$userId = Auth::id();
if ($pemesanan->atasan_1_id !== $userId) {
    return back(); // Unauthorized
}
```

**Plus:**
- Check status sesuai expected flow
- Validate aksi value (setuju/tolak only)

---

### 🔴 Challenge 5: Excel Export Styling

**Problem:** Export to Excel dengan formatting yang bagus

**Solution:**
```php
class PemesananExport implements 
    WithStyles,          // Apply CSS-like styling
    WithEvents,          // Freeze pane, filter
    ShouldAutoSize { }   // Auto width
```

**Features:**
- Header row: bold white text, blue background
- All cells: thin borders
- Freeze first row
- Auto-filter di header

---

## 10. PERFORMANCE & SECURITY

### ⚡ Performance Metrics

| Komponenen | Target | Implementasi |
|-----------|--------|--------------|
| Pemesanan list load | < 1s | Eager loading + pagination |
| Dashboard chart | < 2s | DB aggregation + potential caching |
| Autocomplete search | < 500ms | Pagination (20 per page) + indexed fields |
| Excel export | < 5s | Streaming export, background job (future) |

### 🔐 Security Checklist

| Item | Status | Detail |
|------|--------|--------|
| Authentication | ✅ | Laravel Auth, password hashed |
| Authorization | ✅ | RoleMiddleware, controller checks |
| Input Validation | ✅ | Server-side validation |
| SQL Injection | ✅ | Parameterized queries |
| Mass Assignment | ✅ | $fillable protection |
| CSRF | ✅ | Laravel middleware (default) |
| Activity Logging | ✅ | ActivityLogMiddleware |
| Foreign Keys | ✅ | Cascade rules |

---

## 11. FUTURE IMPROVEMENTS

### 🚀 Potential Enhancements

#### 1. **Real-time Notifications**
```php
// Push notification jika ada pemesanan baru untuk approver
event(new PemesananCreated($pemesanan));

// Broadcast via WebSocket
event(new PemesananApproved($pemesanan))->broadcast();
```

#### 2. **Advanced Filtering & Search**
```php
// Full-text search
->whereFullText(['nama', 'catatan'], $search)

// Status summary dashboard
$counts = Pemesanan::select('status_pemesanan', DB::raw('count(*) as total'))
    ->groupBy('status_pemesanan')
    ->get();
```

#### 3. **Async Processing**
```php
// Queue Excel exports
dispatch(new ExportPemesananJob($filters))->onQueue('exports');

// Send email approvals
dispatch(new SendApprovalEmailJob($pemesanan, $approver));
```

#### 4. **API REST Layer**
```php
// Separate API routes dengan authentication (token-based)
Route::prefix('api')->middleware(['api', 'auth:api'])->group(function () {
    Route::get('/pemesanan', [PemesananApiController::class, 'index']);
    Route::post('/pemesanan', [PemesananApiController::class, 'store']);
});
```

#### 5. **Advanced Analytics**
```php
// Dashboard metrics
- Monthly pemesanan count trend
- Approval time analytics
- Driver utilization rate
- Fuel consumption analysis
```

#### 6. **Mobile App**
- React Native atau Flutter frontend
- Call REST API backend
- Push notifications

#### 7. **Approval Workflow Customization**
- Configure approval levels (1, 2, 3)
- Conditional approval rules
- SLA tracking (approval deadline)

#### 8. **Advanced Access Control**
- Multi-tenant support
- Department-based permissions
- Approval delegation

---

## 12. QUICK REFERENCE

### 📌 Key Statistics

- **Total Models:** 8
- **Total Controllers:** 6
- **Total Migrations:** 3 (kerja pokok)
- **Total Routes:** 15+
- **Middleware:** 2 custom (Role, ActivityLog)
- **Export Classes:** 1 (PemesananExport)

### 🔑 Key Tables

```
kantor (Branches)
├── kendaraan (Vehicles) - belongs to kantor
└── users (Users) - belongs to kantor

pemesanan (Bookings) - central
├── admin_id → users
├── kendaraan_id → kendaraan
├── driver_id → driver
├── atasan_1_id → users
├── atasan_2_id → users
├── log_persetujuan (approval logs)
└── riwayat_pemakaian (usage history)

log_aktivitas (activity audit trail)
└── user_id → users
```

### 🎯 Status Workflow

```
NEW: menunggu_persetujuan
  ↓
IF REJECT: ditolak (END)
  ↓
IF APPROVE: disetujui_level_1
  ↓
IF REJECT: ditolak (END)
  ↓
IF APPROVE: disetujui_final (READY FOR USE)
```

### 🔌 Main API Endpoints (JSON)

```
GET    /pemesanan/search/kendaraan?q=nama&page=1
GET    /pemesanan/search/driver?q=nama&page=1
GET    /pemesanan/search/penyetujui?q=nama&page=1
POST   /pemesanan (create)
POST   /pemesanan/{id}/riwayat (add usage history)
PATCH  /persetujuan/{id} (approve/reject)
GET    /laporan/export (download Excel)
```

---

## SUMMARY FOR INTERVIEW

**Saat interviewer tanya:** "Ceritakan tentang project technical test Anda"

**Template Jawaban:**

> Saya membuat **Sistem Pemesanan Kendaraan Perusahaan** berbasis Laravel 13. Aplikasi ini dirancang untuk perusahaan tambang dengan fitur utama:
>
> 1. **Workflow Approval 2-Level:** Setiap pemesanan harus disetujui oleh 2 approver untuk memastikan akuntabilitas. Menggunakan database transaction untuk menjamin atomicity.
>
> 2. **Dashboard Analytics:** Real-time chart menampilkan total kilometer per kendaraan, aggregated dari riwayat pemakaian.
>
> 3. **Activity Logging:** Setiap user action tercatat untuk audit trail, termasuk approval history dan usage logs.
>
> 4. **Excel Report:** Admin bisa export laporan dengan filter tanggal, ke format Excel dengan custom styling.
>
> **Technology:** Laravel 13, PHP 8.3, MySQL 8, Eloquent ORM, maatwebsite/excel
>
> **Best Practices:** Eager loading untuk prevent N+1 queries, transaction untuk data consistency, middleware untuk authorization, input validation untuk security, role-based access control.
>
> **Key Learning:** Pentingnya state management di workflow, optimize database queries, implement multi-layer security.

---

**Good luck sa interview! 🚀**

