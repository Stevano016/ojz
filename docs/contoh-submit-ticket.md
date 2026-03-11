# Contoh Submit Ticket dari Website

Base URL API: `http://localhost:8000/api` (ganti dengan URL production jika perlu).

---

## 1. Login dulu (ambil token)

```bash
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"admin@ozj.nl\",\"password\":\"password\"}"
```

Response contoh:
```json
{
  "token": "1|abc123...",
  "user": { "id": 1, "name": "Admin OZJ", "email": "admin@ozj.nl" }
}
```

Simpan nilai `token` untuk request berikutnya.

---

## 2. Buat ticket (pakai token)

**Minimal (wajib):**
- `judul_sinyal` (string, max 255)
- `deskripsi_sinyal` (string)
- `level_sinyal` (string, misal: Low, Medium, High)
- `urgensi_sinyal` (string, misal: Rendah, Sedang, Tinggi)

```bash
curl -X POST "http://localhost:8000/api/tickets" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_ANDA_DARI_LOGIN" \
  -d "{
    \"judul_sinyal\": \"Keterlambatan distribusi bantuan wilayah X\",
    \"deskripsi_sinyal\": \"Laporan dari lapangan: distribusi terlambat 3 hari, warga mengeluh.\",
    \"level_sinyal\": \"Medium\",
    \"urgensi_sinyal\": \"Tinggi\"
  }"
```

**Dengan field tambahan (opsional):**

```bash
curl -X POST "http://localhost:8000/api/tickets" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_ANDA_DARI_LOGIN" \
  -d "{
    \"judul_sinyal\": \"Keterlambatan distribusi bantuan wilayah X\",
    \"deskripsi_sinyal\": \"Laporan dari lapangan: distribusi terlambat 3 hari.\",
    \"ringkasan_sinyal\": \"Potensi keterlambatan distribusi bantuan\",
    \"level_sinyal\": \"Medium\",
    \"urgensi_sinyal\": \"Tinggi\",
    \"kategori_sinyal\": \"Logistik\",
    \"nama_program\": \"Program Bantuan Pangan\",
    \"nama_pelapor\": \"Budi Santoso\",
    \"nama_lokasi\": \"Kabupaten Sleman\",
    \"risiko_teridentifikasi\": \"Distribusi tidak merata\",
    \"rekomendasi_ai\": \"Percepat koordinasi dengan gudang regional\"
  }"
```

Response sukses (201):
```json
{
  "id": 1,
  "ticket_id": "OZJ-20260310-1234",
  "judul_sinyal": "Keterlambatan distribusi bantuan wilayah X",
  "deskripsi_sinyal": "Laporan dari lapangan...",
  "status_ticket": "Open",
  "waktu_catat": "2026-03-10T21:30:00.000000Z",
  ...
}
```

---

## 3. Satu perintah: login + buat ticket (bash)

Ganti `API_URL`, email, dan password sesuai environment Anda.

```bash
API_URL="http://localhost:8000/api"
EMAIL="admin@ozj.nl"
PASSWORD="password"

# Login
RESP=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo "$RESP" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
if [ -z "$TOKEN" ]; then echo "Login gagal"; exit 1; fi

# Buat ticket
curl -s -X POST "$API_URL/tickets" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "judul_sinyal": "Contoh ticket dari script",
    "deskripsi_sinyal": "Deskripsi lengkap laporan atau sinyal.",
    "level_sinyal": "Medium",
    "urgensi_sinyal": "Tinggi"
  }'
```
