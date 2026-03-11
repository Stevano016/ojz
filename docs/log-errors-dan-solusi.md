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

## 6. **Scheduled command tickets:sync-sheet failed**

**Penyebab:** Ada scheduler yang menjalankan command `tickets:sync-sheet` yang mungkin sudah dihapus. Sync sekarang dilakukan on-demand (saat buka dashboard / list tiket).

**Solusi:** Tidak perlu jalankan `schedule:work` untuk sync sheet. Jika masih ada jadwal yang panggil `tickets:sync-sheet`, hapus dari `routes/console.php` (di codebase saat ini sudah tidak ada).
