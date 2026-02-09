# Deployment Checklist (Sederhana)

## 1. Konfigurasi Env
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` sesuai domain/IP publik
- `APP_KEY` sudah terisi
- `DB_*` sudah sesuai server produksi

## 2. Persiapan Server
- Web server (Nginx/Apache) sudah install
- PHP extensions: `pdo`, `mbstring`, `openssl`, `fileinfo`, `gd`
- Folder `storage/` dan `bootstrap/cache/` writable

## 3. Deploy Kode
1. Upload kode ke server
2. Jalankan:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## 4. Data Awal
- Buat user admin
- Atur **Pengaturan Resto** (logo, nama, alamat, telp)
- Atur **Unit Conversion**
- Input bahan & stok awal
- Input menu & resep

## 5. Verifikasi Cepat
- Login admin & kasir
- Buat transaksi → bayar → stok berkurang
- Cetak nota
- Laporan penjualan valid

## 6. Backup
- Siapkan cron backup harian:
  ```bash
  bash scripts/db_backup.sh
  ```
