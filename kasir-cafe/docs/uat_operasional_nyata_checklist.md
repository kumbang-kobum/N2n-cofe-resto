# Checklist UAT Operasional Nyata

Dokumen ini dipakai untuk uji lapangan setelah UAT teknis, role, dan permission selesai.

Tujuan utamanya:
- memastikan aplikasi nyaman dipakai dalam operasional nyata
- memastikan data POS, stok, absensi, payroll, pengeluaran, dan laporan tetap sinkron
- mencatat temuan lapangan sebelum fitur dianggap stabil untuk go-live penuh

## Durasi yang Disarankan
- Minimal `3 hari operasional nyata`
- Minimal `1 kali stock opname kecil`
- Minimal `1 simulasi payroll`
- Minimal `1 siklus kas kecil`

## Tim UAT
- `Admin / Owner`
  - fokus ke approval, laporan, kas kecil, payroll, audit, hak akses
- `Manager`
  - fokus ke operasional harian, absensi, roster, pengeluaran, review wajah
- `Cashier`
  - fokus ke POS, nota, pengeluaran, makan karyawan
- `Petugas`
  - fokus ke kiosk absensi dan ubah password

## Persiapan Sebelum UAT
- [ ] `php artisan migrate` sudah dijalankan
- [ ] `php artisan optimize:clear` sudah dijalankan
- [ ] `php artisan config:cache` sudah dijalankan
- [ ] `php artisan route:cache` sudah dijalankan
- [ ] `php artisan view:cache` sudah dijalankan
- [ ] `php artisan permission:cache-reset` sudah dijalankan
- [ ] `php artisan storage:link` sudah aktif
- [ ] Data master dasar sudah ada:
  - produk
  - resep
  - stok bahan
  - satuan
  - kategori pengeluaran
  - master karyawan
  - shift kerja
  - rule keterlambatan
- [ ] Minimal ada akun uji:
  - admin
  - manager
  - cashier
  - petugas

## Hari 1 - POS & Operasional Dasar
### POS Kasir
- [ ] Kasir bisa login dan langsung masuk ke POS.
- [ ] Produk tampil normal di katalog.
- [ ] Qty bisa diisi dengan jelas.
- [ ] Harga di katalog sesuai harga di keranjang.
- [ ] Simpan transaksi `cash`.
- [ ] Simpan transaksi `non-cash`.
- [ ] Hold bill dan buka kembali bill berjalan.
- [ ] Cetak nota utama.
- [ ] Cetak slip makanan.
- [ ] Cetak slip minuman.
- [ ] Hasil cetak tidak menyisakan layout kosong berlebihan.

### Pengeluaran Operasional
- [ ] Cashier bisa membuat pengajuan pengeluaran.
- [ ] Upload bukti belanja berhasil.
- [ ] Pengeluaran muncul sebagai `PENDING`.
- [ ] Cashier tidak bisa approve sendiri.

### Makan Karyawan
- [ ] Transaksi makan karyawan bisa dibuat.
- [ ] Stok berkurang.
- [ ] Transaksi tidak masuk omzet.

## Hari 2 - Stok, Receiving, dan Kas Kecil
### Receiving & Stok
- [ ] Receiving stok bisa dibuat.
- [ ] Batch stok masuk bertambah.
- [ ] Stok saat ini berubah sesuai receiving.
- [ ] Produk terjual mengurangi stok bahan sesuai resep.

### Stock Opname
- [ ] Buat stock opname kecil berhasil.
- [ ] Simpan draft tidak error.
- [ ] Edit draft berhasil.
- [ ] Post opname berhasil.
- [ ] Selisih opname muncul di laporan selisih opname.

### Kas Kecil
- [ ] Admin bisa buka dana kas kecil baru.
- [ ] Manager/cashier bisa memilih sumber dana `Kas Kecil`.
- [ ] Pengeluaran approved dari kas kecil mengurangi saldo kas kecil.
- [ ] Pengeluaran dari kas kecil tidak memotong saldo kas penjualan harian.
- [ ] Ringkasan kategori pengeluaran tampil.

## Hari 3 - Absensi, Payroll, dan Laporan
### Kiosk Absensi
- [ ] `Petugas` login diarahkan ke kiosk absensi.
- [ ] Clock in berhasil.
- [ ] Clock out berhasil.
- [ ] Selfie tersimpan.
- [ ] Skor verifikasi tersimpan.
- [ ] Status review sesuai hasil foto.

### Review Wajah
- [ ] Admin/manager bisa membuka review wajah.
- [ ] Bisa melihat foto referensi dan selfie.
- [ ] Bisa mengubah status review.
- [ ] Audit reviewer tersimpan.

### Leave / Izin / Cuti / Sakit
- [ ] Pengajuan leave bisa dibuat.
- [ ] Approval leave berhasil.
- [ ] Tanggal approved terkunci di roster.

### Payroll
- [ ] Rekap absensi bulanan muncul.
- [ ] Potongan telat muncul sesuai absensi.
- [ ] Potongan makan muncul sesuai transaksi makan.
- [ ] Payroll bisa dibuat.
- [ ] Slip payroll bisa dibuka.
- [ ] Approve payroll berhasil.
- [ ] Mark paid payroll berhasil.

### Laporan
- [ ] Laporan penjualan tampil sesuai transaksi.
- [ ] Laporan keuangan tampil tanpa error.
- [ ] Rekap absensi bulanan tampil dan bisa export.
- [ ] Laporan kas kecil bisa export.
- [ ] Audit log tampil.

## UAT Hak Akses Nyata
- [ ] Admin melihat menu penuh.
- [ ] Manager hanya melihat menu sesuai tugasnya.
- [ ] Cashier hanya melihat menu kasir.
- [ ] Petugas hanya melihat kiosk absensi dan ubah password.
- [ ] Pengguna custom dengan permission khusus berjalan sesuai checklist.

## UAT Upload File
- [ ] Upload logo berhasil.
- [ ] Upload video TV Informasi ukuran kecil berhasil.
- [ ] Upload video besar berhasil setelah setting server disesuaikan.
- [ ] Upload bukti belanja berhasil.
- [ ] Tidak ada `HTTP 500` saat upload normal.

## UAT Responsivitas
- [ ] Landing page cepat dibuka.
- [ ] POS tetap ringan saat banyak item.
- [ ] Halaman stok opname masih bisa dibuka pada data aktual.
- [ ] Halaman laporan tidak terasa berat berlebihan pada periode 1 bulan.
- [ ] Sidebar dan navigasi tidak membingungkan pengguna lapangan.

## Catatan Temuan Lapangan
Gunakan format ini untuk setiap temuan:

- `Tanggal:`
- `Role / Pengguna:`
- `Menu / Halaman:`
- `Langkah yang dilakukan:`
- `Hasil yang muncul:`
- `Yang seharusnya:`
- `Prioritas: Tinggi / Sedang / Rendah`

## Kriteria Lulus UAT
- [ ] Tidak ada error blocker di POS
- [ ] Tidak ada error blocker di absensi
- [ ] Tidak ada error blocker di kas kecil / pengeluaran
- [ ] Tidak ada mismatch angka besar antara transaksi dan laporan
- [ ] Hak akses role berjalan sesuai kebutuhan
- [ ] Tim operasional bisa memakai sistem tanpa pendampingan intensif

## Keputusan Setelah UAT
- [ ] Siap go-live penuh
- [ ] Siap go-live bertahap
- [ ] Perlu perbaikan minor dulu
- [ ] Perlu perbaikan mayor dulu
