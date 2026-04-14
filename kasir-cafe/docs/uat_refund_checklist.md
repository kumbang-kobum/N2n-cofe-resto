# UAT Refund Checklist

Dokumen ini dipakai untuk memastikan proses refund sudah benar pada:
- stok bahan
- laporan penjualan
- laporan keuangan
- ringkasan metode pembayaran (`CASH`, `QRIS`, `DEBIT`)

## Persiapan

Sebelum mulai, siapkan:
- 1 user admin
- 1 user kasir
- minimal 2 produk yang resepnya valid
- stok bahan cukup
- pajak aktif bila ingin menguji skenario pajak
- periode laporan diarahkan ke tanggal transaksi uji

Catat nilai awal berikut sebelum transaksi:
- total `CASH`
- total `QRIS`
- total `DEBIT`
- total `Refund`
- total `COGS (HPP)`
- total `Laba Kotor`
- stok item bahan yang dipakai oleh produk uji

## Skenario 1

### Refund Parsial Cash

Tujuan:
Memastikan refund parsial mengurangi kas masuk dan mengembalikan stok secara proporsional.

Langkah:
1. Login sebagai kasir.
2. Buat transaksi tunai dengan 1 produk qty `2`.
3. Bayar transaksi dan simpan nomor nota.
4. Buka `Laporan Penjualan`.
5. Pastikan transaksi muncul dengan metode `CASH`.
6. Lakukan refund qty `1` dari transaksi tersebut.
7. Refresh `Laporan Penjualan`.
8. Buka `Laporan Keuangan`.

Hasil yang diharapkan:
- nota tetap ada di laporan penjualan
- kolom `Refund` pada nota bertambah
- total `CASH` berkurang sebesar nilai refund efektif
- total `Refund` bertambah
- `COGS (HPP)` berkurang proporsional
- `Laba Kotor` menyesuaikan
- stok bahan bertambah kembali sesuai 1 porsi resep
- status transaksi tetap `PAID` jika baru refund sebagian

## Skenario 2

### Refund Penuh QRIS

Tujuan:
Memastikan refund penuh tidak menghilangkan nota dari laporan dan uang masuk QRIS turun penuh.

Langkah:
1. Login sebagai kasir.
2. Buat transaksi `QRIS` dengan 1 produk qty `1`.
3. Bayar transaksi dan catat nomor nota.
4. Pastikan transaksi muncul di `Laporan Penjualan`.
5. Lakukan refund penuh untuk seluruh qty transaksi.
6. Refresh `Laporan Penjualan`.
7. Buka `Laporan Keuangan`.

Hasil yang diharapkan:
- nota masih muncul di laporan penjualan
- metode pembayaran tetap `QRIS`
- total `QRIS` turun sesuai nilai refund efektif
- total `Refund` naik
- `COGS (HPP)` untuk transaksi itu menjadi `0` atau turun penuh secara efektif
- `Laba Kotor` transaksi menjadi `0` atau netral secara efektif
- stok bahan kembali penuh sesuai resep
- status transaksi berubah menjadi `REFUND`

## Skenario 3

### Refund Transaksi Diskon dan Pajak

Tujuan:
Memastikan refund mengikuti nominal netto transaksi, bukan hanya subtotal mentah.

Langkah:
1. Buat transaksi dengan:
   - subtotal yang mudah dihitung, misalnya `100.000`
   - diskon, misalnya `10.000`
   - pajak aktif
   - metode pembayaran `DEBIT`
2. Bayar transaksi dan catat:
   - subtotal
   - diskon
   - pajak
   - grand total
3. Lakukan refund penuh.
4. Refresh `Laporan Penjualan`.
5. Refresh `Laporan Keuangan`.

Hasil yang diharapkan:
- total `DEBIT` turun mengikuti nilai netto setelah diskon dan pajak yang relevan
- nilai `Refund` di laporan bukan hanya subtotal item mentah
- transaksi tetap muncul di laporan
- `COGS (HPP)` efektif turun penuh
- `Laba Kotor` efektif tidak menyisakan margin palsu

## Skenario 4

### Refund Berulang pada Nota yang Sama

Tujuan:
Memastikan sistem tidak melebihi qty terjual dan akumulasi refund tetap benar.

Langkah:
1. Buat transaksi qty `3`.
2. Refund pertama qty `1`.
3. Refund kedua qty `1`.
4. Refund ketiga qty `1`.
5. Coba refund lagi melebihi qty tersisa.

Hasil yang diharapkan:
- refund terakumulasi bertahap
- nilai `Sudah Refund` bertambah setiap kali
- total refund tidak melebihi qty terjual
- status baru menjadi `REFUND` setelah seluruh qty selesai direfund
- tidak ada crash atau page error

## Skenario 5

### Validasi Laporan Periode

Tujuan:
Memastikan refund masih terbaca saat filter laporan menggunakan tanggal transaksi asli.

Langkah:
1. Gunakan satu transaksi yang sudah direfund.
2. Buka `Laporan Penjualan` dengan periode yang mencakup tanggal `paid_at`.
3. Buka `Laporan Keuangan` dengan periode yang sama.
4. Ulangi dengan periode di luar tanggal transaksi.

Hasil yang diharapkan:
- refund terbaca pada periode yang mencakup tanggal transaksi bayar
- refund tidak muncul jika periode tidak mencakup transaksi asal

## Verifikasi Database Opsional

Jika ingin validasi lebih dalam, cek:
- tabel `sales`
- tabel `sale_refunds`
- tabel `sale_refund_lines`
- tabel `stock_moves`

Yang dicek:
- `sales.refund_total` bertambah
- `sales.status` menjadi `PAID` atau `REFUND` sesuai kondisi
- `sale_refunds.total_refund` terisi
- `sale_refund_lines` sesuai qty refund
- `stock_moves.type = REFUND` tercatat

## Catatan Lulus UAT

Refund dianggap lulus jika:
- nota tidak hilang dari laporan hanya karena full refund
- uang masuk per metode bayar turun sesuai refund
- stok kembali
- COGS dan laba tidak tersisa seolah barang masih terjual
- tidak ada page error saat refund parsial maupun penuh
