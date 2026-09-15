# Deploy Portal Request BTC ke Internet (Gratis) — Render.com + Neon.tech

Panduan ini untuk menaruh aplikasi online supaya bisa diakses tim dari mana saja, pakai layanan gratis:
- **Neon.tech** → database (pengganti SQLite lokal Anda)
- **Render.com** → tempat aplikasinya jalan (hosting)

Beberapa langkah di bawah **wajib dilakukan sendiri** (bikin akun, klik-klik di dashboard) — saya tidak bisa mendaftarkan akun atas nama Anda. Tapi semua file konfigurasi teknisnya (`Dockerfile`, `render.yaml`) sudah saya siapkan di project ini, jadi prosesnya tinggal ikuti saja.

---

## 0. Syarat: Kode harus sudah ada di GitHub

✅ Sudah beres — kode project ini sudah ter-push ke `https://github.com/nyayu-mh/PKL-Portal-Request`. Render nanti connect ke repo ini.

---

## 1. Buat Database Gratis di Neon

1. Buka https://neon.tech → **Sign up** (bisa pakai akun Google, tidak perlu kartu kredit).
2. Setelah masuk, buat **New Project** — nama bebas, misal `portal-request-btc`. Region pilih yang terdekat (Singapore kalau ada).
3. Neon otomatis membuatkan 1 database. Buka menu **Connection Details** / **Dashboard**, cari info berikut (biasanya ada dalam bentuk "connection string" seperti `postgresql://USER:PASSWORD@HOST/DBNAME?sslmode=require` — dari situ Anda bisa pisahkan):
   - **Host** (contoh: `ep-xxxx-xxxx.ap-southeast-1.aws.neon.tech`)
   - **Database name** (biasanya `neondb`)
   - **User**
   - **Password**
4. Catat 4 nilai di atas — nanti dipakai di Langkah 3.

---

## 2. Buat Akun Render & Hubungkan GitHub

1. Buka https://render.com → **Get Started** → daftar/login pakai akun **GitHub yang punya akses ke repo `nyayu-mh/PKL-Portal-Request`** (supaya otomatis terhubung).
2. Kalau diminta izin akses repo, pilih repo `PKL-Portal-Request` (atau izinkan akses ke semua repo).

---

## 3. Deploy Aplikasinya

Cara termudah — pakai file `render.yaml` yang sudah saya siapkan (Render menyebutnya "Blueprint"):

1. Di dashboard Render, klik **New +** → **Blueprint**.
2. Pilih repo `PKL-Portal-Request`. Render akan otomatis membaca `render.yaml` dan menyiapkan 1 service bernama `portal-request-btc` dengan sebagian besar pengaturan sudah terisi.
3. Render akan minta Anda mengisi beberapa nilai yang sengaja saya kosongkan (karena rahasia/khusus akun Anda) — isi seperti ini:

   | Env Var | Isi dengan |
   |---|---|
   | `APP_KEY` | `base64:1hNbUenli6MatdeaxBRVDiv5obGCF2oP9m8r705axng=` (pakai persis ini, atau generate baru dari laptop Anda dengan `php artisan key:generate --show`) |
   | `APP_URL` | Isi setelah deploy pertama jadi (Render kasih tahu URL-nya, formatnya `https://portal-request-btc.onrender.com`) — boleh dikosongkan dulu, isi belakangan lalu redeploy |
   | `DB_HOST` | Host dari Neon (Langkah 1) |
   | `DB_DATABASE` | Nama database dari Neon |
   | `DB_USERNAME` | User dari Neon |
   | `DB_PASSWORD` | Password dari Neon |

4. Klik **Apply** / **Create**. Render akan mulai build (install Composer, npm, build Tailwind) — proses pertama biasanya **5–10 menit**, wajar karena bikin image dari nol.
5. Setelah selesai dan statusnya **Live**, buka URL yang diberikan Render (`https://portal-request-btc.onrender.com`). Halaman login BTC seharusnya muncul.
6. Login pakai akun contoh dari PANDUAN.md (misalnya `admin@brilliantthinkcenter.com` / `password`) — saat pertama kali start, aplikasi otomatis mengisi data contoh ke database Neon (lewat perintah `app:seed-if-empty` yang sudah saya siapkan, aman dijalankan berkali-kali).

> Kalau lebih suka isi manual tanpa Blueprint: **New +** → **Web Service** → pilih repo → Environment pilih **Docker** → Plan **Free** → isi Environment Variables satu-satu sesuai daftar di `render.yaml`.

---

## 4. Yang Perlu Anda Tahu (Batasan Free Tier)

- **Server "tidur" kalau 15 menit tidak ada yang buka.** Pembukaan pertama setelah itu akan lambat (~30–60 detik) sampai server bangun lagi. Setelah itu normal seperti biasa. Ini batasan resmi paket gratis Render, bukan bug.
- **File upload (lampiran GA, foto kondisi/kerusakan, quotation vendor) berisiko hilang** kalau service di-redeploy atau restart — soalnya di paket gratis, penyimpanan filenya tidak permanen (beda dengan database yang sudah aman karena disimpan di Neon, bukan di server Render). Kalau ini penting, nanti bisa saya bantu sambungkan ke penyimpanan file eksternal gratis (misalnya Cloudflare R2) — tinggal bilang.
- **Setiap `git push` ke GitHub, Render otomatis deploy ulang** (auto-deploy). Jadi alur kerja Anda ke depan: minta saya update kode → saya commit lokal → Anda `git push` sekali → Render otomatis update sendiri dalam beberapa menit.
- Database Neon gratis punya batas penyimpanan (0.5 GB) — lebih dari cukup untuk aplikasi internal skala tim kecil.

---

## 5. Kalau Ada Error

- **Build gagal** → buka tab **Logs** di service Render tersebut, baca pesan errornya, kirim ke saya.
- **Halaman muncul tapi error 500** → biasanya `APP_KEY` belum diisi atau salah satu `DB_*` salah ketik. Cek lagi di tab **Environment**.
- **"could not find driver" / error koneksi database** → pastikan `DB_SSLMODE=require` sudah ada (sudah saya siapkan di `render.yaml`) — Neon mewajibkan koneksi SSL.
