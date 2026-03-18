# Technical Test - Aplikasi Pemesanan Kendaraan

Aplikasi web berbasis Laravel untuk pemesanan kendaraan perusahaan tambang dengan alur persetujuan berjenjang 2 level, dashboard grafik pemakaian kendaraan, dan laporan periodik export Excel.

## Tech Stack

- Framework: Laravel 13
- PHP Version: 8.3+
- Database: MySQL 8.x (direkomendasikan), kompatibel dengan engine yang mendukung enum
- Frontend Build Tool: Vite
- Export Excel: `maatwebsite/excel` (PhpSpreadsheet)

## Fitur Utama

1. 2 role user: `admin` dan `penyetujui`
2. Admin dapat input pemesanan kendaraan + pilih driver + penyetuju level 1 & 2
3. Persetujuan berjenjang minimal 2 level di aplikasi
4. Dashboard grafik pemakaian kendaraan (total km berdasarkan riwayat pemakaian)
5. Laporan periodik pemesanan kendaraan dengan filter tanggal dan export Excel (`.xlsx`)

## Akun Login Default

Semua akun menggunakan password yang sama: `password123`

- Admin
	- Nama: `Admin1`
	- Email: `admin@booking.test`
	- Role: `admin`
- Penyetuju Level 1
	- Nama: `Adi Saputra`
	- Email: `approver1@booking.test`
	- Role: `penyetujui`
- Penyetuju Level 2
	- Nama: `Budi Santoso`
	- Email: `approver2@booking.test`
	- Role: `penyetujui`

## Struktur Data (ERD Implemented)

Referensi ERD (dbdiagram.io):

- https://dbdiagram.io/d/69b8c4e9fb2db18e3b97169c

Tabel utama yang diimplementasikan:

- `kantor`
- `users`
- `kendaraan`
- `driver`
- `pemesanan`
- `log_persetujuan`
- `riwayat_pemakaian`
- `log_aktivitas`

## Diagram Activity

Referensi diagram activity (Draw.io export):

- https://drive.google.com/file/d/1IfU-X-Sh6OWGWROe6kzWYWLbZZEzkVaO/view?usp=sharing

## Cara Menjalankan Aplikasi

1. Install dependency backend

```bash
composer install
```

2. Install dependency frontend

```bash
npm install
```

3. Copy file environment dan generate app key

```bash
cp .env.example .env
php artisan key:generate
```

> Di Windows PowerShell, copy bisa pakai: `Copy-Item .env.example .env`

4. Atur konfigurasi database di `.env`

Contoh:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_vehicle
DB_USERNAME=root
DB_PASSWORD=
```

5. Migrasi database + seed data awal

```bash
php artisan migrate:fresh --seed
```

6. Build asset frontend

```bash
npm run build
```

7. Jalankan server

```bash
php artisan serve
```

Akses aplikasi di:

- `http://127.0.0.1:8000`

## Menjalankan dengan Docker (DevOps Ready)

Project ini sudah disiapkan agar bisa dijalankan menggunakan Docker Compose dengan 3 service:

- `app` : Laravel (PHP-FPM)
- `web` : Nginx
- `db` : MySQL 8

### Prasyarat

- Docker Desktop (Windows/Mac) atau Docker Engine + Docker Compose (Linux)

### Menjalankan service

```bash
docker compose up -d --build
```

### Akses aplikasi

- Web App: `http://localhost:8080`
- MySQL Host Port: `3307`

### Command umum Docker

Stop service:

```bash
docker compose down
```

Stop + hapus volume database:

```bash
docker compose down -v
```

Lihat log service:

```bash
docker compose logs -f
```

Masuk ke container app:

```bash
docker compose exec app sh
```

### Catatan Docker

- Migrasi otomatis berjalan saat container `app` start (`RUN_MIGRATIONS=true`).
- Asset frontend sudah dibuild dalam image Docker (Vite build stage).
- Konfigurasi ada di file:
	- `Dockerfile`
	- `docker-compose.yml`
	- `docker/nginx/default.conf`
	- `docker/entrypoint.sh`

## Panduan Penggunaan Singkat

1. Login sebagai admin (`admin@booking.test`)
2. Buka menu **Pemesanan**
3. Input pemesanan kendaraan dan tentukan:
	 - kendaraan
	 - driver
	 - penyetuju level 1
	 - penyetuju level 2
	 - periode pemakaian
4. Logout, lalu login sebagai penyetuju level 1 untuk melakukan approval/penolakan
5. Login sebagai penyetuju level 2 untuk approval final
6. Login kembali sebagai admin untuk isi **Riwayat Pemakaian** setelah status `disetujui_final`
7. Buka **Dashboard** untuk melihat grafik pemakaian kendaraan
8. Buka **Laporan** untuk filter periodik dan klik **Export Excel**

## Catatan

- Aksi approval level 2 hanya aktif setelah level 1 menyetujui.
- Jika pemesanan ditolak di salah satu level, status menjadi `ditolak`.
- Driver otomatis menjadi `sibuk` saat booking dibuat, dan kembali `tersedia` saat riwayat pemakaian diisi.
