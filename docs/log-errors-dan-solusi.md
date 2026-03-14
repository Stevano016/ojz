# Penyebab Error di laravel.log & Solusi

Dari pengecekan `storage/logs/laravel.log`:

---

## 1. **Class "Google\Client" not found** (saat buat/update ticket → sync ke Sheet gagal)

**Penyebab:** Package `google/apiclient` belum ter-load (autoload atau dependency belum lengkap).

**Solusi:**
```bash
composer dump-autoload
composer install
```
Restart server/queue jika pakai `php artisan serve` atau `php artisan queue:work`.  
Jika masih error, pastikan tidak ada konflik PHP version (perlu PHP 8.1+).

---

## 2. **Column 'skor_urgensi_hukum' cannot be null** (sync dari Sheet → DB gagal)

**Penyebab:** Baris CSV dari Google Sheet ada kolom kosong untuk integer; DB tidak mengizinkan NULL.

**Solusi:** Sudah diperbaiki di kode: kolom `skor_urgensi_hukum`, `skor_urgensi_tertinggi`, `sinyal_index`, `sinyal_total` sekarang pakai default `0` jika kosong. Pastikan kode terbaru ter-deploy.

---

## 3. **cURL error 28: Operation timed out** (ambil CSV dari Google)

**Penyebab:** Request ke Google Sheets (pub?output=csv) timeout (jaringan lambat / firewall / Google sibuk).

**Solusi:** Timeout fetch CSV sudah dinaikkan jadi 25 detik. Jika masih sering timeout: cek jaringan, VPN, atau proxy; atau coba lagi di waktu lain.

---

## 3b. **cURL error 28: Connection timed out** untuk `oauth2.googleapis.com/token` (sync ke Sheet gagal)

**Penyebab:** Koneksi ke Google (ambil token OAuth) tidak terbentuk dalam waktu yang diset (sebelumnya 10 detik). Sering terjadi di jaringan lambat, VPN, atau firewall yang membatasi akses ke `*.googleapis.com`.

**Solusi:**
1. **Naikkan timeout** di `.env` (nilai dalam detik):
   ```env
   GOOGLE_CONNECT_TIMEOUT=25
   GOOGLE_HTTP_TIMEOUT=45
   ```
   Default di kode sudah 25/45. Jika masih timeout, coba `40` dan `60`.
2. **Cek jaringan:** Pastikan `https://oauth2.googleapis.com` dan `https://sheets.googleapis.com` bisa diakses dari mesin yang menjalankan Laravel (browser atau `curl -I https://oauth2.googleapis.com`).
3. **VPN/Proxy:** Jika pakai VPN atau proxy korporat, coba matikan atau gunakan jaringan lain; atau jalankan aplikasi di server yang punya akses stabil ke Google.

---

## 4. **Table 'personal_access_tokens' doesn't exist**

**Penyebab:** Migration Sanctum belum dijalankan.

**Solusi:**
```bash
php artisan migrate
```

---

## 5. **Duplicate entry 'admin@ozj.nl'** (saat db:seed)

**Penyebab:** UserSeeder pakai `create()` sehingga seed ulang bentrok.

**Solusi:** Sudah diperbaiki: UserSeeder pakai `firstOrCreate` sehingga aman dijalankan ulang.

---

## 6. **Google service account credentials file not found** (sync ke Sheet gagal, banner kuning di halaman tiket)

**Penyebab:** File JSON credentials tidak ada di path yang diset di `.env` (`GOOGLE_APPLICATION_CREDENTIALS`). Default: `storage/app/google/service-account.json`.

**Solusi:**
1. Buat folder `storage/app/google/` bila belum ada.
2. Di Google Cloud Console: enable Google Sheets API, buat Service Account, download key JSON.
3. Simpan file tersebut sebagai `storage/app/google/service-account.json`.
4. Share spreadsheet (Google Sheets) dengan email service account (…@….iam.gserviceaccount.com) sebagai **Editor**.
5. Cek `.env`: `GOOGLE_SHEETS_SPREADSHEET_ID` = ID spreadsheet (dari URL: `/d/ID_INI/edit`).

Lihat juga `storage/app/google/README.md` untuk langkah detail.

---

## 7. **Scheduled command tickets:sync-sheet failed**

**Penyebab:** Ada scheduler yang menjalankan command `tickets:sync-sheet` yang mungkin sudah dihapus. Sync sekarang dilakukan on-demand (saat buka dashboard / list tiket).

**Solusi:** Tidak perlu jalankan `schedule:work` untuk sync sheet. Jika masih ada jadwal yang panggil `tickets:sync-sheet`, hapus dari `routes/console.php` (di codebase saat ini sudah tidak ada).
