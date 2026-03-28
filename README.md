<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Video Instalasi App Resto

https://youtu.be/Jm6dMf-rQG8

## Panduan Penggunaan (PDF)
- Panduan lengkap: `kasir-cafe/docs/Panduan_Penggunaan_Sistem_n2N.pdf`
- Panduan kasir 1 halaman: `kasir-cafe/docs/Panduan_Kasir_1_Halaman.pdf`

## Kasir Cafe

<img src="kasir-cafe/n2Nlogo.png" width="140" alt="n2N" />

## Traktir Kopi BCA 8110400102 A/N Chandra Irawan** ☕🙏 
## ☕ Donasi
Dukung pengembangan aplikasi ini melalui Saweria:  

| Scan QR Code | Klik Link |
|--------------|-----------|
| <img src="screenshot/qrsaweria.png" width="180" /> | [👉 Saweria.co](https://saweria.co/KumbangKobum) | 
 

## Screenshot Aplikasi
<table>
  <tr>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.51.05.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.51.21.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.51.33.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.51.42.png" width="180" /></td>
  </tr>
  <tr>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.51.50.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.01.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.07.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.13.png" width="180" /></td>
  </tr>
  <tr>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.19.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.25.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.31.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.37.png" width="180" /></td>
  </tr>
  <tr>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.47.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.52.55.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.53.00.png" width="180" /></td>
    <td><img src="screenshot/Screenshot%202026-02-10%20at%2011.53.05.png" width="180" /></td>
  </tr>
</table>


## Screenshot Per Menu
Gunakan bagian ini sebagai panduan cepat onboarding tim untuk mengenali area utama aplikasi.

| Menu / Area | Preview | Keterangan Singkat |
|---|---|---|
| Dashboard | <img src="screenshot/Screenshot%202026-02-10%20at%2011.51.05.png" width="220" /> | Ringkasan omzet, COGS, transaksi, dan menu terlaris. |
| Produk / Menu | <img src="screenshot/Screenshot%202026-02-10%20at%2011.51.21.png" width="220" /> | Kelola daftar produk, harga jual, status aktif, dan kategori menu. |
| Stok Bahan | <img src="screenshot/Screenshot%202026-02-10%20at%2011.51.33.png" width="220" /> | Master bahan, stok aktif, satuan dasar, dan nilai stok. |
| Penerimaan Stok | <img src="screenshot/Screenshot%202026-02-10%20at%2011.51.42.png" width="220" /> | Input pembelian batch baru agar cost dan expired tercatat rapi. |
| Resep / BOM | <img src="screenshot/Screenshot%202026-02-10%20at%2011.51.50.png" width="220" /> | Menentukan komposisi bahan per porsi untuk potong stok otomatis. |
| POS Kasir | <img src="screenshot/Screenshot%202026-02-10%20at%2011.52.01.png" width="220" /> | Layar transaksi cepat untuk dine-in, takeaway, open bill, dan pembayaran. |
| Laporan Keuangan | <img src="screenshot/Screenshot%202026-02-10%20at%2011.52.07.png" width="220" /> | Rekap omzet, pajak, refund, HPP, laba, pengeluaran, dan payroll. |
| Payroll & Absensi | <img src="screenshot/Screenshot%202026-02-10%20at%2011.52.13.png" width="220" /> | Penggajian petugas, rekap absensi, potongan telat, dan slip gaji. |

Sistem kasir & stok bahan untuk cafe/resto dengan fitur:
- POS kasir + struk thermal 80mm
- Stok bahan dengan FEFO + batch expiry
- Resep / BOM untuk pemotongan stok otomatis
- Laporan penjualan, COGS, laba, pajak, diskon
- Refund/void parsial + stok kembali otomatis
- Multi user (admin/manager/kasir)
- Lisensi aplikasi (trial 30 hari + license key tervalidasi)
- Audit log perubahan harga & stok
- Inventaris resto + laporan kerusakan/pemusnahan

Cara membaca ringkasan di atas:

Subtotal = total sebelum diskon & pajak
Diskon = potongan uang
Pajak = 10% dari (Subtotal − Diskon)
Pajak bisa di aktifkan dan di nonaktifkan
Omzet = (Subtotal − Diskon) + Pajak
Refund = total refund pada periode
COGS (HPP) = harga pokok bahan
Laba Kotor = (Subtotal − Diskon) − COGS
menu payroll petugas
pengeluaran harian
→ Jadi bisa negatif kalau COGS lebih besar dari penjualan

Cara yang benar untuk kenaikan harga bahan baku:

Saat ada pembelian baru (harga 135.000/kg), input lewat Penerimaan Barang.
Sistem akan membuat batch baru dengan biaya 135.000.
COGS otomatis pakai harga sesuai batch yang dipakai (FEFO/FIFO), jadi:
Stok lama tetap pakai harga 130.000
Stok baru pakai harga 135.000
Laba jadi akurat sesuai “harga asli saat dibeli”.
Jadi bukan mengikuti harga terbaru untuk semua stok. Yang terbaik adalah pisah per batch 

Kalau kamu hanya “mengganti harga” tanpa ada pembelian baru, itu akan mengubah cost untuk stok lama dan bikin COGS jadi tidak akurat. Lebih baik selalu lewat penerimaan barang.

## FEFO vs FIFO (Pemakaian Stok)
**FEFO (First Expired, First Out)**:
Stok yang **paling cepat kadaluarsa** dipakai dulu. Cocok untuk bahan dengan expiry date (susu, daging, sayur).

**FIFO (First In, First Out)**:
Stok yang **paling lama masuk** dipakai dulu. Cocok bila tidak ada expiry atau semua batch punya umur simpan mirip.

**Di sistem ini**:
- Jika batch punya `expired_at`, maka dipakai **FEFO** (yang kadaluarsa paling cepat).
- Jika `expired_at` kosong, maka dipakai **FIFO** (yang masuk lebih dulu).

Dengan ini, COGS selalu mengikuti **biaya per batch yang benar**, bukan harga terbaru.

## Contoh Input Benar (Agar COGS Akurat)
Prinsip:
- `Qty` di penerimaan = jumlah barang datang.
- `Unit` = satuan input saat beli.
- `Mode biaya`:
  - `Harga per unit` = isi harga 1 unit.
  - `Total harga` = isi total belanja baris itu.

Contoh penerimaan yang benar:

| Item | Qty | Unit | Mode Biaya | Nilai Biaya Diisi | Hasil Biaya/Unit |
|---|---:|---|---|---:|---:|
| Kopi Arabica | 10 | kg | Harga per unit | 100000 | 100000/kg |
| Kopi Arabica | 10 | kg | Total harga | 1000000 | 100000/kg |
| Gula | 10 | kg | Total harga | 200000 | 20000/kg |
| Susu | 10 | liter | Total harga | 90000 | 9000/liter |
| Set Cup Minuman | 50 | pcs | Total harga | 7000 | 140/pcs |

Contoh resep yang benar:
- Kopi Cappucino:
  - Kopi `18 g`
  - Gula `15 g`
  - Susu `100 ml`
  - Cup `1 pcs`

Catatan penting:
- Jangan isi total belanja pada mode `Harga per unit`.
- Jika item base `liter`, input pembelian susu pakai `liter` (bukan `ml`).
- Salah input unit/biaya akan membuat COGS membengkak dan laba terlihat minus.

## Alur Kerja Sistem
1. **Input Bahan & Stok Awal**
   - Admin input bahan (item), satuan, stok awal melalui receiving/stock opname.
2. **Input Menu & Resep**
   - Admin buat produk/menu dan set resep per porsi (gram/ml/dll).
3. **Kasir Melayani Pesanan**
   - Kasir input pesanan di POS → bayar → stok bahan otomatis berkurang berdasarkan resep.
4. **Monitoring & Laporan**
   - Owner/admin cek stok, opname, penjualan, COGS, laba, pajak.
5. **Inventaris Resto**
   - Catat aset, kondisi, lokasi/kategori, serta laporan kerusakan/pemusnahan.

## Install Cepat dari GitHub
1. Clone project:
   ```bash
   git clone https://github.com/kumbang-kobum/N2n-cofe-resto.git
   cd N2n-cofe-resto/kasir-cafe
   ```
2. Copy file environment:
   ```bash
   cp .env.example .env
   ```
3. Isi koneksi database di `.env`, lalu generate key:
   ```bash
   php artisan key:generate
   ```

## Cara Install (Development)
1. Install dependency backend dan frontend:
   ```bash
   composer install
   npm install
   npm run build
   ```
2. Inisialisasi aplikasi:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
3. Jalankan aplikasi:
   ```bash
   php artisan serve
   ```
4. Jika tampilan atau route belum ikut update:
   ```bash
   php artisan optimize:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```
5. Jika ada masalah permission upload/cache di Linux:
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

## Cara Install (Server / Production)
1. Upload kode ke server atau clone langsung dari repo.
2. Masuk ke folder aplikasi:
   ```bash
   cd /path-ke-project/N2n-cofe-resto/kasir-cafe
   ```
3. Siapkan `.env` dari `.env.example`, lalu isi minimal:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=http://domain-atau-ip-kamu`
   - `DB_*` sesuai server
   - `LICENSE_MASTER_KEY=...`
4. Install dependency:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```
5. Inisialisasi Laravel:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   ```
6. Bangun cache production:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
7. Pastikan permission writable:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
8. Jika memakai queue worker:
   ```bash
   php artisan queue:restart
   ```

## Cara Update Mengikuti Repo
Gunakan alur ini setiap kali server production mengikuti update dari GitHub.

1. Masuk ke folder repo utama:
   ```bash
   cd /www/wwwroot/caferesto/N2n-cofe-resto
   ```
   Sesuaikan dengan lokasi repo di server kamu.
2. Ambil update dari GitHub dan samakan dengan branch utama:
   ```bash
   git fetch origin
   git reset --hard origin/main
   ```
3. Jika server punya file lokal yang tidak perlu di-commit, abaikan di exclude:
   ```bash
   echo "kasir-cafe/public/.user.ini" >> .git/info/exclude
   git status
   ```
4. Masuk ke folder aplikasi Laravel:
   ```bash
   cd kasir-cafe
   ```
5. Install dependency backend:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
6. Install dependency frontend lalu build asset:
   ```bash
   npm install
   npm run build
   ```
   Langkah ini wajib jika ada perubahan Blade, Tailwind/CSS, landing page, login, dark mode, TV Informasi, atau halaman dashboard.
7. Jalankan migration aman:
   ```bash
   php artisan migrate --force
   ```
8. Bersihkan lalu bangun ulang cache Laravel:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
9. Jika memakai queue:
   ```bash
   php artisan queue:restart
   ```
10. Pastikan permission storage/cache tetap benar:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
11. Jika tampilan belum berubah:
   - hard refresh browser
   - coba mode incognito
   - pastikan `public/build` sudah ter-update
   - restart PHP/Apache/Nginx bila perlu

### Ringkasan Update Production
```bash
git fetch origin
git reset --hard origin/main
cd kasir-cafe
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
chmod -R 775 storage bootstrap/cache
```

Catatan penting:
- Aman menggunakan `php artisan migrate --force` di production.
- Jangan gunakan `php artisan migrate:fresh` di production.
- Jika perubahan UI tidak muncul, penyebab paling sering adalah lupa `npm run build`.

## Deployment Otomatis (Server)
1. Pastikan `.env` sudah benar (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, `LICENSE_MASTER_KEY`).
2. Jalankan script:
   ```bash
   bash scripts/deploy.sh
   ```
3. Setelah deploy, tetap cek:
   ```bash
   php artisan migrate --force
   php artisan route:cache
   php artisan view:cache
   ```

## UAT Absensi + Payroll
- Checklist uji coba operasional tersedia di:
  - `docs/uat_absensi_payroll_checklist.md`
- Gunakan checklist ini sebelum fitur absensi dan payroll dipakai penuh di lapangan.

## Absensi & Payroll

### Modul yang Sudah Tersedia
- `Master Shift`: jam masuk, jam pulang, toleransi telat, aturan dasar lembur.
- `Rule Keterlambatan`: nominal potongan telat bisa diatur manual sesuai kebijakan perusahaan.
- `Roster Bulanan`: jadwal kerja model kalender bulanan, lengkap dengan bulk action dan copy roster minggu/bulan sebelumnya.
- `Kiosk Absensi`: absensi tanpa login untuk karyawan dari tablet atau layar khusus.
- `Selfie & Verifikasi Ringan`: selfie masuk/pulang, skor verifikasi, dan review manual oleh admin/manager.
- `Izin / Cuti / Sakit`: tanggal approved otomatis terkunci di roster dan ikut masuk ke rekap payroll.
- `Payroll`: preview potongan telat, potongan makan, rekap absensi bulanan, dan slip gaji.

### Alur Pakai yang Disarankan
1. Isi `Master Shift` dan `Rule Keterlambatan` terlebih dahulu.
2. Set `Shift Default` dan, jika dipakai, `Foto Referensi Wajah` di `Master Karyawan`.
3. Susun jadwal di `Roster Bulanan Shift`.
4. Karyawan melakukan `Clock In` dan `Clock Out` dari `Absensi Karyawan` / kiosk.
5. Admin atau manager review selfie yang statusnya `Perlu Review`.
6. Cek `Rekap Absensi Bulanan` sebelum membuat payroll.
7. Buat payroll setelah preview potongan telat, potongan makan, dan rekap hadir sudah sesuai.

### Menu Penting untuk Tim
- `Operasional > Absensi Karyawan`: daftar absensi harian, selfie, dan status verifikasi.
- `Operasional > Roster Bulanan Shift`: menyusun jadwal kerja dan hari libur.
- `Operasional > Review Wajah Absensi`: review selfie yang masih perlu verifikasi manual.
- `Operasional > Izin / Cuti / Sakit`: pengajuan dan approval leave.
- `Laporan > Rekap Absensi Bulanan`: ringkasan per karyawan dan export Excel.
- `Payroll / Penggajian Petugas`: preview potongan dan pembuatan slip gaji.

### Checklist Operasional Sebelum Payroll
- Pastikan semua absensi masuk/pulang pada periode tersebut sudah lengkap.
- Pastikan status `Perlu Review` sudah diselesaikan oleh admin/manager.
- Pastikan izin, cuti, dan sakit yang valid sudah `APPROVED`.
- Pastikan potongan telat dan potongan makan pada preview payroll sudah sesuai kebijakan.
- Export rekap absensi bulanan jika perlu arsip atau audit internal.

### Dokumen Pendukung
- Checklist UAT operasional: `docs/uat_absensi_payroll_checklist.md`
- Panduan penggunaan lengkap: `docs/Panduan_Penggunaan_Sistem_n2N.pdf`
- Panduan kasir singkat: `docs/Panduan_Kasir_1_Halaman.pdf`

### Troubleshooting Absensi
- `Kamera tidak muncul di kiosk`:
  - pastikan browser diberi izin akses kamera
  - gunakan `http`/`https` yang sama secara konsisten
  - coba refresh halaman atau buka ulang kiosk di tab baru
- `Selfie gagal tersimpan`:
  - cek permission folder `storage` dan jalankan `php artisan storage:link`
  - pastikan ukuran upload PHP tidak terlalu kecil jika foto atau file pendukung gagal masuk
- `Status masih Perlu Review`:
  - cek apakah foto referensi wajah di master karyawan sudah ada dan jelas
  - review manual dari menu `Review Wajah Absensi` jika skor verifikasi rendah
- `Potongan telat tidak muncul di payroll`:
  - pastikan absensi ada di periode yang sama dengan payroll
  - cek rule keterlambatan, status absensi, dan preview payroll sebelum simpan
- `Tanggal roster tidak bisa diubah`:
  - kemungkinan ada `cuti`, `sakit`, atau `izin` yang sudah `APPROVED`
  - cek menu leave agar tidak terjadi bentrok jadwal
- `Export Excel kosong atau tidak sesuai filter`:
  - pastikan periode dan karyawan sudah dipilih sebelum klik export
  - refresh halaman jika filter baru saja diubah


## Instalasi macOS (XAMPP) - Lengkap
1. Install XAMPP for macOS, lalu start `Apache` dan `MySQL`.
2. Taruh project di:
   - `/Applications/XAMPP/xamppfiles/htdocs/N2n-cofe-resto/kasir-cafe`
3. Masuk folder project:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/N2n-cofe-resto/kasir-cafe
   ```
4. Buat database (mis. `kasir_cafe`) via `http://localhost/phpmyadmin`.
5. Copy env:
   ```bash
   cp .env.example .env
   ```
6. Set `.env` minimum:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=http://localhost/N2n-cofe-resto/kasir-cafe/public`
   - `ASSET_URL=http://localhost/N2n-cofe-resto/kasir-cafe/public`
   - `SESSION_SECURE_COOKIE=false`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=kasir_cafe`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=` (kosong default XAMPP)
7. Install dependency:
   ```bash
   composer install
   npm install
   npm run build
   ```
8. Inisialisasi Laravel:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```
9. Set permission agar Apache XAMPP (user `daemon`) bisa menulis cache/log:
   ```bash
   sudo chown -R daemon:daemon storage bootstrap/cache
   sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
   sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
   ```
10. Bersihkan dan cache ulang:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
11. Restart Apache dari XAMPP Control Panel.
12. Akses aplikasi:
   - `http://localhost/N2n-cofe-resto/kasir-cafe/public`

Troubleshooting macOS XAMPP:
- Error `500 Internal Server Error` dan `storage/logs/laravel.log` kosong:
  - Jalankan ulang langkah permission (langkah 9).
- Error `405 Method Not Allowed` saat login:
  - Pastikan login dari URL:
    - `http://localhost/N2n-cofe-resto/kasir-cafe/public/login`
  - Cek route:
    ```bash
    php artisan route:list | grep -E "login|logout"
    ```
- Jika route belum terbaca, clear cache:
  ```bash
  php artisan optimize:clear
  ```
- Jika file `public/.htaccess` hilang, buat isi standar Laravel rewrite.
- Jangan pakai `https://localhost/...` kecuali SSL lokal memang sudah dikonfigurasi benar.

## Instalasi Windows (XAMPP, 1 Klik)
1. Install XAMPP di `C:\xampp`, lalu start `Apache` dan `MySQL`.
2. Clone/copy project ke:
   - `C:\n2n-kasir\N2n-cofe-resto`
3. Masuk ke folder aplikasi Laravel:
   ```bat
   cd C:\n2n-kasir\N2n-cofe-resto\kasir-cafe
   ```
4. Install dependency di Windows (wajib, jangan pakai `vendor/node_modules` dari OS lain):
   ```bat
   composer install
   npm install
   npm run build
   ```
5. Inisialisasi database otomatis:
   ```bat
   scripts\windows\init_db.bat
   ```
6. Jalankan aplikasi:
   ```bat
   scripts\windows\start_pos.bat
   ```
   Aplikasi akan terbuka di `http://localhost/`.

Catatan anti-gagal:
- Jalankan semua command dari folder `kasir-cafe`.
- Default MySQL root password kosong. Jika root pakai password, isi `MYSQL_ROOT_PASSWORD` di `scripts\windows\init_db.bat`.
- Jika tampilan tidak update, jalankan:
  ```bat
  php artisan optimize:clear
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

Stop server:
```bat
scripts\windows\stop_pos.bat
```

## Installer Windows (.exe, Inno Setup)
1. Install Inno Setup di PC build.
2. Buka file:
   `kasir-cafe\installer\n2n_kasir_xampp.iss`
3. Jika ingin bundling XAMPP installer:
   - Simpan file installer XAMPP di:
     `kasir-cafe\installer\extras\xampp-installer.exe`
4. Klik **Compile** → hasilnya `n2n-kasir-setup.exe`.
5. Jalankan installer di PC client.

## Checklist Go-Live (Produksi)
1. **Env & keamanan**
   - `APP_ENV=production`, `APP_DEBUG=false`
   - `.env` tidak ikut git
   - `APP_KEY` unik per client
2. **Database**
   - Backup cron aktif (8 jam sekali, simpan 20 file)
   - Migrasi sudah dijalankan di server
3. **Cache**
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
4. **Operasional POS**
   - Uji alur POS end-to-end (input bahan → resep → jual → laporan)
   - Uji printer thermal 80mm di lapangan
5. **Monitoring**
   - Log error aktif (`LOG_CHANNEL=stack`/`daily`)
   - Cek disk usage secara berkala
6. **Lisensi**
   - Aktivasi license key untuk client sudah dilakukan

## Lisensi
1. Set `LICENSE_MASTER_KEY` di `.env`.
2. Login admin → **Pengaturan Resto** → copy **Installation Code**.
3. Generate license:
   ```bash
   php scripts/generate_license.php INSTALLATION_CODE MASTER_KEY
   ```
4. Input hasilnya ke **License Key** di Pengaturan Resto.

## Smoke Test POS
```bash
bash scripts/smoke_pos.sh
```

## Backup & Restore DB
Backup:
```bash
bash scripts/db_backup.sh
```

Restore:
```bash
bash scripts/db_restore.sh backups/backup_YYYYMMDD_HHMMSS.sql
```

## Inventaris & Kerusakan
Menu:
- **Inventaris**
- **Master Kategori**
- **Master Lokasi**
- **Kerusakan/Pemusnahan**

Isi master kategori & lokasi terlebih dahulu agar dropdown inventaris tersedia.

## Auto Backup 8 Jam
Lihat panduan cron:
`docs/cron_backup.md`

## cara update mengikuti repo
Gunakan urutan ini setiap kali server production mengikuti update dari GitHub.

1. Masuk ke folder repo:
   ```bash
   cd /www/wwwroot/caferesto/N2n-cofe-resto
   ```
   Sesuaikan dengan folder server kamu.

2. Ambil update dari GitHub dan samakan persis:
   ```bash
   git fetch origin
   git reset --hard origin/main
   ```

3. Abaikan file lokal server seperti `.user.ini` jika perlu:
   ```bash
   echo "kasir-cafe/public/.user.ini" >> .git/info/exclude
   git status
   ```

4. Masuk ke folder aplikasi Laravel:
   ```bash
   cd kasir-cafe
   ```

5. Install dependency backend:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

6. Install dependency frontend dan build asset:
   ```bash
   npm install
   npm run build
   ```
   Ini wajib jika ada perubahan:
   - tampilan Blade
   - Tailwind/CSS
   - dark mode
   - landing page
   - login page
   - TV Informasi

7. Jalankan migration aman:
   ```bash
   php artisan migrate --force
   ```

8. Bersihkan lalu bangun ulang cache Laravel:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

9. Jika pakai queue:
   ```bash
   php artisan queue:restart
   ```

10. Pastikan permission storage/cache benar:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

11. Jika tampilan masih tidak berubah:
   - hard refresh browser
   - coba incognito
   - restart PHP dari aaPanel

12. Ringkasan singkat deploy production:
   ```bash
   git fetch origin
   git reset --hard origin/main
   cd kasir-cafe
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   php artisan migrate --force
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan queue:restart
   chmod -R 775 storage bootstrap/cache
   ```

Catatan penting:
- Aman gunakan `php artisan migrate --force`
- Jangan gunakan `php artisan migrate:fresh` di production
- Jika perubahan UI tidak muncul, penyebab paling sering adalah lupa `npm run build`

## About Laravel
Laravel adalah framework PHP untuk membangun aplikasi web. Dokumentasi: https://laravel.com/docs
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
