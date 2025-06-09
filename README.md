# Teman Bicara Backend - [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=Febry3_temanbicara-backend&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=Febry3_temanbicara-backend) [![Bugs](https://sonarcloud.io/api/project_badges/measure?project=Febry3_temanbicara-backend&metric=bugs)](https://sonarcloud.io/summary/new_code?id=Febry3_temanbicara-backend) [![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=Febry3_temanbicara-backend&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=Febry3_temanbicara-backend)

Teman Bicara Backend adalah API backend yang dibangun menggunakan [Laravel](https://laravel.com/), dirancang untuk mendukung aplikasi Teman Bicara. Proyek ini menyediakan endpoint RESTful untuk berbagai fitur aplikasi.

## 🚀 Arsitektur Aplikasi
![{1459F249-04A3-4205-A1F8-40F3E6995594}](https://github.com/user-attachments/assets/1445d41c-a1b7-4d23-948f-ca1b7812ed42)



## 🧱 Teknologi yang Digunakan

- **Framework**: Laravel 11
- **Bahasa Pemrograman**: PHP 8.3
- **Database**: MySQL
- **Manajemen Paket**: Composer
- **Testing**: PHPUnit

## 📁 Struktur Folder

```
.
├── app/                # Kode sumber utama (Controllers, Models, dll.)
├── bootstrap/          # File bootstrap Laravel
├── config/             # Konfigurasi aplikasi
├── database/           # Migrasi dan seeder database
├── public/             # Entry point aplikasi (index.php)
├── resources/          # View dan aset frontend
├── routes/             # Definisi rute aplikasi
├── storage/            # File yang dihasilkan aplikasi
├── tests/              # Tes unit dan fitur
├── .env.example        # Contoh file environment
├── artisan             # CLI Laravel
├── composer.json       # Dependensi PHP
├── package.json        # Dependensi JavaScript
└── README.md           # Dokumentasi proyek
```

## ⚙️ Persiapan dan Instalasi

1. **Clone repositori**
   ```bash
   git clone https://github.com/Febry3/temanbicara-backend.git
   cd temanbicara-backend
   ```

2. **Install dependensi PHP**
   ```bash
   composer install
   ```

3. **Salin file `.env` dan konfigurasi**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi database**
   *Pastikan MySQL sudah berjalan pada device anda
   ```bash
   php artisan migrate:fresh
   ```

6. **Jalankan server lokal**
   ```bash
   php artisan serve
   php artisan queue:work
   ```

6. **Opsional**
   via docker 🐋
   ```bash
   docker compose up --build
   ```
   Kemudian
   ```bash
   docker exec -it <app-1> bash
   ```
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate:fresh
   php artisan serve
   php artisan queue:work
   ```
## 🧪 Testing

Untuk menjalankan tes unit dan fitur:

```bash
php artisan test
```
