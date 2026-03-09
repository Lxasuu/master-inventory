# Meta Inventory Application

Sistem manajemen inventaris PC berbasis web yang dibangun untuk **Politeknik META Industri**. Aplikasi ini membantu melacak kondisi, lokasi, dan status aplikasi PC secara real-time dengan tampilan dashboard yang modern.

## 🚀 Fitur Utama

- **Dashboard Interaktif**: Grafik statis dan visualisasi peta Lantai 2 & Lantai 3 untuk distribusi PC.
- **Manajemen Data PC**: Tambah, edit, view, dan hapus data PC. Didukung **Import Excel** (.xls, .xlsx, .csv) dan **Bulk Delete** (pilih banyak sekaligus).
- **Manajemen Pengguna**: Kelola user dengan role Admin, PIC, dan User. Mendukung **Bulk Delete** pengguna.
- **Role-Based Access Control**: Kontrol akses berdasar peran — fitur tertentu hanya bisa diakses Admin atau PIC.
- **Notifikasi Real-time**: Bell notification untuk aktivitas CREATE, UPDATE, DELETE PC. Mendukung "Baca Semua" yang langsung membersihkan daftar notifikasi.
- **Profil & Avatar**: Upload foto profil pengguna, pengaturan data diri.
- **Activity Logs**: Rekam jejak semua perubahan data di sistem.
- **CSRF Protection**: Setiap form dan AJAX request dilindungi token CSRF.

## 🛠️ Tech Stack

- **Backend**: PHP 8.x (native, tanpa framework)
- **Database**: MySQL / MariaDB
- **Frontend**: Bootstrap 5, Morvin Admin Template
- **Chart**: ApexCharts
- **Tabel**: DataTables (dengan export Excel & PDF)
- **Alert/Konfirmasi**: SweetAlert2
- **Import Excel**: PHPOffice/PhpSpreadsheet

## 📦 Instalasi

1. **Clone repository** ke server lokal (Laragon / XAMPP):
   ```
   git clone https://github.com/Lxasuu/master-inventory.git HTML
   ```
2. **Setup Database**:
   - Buat database bernama `meta_inventory_sql`.
   - Import file `database.sql` untuk schema dan data awal.
3. **Konfigurasi**:
   - Update `config/db.php` dengan kredensial database Anda.
4. **Install dependensi PHP** (untuk fitur Import Excel):
   ```
   composer install
   ```
5. **Akses Aplikasi**:
   - URL: `http://localhost/HTML/`
   - Default Admin: `admin` / `admin123`

## 📁 Struktur Folder

```
HTML/
├── assets/          # CSS, JS, gambar
├── config/          # Konfigurasi DB
├── notification/    # AJAX notifikasi
├── partials/        # Komponen reusable (topbar, sidebar, dll.)
├── pcs/             # Modul Data PC (index, create, edit, view, import)
├── profile/         # Modul Profil Pengguna
├── uploads/         # File upload pengguna
├── users/           # Modul Manajemen Pengguna
├── vendor/          # Composer dependencies
├── index.php        # Dashboard utama
└── auth-login.php   # Halaman login
```

---
© 2026 Politeknik META Industri
