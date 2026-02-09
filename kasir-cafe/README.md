<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Kasir Cafe

![alt text](<Screenshot 2026-02-09 at 12.40.18.png>)

Sistem kasir & stok bahan untuk cafe/resto dengan fitur:
- POS kasir + struk thermal 80mm
- Stok bahan dengan FEFO + batch expiry
- Resep / BOM untuk pemotongan stok otomatis
- Laporan penjualan, COGS, laba, pajak, diskon
- Multi user (admin/manager/kasir)
- Lisensi aplikasi (trial 30 hari + license key tervalidasi)

## Alur Kerja Sistem
1. **Input Bahan & Stok Awal**
   - Admin input bahan (item), satuan, stok awal melalui receiving/stock opname.
2. **Input Menu & Resep**
   - Admin buat produk/menu dan set resep per porsi (gram/ml/dll).
3. **Kasir Melayani Pesanan**
   - Kasir input pesanan di POS → bayar → stok bahan otomatis berkurang berdasarkan resep.
4. **Monitoring & Laporan**
   - Owner/admin cek stok, opname, penjualan, COGS, laba, pajak.

## Cara Install (Local)
1. Copy `.env`:
   ```bash
   cp .env.example .env
   ```
2. Set database & key:
   ```bash
   php artisan key:generate
   ```
3. Install dependency:
   ```bash
   composer install
   npm install && npm run build
   ```
4. Migrasi & storage:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```
5. Jalankan:
   ```bash
   php artisan serve
   ```

## Deployment Otomatis (Server)
1. Pastikan `.env` sudah benar (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, `LICENSE_MASTER_KEY`).
2. Jalankan script:
   ```bash
   bash scripts/deploy.sh
   ```

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

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
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
