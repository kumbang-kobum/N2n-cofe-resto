# Panduan Penggunaan Sistem n2N Kasir Cafe

Versi: 1.0

## 1. Login dan Role
- Admin: akses penuh (master data, operasional, laporan, pengaturan).
- Manager: akses kontrol operasional dan laporan sesuai hak akses.
- Kasir: akses POS, bill terbuka, dan laporan kasir.

## 2. Setup Awal
1. Buat Satuan (unit): kg, g, liter, ml, pcs.
2. Pastikan konversi unit aktif:
   - kg -> g = 1000
   - g -> kg = 0.001
   - liter -> ml = 1000
   - ml -> liter = 0.001
3. Buat bahan di Master Bahan dengan unit dasar yang benar.
4. Isi minimal stok per bahan agar warning stok menipis muncul.

## 3. Input Master Bahan (Benar)
Prinsip:
- Unit dasar item = unit utama stok disimpan.
- Qty receiving = jumlah fisik barang datang.
- Biaya bisa diisi sebagai harga per unit atau total harga.

Contoh unit dasar yang disarankan:
- Kopi, gula, beras, ayam: kg
- Susu: liter
- Cup: pcs

## 4. Penerimaan Stok (Receiving)
Per baris receiving, isi:
- Item
- Qty
- Unit input
- Mode biaya: Harga per unit / Total harga
- Biaya
- Expired at

Contoh input benar:
- Kopi Arabica: Qty 10, Unit kg, Mode Total harga, Biaya 1000000
  (hasil biaya per unit = 100000/kg)
- Gula: Qty 10, Unit kg, Mode Total harga, Biaya 200000
  (hasil biaya per unit = 20000/kg)
- Susu: Qty 10, Unit liter, Mode Total harga, Biaya 90000
  (hasil biaya per unit = 9000/liter)
- Cup: Qty 50, Unit pcs, Mode Total harga, Biaya 7000
  (hasil biaya per unit = 140/pcs)

Kesalahan umum:
- Mengisi total belanja pada mode Harga per unit.
- Salah unit (contoh beli 10 liter tapi input 10 ml).
Kedua kesalahan ini membuat COGS dan laba jadi tidak akurat.

## 5. Produk dan Resep (BOM)
1. Buat produk dan harga jual.
2. Buat resep per 1 porsi.
3. Gunakan unit resep sesuai pemakaian riil (g/ml/pcs).

Contoh resep Kopi Cappucino:
- Kopi 18 g
- Gula 15 g
- Susu 100 ml
- Cup 1 pcs

## 6. Alur POS Harian
1. Klik Transaksi Baru.
2. Isi nama tamu / nomor meja.
3. Tambah menu ke keranjang.
4. Isi diskon jika ada (dalam Rupiah).
5. Pilih metode bayar: CASH / QRIS / DEBIT.
6. Isi uang dibayar (khusus cash) untuk hitung kembalian.
7. Klik Bayar.

Catatan:
- Simpan & Tahan dipakai untuk bill yang dibayar belakangan.
- Stok berkurang saat transaksi dibayar.

## 7. Open Bills
- Open Bills menampung transaksi yang belum dibayar.
- Bisa dicari berdasarkan nama tamu/meja/ID.
- Bisa dibuka kembali untuk lanjut bayar.

## 8. Refund / Void
- Refund parsial diperbolehkan sesuai qty.
- Stok akan dikembalikan sesuai item resep yang direfund.
- Semua aksi refund tercatat di laporan.

## 9. Laporan
- Laporan Penjualan: omzet, diskon, pajak, COGS, laba, metode bayar.
- Laporan Keuangan: ringkasan harian/bulanan, pengeluaran kas, payroll.
- Export Excel tersedia untuk analisis lanjutan.

## 10. Cara Membaca Angka Laporan
- Subtotal = total sebelum diskon dan pajak.
- Pajak = persentase dari (Subtotal - Diskon) saat pajak aktif.
- Omzet = (Subtotal - Diskon) + Pajak.
- COGS = biaya bahan yang terpakai dari batch.
- Laba Kotor = (Subtotal - Diskon) - COGS.

## 11. Troubleshooting Cepat
A. Tampilan berantakan saat akses via IP
- Set APP_URL dan ASSET_URL ke URL IP yang dipakai client.
- Jalankan: php artisan optimize:clear lalu config:cache.

B. Login error 405/500 di XAMPP macOS
- Pastikan akses via http:// (bukan https jika SSL belum diset).
- Pastikan permission writable untuk storage dan bootstrap/cache.

C. Laba minus padahal penjualan ada
- Cek mode biaya dan unit saat receiving.
- Cek unit dasar item dan konversi unit.

## 12. Rekomendasi SOP Harian
- Pagi: cek stok menipis dan open bills.
- Siang: input receiving jika ada barang datang.
- Malam: cek laporan harian dan tutup kas.
- Mingguan: stock opname sebagian (partial) untuk item kritis.

