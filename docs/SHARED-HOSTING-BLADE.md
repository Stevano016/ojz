# Deploy ke Shared Hosting (Blade)

Aplikasi sekarang menggunakan **Blade** (bukan React/SPA) sehingga bisa di-publish ke shared hosting **tanpa Node.js atau npm build**.

## Yang berubah

- **Frontend:** Semua halaman sekarang di-render server-side dengan Blade (`resources/views/`).
- **Auth:** Login/logout pakai **session** (bukan token API). Cocok untuk hosting yang hanya support PHP.
- **CSS:** Tailwind dipakai lewat **CDN** (cdn.tailwindcss.com), tidak perlu `npm run build`.
- **Route:** Semua akses web lewat `routes/web.php`. API (`/api/*`) tetap ada untuk integrasi eksternal.

## Langkah deploy

1. Upload seluruh isi project ke hosting (kecuali `node_modules`, `.env` disesuaikan di server).
2. Set **document root** ke folder `public` (bukan root project).
3. Pastikan PHP 8.2+ dan ekstensi Laravel biasa (mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath, fileinfo).
4. Di server, copy `.env.example` ke `.env`, isi `APP_KEY`, database, dan konfigurasi lain. Jalankan `php artisan key:generate` bila perlu.
5. Jalankan `php artisan migrate --force` (dan `php artisan config:cache`, `route:cache` bila ingin).
6. **Tidak perlu** menjalankan `npm install` atau `npm run build` untuk tampilan web.

## Route utama (web)

| URL | Keterangan |
|-----|------------|
| `/` | Redirect ke login |
| `/login` | Form login (session) |
| `/dashboard` | Dashboard (perlu login) |
| `/tickets` | Daftar tiket |
| `/tickets/new` | Form tiket manual |
| `/tickets/{id}` | Detail & edit tiket |
| `/track` | Cek status tiket (public) |
| `/track/{ticket_id}` | Cek status by ID (public) |

File React di `resources/js/` tidak dipakai lagi untuk tampilan web; bisa dihapus jika tidak dipakai untuk keperluan lain.
