# Panduan Lengkap Deploy LMS Informatika ke Server Ubuntu

Dokumen ini berisi panduan langkah demi langkah (*step-by-step*) untuk melakukan *deployment* aplikasi **LMS Informatika (Ruang Informatika)** berbasis **Laravel 13 + Livewire + Vite** pada server **Ubuntu (20.04 / 22.04 / 24.04 LTS)** dengan menggunakan **Nginx** sebagai web server dan **MySQL** sebagai basis data.

---

## Daftar Isi
1. [Prasyarat Sistem](#1-prasyarat-sistem)
2. [Langkah 1: Update Server & Paket Pendukung](#langkah-1-update-server--paket-pendukung)
3. [Langkah 2: Instalasi & Konfigurasi Nginx Web Server](#langkah-2-instalasi--konfigurasi-nginx-web-server)
4. [Langkah 3: Instalasi & Konfigurasi Database MySQL](#langkah-3-instalasi--konfigurasi-database-mysql)
5. [Langkah 4: Instalasi PHP 8.3 FPM & Ekstensi Lengkap](#langkah-4-instalasi-php-83-fpm--ekstensi-lengkap)
6. [Langkah 5: Konfigurasi Batas Upload PHP (Penting untuk Tugas Siswa)](#langkah-5-konfigurasi-batas-upload-php-penting)
7. [Langkah 6: Instalasi Composer & Node.js (Vite)](#langkah-6-instalasi-composer--nodejs-vite)
8. [Langkah 7: Clone Repository dari GitHub](#langkah-7-clone-repository-dari-github)
9. [Langkah 8: Setup Environment (.env) & Kunci Aplikasi](#langkah-8-setup-environment-env--kunci-aplikasi)
10. [Langkah 9: Setup Database (Migrasi / Import Data)](#langkah-9-setup-database-migrasi--import-data)
11. [Langkah 10: Instalasi Dependencies & Build Aset Frontend](#langkah-10-instalasi-dependencies--build-aset-frontend)
12. [Langkah 11: Konfigurasi Symlink Storage & Hak Akses (Permissions)](#langkah-11-konfigurasi-symlink-storage--hak-akses-permissions)
13. [Langkah 12: Konfigurasi VirtualHost Nginx Lengkap](#langkah-12-konfigurasi-virtualhost-nginx-lengkap)
14. [Langkah 13: Optimasi Cache Laravel untuk Produksi](#langkah-13-optimasi-cache-laravel-untuk-produksi)
15. [Langkah 14: Konfigurasi Cron Job Scheduler (Otomatisasi Sistem)](#langkah-14-konfigurasi-cron-job-scheduler-otomatisasi-sistem)
16. [Langkah 15: Konfigurasi SSL / HTTPS dengan Let's Encrypt (Opsional)](#langkah-15-konfigurasi-ssl--https-dengan-lets-encrypt-opsional)
17. [Panduan Pemeliharaan & Update Aplikasi di Masa Mendatang](#panduan-pemeliharaan--update-aplikasi-di-masa-mendatang)

---

## 1. Prasyarat Sistem
- **Sistem Operasi**: Ubuntu Server 20.04 / 22.04 / 24.04 LTS (Akses root atau akun dengan hak akses `sudo`)
- **Web Server**: Nginx
- **Database**: MySQL Server 8.0+ / MariaDB
- **PHP**: PHP 8.3 atau 8.4 (PHP-FPM)
- **Node.js & NPM**: Node.js v20 LTS (untuk *compilation* aset Vite & Tailwind)
- **Composer**: Composer v2
- **Akses Jaringan**: Port 80 (HTTP) dan Port 443 (HTTPS) terbuka di firewall sekolah/cloud

---

## Langkah 1: Update Server & Paket Pendukung

Masuk ke server Ubuntu via SSH:
```bash
ssh username@IP_SERVER_SEKOLAH
```

Perbarui paket indeks repository dan pasang paket utilitas dasar:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip zip software-properties-common ca-certificates lsb-release ufw htop
```

---

## Langkah 2: Instalasi & Konfigurasi Nginx Web Server

Pasang Nginx dan pastikan layanannya otomatis berjalan saat server booting:
```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

Periksa status Nginx:
```bash
sudo systemctl status nginx
```

---

## Langkah 3: Instalasi & Konfigurasi Database MySQL

### 1. Pasang MySQL Server
```bash
sudo apt install -y mysql-server
sudo systemctl enable mysql
sudo systemctl start mysql
```

### 2. Buat Basis Data dan User Khusus LMS
Masuk ke antarmuka terminal MySQL:
```bash
sudo mysql
```

Jalankan query berikut di dalam MySQL prompt *(sesuaikan password yang aman)*:
```sql
CREATE DATABASE lms_informatika CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'lms_user'@'localhost' IDENTIFIED BY 'PasswordKuatSekolah123!';

GRANT ALL PRIVILEGES ON lms_informatika.* TO 'lms_user'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

---

## Langkah 4: Instalasi PHP 8.3 FPM & Ekstensi Lengkap

LMS Informatika menggunakan Laravel 13 yang mensyaratkan **PHP minimal versi 8.3**, serta ekstensi untuk **DomPDF** (cetak rapor), **Excel/Maatwebsite**, dan proses manipulasi file.

Tambahkan repository resmi PPA Ondřej Surý:
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

Pasang PHP 8.3 FPM beserta modul-modul wajib:
```bash
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-gd \
                    php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl
```

Pastikan layanan PHP 8.3 FPM aktif:
```bash
sudo systemctl enable php8.3-fpm
sudo systemctl start php8.3-fpm
```

---

## Langkah 5: Konfigurasi Batas Upload PHP (PENTING)

Secara default, PHP hanya mengizinkan batas unggah file sebesar 2 MB. Di LMS Informatika, siswa dapat mengunggah berkas jawaban tugas (PDF, Dokumen, Gambar, ZIP) hingga **20 MB**. Jika batas upload tidak dinaikkan, siswa akan mengalami galat *413 Payload Too Large*.

Buka konfigurasi `php.ini` milik PHP-FPM:
```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Cari (gunakan `Ctrl + W`) dan ubah nilai variabel berikut:
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 300
```
Simpan file (`Ctrl + O`, tekan `Enter`, lalu `Ctrl + X`).

Restart PHP-FPM agar perubahan diterapkan:
```bash
sudo systemctl restart php8.3-fpm
```

---

## Langkah 6: Instalasi Composer & Node.js (Vite)

### 1. Pasang Composer v2
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 2. Pasang Node.js v20 (LTS) & NPM
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

---

## Langkah 7: Clone Repository dari GitHub

Arahkan ke folder `/var/www` dan lakukan clone repository dari branch **`main`**:
```bash
cd /var/www
sudo git clone -b main https://github.com/rplsmkn1tp-ux/lms_informatika.git lms-informatika
```

Berikan hak kepemilikan sementara ke pengguna Ubuntu Anda agar dapat melakukan konfigurasi file:
```bash
sudo chown -R $USER:$USER /var/www/lms-informatika
cd /var/www/lms-informatika
```

---

## Langkah 8: Setup Environment (.env) & Kunci Aplikasi

Salin file contoh konfigurasi:
```bash
cp .env.example .env
nano .env
```

Sesuaikan baris-baris penting berikut untuk server produksi:
```ini
APP_NAME="Ruang Informatika"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://IP_SERVER_SEKOLAH    # Atau https://lms.sekolah.sch.id jika menggunakan domain

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_informatika
DB_USERNAME=lms_user
DB_PASSWORD=PasswordKuatSekolah123!

SESSION_DRIVER=database
QUEUE_CONNECTION=database
```
Simpan file (`Ctrl + O`, tekan `Enter`, lalu `Ctrl + X`).

Generate Application Key:
```bash
php artisan key:generate
```

---

## Langkah 9: Setup Database (Migrasi / Import Data)

Pilih salah satu dari 2 metode berikut:

### Opsi A: Import Data dari Backup Laragon (Direkomendasikan jika sudah ada data master)
Jika sebelumnya Anda sudah mengekspor data dari Laragon (misal file `lms_backup.sql`), salin file tersebut ke server (misal melalui WinSCP atau `scp`), lalu jalankan:
```bash
mysql -u lms_user -p lms_informatika < lms_backup.sql
```

### Opsi B: Migrasi Bersih dari Awal
Jika Anda ingin memulai dari basis data baru beserta data seeder bawaan:
```bash
php artisan migrate --force --seed
```

---

## Langkah 10: Instalasi Dependencies & Build Aset Frontend

Jalankan instalasi dependensi PHP (tanpa paket pengujian developer):
```bash
composer install --no-dev --optimize-autoloader
```

Jalankan kompilasi aset CSS dan JavaScript (Vite + Tailwind CSS):
```bash
npm install
npm run build
```

---

## Langkah 11: Konfigurasi Symlink Storage & Hak Akses (Permissions)

### 1. Buat Symbolic Link Storage
Perintah ini wajib agar file yang diunggah siswa (jawaban tugas) dan guru (lampiran materi) dapat diakses publik:
```bash
php artisan storage:link
```

### 2. Atur Kepemilikan & Hak Akses Folder
Server Nginx berjalan menggunakan pengguna sistem `www-data`. Berikan hak akses baca-tulis penuh pada folder `storage` dan `bootstrap/cache`:
```bash
sudo chown -R www-data:www-data /var/www/lms-informatika
sudo find /var/www/lms-informatika -type f -exec chmod 644 {} \;
sudo find /var/www/lms-informatika -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/lms-informatika/storage
sudo chmod -R 775 /var/www/lms-informatika/bootstrap/cache
```

---

## Langkah 12: Konfigurasi VirtualHost Nginx Lengkap

Buat file konfigurasi server block baru untuk LMS Informatika:
```bash
sudo nano /etc/nginx/sites-available/lms-informatika
```

Tempelkan (*paste*) konfigurasi berikut:
```nginx
server {
    listen 80;
    listen [::]:80;

    # Ganti dengan IP server sekolah atau Domain (misal: lms.smkn1tp.sch.id)
    server_name IP_SERVER_SEKOLAH;

    # Document Root WAJIB mengarah ke folder public Laravel
    root /var/www/lms-informatika/public;
    index index.php index.html index.htm;

    # Batas ukuran payload file upload dari client (50 Megabytes)
    client_max_body_size 50M;

    charset utf-8;

    # Livewire & URL Rewrite Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # Pemrosesan skrip PHP via PHP 8.3 FPM Unix Socket
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Blokir akses langsung ke file sensitif (.env, .git, .htaccess)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache browser untuk aset statis agar loading super cepat
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```
Simpan file (`Ctrl + O`, tekan `Enter`, lalu `Ctrl + X`).

Aktifkan konfigurasi dengan membuat symlink ke folder `sites-enabled`:
```bash
sudo ln -s /etc/nginx/sites-available/lms-informatika /etc/nginx/sites-enabled/
```

Hapus konfigurasi default Nginx (jika server hanya menjalankan LMS ini):
```bash
sudo rm -f /etc/nginx/sites-enabled/default
```

Uji sintaks konfigurasi Nginx:
```bash
sudo nginx -t
```
*Pastikan muncul pesan: `syntax is ok` dan `test is successful`.*

Muat ulang (*reload*) layanan Nginx:
```bash
sudo systemctl reload nginx
```

---

## Langkah 13: Optimasi Cache Laravel untuk Produksi

Agar aplikasi berjalan sangat cepat tanpa melakukan pembacaan file konfigurasi dan route berulang kali:
```bash
cd /var/www/lms-informatika
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Sekarang Anda dapat membuka browser dan mengakses:
```text
http://IP_SERVER_SEKOLAH
```

---

## Langkah 14: Konfigurasi Cron Job Scheduler (Otomatisasi Sistem)

Laravel memiliki penjadwal otomatis (*Task Scheduler*) untuk mengecek tugas kadaluwarsa, pembersihan sesi, dan pengiriman notifikasi.

Buka konfigurasi cron milik pengguna web server:
```bash
sudo crontab -e -u www-data
```

Tambahkan baris berikut di baris paling bawah:
```cron
* * * * * cd /var/www/lms-informatika && php artisan schedule:run >> /dev/null 2>&1
```
Simpan dan keluar.

---

## Langkah 15: Konfigurasi SSL / HTTPS dengan Let's Encrypt (Opsional)

Jika server sekolah Anda memiliki domain publik resmi (contoh: `lms.smkn1tp.sch.id`), pasang sertifikat SSL gratis dari Let's Encrypt:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d lms.smkn1tp.sch.id
```

Certbot akan secara otomatis mengonfigurasi sertifikat SSL pada file Nginx Anda dan mengatur perpanjangan otomatis (*auto-renewal*).

---

## Panduan Pemeliharaan & Update Aplikasi di Masa Mendatang

Jika di kemudian hari ada pembaruan kode atau perbaikan fitur dari repositori GitHub, jalankan langkah berikut di server:

```bash
cd /var/www/lms-informatika

# 1. Tarik pembaruan kode dari branch main
sudo git pull origin main

# 2. Update dependensi jika ada penambahan pustaka
composer install --no-dev --optimize-autoloader
npm run build

# 3. Jalankan migrasi basis data jika ada tabel baru
php artisan migrate --force

# 4. Refresh cache aplikasi Laravel
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Pastikan hak akses tetap aman
sudo chown -R www-data:www-data storage bootstrap/cache
```
