# Resume Fitur & Library Backend (Laravel + Blade)

Proyek ini adalah sistem manajemen tiket (OZJ Backend) yang dirancang untuk mengelola laporan dan sinyal dari masyarakat (khususnya pemuda). Berikut adalah detail teknisnya:

## 1. Fitur-Fitur Utama

### Manajemen Tiket (CRUD & Monitoring)
- **Ticket CRUD**: Admin dapat melihat daftar tiket, membuat tiket baru, melihat detail, dan memperbarui status/informasi tiket.
- **Ticket Actions (Log)**: Setiap perubahan atau pembaruan pada tiket dicatat sebagai "Action" untuk melacak riwayat penanganan.
- **Tracking Publik**: Halaman `/track` memungkinkan pelapor mengecek status tiket mereka menggunakan `Ticket ID` tanpa perlu login.

### Integrasi Eksternal
- **Google Sheets Sync**: Fitur untuk melakukan sinkronisasi data tiket dari Google Sheets melalui file CSV (menggunakan Command `php artisan tickets:sync-sheet`).
- **n8n Webhook**: Endpoint API `/api/webhook/n8n` yang siap menerima data dari n8n (kemungkinan untuk integrasi WhatsApp atau otomasi lainnya).

### Dashboard & Pelaporan
- **Dashboard**: Statistik visual mengenai jumlah tiket berdasarkan status dan kategori (tersedia di web Blade dan API).
- **Export Excel**:
    - **Rekap**: Export seluruh daftar tiket dalam format Excel.
    - **Per-Ticket**: Export detail tiket tertentu secara mendalam.

### Keamanan & Akses
- **Multi-Auth**: Mendukung login admin melalui sistem Blade (Session) dan React (Sanctum/API).
- **Sanctum**: Digunakan untuk mengamankan API yang dikonsumsi oleh frontend React.

### Lokalisasi (Multi-bahasa)
- Mendukung 3 bahasa: **Bahasa Indonesia (ID)**, **Inggris (EN)**, dan **Belanda (NL)**. Pengguna dapat berpindah bahasa melalui switcher yang tersedia.

## 2. Library Utama yang Digunakan

| Library | Kegunaan |
|---------|----------|
| **`maatwebsite/excel`** | Digunakan untuk mengolah dan mengekspor data tiket ke file Excel (.xlsx). |
| **`google/apiclient`** | Digunakan untuk berinteraksi dengan API Google (Drive & Sheets) untuk kebutuhan sinkronisasi data. |
| **`laravel/sanctum`** | Menyediakan sistem otentikasi API yang ringan untuk SPA (Single Page Application) atau mobile app. |
| **`laravel/tinker`** | Memudahkan eksekusi kode PHP secara interaktif melalui CLI untuk debugging data. |

## 3. Struktur Routing
- **Web (`routes/web.php`)**: Melayani halaman berbasis Blade untuk admin (dashboard, tiket, setting, login).
- **API (`routes/api.php`)**: Melayani endpoint JSON untuk frontend React dan integrasi pihak ketiga (n8n).
- **Console (`routes/console.php`)**: Berisi perintah CLI kustom seperti `tickets:sync-sheet`.
