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
