# Panduan Portal Request — Brilliant Think Center

Halo Patur 👋 Ini panduan menjalankan aplikasi **Portal Request BTC** di komputer Anda, ditulis santai selangkah demi selangkah untuk pemula. Ikuti urut dari atas ke bawah, jangan loncat-loncat dulu.

Aplikasi ini dibangun pakai **Laravel** (framework PHP paling populer) + **Livewire** (biar halaman terasa interaktif tanpa nulis banyak JavaScript) + **Tailwind CSS** (untuk tampilan modern).

---

## 0. Sebelum Mulai — Istilah yang Perlu Anda Tahu

- **PHP** — bahasa pemrograman yang dipakai Laravel.
- **Composer** — "installer" untuk paket-paket PHP (mirip Play Store, tapi untuk kode).
- **Node.js & npm** — dipakai untuk memproses tampilan (CSS/JS) supaya jadi modern.
- **SQLite** — database yang dipakai aplikasi ini. Kelebihannya: **tidak perlu install MySQL/phpMyAdmin apa pun**, cukup 1 file di folder project. Sangat cocok untuk belajar & development. (Nanti kalau mau dipakai serius di server kantor, gampang kok pindah ke MySQL — tinggal ubah beberapa baris di file `.env`, tanya saya lagi kalau sudah sampai tahap itu.)
- **Terminal / Command Prompt** — aplikasi buat mengetik perintah. Di Windows namanya *Command Prompt* atau *PowerShell*, di Mac namanya *Terminal*.

---

## 1. Install Software yang Dibutuhkan

Cek dulu apakah sudah ada di komputer Anda. Buka Terminal/Command Prompt, ketik:

```
php -v
composer -V
node -v
npm -v
```

Kalau semua muncul versinya (bukan pesan error "command not found"), lanjut ke Langkah 2.

Kalau belum ada, cara paling gampang:

