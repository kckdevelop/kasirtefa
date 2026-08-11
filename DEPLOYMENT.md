# Panduan Deployment Aplikasi Kasir TEFA & Peminjaman Alat (Laravel)

Dokumen ini berisi panduan langkah demi langkah untuk melakukan *deployment* aplikasi ini ke server produksi (*Shared Hosting/cPanel* maupun *VPS*).

---

## 📋 Prasyarat Server (System Requirements)

- **PHP Version**: >= 8.2
- **PHP Extensions**: `OpenSSL`, `PDO`, `Mbstring`, `Tokenizer`, `XML`, `Ctype`, `JSON`, `BCMath`, `Fileinfo`, `GD`
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Web Server**: Apache (`mod_rewrite` aktif) atau Nginx
- **Node.js**: v18+ (diperlukan di komputer lokal untuk build asset frontend)

---

## 🛠️ Persiapan Sebelum Upload (Di Komputer Lokal)

Sebelum mengunggah kode ke server, jalankan perintah berikut di komputer lokal:

1. **Install Dependensi Frontend & Kompilasi Asset Production**:
   ```bash
   npm install
   npm run build
   ```
   *Pastikan folder `public/build/` telah terbentuk.*

2. **Pastikan File `.gitignore` Mengabaikan File Sensitif**:
   - File `.env` **TIDAK Boleh** di-upload/di-push ke Git.
   - Folder `node_modules/` **TIDAK Boleh** di-upload.

---

## 🚀 Opsi 1: Deployment di Shared Hosting / cPanel

### Langkah 1: Upload File ke cPanel
1. Compress seluruh isi folder proyek Anda (kecuali `node_modules`, `.git`, `.env`) menjadi file `.zip`.
2. Masuk ke **cPanel File Manager**.
3. Upload file `.zip` ke folder utama (misalnya `/home/username/tefa_alat/` atau langsung di dalam `public_html/`).
4. Extract file `.zip` tersebut.

### Langkah 2: Pengaturan Document Root / Directing URL
- **Jika proyek diletakkan di luar `public_html` (Direkomendasikan)**:
  1. Buat folder proyek misal `tefa_alat` di root cPanel (`/home/username/tefa_alat`).
  2. Pindahkan seluruh isi folder `public/` milik Laravel ke dalam folder `public_html/`.
  3. Edit file `public_html/index.php`:
     ```php
     // Ubah path autoloader dan app:
     require __DIR__.'/../tefa_alat/vendor/autoload.php';
     $app = require_once __DIR__.'/../tefa_alat/bootstrap/app.php';
     ```
- **Jika proyek diletakkan langsung di dalam `public_html`**:
  Pastikan file `.htaccess` di root proyek berfungsi untuk mengarahkan request ke folder `public/`:
  ```apache
  <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteCond %{REQUEST_URI} !^/public/
      RewriteRule ^(.*)$ public/$1 [L]
  </IfModule>
  ```

### Langkah 3: Membuat Database & User Database
1. Buka **MySQL Database Wizard** di cPanel.
2. Buat database baru (contoh: `user_kasirtefa`).
3. Buat user database baru dan berikan **ALL PRIVILEGES** ke database tersebut.

### Langkah 4: Konfigurasi File `.env`
1. Di cPanel File Manager, buat file `.env` di folder proyek (salin dari `.env.example`).
2. Sesuaikan konfigurasi berikut:
   ```ini
   APP_NAME="Kasir TEFA & Peminjaman Alat"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=user_kasirtefa
   DB_USERNAME=user_dbuser
   DB_PASSWORD=password_db_anda
   ```

### Langkah 5: Generate APP_KEY & Migrasi Database
Jika Anda memiliki akses **Terminal** di cPanel:
```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
```

*Jika tidak ada akses Terminal cPanel:*
1. Dapatkan `APP_KEY` dari lokal dan paste ke file `.env` di cPanel.
2. Export database dari komputer lokal (.sql) via phpMyAdmin lokal, lalu **Import** file `.sql` tersebut via phpMyAdmin di cPanel.
3. Untuk storage link, buat file `symlink.php` di `public_html`:
   ```php
   <?php
   target = '/home/username/tefa_alat/storage/app/public';
   shortcut = '/home/username/public_html/storage';
   symlink($target, $shortcut);
   echo "Symlink Completed";
   ?>
   ```
   Akses `https://domain-anda.com/symlink.php` di browser sekali, lalu hapus file `symlink.php` tersebut.

### Langkah 6: Set Permission Folder
Pastikan folder berikut memiliki izin akses (permission) `775` atau `755`:
- `storage/` (dan seluruh subfoldernya)
- `bootstrap/cache/`

---

## 🖥️ Opsi 2: Deployment di VPS (Ubuntu / Debian + Nginx / Apache)

### Langkah 1: Clone Repository & Install Dependensi
```bash
cd /var/www
git clone <URL_REPOSITORY_ANDA> tefa_alat
cd tefa_alat

# Install dependensi PHP
composer install --optimize-autoloader --no-dev

# Setup .env
cp .env.example .env
nano .env
```

### Langkah 2: Setup Database & Application Key
```bash
php artisan key:generate
php artisan migrate --seed --force
php artisan storage:link
```

### Langkah 3: Set Permission Folder
```bash
sudo chown -R www-data:www-data /var/www/tefa_alat
sudo chmod -R 775 /var/www/tefa_alat/storage /var/www/tefa_alat/bootstrap/cache
```

### Langkah 4: Konfigurasi Nginx Server Block
Buat file konfigurasi `/etc/nginx/sites-available/tefa_alat`:
```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/tefa_alat/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan situs & reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/tefa_alat /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## ⚡ Perintah Optimasi Produksi (Production Optimization)

Setelah aplikasi berhasil di-deploy dan berjalan, jalankan perintah optimasi Laravel ini untuk meningkatkan performa secara signifikan:

```bash
# Cache Konfigurasi
php artisan config:cache

# Cache Route/Rute
php artisan route:cache

# Cache View Blade
php artisan view:cache

# Optimasi Gabungan (Laravel 11/12)
php artisan optimize
```

> **Catatan:** Jika Anda mengubah file `.env` atau `routes/web.php` di kemudian hari, pastikan untuk menjalankan `php artisan optimize:clear` lalu `php artisan optimize` kembali.

---

## 🔒 Checklist Keamanan Sebelum Launching

- [ ] `APP_ENV` sudah diset ke `production`
- [ ] `APP_DEBUG` sudah diset ke `false`
- [ ] `APP_KEY` sudah terisi string acak base64
- [ ] File `.env` tidak dapat diakses publik dari browser
- [ ] SSL / HTTPS sudah diaktifkan (menggunakan Let's Encrypt / SSL cPanel)
- [ ] Permission folder `storage` & `bootstrap/cache` sudah diset dengan benar

---

## 📧 Akun Default / Seeder Aplikasi

Jika Anda menjalankan `php artisan db:seed`, akun berikut dibuat secara otomatis:
- **Email Admin/Petugas**: (Sesuai `DatabaseSeeder.php`)
- Silakan ganti password default setelah login pertama kali.
