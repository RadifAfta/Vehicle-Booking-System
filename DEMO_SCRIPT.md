# 🎬 INTERVIEW DEMO SCRIPT & FAQ PRAKTIS

---

## TABLE OF CONTENTS

1. [Pre-Interview Checklist](#pre-interview-checklist)
2. [Demo Script Step-by-Step](#demo-script-step-by-step)
3. [Live Coding Scenarios](#live-coding-scenarios)
4. [FAQ Praktis (Bahasa Indonesia)](#faq-praktis)
5. [Troubleshooting During Interview](#troubleshooting-during-interview)

---

## 🗂️ PRE-INTERVIEW CHECKLIST

### ✅ Sebelum Hari H

**Hari Sebelumnya:**
- [ ] Test run aplikasi penuh
- [ ] Baca INTERVIEW_PREPARATION.md
- [ ] Siapkan demo accounts login
- [ ] Test browser history (clear cache)
- [ ] Test internet connection
- [ ] Siapkan notepad untuk catatan

**Hari Interview:**
- [ ] Start server: `php artisan serve`
- [ ] Jika pakai Docker: `docker-compose up`
- [ ] Verify database connected
- [ ] Clear browser cache
- [ ] Mute notifications
- [ ] Prepare multiple browser tabs (admin + approver)

**Setup Commands:**
```bash
# Terminal 1: Start server
cd e:\Projects\Technical-test-sekawan\Booking-System-Vehicle
php artisan serve

# Terminal 2 (optional): Build frontend (if using Vite dev)
npm run dev

# Visit: http://localhost:8000
```

---

## 🎬 DEMO SCRIPT STEP-BY-STEP

### 🔓 Fase 1: Login & Overview (2 menit)

**Objective:** Show aplikasi bisa jalan, user management works

**Action:**
1. Open browser → `http://localhost:8000`
2. Login sebagai Admin
   - Email: `admin@booking.test`
   - Password: `password123`
3. Explain: "Aplikasi ini punya 2 role: Admin dan Penyetujui"

**Talking Points:**
- "Authentication menggunakan Laravel built-in Auth"
- "Password di-hash dengan bcrypt"
- "Session-based untuk maintain login state"

---

### 📊 Fase 2: Dashboard Analytics (2 menit)

**Objective:** Show business metrics & data aggregation

**Action:**
1. Click Dashboard menu
2. Show metrics:
   - Total Kendaraan: X
   - Total Pemesanan: Y
   - Chart: Kendaraan → Total KM

**Talking Points:**
- "Chart data di-aggregate di database pakai GROUP BY"
- "Query join 3 tables: `riwayat_pemakaian` → `pemesanan` → `kendaraan`"
- "Database calculation lebih efficient daripada application layer"

**Code Snippet to Mention:**
```php
// DashboardController.php
$chartRows = RiwayatPemakaian::query()
    ->join('pemesanan', ...)
    ->join('kendaraan', ...)
    ->select('nama', DB::raw('SUM(jarak) as total_km'))
    ->groupBy('nama')
    ->orderByDesc('total_km')
    ->get();
```

---

### ✍️ Fase 3: Create Pemesanan (3 menit)

**Objective:** Demonstrate input form & autocomplete search

**Action:**
1. Go to Pemesanan menu
2. Click "Buat Pemesanan" atau form pemesanan
3. Fill form:
   - Kendaraan: Search "angkutan" → Show autocomplete
   - Driver: Search "manager" → Show autocomplete
   - Atasan Level 1: Select user
   - Atasan Level 2: Select user (berbeda dari level 1)
   - Tanggal: Set date range
   - Catatan: Optional notes
4. Submit form

**Talking Points:**
- "Autocomplete fields use AJAX pagination untuk handle large datasets"
- "Search logic: LIKE query pada multiple fields (nama, jenis, kantor)"
- "Response format: JSON dengan id, text, pagination info"
- "Pagination 20 items per page untuk performance"

**Show Autocomplete Work:**
```php
// searchKendaraan() menghasilkan:
{
  "results": [
    {"id": 1, "text": "Truk A - angkutan_barang - Kantor Pusat"},
    {"id": 2, "text": "Mobil B - angkutan_orang - Kantor Cabang"}
  ],
  "pagination": {"more": false}
}
```

**Explain Validasi:**
- "Server-side validation cek semua required fields"
- "Dropdown values di-validate dari database (tidak bisa bypass)"
- "Status pemesanan otomatis set ke 'menunggu_persetujuan'"

---

### ✅ Fase 4: Approval Workflow - Level 1 (4 menit)

**Objective:** Demonstrate 2-level approval flow

**Action:**
1. **Switch to Approver Level 1 account:**
   - Logout admin
   - Login sebagai: `approver1@booking.test` / `password123`

2. **Go to menu Persetujuan:**
   - Show list pemesanan pending
   - Click pemesanan yang dibuat tadi

3. **Review Details:**
   - Show kendaraan, driver, admin info
   - Explain: "Level 1 approver review pemesanan"

4. **Approve Pemesanan:**
   - Choose aksi: "Setuju"
   - Optional catatan: "Approved, cek kendaraan siap"
   - Click Submit

**Talking Points:**
- "Setiap approval create log entry dengan timestamp"
- "Status transition: `menunggu_persetujuan` → `disetujui_level_1`"
- "Backend validate: user ID harus match `atasan_1_id`"
- "Menggunakan database transaction untuk atomicity"

**Show Code Concept:**
```php
// PersetujuanController.update()
if ($pemesanan->atasan_1_id === $userId && 
    $pemesanan->status === 'menunggu_persetujuan') {
    DB::transaction(function () {
        LogPersetujuan::create([...]);
        $pemesanan->update(['status' => 'disetujui_level_1']);
    });
}
```

**Explain Security:**
- "Validate user ID (approver tidak bisa approve bukan tanggung jawab mereka)"
- "Validate status (tidak bisa skip level)"
- "Validate aksi hanya 'setuju' atau 'tolak'"

---

### ✅ Fase 5: Approval Workflow - Level 2 (3 menit)

**Objective:** Show approval completes the workflow

**Action:**
1. **Switch to Approver Level 2:**
   - Logout approver 1
   - Login sebagai: `approver2@booking.test` / `password123`

2. **Go to Persetujuan:**
   - Show same pemesanan dengan status `disetujui_level_1`

3. **Final Approval:**
   - Choose aksi: "Setuju"
   - Catatan: "Final approval granted"
   - Submit

4. **Verify Status Change:**
   - Status now: `disetujui_final`
   - Pemesanan siap untuk digunakan

**Talking Points:**
- "2-level approval ensures checks & balances"
- "Cannot approve same pemesanan 2x"
- "Cannot skip from level 1 to final without level 2"
- "Business rule ensures quality control"

---

### 📝 Fase 6: Input Riwayat Pemakaian (2 menit)

**Objective:** Show usage tracking feature

**Action:**
1. **Back to Admin Account:**
   - Logout approver 2
   - Login admin again

2. **Go to Pemesanan:**
   - Find pemesanan dengan status `disetujui_final`

3. **Input Riwayat Pemakaian:**
   - Jarak tempuh: "150 km"
   - BBM terpakai: "12.5 liter"
   - Keterangan: "Perjalanan ke site cabang"
   - Submit

**Talking Points:**
- "Admin dapat input riwayat pemakaian hanya setelah final approval"
- "Data riwayat digunakan untuk dashboard analytics"
- "Tracking km & BBM untuk cost analysis"

---

### 📊 Fase 7: Reports (3 menit)

**Objective:** Show reporting & export capabilities

**Action:**
1. **Laporan Pemesanan:**
   - Set filter tanggal
   - Show data di table
   - Explain columns: Admin, Kendaraan, Driver, Status

2. **Export to Excel:**
   - Click "Export" atau "Download"
   - File downloads
   - Open di Excel/LibreOffice

3. **Show Excel Features:**
   - Header styling (blue background, white text, bold)
   - Column widths auto-sized
   - Freeze pane di row 1
   - Auto-filter di header
   - Borders di semua cells

4. **Other Reports:**
   - Log Persetujuan: Show approval history
   - Log Aktivitas: Show user activity audit trail

**Talking Points:**
- "Excel export pakai maatwebsite/excel library"
- "Custom styling dengan PhpSpreadsheet"
- "Data queried dari database dengan filter"
- "Export bisa handle Date filtering untuk compliance"

**Show Export Code:**
```php
// PemesananExport.php
class PemesananExport implements 
    FromCollection, WithHeadings, WithStyles, WithEvents
{
    // Styling: bold header, blue background
    // Events: freeze pane, autofilter, borders
}
```

---

## 💻 LIVE CODING SCENARIOS

### Scenario 1: "Fix a Bug: User tidak bisa approve"

**Interviewer:** "Gimana jika approver tidak bisa approve pemesanan mereka?"

**Action:**
1. Open file: `app/Http/Controllers/PersetujuanController.php`
2. Navigate to `update()` method
3. Point to validation:
   ```php
   if ($pemesanan->atasan_1_id !== $userId) {
       return; // Guard condition
   }
   ```

**Explain:**
- "Backend validate user ID sesuai dengan atasan_1_id atau atasan_2_id"
- "Tidak bisa pakai PATCH request dengan user ID lain"
- "Even if frontend di-bypass, backend masih protect"

---

### Scenario 2: "Add a Feature: Log approval comments"

**Interviewer:** "Bagaimana jika kita mau show approval comments di history?"

**Action:**
1. Show LogPersetujuan model:
   ```php
   public function penyetujui(): BelongsTo {
       return $this->belongsTo(User::class, 'penyetujui_id');
   }
   ```

2. Explain implementation:
   ```php
   // Di view, loop log approval:
   @foreach($pemesanan->logPersetujuan as $log)
       <p>{{ $log->penyetujui->nama }} - {{ $log->aksi }}</p>
       <p>{{ $log->catatan_tambahan }}</p>
       <p>{{ $log->created_at->format('d-m-Y H:i') }}</p>
   @endforeach
   ```

**Talk About:**
- "Eager load relasi untuk avoid N+1"
- "Use date formatting untuk readable timestamps"
- "Show who, what, when in audit trail"

---

### Scenario 3: "Optimize Dashboard Query"

**Interviewer:** "Dashboard chart lambat, bagaimana optimize?"

**Action:**
1. Show current query:
   ```php
   RiwayatPemakaian::query()
       ->join('pemesanan', ...)
       ->join('kendaraan', ...)
       ->select('kendaraan.nama', DB::raw('SUM(jarak) as total_km'))
       ->groupBy('kendaraan.nama')
   ```

2. Suggest improvements:
   ```php
   // Option 1: Add index
   DB::statement('ALTER TABLE riwayat_pemakaian ADD INDEX (pemesanan_id)');
   
   // Option 2: Cache result
   $data = Cache::remember('chart_data', 1_hour, fn() => 
       RiwayatPemakaian::query()->...->get()
   );
   
   // Option 3: Materialized view (MySQL)
   // Create summary table per kendaraan
   ```

**Discuss Trade-offs:**
- "Index faster, but slower writes"
- "Cache faster, but outdated data"
- "Materialized view best for reporting"

---

## ❓ FAQ PRAKTIS

### Q: "Apa yang Anda paling bangga dari project ini?"

**A:**
"Saya paling bangga pada implementasi 2-level approval workflow dengan menggunakan database transaction. Ini bukan hanya business logic yang kompleks, tapi juga memastikan data consistency. Jika ada error di tengah proses, semua di-rollback otomatis. Ini adalah best practice yang penting untuk production system."

---

### Q: "Apa yang paling sulit saat development?"

**A:**
"Pertama adalah memahami alur approval workflow dan memastikan tidak ada edge case (misalnya approver tidak bisa approve 2x). Kedua adalah optimization query di dashboard - saya harus join 3 tables dan aggregate data, jadi important untuk pakai DB aggregation bukan application. Ketiga adalah ensuring data consistency dengan foreign keys yang proper."

---

### Q: "Bagaimana Anda implement audit trail?"

**A:**
"Saya gunakan middleware ActivityLogMiddleware yang di-attach ke routes. Setiap request log:
- User ID
- Action (extracted dari route)
- Endpoint
- IP Address
- User Agent
- Timestamp

Di view laporan, admin bisa filter by tanggal & user untuk see detailed user activity history."

---

### Q: "Bagaimana handle jika user coba bypass approval?"

**A:**
"Multiple layers of protection:
1. Frontend: Form field tidak bisa diubah
2. Database: Foreign key constraints
3. Controller: Validate user ID sesuai role
4. Validation: Aksi hanya 'setuju' atau 'tolak' (via Rule::in)
5. Transaction: All-or-nothing untuk consistency

Jadi bahkan jika hacker bypass frontend dan kirim raw HTTP request, backend masih validate semua rules."

---

### Q: "Gimana jika database down saat approval?"

**A:**
"Jika database error terjadi saat transaction:
- All changes di-rollback
- User get error message
- Log entry tidak created, status tidak updated
- Pemesanan stay di `disetujui_level_1` status

Jadi data tetap consistent. Potential improvement: retry mechanism atau queue untuk reliability."

---

### Q: "Bagaimana scale aplikasi ini untuk 10,000 users?"

**A:**
"Beberapa strategy:
1. Database: Add index pada frequently searched fields, partition tables
2. Caching: Cache dashboard chart, cache approver lists
3. Async: Queue long-running tasks (Excel export, email notification)
4. API: Separate API layer dengan token-based auth
5. Monitoring: Implement logging & monitoring
6. DevOps: Docker deployment, auto-scaling

Current implementation sudah good foundation untuk scale."

---

### Q: "Kenapa gunakan Laravel untuk project ini?"

**A:**
"Laravel chosen karena:
1. Powerful ORM (Eloquent) untuk complex relationships
2. Routing & middleware built-in
3. Validation framework comprehensive
4. Migration system untuk database versioning
5. Testing framework (Pest) available
6. Large community & good documentation

For this use case, Laravel adalah perfect fit."

---

### Q: "Apa yang Anda learn dari project ini?"

**A:**
"Key learnings:
1. Importance of database transaction untuk consistency
2. Eager loading untuk prevent N+1 queries
3. Multi-layer security (middleware + controller + validation)
4. State management di workflow applications
5. Testing & quality assurance importance
6. Scaling considerations dari awal

Pelajaran ini akan saya apply ke project di future."

---

## 🛠️ TROUBLESHOOTING DURING INTERVIEW

### 🔴 Problem: Aplikasi tidak connect ke database

**Solution:**
1. Check file `.env` - pastikan DB credentials correct
2. Run: `php artisan migrate:fresh --seed`
3. Verify: `php artisan tinker` → `User::count()`
4. Restart server

**Jika tidak bisa fix:**
- "Ini adalah environment issue, logic aplikasi OK"
- "Normally di-test locally dah berjalan fine"
- "Can explain code instead of live demo"

---

### 🔴 Problem: Login tidak berfungsi

**Solution:**
1. Check user ada di database: `php artisan tinker`
   ```php
   User::where('email', 'admin@booking.test')->first()
   ```
2. Check password correct
3. Verify session config

**Talking Point:**
- "Authentication using Laravel's built-in Auth guard"
- "Password di-hash dengan bcrypt"

---

### 🔴 Problem: Demo sudah lama, interviewer bosan

**Action:**
1. "Ini saja yang keliatan, code structure sudah menunjukkan ke Anda"
2. "Kalau interest, kita bisa deep dive ke specific implementation"
3. "Atau saya tunjukkan key parts dari code directly"

**Pivot to Code Review:**
- Show GitHub/VCS
- Explain architecture diagram
- Discuss design decisions

---

### 🔴 Problem: Forgot login credentials

**Action:**
1. Open PHPMyAdmin / Database client
2. Query: `SELECT email, role FROM users LIMIT 5;`
3. Or check database seeder file

**Backup:**
- Biasanya dokumentasi ada di README.md
- Check phpmyadmin buat reset password

---

### 💡 SAVE YOURSELF

Record demo walkthrough video sebelum interview:
```bash
# Install OBS atau use built-in screen record
# Record 10 menit demo walkthrough
# Practice explanation sambil play video
```

This way, jika ada technical issue, Anda bisa show video instead.

---

## 📋 LAST-MINUTE TIPS

1. **Know Your Numbers:**
   - 8 Models
   - 6 Controllers
   - 2 Levels approval
   - 4 Status states
   - 50+ Lines average code

2. **Key Phrases to Use:**
   - "Best practices in Laravel"
   - "Data consistency & atomicity"
   - "Security at multiple layers"
   - "Scalable & maintainable code"
   - "Performance optimization"

3. **What NOT to Do:**
   - Don't apologize untuk incomplete features
   - Don't bash own code ("This is terrible")
   - Don't show unfinished work
   - Don't lie about what you did/didn't do
   - Don't spend too long on any one feature

4. **Energy Management:**
   - Stay confident & calm
   - Speak clearly & pause (not rushing)
   - Make eye contact (via webcam)
   - Show enthusiasm untuk project

---

**GOOD LUCK! Semoga interview berjalan lancar! 🚀**

