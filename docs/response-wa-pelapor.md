# Response WhatsApp untuk pelapor (saat pertama melaporkan)

Saat pelapor mengirim laporan pertama kali lewat WhatsApp, balas dengan konfirmasi dan nomor tiket agar mereka bisa mengecek status.

---

## Contoh teks balasan

```
Terima kasih atas laporannya. Saya bantu untuk diproses.

Ini nomor tiket Anda: #OZJ-20260310-1234

Anda bisa cek status kapan saja di:
[URL_WEBSITE]/track?ticket_id=OZJ-20260310-1234
```

Versi singkat:

```
Terima kasih atas laporannya, saya bantu untuk diproses. Ini nomor tiket Anda: #OZJ-20260310-1234. Cek status: [URL]/track?ticket_id=OZJ-20260310-1234
```

---

## Cara menerapkan di n8n

Di workflow **"OZJ — AI Signal Intelligence Youth Care (Enhanced)"**:

1. Setelah node yang menghasilkan `ticket_id` (misalnya setelah **Fungsi: Flatten Sinyal** atau setelah **Respons: Konfirmasi Input**), tambah node **WAHA: Send Text** (atau node kirim WhatsApp yang Anda pakai).
2. Konfigurasi:
   - **chatId**: `{{ $json.wa_chat_id }}` (nomor pengirim yang melaporkan)
   - **text**: gunakan teks di atas, dengan `{{ $json.ticket_id }}` untuk nomor tiket.
3. Contoh ekspresi untuk **text**:
   ```
   Terima kasih atas laporannya. Saya bantu untuk diproses.

   Ini nomor tiket Anda: #{{ $json.ticket_id }}

   Cek status laporan di: [BASE_URL_WEBSITE]/track?ticket_id={{ $json.ticket_id }}
   ```
   Ganti `[BASE_URL_WEBSITE]` dengan URL aplikasi Anda (mis. `https://ozj.example.com`).

4. Pastikan node ini dijalankan untuk **semua** laporan masuk (dari WA maupun input manual yang punya `wa_chat_id`), agar pelapor WA selalu dapat balasan pertama dengan nomor tiket dan link tracking.

---

## Ringkasan

| Yang dikirim ke pelapor | Isi |
|-------------------------|-----|
| Ucapan terima kasih     | "Terima kasih atas laporannya, saya bantu untuk diproses." |
| Nomor tiket             | "#OZJ-YYYYMMDD-XXXX" |
| Link cek status         | `[URL_WEBSITE]/track?ticket_id=OZJ-YYYYMMDD-XXXX` |

Halaman **/track** adalah halaman **public** (tanpa login) untuk pelapor mengecek status tiket.
