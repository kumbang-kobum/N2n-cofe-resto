# Deploy Recommendations — Kasir Cafe (n2N)

Ringkasan singkat: panduan ini memberikan langkah cepat untuk men-deploy aplikasi Laravel ini pada server ringan dan opsi tuning untuk skala kecil hingga menengah.

## Quick checklist (minimal)
- Pastikan `APP_KEY` terisi: `php artisan key:generate`
- Jangan commit `.env`, `vendor/`, atau `node_modules` ke repo.
- Set `APP_ENV=production` dan `APP_DEBUG=false` pada `.env` produksi.
- Pastikan `storage/` dan `bootstrap/cache/` writable.

## Minimum recommended server (very small / single-server)
- OS: Linux (Debian/Ubuntu/CentOS) atau macOS for local test
- CPU: 1 vCPU
- RAM: 1.5–2 GB
- Disk: 5–10 GB
- PHP: 8.2 CLI/FPM (match `composer.json`)
- Node.js & npm (build step only)

## Production recommended (multi-kasir / moderate load)
- CPU: 2–4 vCPU
- RAM: 4–8 GB
- DB: MySQL 8 / MariaDB / PostgreSQL (avoid SQLite for concurrent writes)
- Cache/session: Redis (recommended) or Memcached
- Web: nginx + php-fpm + Opcache

## Quick deployment commands
Gunakan dari folder proyek root:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
npm install && npm run build
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika ingin setup sangat ringan (development atau single-kasir, tanpa worker):

```bash
# gunakan sqlite untuk cepat (opsional)
# set DB_CONNECTION=sqlite di .env
php artisan migrate --force
# gunakan sync queue dan file session jika tidak ingin database queue/session
# set QUEUE_CONNECTION=sync and SESSION_DRIVER=file di .env
```

## PHP / FPM tuning (simple)
- Aktifkan Opcache di PHP-FPM.
- `opcache.memory_consumption=128` dan `opcache.max_accelerated_files=10000` adalah titik awal yang baik.
- Atur `pm = dynamic`, `pm.max_children` sesuai memori (mis. 10–30 untuk 4GB RAM tergantung app).

## Database & concurrency
- Untuk 1–2 kasir, SQLite + file sessions bisa cukup, tetapi:
  - SQLite tidak cocok jika banyak penulisan simultan (race pada receipt/stock updates).
  - Untuk produksi gunakan MySQL/Postgres + InnoDB/pg default.
- Gunakan transaksi saat melakukan update stok/COGS untuk integritas.

## Queue, session, cache
- Untuk stabilitas: gunakan `QUEUE_CONNECTION=database` atau `redis` (pegawai worker terpisah).
- Session: `file` atau `database` untuk kecil; `redis` untuk performa.
- Cache: aktifkan cache driver (`redis`/`memcached`) untuk laporan dan query heavy.

## File permissions & security
- Pastikan `storage/` dan `bootstrap/cache/` writable oleh user web (www-data/nginx).
- Jangan simpan kunci lisensi atau kredensial di repo. Gunakan environment variables atau secret manager.

## Repo hygiene
- Hapus `vendor/` dan `node_modules/` dari repo (use `.gitignore`).
- Tambahkan or periksa `.env` di `.gitignore`.

## Backups & maintenance
- Backup DB harian (mysqldump/pg_dump) dan file `storage/app/public` jika menyimpan file upload.
- Pertimbangkan rotasi log dan space monitoring.

## Monitoring & scaling notes
- Mulai dengan single app server + managed DB.
- Jika concurrency tinggi: pisahkan DB, gunakan Redis untuk session/cache, dan worker pool terpisah untuk queue.

## Optional: Lightweight commands for testing locally

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

---
Jika mau, saya bisa: (1) membuat file systemd service untuk `php artisan queue:work`, (2) menambahkan contoh konfigurasi nginx, atau (3) menyiapkan skrip backup sederhana. Mau yang mana?