**Windows** — Install **Laragon** (https://laragon.org/download/) — satu aplikasi yang sudah termasuk PHP & Composer sekaligus. Setelah install, PHP & Composer otomatis bisa dipakai dari Terminal.

**Mac** — Buka Terminal, jalankan:
```
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"
```
Perintah ini otomatis install PHP, Composer, dan Laravel installer sekaligus.

**Node.js** (untuk Windows maupun Mac) — download & install dari https://nodejs.org (pilih versi **LTS**).

Setelah install apa pun di atas, **tutup dan buka lagi** Terminal/Command Prompt Anda, baru lanjut.

---

## 2. Ambil Kode Aplikasi

Anda akan menerima file `portal-request.zip`. Extract (unzip) ke folder yang mudah diingat, misalnya:

- Windows: `C:\laragon\www\portal-request` (kalau pakai Laragon, taruh di dalam folder `www`)
- Mac: `~/Sites/portal-request` atau folder mana saja yang Anda suka

Buka Terminal/Command Prompt, lalu masuk ke folder tersebut, contoh:

```
cd C:\laragon\www\portal-request
```

---

## 3. Install "Mesin" Aplikasi (Composer & npm)

Masih di folder yang sama, jalankan satu-satu (tunggu sampai selesai sebelum lanjut ke baris berikutnya):

```
composer install
```

Ini akan download semua "komponen" Laravel yang dipakai aplikasi (butuh koneksi internet, biasanya 1–3 menit).

```
npm install
```

Ini download komponen untuk tampilan (Tailwind CSS, Vite).

> File `.env` (pengaturan aplikasi) **sudah disiapkan** di dalam paket ini, jadi Anda tidak perlu bikin dari nol. Tampilan (folder `public/build`) juga sudah dibuat, jadi aplikasi bisa langsung dicoba setelah `composer install` selesai — TIDAK wajib jalankan `npm run build` dulu di percobaan pertama.
>
> Soal database (`database/database.sqlite`): **mulai paket update ini, file tersebut sengaja TIDAK disertakan** di dalam zip (supaya update berikutnya tidak menimpa/menghapus data Anda yang sudah diisi). Kalau file ini belum ada di folder Anda, buat dulu file kosong dengan nama persis `database.sqlite` di dalam folder `database/` (klik kanan → New → Text Document, lalu rename jadi `database.sqlite`, hapus akhiran `.txt`-nya) — nanti otomatis terisi tabel & data pas Langkah 4.

---

## 4. Siapkan Database

Jalankan perintah ini untuk membuat semua tabel + mengisi beberapa akun & data contoh:

```
php artisan migrate:fresh --seed
```

> Catatan: kalau sebelumnya Anda sudah pernah menjalankan `migrate --seed` versi lama (sebelum ada fitur approval atasan), **wajib pakai `migrate:fresh --seed`** (bukan `migrate --seed` biasa) supaya struktur tabel yang berubah (approval atasan) ke-update dengan bersih. `migrate:fresh` akan menghapus data lama di database SQLite Anda dan membuat ulang dari awal — aman dipakai selama masih tahap belajar/testing, tapi **jangan dipakai lagi kalau sudah ada data sungguhan** (nanti kalau sudah tahap itu, tanya saya cara migrasi yang aman).

Kalau berhasil, Anda akan melihat daftar tabel yang dibuat satu per satu tanpa pesan merah/error.

Lalu jalankan ini (supaya file lampiran/upload bisa tampil di browser):

```
php artisan storage:link
```

---

## 5. Jalankan Aplikasinya!

```
php artisan serve
```

Biarkan Terminal ini tetap terbuka (jangan ditutup selama Anda memakai aplikasi). Buka browser, akses:

```
http://127.0.0.1:8000
```

Anda akan diarahkan ke halaman **Login**. 🎉

### Akun Contoh untuk Login

Semua password akun contoh di bawah: **`password`**

| Peran | Email | Kegunaan |
|---|---|---|
| Admin | admin@brilliantthinkcenter.com | Akses penuh, kelola user & semua master data |
| Tim HR | hr@brilliantthinkcenter.com | Proses & update status ERF |
| Tim GA (Gerry Anda) | ga@brilliantthinkcenter.com | Proses & update status request GA |
| Contoh Manager | manager@brilliantthinkcenter.com | Bisa bikin ERF & GA tanpa perlu approval (langsung ke HR/GA). Juga berperan sebagai **atasan** dari "Contoh Leader" — jadi akun ini akan melihat 1 request GA contoh menunggu approvalnya di menu **Approval Saya** |
| Contoh Leader | leader@brilliantthinkcenter.com | Bisa bikin ERF & GA, tapi request-nya harus **disetujui dulu oleh atasannya (Contoh Manager) di dalam aplikasi** sebelum diteruskan ke HR/GA |
| Contoh Staff | staff@brilliantthinkcenter.com | Tidak bisa bikin ERF/GA (sesuai aturan) — hanya lihat dashboard |

**Segera ganti semua password ini** setelah login pertama kali, lewat menu *Master Data → Manajemen User* (login sebagai Admin).

---

## 6. Coba Alurnya

1. Login sebagai **Contoh Leader** → menu **Request ERF** → **Buat ERF** → isi form → kirim. Perhatikan: field berubah otomatis sesuai "Jenis ERF" yang dipilih. Karena Leader butuh approval atasan, akan muncul info bahwa request ini menunggu persetujuan atasannya (Contoh Manager) — **tidak perlu upload lampiran apa pun**, cukup ajukan.
2. Login sebagai **Contoh Manager** → buka menu **Approval Saya** di sidebar (ada badge angka merah kalau ada yang menunggu) → buka ERF tadi → di kartu **Approval Atasan**, klik **Setujui** (atau **Kembalikan untuk Revisi** kalau mau coba alur ditolak — wajib isi catatan alasan).
3. Login sebagai **Tim HR** → buka ERF yang sudah disetujui tadi → sekarang bagian **Update Proses HR** sudah bisa diisi (sebelum disetujui atasan, bagian ini tersembunyi & muncul pesan "belum bisa diproses HR"). Ubah status, isi tanggal → Simpan. Riwayat perubahan otomatis tercatat.
4. **Coba alur ditolak**: kalau di langkah 2 tadi Anda pilih "Kembalikan untuk Revisi", login lagi sebagai **Contoh Leader** → buka ERF tersebut → akan terlihat catatan revisi dari atasan, dan ada 2 tombol: **Edit & Ajukan Ulang** (edit form lalu kirim ulang, otomatis menunggu approval atasan lagi) atau **Hapus Request** (kalau memang mau dibatalkan).
5. Login sebagai **Contoh Leader** lagi → coba **Request GA** dengan Jenis Request = Maintenance → perhatikan muncul field wajib "Lampiran Bukti Kondisi/Kerusakan", dan info yang sama soal approval atasan.
6. Login sebagai **Contoh Manager** → **Approval Saya** → setujui request GA tadi.
7. Login sebagai **Tim GA (Gerry Anda)** → buka request GA tadi → tambahkan minimal 3 quotation vendor, pilih salah satu jadi vendor terpilih, update status sampai Selesai.
8. Login sebagai **Admin** → menu **Master Data** → coba tambah Jabatan & TTF baru, tambah karyawan, tambah hari libur di Kalender Kerja, atau tambah user baru (termasuk atur **Atasan Langsung**-nya, lihat Langkah 7 di bawah).

---

## 7. Melengkapi Data Sebelum Dipakai Sungguhan

Sebelum tim benar-benar pakai aplikasi ini, sebagai **Admin** lengkapi dulu (menu **Master Data**):

- **Manajemen User** — daftarkan semua karyawan yang perlu login (leader, manager, HR, GA), sesuai role & level jabatan masing-masing. Ingat: **tidak ada pendaftaran mandiri**, semua akun dibuat manual oleh Admin. **Penting:** untuk setiap user dengan level **Junior Leader** atau **Leader**, wajib isi field **"Atasan Langsung"** (pilih dari dropdown) — kalau tidak diisi, user tersebut akan diblokir mengajukan ERF/GA sampai Admin melengkapi atasannya (karena sistem butuh tahu siapa yang harus approve requestnya).
- **Jabatan & TTF** — daftarkan semua nama jabatan yang biasa direquest di ERF, siapa PIC HR-nya, dan target hari kerja fulfillment masing-masing (ganti data contoh yang sudah ada).
- **Data Karyawan** — daftarkan karyawan aktif (dipakai di dropdown "Karyawan yang Diganti" saat ERF jenis Pengganti).
- **Kalender Kerja** — tambahkan tanggal libur nasional & cuti bersama tahun berjalan (data contoh yang ada baru 1 baris, harus dilengkapi supaya perhitungan estimasi tanggal fulfillment akurat). Defaultnya Senin–Jumat dianggap hari kerja, Sabtu–Minggu libur.

### 7a. Isi Master Data Sekaligus Banyak (Import CSV)

Keempat halaman Master Data di atas sekarang punya tombol **"↑ Import CSV"** di pojok kanan atas, jadi tidak perlu input satu per satu:

1. Klik **Import CSV** → klik **Download Template CSV**. File template berisi baris judul kolom + 2–3 baris contoh.
2. Buka template itu di Excel / Google Sheets, **hapus baris contoh**, isi data Anda. Jangan ubah nama/urutan kolom di baris pertama.
3. Simpan / download lagi sebagai **CSV** (di Excel: *Save As → CSV UTF-8*; di Google Sheets: *File → Download → Comma-separated values*).
4. Kembali ke aplikasi, pilih file itu di kolom upload, klik **Proses Import**.
5. Sistem menampilkan berapa baris berhasil, plus daftar baris yang dilewati beserta alasannya (mis. email tidak valid). Perbaiki baris itu di file, lalu import ulang — baris yang sudah masuk tidak akan dobel (dicocokkan: Karyawan by email/nama, Jabatan by nama jabatan, Kalender by tanggal, User by email).

Catatan per menu:
- **Manajemen User** — kolom `password` boleh dikosongkan untuk user baru (otomatis jadi `password`, minta user ganti setelah login). Kolom `atasan_email` diisi email atasan langsung; kalau atasannya ada di file yang sama, **jalankan import 2×** (putaran pertama membuat semua user, putaran kedua mengaitkan atasannya).
- **Jabatan & TTF** — kolom `pic_hr_email` diisi email user yang rolenya Tim HR (boleh kosong).
- **Kalender Kerja** — `tanggal` format `YYYY-MM-DD`, `tipe` diisi `libur` atau `kerja`.
- **Data Karyawan** — `status_aktif` diisi `aktif` / `nonaktif`.

---

## 8. Kalau Ada Error / Bingung

Beberapa pesan error umum & solusinya:

- **"could not find driver"** → biasanya ekstensi PHP SQLite belum aktif. Kalau pakai Laragon, cek di menu Laragon → PHP → php.ini, pastikan baris `extension=pdo_sqlite` & `extension=sqlite3` tidak diberi tanda `;` di depannya.
- **Halaman putih / error 500** → jalankan `php artisan config:clear` lalu coba refresh lagi.
- **Tampilan berantakan/CSS tidak muncul** → jalankan `npm run build` lalu refresh browser (tekan Ctrl+Shift+R / Cmd+Shift+R untuk hard refresh).
- **"no such table: sessions" / "no such table: users" (error 500 pas buka halaman apa saja)** → artinya file `database/database.sqlite` di folder Anda kosong/belum ada isi tabel. Ini paling sering kejadian setelah Anda **timpa/replace semua file** dengan paket update dari saya — mulai paket update berikutnya, saya tidak lagi menyertakan file `database.sqlite` di dalam zip (supaya tidak menimpa data Anda), tapi kalau ini sudah terlanjur terjadi, tinggal jalankan ulang: `php artisan migrate:fresh --seed`. Catatan: ini akan reset data ke data contoh lagi — wajar & aman selama masih tahap belajar/testing.
- Error lain → **screenshot pesan errornya, kirim ke saya (Claude)**, nanti saya bantu telusuri.

> **Catatan teknis dari saya:** Kode ini saya susun manual baris demi baris karena lingkungan kerja saya saat ini tidak bisa mengakses Packagist (server tempat Composer download paket PHP), jadi saya belum bisa menjalankan `composer install` & test langsung dari sisi saya. Saya sudah cek ketat: sintaks semua file PHP (`php -l`), proses build tampilan (Tailwind/Vite berhasil), dan saya baca ulang alur logika tiap fitur. Tapi karena belum pernah benar-benar dijalankan end-to-end, ada kemungkinan kecil ada typo/bug yang lolos. **Kalau nemu error saat menjalankan, kirim saja pesan errornya ke saya — saya bantu perbaiki cepat.**

---

## 9. Struktur Folder (buat yang penasaran)

```
app/Models/          → definisi tabel database (User, ErfRequest, GaRequest, dst)
app/Livewire/         → "otak" tiap halaman interaktif (form, list, detail)
app/Services/         → logika perhitungan hari kerja & pembuatan nomor ID otomatis
database/migrations/  → cetak biru struktur tabel database
database/seeders/     → data awal/contoh
resources/views/       → tampilan halaman (Blade + Livewire)
routes/web.php         → daftar semua alamat URL aplikasi
```

---

## 10. Rencana Lanjutan (belum dikerjakan di versi ini)

Sesuai arahan Anda, yang dikerjakan dulu adalah **Request ERF** dan **Request GA**. Menu berikut sudah disiapkan di sidebar tapi masih halaman "Segera Hadir":

- **Request Tech** — nanti diintegrasikan menyusul.
- **Request Creative Design** — nanti diintegrasikan menyusul (saat ini masih pakai alur ClickUp).
- **Business Trip** — masih tahap perencanaan.

Beberapa asumsi yang saya ambil saat membangun (boleh diubah, tinggal bilang ke saya):

- **ERF**: hanya Leader & Manager yang bisa mengajukan (sesuai definisi awal "digunakan oleh leader/manajer"). Approval atasan wajib untuk Leader, tidak wajib untuk Manager.
- **GA**: Junior Leader, Leader, & Manager bisa mengajukan (Staff tidak bisa). Approval atasan wajib untuk Junior Leader & Leader, tidak wajib untuk Manager.
- **Approval atasan** (update terbaru): sekarang **bukan lagi lampiran file**, tapi alur approve/reject langsung di aplikasi. Kalau atasan klik **Kembalikan untuk Revisi**, request tidak langsung jadi "Ditolak" — tapi dikembalikan ke pemohon untuk diedit & diajukan ulang, dan pemohon juga bisa menghapus/membatalkan request tersebut lewat menu yang sama. Selama menunggu/direvisi, HR/GA belum bisa memproses request tersebut. Menu **Approval Saya** (di sidebar, ada badge angka) menampilkan semua request yang menunggu approval dari akun yang sedang login.
- **Zona waktu**: aplikasi sekarang diset ke **Asia/Jakarta (WIB)**, supaya tanggal-tanggal otomatis (mis. Tanggal Mencari Kandidat) sesuai tanggal hari ini di Indonesia.
- **Aturan jam 16:00 WIB**: kalau ERF diajukan jam 16:00 WIB atau lebih malam, sistem menganggapnya diajukan **besok** untuk keperluan perhitungan H+1/estimasi tanggal — sesuai arahan Anda.
- **Pembatasan akses request (data sensitif)**: di menu **Request ERF**/**Request GA**, **Dashboard**, dan halaman detail (termasuk kalau URL-nya dibuka langsung), setiap user hanya bisa melihat request yang **ia buat sendiri** atau yang **ia jadi atasan langsungnya** (untuk approval/pemantauan). Pengecualian: **Tim HR** melihat ERF dan **Tim GA** melihat request GA yang sudah disetujui atasan (atau tidak perlu approval), dan **Super Admin** bisa melihat semuanya. Pejabat yang perlu memantau seluruh request (mis. **Manager HRBP**, karena semua request bermuara ke divisi HRBP) bisa diberi centang **"Bisa melihat semua request ERF & GA"** di Master Data → Manajemen User; user ini melihat ERF dan GA semuanya, terlepas dari pemisahan Tim HR/GA di bawah. **Tim HR dan Tim GA saling terpisah**: Tim HR tidak punya akses ke modul Request GA (menu, dashboard, list, detail), dan Tim GA tidak punya akses ke modul Request ERF; hanya Super Admin yang bisa melihat keduanya. Aturannya dipusatkan di model (`ErfRequest`/`GaRequest::scopeVisibleTo` dan `isVisibleTo`) dan dites di `tests/Feature/RequestVisibilityTest.php`.
- **PIC GA** otomatis mengambil user pertama yang rolenya "Tim GA" (saat ini akun contoh "Gerry Anda").
- **Perhitungan hari kerja**: default Senin–Jumat kerja, Sabtu–Minggu libur, bisa dikustomisasi lewat menu Kalender Kerja (misal ada Sabtu masuk, atau hari libur nasional).

Kalau ada yang mau diubah dari asumsi di atas, atau siap lanjut ke modul Tech/Creative Design/Business Trip, atau mau bantuan deploy ke server kantor — tinggal chat saya lagi ya, Patur!
