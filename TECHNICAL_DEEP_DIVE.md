# 🔬 TECHNICAL DEEP DIVE - CODE EXPLANATIONS

---

## 📌 TABLE OF CONTENTS

1. [Core Architecture Walkthrough](#core-architecture-walkthrough)
2. [Models & Relationships](#models--relationships-detailed)
3. [Controllers Deep Dive](#controllers-deep-dive)
4. [Middleware Explanation](#middleware-explanation)
5. [Database Design & Constraints](#database-design--constraints)
6. [Advanced Query Patterns](#advanced-query-patterns)
7. [Performance Optimization Tips](#performance-optimization-tips)
8. [Security Implementation Details](#security-implementation-details)

---

## 🏛️ CORE ARCHITECTURE WALKTHROUGH

### Request Lifecycle

```
1. USER REQUEST
   └─> GET /pemesanan HTTP/1.1

2. ROUTING (routes/web.php)
   ├─ Match route
   ├─ Resolve controller & method
   └─ Pass parameters

3. MIDDLEWARE STACK
   ├─ auth (verify login)
   ├─ role:admin (check role)
   └─ activity.log (log action)

4. CONTROLLER LOGIC
   ├─ Validate input
   ├─ Query data
   ├─ Transform data
   └─ Pass to view

5. VIEW (Blade template)
   ├─ Render HTML
   ├─ Display data
   └─ Add forms

6. RESPONSE
   └─> HTTP/1.1 200 OK
       Content: HTML/JSON
```

### Example: Create Pemesanan Request

```
POST /pemesanan HTTP/1.1
Content-Type: application/x-www-form-urlencoded

kendaraan_id=1&driver_id=2&atasan_1_id=3&atasan_2_id=4&...
```

#### Step 1: Routing
```php
// routes/web.php
Route::post('/pemesanan', [PemesananController::class, 'store'])
    ->name('pemesanan.store');
```

#### Step 2: Middleware Pipeline
```php
Route::middleware(['auth', 'activity.log'])->group(function () {
    // First: auth middleware checks Auth::check()
    // Second: activity.log middleware logs action
    // Then: RoleMiddleware (role:admin) checks role
    // Finally: Route handler executes
});
```

#### Step 3: Controller Validation
```php
// app/Http/Controllers/PemesananController.php
public function store(Request $request): RedirectResponse {
    // Validate input
    $data = $request->validate([
        'kendaraan_id' => ['required', 'exists:kendaraan,id'],
        'driver_id' => ['required', 'exists:driver,id'],
        'atasan_1_id' => ['required', 'exists:users,id'],
        'atasan_2_id' => ['required', 'exists:users,id'],
        'tanggal_mulai' => ['required', 'date'],
        'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        // ... more validation
    ]);
    
    // Create pemesanan
    Pemesanan::create([
        ...$data,
        'admin_id' => Auth::id(),
        'status_pemesanan' => 'menunggu_persetujuan',
    ]);
    
    return redirect()->back()->with('success', 'Pemesanan berhasil dibuat');
}
```

**Key Points:**
- `exists:` validation checks nilai ada di database
- `after_or_equal:` ensures tanggal_selesai >= tanggal_mulai
- `Auth::id()` get current user ID automatically
- Default status set di controller, bukan frontend

#### Step 4: Database Operation
```sql
INSERT INTO pemesanan 
  (admin_id, kendaraan_id, driver_id, atasan_1_id, atasan_2_id, 
   tanggal_mulai, tanggal_selesai, status_pemesanan, created_at, updated_at)
VALUES 
  (1, 5, 3, 2, 4, '2026-03-25 08:00:00', '2026-03-25 17:00:00', 
   'menunggu_persetujuan', NOW(), NOW());
```

#### Step 5: Response
```php
// Redirect back with success message
return redirect()->back()->with('success', 'Pemesanan berhasil dibuat');
```

---

## 🔗 MODELS & RELATIONSHIPS (DETAILED)

### Pemesanan Model - Center Hub

```php
// app/Models/Pemesanan.php
class Pemesanan extends Model {
    use HasFactory;
    
    protected $table = 'pemesanan';
    
    // Define what can be mass-assigned
    protected $fillable = [
        'admin_id',           // Who created this booking
        'kendaraan_id',       // Which vehicle
        'driver_id',          // Which driver
        'atasan_1_id',        // First approver
        'atasan_2_id',        // Second approver
        'tanggal_mulai',      // Start date
        'tanggal_selesai',    // End date
        'status_pemesanan',   // Current status
        'catatan',            // Notes
    ];
    
    // Type casting
    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];
    
    // ============ RELATIONSHIPS ============
    
    // Creator of booking (admin)
    public function admin(): BelongsTo {
        return $this->belongsTo(User::class, 'admin_id');
    }
    
    // Vehicle being booked
    public function kendaraan(): BelongsTo {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }
    
    // Driver assigned
    public function driver(): BelongsTo {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
    
    // First level approver
    public function atasan1(): BelongsTo {
        return $this->belongsTo(User::class, 'atasan_1_id');
    }
    
    // Second level approver
    public function atasan2(): BelongsTo {
        return $this->belongsTo(User::class, 'atasan_2_id');
    }
    
    // All approval logs for this booking
    public function logPersetujuan(): HasMany {
        return $this->hasMany(LogPersetujuan::class, 'pemesanan_id');
    }
    
    // All usage records for this booking
    public function riwayatPemakaian(): HasMany {
        return $this->hasMany(RiwayatPemakaian::class, 'pemesanan_id');
    }
}
```

**Usage Example:**
```php
// Load pemesanan dengan semua relasi
$pemesanan = Pemesanan::with([
    'admin',              // BelongsTo - one user
    'kendaraan',          // BelongsTo - one vehicle
    'driver',             // BelongsTo - one driver
    'atasan1',            // BelongsTo - one user
    'atasan2',            // BelongsTo - one user
    'logPersetujuan',     // HasMany - multiple logs
    'riwayatPemakaian'    // HasMany - multiple usages
])->find(1);

// Access tanpa N+1 query
echo $pemesanan->admin->nama;           // No extra query
echo $pemesanan->kendaraan->nama;       // No extra query
foreach($pemesanan->logPersetujuan as $log) {
    echo $log->penyetujui->nama;        // No extra query
}
```

### LogPersetujuan Model - Approval Audit

```php
class LogPersetujuan extends Model {
    use HasFactory;
    
    protected $table = 'log_persetujuan';
    
    protected $fillable = [
        'pemesanan_id',      // Which booking
        'penyetujui_id',     // Who approved
        'level',             // Level 1 or 2
        'aksi',              // 'setuju' or 'tolak'
        'catatan_tambahan',  // Optional notes
    ];
    
    // Which booking this log belongs to
    public function pemesanan(): BelongsTo {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }
    
    // Who made the approval
    public function penyetujui(): BelongsTo {
        return $this->belongsTo(User::class, 'penyetujui_id');
    }
}
```

**Purpose:**
- Track setiap approval action (who, what, when)
- Audit trail untuk compliance
- Cannot delete - permanent record

**Typical Data:**
```
id=1, pemesanan_id=5, penyetujui_id=2, level=1, aksi='tolak', 
catatan='Kendaraan dalam servis', created_at='2026-03-25 10:30:00'
```

### Relationship Diagram pada Queries

```php
// Saat query pemesanan dengan approval history:
$pemesanan = Pemesanan::with('logPersetujuan.penyetujui')->find(1);

// Data structure:
$pemesanan = {
    id: 1,
    status: 'disetujui_level_1',
    logPersetujuan: [
        {
            id: 1,
            level: 1,
            aksi: 'tolak',
            created_at: '2026-03-25 10:00:00',
            penyetujui: {
                id: 2,
                nama: 'Adi Saputra',
                email: 'approver1@booking.test'
            }
        }
    ]
}

// Nested relasi dapat diakses:
$pemesanan->logPersetujuan[0]->penyetujui->nama
// Output: "Adi Saputra"
```

---

## 🎛️ CONTROLLERS DEEP DIVE

### PemesananController - Full Analysis

#### 1. index() Method

**Purpose:** Display list pemesanan with filters

```php
public function index(): View {
    // Keep "old" form data jika form submission failed
    $selectedKendaraan = old('kendaraan_id')
        ? Kendaraan::with('kantor')->find(old('kendaraan_id'))
        : null;
    
    $selectedDriver = old('driver_id')
        ? Driver::find(old('driver_id'))
        : null;
    
    $selectedAtasan1 = old('atasan_1_id')
        ? User::where('role', 'penyetujui')->find(old('atasan_1_id'))
        : null;
    
    $selectedAtasan2 = old('atasan_2_id')
        ? User::where('role', 'penyetujui')->find(old('atasan_2_id'))
        : null;
    
    // Load semua pemesanan dengan eager loading
    return view('pemesanan.index', [
        'pemesananList' => Pemesanan::query()
            ->with(['admin', 'kendaraan', 'driver', 'atasan1', 'atasan2', 'riwayatPemakaian'])
            ->latest()  // Order by created_at DESC
            ->get(),
        'selectedKendaraan' => $selectedKendaraan,
        'selectedDriver' => $selectedDriver,
        'selectedAtasan1' => $selectedAtasan1,
        'selectedAtasan2' => $selectedAtasan2,
    ]);
}
```

**Key Techniques:**
- **`old()`** - Repopulate form with previous values (UX)
- **`()->with()`** - Eager load relations
- **`->latest()`** - Shortcut untuk `->orderBy('created_at', 'DESC')`

#### 2. searchKendaraan() Method - AJAX Endpoint

```php
public function searchKendaraan(Request $request): JsonResponse {
    // Get search query dari URL parameter
    $search = (string) $request->query('q', '');
    $page = max((int) $request->query('page', 1), 1);
    $perPage = 20;
    
    // Build query
    $query = Kendaraan::query()->with('kantor')->orderBy('nama');
    
    // Filter jika ada search term
    if ($search !== '') {
        $query->where(function ($builder) use ($search): void {
            // Must match at least one condition
            $builder->where('nama', 'like', "%{$search}%")
                ->orWhere('jenis', 'like', "%{$search}%")
                ->orWhereHas('kantor', function ($kantorQuery) use ($search): void {
                    // Search in related kantor
                    $kantorQuery->where('nama', 'like', "%{$search}%");
                });
        });
    }
    
    // Paginate results
    $result = $query->paginate($perPage, ['*'], 'page', $page);
    
    // Format response sesuai Select2.js format
    return response()->json([
        'results' => $result->getCollection()->map(function (Kendaraan $kendaraan): array {
            return [
                'id' => $kendaraan->id,
                'text' => $kendaraan->nama.' - '.$kendaraan->jenis.' - '.($kendaraan->kantor->nama ?? '-'),
            ];
        })->values(),
        'pagination' => [
            'more' => $result->hasMorePages(),
        ],
    ]);
}
```

**Breaking Down:**

1. **Input Validation:**
   ```php
   $search = (string) $request->query('q', '');  // Cast to string, default ''
   $page = max((int) $request->query('page', 1), 1);  // Min 1, default 1
   ```

2. **Query Building:**
   ```php
   ->orWhereHas('kantor', function($q) use($search) { ... })
   // Search di related table
   ```

3. **Pagination:**
   ```php
   $result->paginate(20, ['*'], 'page', 1);
   // 20 per page, get all columns, page=1
   ```

4. **Response Format:**
   ```json
   {
     "results": [
       {"id": 1, "text": "Truk A - angkutan_barang - Kantor Pusat"}
     ],
     "pagination": {"more": false}
   }
   ```
   Ini format yang diexpect Select2.js library

#### 3. store() Method - Detailed Walkthrough

```php
public function store(Request $request): RedirectResponse {
    // All request data already tied to pemesanan creation
    // No need explicit validation for these
    $data = $request->validate([
        'kendaraan_id' => ['required', 'exists:kendaraan,id'],
        'driver_id' => ['required', 'exists:driver,id'],
        'atasan_1_id' => ['required', 'exists:users,id'],
        'atasan_2_id' => ['required', 'exists:users,id'],
        'tanggal_mulai' => ['required', 'date'],
        'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        'catatan' => ['nullable', 'string'],
    ]);
    
    // Create pemesanan
    Pemesanan::create([
        ...$data,  // Spread validated data
        'admin_id' => Auth::id(),  // Who's creating
        'status_pemesanan' => 'menunggu_persetujuan',  // Default status
    ]);
    
    // Redirect back with success message
    return redirect()->back()->with('success', 'Pemesanan berhasil dibuat');
}
```

**Validation Rules Explained:**

| Rule | Purpose |
|------|---------|
| `required` | Field wajib ada |
| `exists:table,column` | Value harus exist di database |
| `date` | Valid date format (YYYY-MM-DD) |
| `after_or_equal:field` | Date >= another field |
| `nullable` | Bisa null/kosong |
| `string` | Must be text |

**Security Features:**
1. Only `$fillable` fields dari Pemesanan class bisa diisi
2. `exists:` prevent foreign key violations
3. `admin_id` diset dari Auth::id() - tidak bisa dimanipulasi dari form

---

### PersetujuanController - The Critical One

```php
public function update(Request $request, Pemesanan $pemesanan): RedirectResponse {
    // Validate request data
    $data = $request->validate([
        'aksi' => ['required', Rule::in(['setuju', 'tolak'])],
        'catatan_tambahan' => ['nullable', 'string'],
    ]);
    
    $userId = Auth::id();
    
    // This is CRITICAL - transaction ensures atomicity
    DB::transaction(function () use ($pemesanan, $data, $userId): void {
        // Guard 1: Check if already finished
        if ($pemesanan->status_pemesanan === 'ditolak' || 
            $pemesanan->status_pemesanan === 'disetujui_final') {
            return;  // Cannot change again
        }
        
        // Guard 2: Level 1 approval
        if ($pemesanan->atasan_1_id === $userId && 
            $pemesanan->status_pemesanan === 'menunggu_persetujuan') {
            
            // Log the approval action
            LogPersetujuan::create([
                'pemesanan_id' => $pemesanan->id,
                'penyetujui_id' => $userId,
                'level' => 1,
                'aksi' => $data['aksi'],
                'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            ]);
            
            // Update pemesanan status
            $pemesanan->update([
                'status_pemesanan' => $data['aksi'] === 'setuju' 
                    ? 'disetujui_level_1'  // Move to level 2
                    : 'ditolak'             // Reject completely
            ]);
            
            return;  // Done with level 1
        }
        
        // Guard 3: Level 2 approval
        if ($pemesanan->atasan_2_id === $userId && 
            $pemesanan->status_pemesanan === 'disetujui_level_1') {
            
            // Same workflow sebagai level 1
            LogPersetujuan::create([
                'pemesanan_id' => $pemesanan->id,
                'penyetujui_id' => $userId,
                'level' => 2,
                'aksi' => $data['aksi'],
                'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            ]);
            
            $pemesanan->update([
                'status_pemesanan' => $data['aksi'] === 'setuju'
                    ? 'disetujui_final'    // Final approval
                    : 'ditolak'             // Reject
            ]);
        }
    });
    
    return back()->with('success', 'Aksi persetujuan berhasil diproses.');
}
```

**Security Analysis:**

1. **User Validation:**
   ```php
   if ($pemesanan->atasan_1_id === $userId) { ... }
   // Ensure user adalah actually atasan_1 untuk pemesanan ini
   ```

2. **Status Validation:**
   ```php
   && $pemesanan->status_pemesanan === 'menunggu_persetujuan'
   // Ensure di state yang benar
   ```

3. **Action Validation:**
   ```php
   Rule::in(['setuju', 'tolak'])
   // Only allowed values
   ```

4. **Atomicity:**
   ```php
   DB::transaction(function() { ... })
   // All-or-nothing
   ```

**State Transition Diagram:**
```
menunggu_persetujuan
├─ User = atasan_1 & aksi = setuju → disetujui_level_1
├─ User = atasan_1 & aksi = tolak → ditolak
│
disetujui_level_1
├─ User = atasan_2 & aksi = setuju → disetujui_final
├─ User = atasan_2 & aksi = tolak → ditolak
│
disetujui_final / ditolak
└─ No changes allowed (terminal states)
```

---

## 🔐 MIDDLEWARE EXPLANATION

### 1. ActivityLogMiddleware.php

```php
<?php
namespace App\Http\Middleware;

use App\Models\LogAktivitas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogMiddleware {
    public function handle(Request $request, Closure $next): Response {
        // Execute request first
        $response = $next($request);
        
        // After response, log the action
        if (auth()->check()) {
            LogAktivitas::create([
                'user_id' => auth()->id(),
                'action' => $this->getAction($request),  // 'pemesanan.store'
                'endpoint' => $request->getPathInfo(),   // '/pemesanan'
                'ip_address' => $request->ip(),          // '192.168.1.1'
                'user_agent' => $request->userAgent(),   // 'Mozilla/5.0...'
            ]);
        }
        
        return $response;
    }
    
    private function getAction(Request $request): string {
        // Extract action from route name
        return $request->route()?->getName() ?? 'unknown';
    }
}
```

**How It Works:**
1. Request comes in
2. Middleware calls `$next($request)` - let request go through
3. After response generated, log the action
4. Return response to user

**Attached To:**
```php
Route::middleware(['auth', 'activity.log'])->group(function () {
    // All routes here akan dilog
});
```

### 2. RoleMiddleware.php

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware {
    public function handle(Request $request, Closure $next, ...$roles): Response {
        // Check if user has required role
        if (!auth()->check()) {
            // Not authenticated - deny
            abort(401, 'Unauthorized');
        }
        
        if (!in_array(auth()->user()->role, $roles)) {
            // User exist but wrong role - deny
            abort(403, 'Forbidden - insufficient permissions');
        }
        
        // All checks passed - continue
        return $next($request);
    }
}
```

**Usage in Routes:**
```php
Route::middleware('role:admin')->group(function () {
    // Only users with role='admin' can access
    Route::get('/pemesanan', ...);
    Route::post('/laporan/export', ...);
});

Route::middleware('role:penyetujui')->group(function () {
    // Only users with role='penyetujui' can access
    Route::get('/persetujuan', ...);
});
```

**Difference from Authorization in Controller:**
- **Middleware:** Check di gate - prevent route access
- **Controller:** Check di logic - allow route, deny action

**Example:**
```php
// Middleware catches here:
GET /admin/dashboard → RoleMiddleware → Check role → 403 if not admin

// Controller checks:
if ($pemesanan->atasan_1_id !== $userId) {
    return back();  // Allowed route, denied action
}
```

---

## 🗄️ DATABASE DESIGN & CONSTRAINTS

### Foreign Key Strategy

```sql
-- pemesanan table
CREATE TABLE pemesanan (
    id BIGINT PRIMARY KEY,
    admin_id BIGINT NOT NULL,
    kendaraan_id BIGINT NOT NULL,
    driver_id BIGINT NOT NULL,
    atasan_1_id BIGINT NOT NULL,
    atasan_2_id BIGINT NOT NULL,
    
    -- Foreign key constraints
    CONSTRAINT fk_admin 
        FOREIGN KEY (admin_id) 
        REFERENCES users(id) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT,
    
    CONSTRAINT fk_kendaraan 
        FOREIGN KEY (kendaraan_id) 
        REFERENCES kendaraan(id) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT,
    
    -- ... more FKs
);
```

**ON UPDATE CASCADE:**
- Jika users.id berubah (unlikely tapi mungkin), update jg pemesanan.admin_id
- Maintaining referential integrity

**ON DELETE RESTRICT:**
- Tidak boleh delete user jika masih jadi admin_id di pemesanan
- Prevent orphaned records
- Force explicit handling

**Cascade Rules Explained:**

```
ON DELETE CASCADE:
└─ Jika parent deleted, child jg deleted
└─ Used untuk log tables (jika pemesanan deleted, log jg dihapus)

ON DELETE RESTRICT:
└─ Tidak boleh delete parent punya child
└─ Used untuk core tables (prevent data loss)

ON UPDATE CASCADE:
└─ Jika parent ID berubah, child updated juga
└─ Maintaining referential integrity

ON UPDATE RESTRICT:
└─ Tidak boleh update parent ID jika punya child
```

---

## 📊 ADVANCED QUERY PATTERNS

### Pattern 1: Complex Joins dengan Group By

```php
// Dashboard chart query
$chartData = RiwayatPemakaian::query()
    // Join ke tabel kendaraan untuk get nama
    ->join('pemesanan', 'riwayat_pemakaian.pemesanan_id', '=', 'pemesanan.id')
    ->join('kendaraan', 'pemesanan.kendaraan_id', '=', 'kendaraan.id')
    
    // Select kendaraan dan aggregate KM
    ->select(
        'kendaraan.nama',
        DB::raw('SUM(riwayat_pemakaian.jarak_tempuh_km) as total_km')
    )
    
    // Group per kendaraan
    ->groupBy('kendaraan.id', 'kendaraan.nama')
    
    // Order by total KM descending
    ->orderByDesc('total_km')
    
    // Execute
    ->get();

// Result:
[
    ['nama' => 'Truk A', 'total_km' => 1500],
    ['nama' => 'Mobil B', 'total_km' => 800],
]
```

**Optimizations Done Here:**
1. **DB-Level Aggregation:** SUM di database, bukan app
2. **Join Strategy:** Efficient join order (small to large)
3. **Grouping:** Aggregate sebelum return ke app
4. **Ordering:** Di database untuk efficiency

### Pattern 2: Conditional Query Building

```php
$query = Pemesanan::query()
    ->with(['admin', 'kendaraan', 'driver', 'atasan1', 'atasan2']);

// Dynamic filter based on user rolle
if (auth()->user()->role === 'penyetujui') {
    $query->where(function ($q) {
        $q->where('atasan_1_id', auth()->id())
          ->orWhere('atasan_2_id', auth()->id());
    });
}

// Date filter
if ($request->has('start_date') && $request->has('end_date')) {
    $query->whereBetween('tanggal_mulai', [
        $request->start_date,
        $request->end_date
    ]);
}

// Status filter
if ($request->has('status')) {
    $query->where('status_pemesanan', $request->status);
}

// Execute
$pemesanans = $query->latest()->get();
```

### Pattern 3: Pagination dengan Metadata

```php
$perPage = 20;
$page = request('page', 1);

$result = User::query()
    ->where('role', 'penyetujui')
    ->paginate($perPage, ['*'], 'page', $page);

// Response untuk autocomplete:
return response()->json([
    'results' => $result->map(fn($u) => [
        'id' => $u->id,
        'text' => $u->nama,
    ]),
    'pagination' => [
        'more' => $result->hasMorePages(),
    ],
]);

// $result Properties:
// - current_page: 1
// - per_page: 20
// - total: 50
// - last_page: 3
// - next_page_url: '?page=2'
// - data: actual records
```

---

## ⚡ PERFORMANCE OPTIMIZATION TIPS

### Optimization 1: Eager Loading

**Problem (N+1 Query):**
```php
// Query 1: Get all pemesanans
$pemesanans = Pemesanan::all();

// Loop - Query N+3 per pemesanan
foreach ($pemesanans as $p) {
    echo $p->admin->nama;         // Query 2..N
    echo $p->kendaraan->nama;     // Query N+1..2N
    echo $p->driver->nama;        // Query 2N+1..3N
}
```

**Solution (Eager Loading):**
```php
// Single query with joins
$pemesanans = Pemesanan::with(['admin', 'kendaraan', 'driver'])->get();

foreach ($pemesanans as $p) {
    echo $p->admin->nama;     // From memory
    echo $p->kendaraan->nama; // From memory
    echo $p->driver->nama;    // From memory
}
```

**Query Comparison:**
```
N+1 Query:
└─ SELECT * FROM pemesanan (1)
└─ SELECT * FROM users WHERE id = ? (per admin_id)
└─ SELECT * FROM kendaraan WHERE id = ? (per kendaraan_id)
└─ SELECT * FROM driver WHERE id = ? (per driver_id)
Total: 1 + (N * 3) queries

Eager Loading:
└─ SELECT * FROM pemesanan (1)
└─ SELECT * FROM users WHERE id IN (...) (1)
└─ SELECT * FROM kendaraan WHERE id IN (...) (1)
└─ SELECT * FROM driver WHERE id IN (...) (1)
Total: 4 queries (constant, regardless N)
```

### Optimization 2: Query Caching

```php
// Cache dashboard chart untuk 1 jam
$chartData = Cache::remember('dashboard_chart', 60*60, function () {
    return RiwayatPemakaian::query()
        ->join('pemesanan', ...)
        ->select('kendaraan.nama', DB::raw('SUM(...) as total_km'))
        ->groupBy('kendaraan.nama')
        ->orderByDesc('total_km')
        ->get();
});

// Invalidate cache jika ada changes
// Di storeRiwayat() method:
Cache::forget('dashboard_chart');
```

### Optimization 3: Indexing Strategy

```sql
-- Foreign key fields (auto-indexed)
CREATE INDEX idx_pemesanan_admin_id ON pemesanan(admin_id);
CREATE INDEX idx_pemesanan_status ON pemesanan(status_pemesanan);

-- Search fields
CREATE INDEX idx_kendaraan_nama ON kendaraan(nama);
CREATE INDEX idx_driver_nama ON driver(nama);

-- Filter fields
CREATE INDEX idx_log_aktivitas_user_id ON log_aktivitas(user_id);
CREATE INDEX idx_log_aktivitas_created_at ON log_aktivitas(created_at);
```

**Indexing Decision:**
- Foreign key: ALWAYS index (join filter)
- Status: OFTEN index (filtering)
- Search fields: YES index (WHERE clause)
- created_at: MAYBE index (if querying by date often)

---

## 🔐 SECURITY IMPLEMENTATION DETAILS

### Security Layer 1: Input Validation

```php
// Validate data type & format
$data = $request->validate([
    'aksi' => ['required', Rule::in(['setuju', 'tolak'])],
    'catatan_tambahan' => ['nullable', 'string', 'max:1000'],
    'tanggal_mulai' => ['required', 'date_format:Y-m-d H:i:s'],
]);

// Result: $data only contains safe values
// No HTML/script injection possible
```

### Security Layer 2: Mass Assignment Protection

```php
// Model level
class Pemesanan extends Model {
    #[Fillable(['admin_id', 'kendaraan_id', 'driver_id', ...])]
    // Only these fields can be filled from Request
}

// Even if attacker sends:
// POST /pemesanan with status_pemesanan=disetujui_final
// This field ignored karena tidak di $fillable
```

### Security Layer 3: Authorization

```php
// Route level
Route::middleware('role:admin')->group(function () {
    Route::get('/pemesanan', ...);  // Only admin
});

// Controller level
if ($pemesanan->admin_id !== auth()->id()) {
    abort(403);  // User cannot edit
}
```

### Security Layer 4: SQL Injection Prevention

```php
// ✅ SAFE - Parameterized
->where('nama', 'like', "%{$search}%")

// ❌ DANGEROUS - String concatenation
->whereRaw("nama LIKE '%{$search}%'")

// Laravel Eloquent automatically handles parameterization
// User input never directly in SQL
```

---

**NEXT STEP:** Review INTERVIEW_PREPARATION.md & DEMO_SCRIPT.md untuk complete picture!

