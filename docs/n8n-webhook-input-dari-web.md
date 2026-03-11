# Input Sinyal dari Web masuk ke Workflow n8n

**Aturan:** Input manual dari web **hanya** masuk ke webhook n8n. Data **tidak** disimpan ke DB oleh Laravel. Setelah n8n menyimpan ke spreadsheet, data baru dibaca di website (sync Sheet → DB saat buka dashboard/daftar tiket).

---

## 1. Alur singkat

```
[User isi form di website]
        ↓
POST /api/tickets (Laravel, dengan auth)
        ↓
Laravel: TIDAK simpan ke DB — langsung POST ke n8n webhook
        ↓
n8n: Webhook "Input Manual Sinyal" (path: ozj-log-signal)
     → Gemini ekstraksi sinyal
     → Parse JSON → Flatten → Append Google Sheet
     → Routing (skor/urgensi) → Voice call / WhatsApp / Email / Log
     → Response konfirmasi (ticket_id, dll)
        ↓
[User buka dashboard / daftar tiket]
        ↓
Laravel: sync dari spreadsheet (CSV) → DB → tampilkan data
```

- **Create dari web:** response API **202 Accepted** + `message`, `ticket_id` (dari n8n).
- **Baca di website:** selalu dari sync Sheet → DB (dashboard & list tiket sudah sync dari CSV di awal request).

---

## 2. Konfigurasi di Laravel (tinggal isi URL)

Tambahkan atau edit di **`.env`**:

```env
# URL webhook n8n untuk trigger "Webhook: Input Manual Sinyal" (path: ozj-log-signal)
N8N_SIGNAL_WEBHOOK_URL=https://NAMA_ANDA.app.n8n.cloud/webhook/ozj-log-signal
```

- Ganti dengan URL n8n Anda (cloud atau self-hosted).
- Path harus sama dengan path di node **Webhook: Input Manual Sinyal** di workflow (`ozj-log-signal`).
- Untuk **test** di n8n biasanya pakai: `https://.../webhook-test/ozj-log-signal`.

Contoh self-hosted:

```env
N8N_SIGNAL_WEBHOOK_URL=http://localhost:5678/webhook/ozj-log-signal
```

---

## 3. Format payload yang dikirim Laravel ke n8n

Laravel mengirim **POST JSON** dengan struktur yang dipakai node **Gemini: Ekstraksi Sinyal dari Observasi** di workflow:

| Field | Isi |
|-------|-----|
| `payload.body` | Teks observasi (gabungan Judul, Deskripsi, Level, Urgensi, Pelapor, Lokasi, dll.) |
| `payload._data.notifyName` | Nama pelapor atau "Web" |
| `payload.from` | Nomor/wa_chat_id (kosong jika dari web) |
| `payload.timestamp` | Unix timestamp waktu catat |
| `nama_program` | Dari tiket |
| `nama_lokasi` | Dari tiket |
| `level_laporan` | Level sinyal / laporan |
| `urgensi_laporan` | Urgensi awal |
| `ticket_id` | ID tiket dari Laravel (OZJ-YYYYMMDD-XXXX) |

Workflow n8n memakai `payload.body` sebagai **teks observasi** untuk Gemini; field lain dipakai untuk konteks (nama, lokasi, program, urgensi).

---

## 4. Yang perlu dicek di n8n

1. **Workflow aktif**  
   Pastikan workflow **"OZJ — AI Signal Intelligence Youth Care (Enhanced)"** dalam status **Active**.

2. **Node Webhook**  
   Node **"Webhook: Input Manual Sinyal"**:
   - Method: **POST**
   - Path: **ozj-log-signal**
   - Production URL: `https://<n8n-base>/webhook/ozj-log-signal`  
   URL inilah yang dipakai di `N8N_SIGNAL_WEBHOOK_URL`.

3. **Credential & node berikutnya**  
   Pastikan credential untuk Gemini, Google Sheets, WAHA, Zenziva, Gmail sudah terisi dan node-node setelah webhook (Gemini → Parse → Flatten → Sheet → Switch → Alert) jalan normal.

---

## 5. Tanpa Laravel (langsung dari frontend ke n8n)

Jika ingin **frontend** memanggil n8n **langsung** (tanpa lewat Laravel):

1. Frontend **tetap** POST ke Laravel `POST /api/tickets` untuk menyimpan tiket dan sync ke Sheet.
2. **Tambahan:** frontend bisa juga POST ke URL n8n yang sama dengan format di atas (opsional, biasanya cukup lewat Laravel sekali).

Format body yang diterima n8n (untuk manual / testing):

```json
{
  "payload": {
    "body": "Judul: Keterlambatan distribusi.\nDeskripsi: Laporan dari lapangan...\nLevel: Medium.\nUrgensi: Tinggi.",
    "_data": { "notifyName": "Web" },
    "from": "",
    "timestamp": 1700000000
  },
  "nama_program": "Program Bantuan Pangan",
  "nama_lokasi": "Kabupaten Sleman",
  "level_laporan": "Medium",
  "urgensi_laporan": "Tinggi",
  "ticket_id": "OZJ-20260310-1234"
}
```

---

## 6. Ringkasan

| Yang diatur | Nilai |
|-------------|--------|
| **Laravel** | Set `N8N_SIGNAL_WEBHOOK_URL` di `.env` ke URL webhook n8n (path `ozj-log-signal`). |
| **Perilaku** | Setiap tiket yang berhasil dibuat lewat `POST /api/tickets` otomatis di-forward ke n8n. |
| **n8n** | Workflow harus aktif; node Webhook path = `ozj-log-signal`. |

Dengan ini, **input sinyal dari web** (form tiket) akan masuk ke workflow n8n yang sama (AI, routing, alert) seperti input manual/WhatsApp di workflow tersebut.
