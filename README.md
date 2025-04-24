# Teman Bicara Backend

Teman Bicara Backend adalah API backend yang dibangun menggunakan [Laravel](https://laravel.com/), dirancang untuk mendukung aplikasi Teman Bicara. Proyek ini menyediakan endpoint RESTful untuk berbagai fitur aplikasi.

## 🚀 Fitur Utama

- Autentikasi dan otorisasi pengguna
- Manajemen sesi dan token
- Integrasi dengan layanan pihak ketiga
- CRUD untuk entitas utama aplikasi
- Validasi dan sanitasi input
- Dokumentasi API dengan Swagger/OpenAPI

## 🧱 Teknologi yang Digunakan

- **Framework**: Laravel
- **Bahasa Pemrograman**: PHP
- **Database**: MySQL
- **Manajemen Paket**: Composer
- **Testing**: PHPUnit
- **Deployment**: Vercel

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

2. **Install dependensi PHP dan JavaScript**
   ```bash
   composer install
   npm install && npm run dev
   ```

3. **Salin file `.env` dan konfigurasi**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi dan seeding database**
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan server lokal**
   ```bash
   php artisan serve
   ```

## 🧪 Testing

Untuk menjalankan tes unit dan fitur:

```bash
php artisan test
```

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

> Untuk kontribusi, silakan fork repositori ini dan ajukan pull request dengan perubahan yang diusulkan.
