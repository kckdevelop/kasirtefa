# Panduan Deploy & Setting Database SQLite

Aplikasi **Kasir TEFA & Peminjaman Alat** telah dikonfigurasi untuk menggunakan database **SQLite** dan siap untuk dideploy ke lingkungan produksi.

---

## 1. Perubahan Konfigurasi Database
File `.env` dan `.env.example` telah diperbarui dengan konfigurasi berikut:

```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=kasir-tefa
# DB_USERNAME=root
# DB_PASSWORD=
```

File database SQLite tersimpan secara otomatis pada:
`database/database.sqlite`

---

## 2. Langkah Deploy ke Server (VPS / Cloud / Shared Hosting)

### Step 1: Clone / Upload Repository & Install Dependensi
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Step 2: Set Environment Production
Salin `.env.example` menjadi `.env` jika belum ada:
```bash
cp .env.example .env
```
Ubah pengaturan di `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
```

Generate application key:
```bash
php artisan key:generate
```

### Step 3: Inisialisasi Database SQLite & Storage Link
Pastikan file database SQLite ada dan miliki izin akses tulis (*write permission*):
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
chmod 775 database storage bootstrap/cache
```

Jalankan migrasi database:
```bash
php artisan migrate --force
```
*(Opsional) Jika butuh data awal / seeder:*
```bash
php artisan db:seed --force
```

Buat symbolic link storage untuk file upload:
```bash
php artisan storage:link
```

### Step 4: Optimasi Cache Production
Jalankan perintah optimasi Laravel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. TroubleShooting: Jika Deployment FAILED "Database migrations failed"

Jika Anda mengalami error seperti `[FAILED] DEPLOYMENT ERROR: Database migrations failed during deployment`:

1. **Hapus / Ubah Environment Variables di Dashboard Layanan Cloud (PaaS)**:
   - Di platform deployment seperti Railway, Render, Koyeb, Coolify, Fly.io, dsb., variabel lingkungan yang ada di **Dashboard Cloud / Environment Variables tab** akan **MENIMPA (override)** file `.env` di git repository.
   - Pastikan di Dashboard Cloud Anda sudah diset:
     - `DB_CONNECTION=sqlite`
   - Hapus atau kosongkan variabel berikut dari Dashboard Cloud jika ada: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

2. **Izin File & Folder**:
   - Pastikan direktori `database/` dan file `database/database.sqlite` memiliki izin akses `read/write` oleh web server (misal `www-data` / `nginx` / `apache`).

3. **Auto-Creation Database SQLite**:
   - `composer.json` telah dilengkapi skrip otomatis `@php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"` pada perintah `post-autoload-dump` sehingga file SQLite dibuat otomatis saat deployment build.
