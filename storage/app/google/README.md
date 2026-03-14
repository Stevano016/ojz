# Google Service Account credentials

Agar sinkron ke Google Sheets jalan (update tiket dari web → database + spreadsheet), letakkan file **service account JSON** di sini dengan nama:

```
service-account.json
```

## Cara dapat file

1. Buka [Google Cloud Console](https://console.cloud.google.com/) → pilih project (atau buat baru).
2. **APIs & Services** → **Library** → cari **Google Sheets API** → Enable.
3. **APIs & Services** → **Credentials** → **Create credentials** → **Service account**.
4. Buat service account (nama bebas) → **Keys** → **Add key** → **Create new key** → **JSON**.
5. Download file JSON, rename jadi `service-account.json`, pindahkan ke folder ini:  
   `storage/app/google/service-account.json`
6. Di Google Sheets: **Share** spreadsheet dengan **email service account** (format: `...@....iam.gserviceaccount.com`) sebagai **Editor**.

Pastikan di `.env`:

- `GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/service-account.json`
- `GOOGLE_SHEETS_SPREADSHEET_ID=` (ID dari URL sheet: `.../d/ID_INI/edit`)
- `GOOGLE_SHEETS_TICKETS_RANGE=` nama sheet + range. Jika tab pertama bernama **A1**: `'A1'!A1:AH`. Jika bernama **Sheet1**: `Sheet1!A1:AH`. (Nama yang mirip sel harus pakai tanda kutip.)
- `GOOGLE_SHEETS_SYNC_ENABLED=true` agar update tiket ikut ke Sheet.

## Jaringan & timeout (cURL 28)

Agar sync berhasil, **mesin yang menjalankan Laravel** harus bisa mengakses:

- `https://oauth2.googleapis.com/token`
- `https://sheets.googleapis.com`

Jika dari laptop/PC kamu kena **Connection timed out**:

1. **Cek akses:** di terminal: `curl -I --connect-timeout 10 https://oauth2.googleapis.com`
2. **Jalankan di server yang bisa akses Google:** deploy Laravel ke VPS/cloud (GCP, AWS, dll.) atau server kantor yang tidak memblok Google. Dari sana sync akan jalan.
3. **VPN:** jika ada firewall/ISP yang memblok, coba pakai VPN yang bisa akses Google.
4. **Sementara tanpa sync:** set `GOOGLE_SHEETS_SYNC_ENABLED=false` di `.env` — data tetap tersimpan di database, hanya tidak ke Sheet sampai sync diaktifkan lagi di lingkungan yang bisa akses Google.
