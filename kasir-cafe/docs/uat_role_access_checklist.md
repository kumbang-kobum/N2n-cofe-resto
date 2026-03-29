# Checklist UAT Akses per Role

Dokumen ini dipakai untuk memastikan menu, route, dan aksi penting benar-benar sesuai hak akses tiap role:

- `admin`
- `manager`
- `cashier`
- `petugas`

Gunakan mode `incognito/private` atau browser/profile terpisah agar session antar role tidak tercampur.

## Persiapan
- [ ] Sudah menjalankan:
  - `php artisan migrate`
  - `php artisan optimize:clear`
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
  - `php artisan permission:cache-reset`
- [ ] Minimal ada 4 user uji:
  - `admin`
  - `manager`
  - `cashier`
  - `petugas`
- [ ] Jika perlu, siapkan 1 user tambahan untuk uji custom permission.

## Skenario Admin
### Menu
- [ ] `Admin Dashboard` tampil.
- [ ] Semua grup menu utama tampil:
  - `Master Data`
  - `Operasional`
  - `Laporan`
- [ ] Menu `Pengguna & Hak Akses` tampil.
- [ ] Menu `Kas Kecil`, `Payroll`, `Audit Log`, `Laporan Keuangan` tampil.

### Aksi Sensitif
- [ ] Bisa approve/reject/hapus pengeluaran operasional.
- [ ] Bisa approve payroll.
- [ ] Bisa mark payroll sebagai paid.
- [ ] Bisa hapus payroll draft/belum paid.
- [ ] Bisa tutup kas kecil.
- [ ] Bisa hapus audit log.
- [ ] Bisa approve/reject izin/cuti/sakit.

## Skenario Manager
### Menu
- [ ] `Dashboard Manager` tampil.
- [ ] Menu operasional manager tampil:
  - `Master Karyawan`
  - `Master Shift`
  - `Rule Keterlambatan`
  - `Roster Bulanan Shift`
  - `Absensi Karyawan`
  - `Review Wajah Absensi`
  - `Izin / Cuti / Sakit`
  - `Makan Karyawan`
  - `Pengeluaran Operasional`
- [ ] Menu laporan manager tampil:
  - `Laporan Penjualan`
  - `Laporan Keuangan`
  - `Rekap Absensi Bulanan`
  - `Laporan Selisih Opname`
  - `Audit Log`

### Tidak Boleh Tampil / Tidak Boleh Masuk
- [ ] `Pengguna & Hak Akses` tidak tampil.
- [ ] `Kas Kecil` tidak tampil.
- [ ] `Kategori Pengeluaran` tidak tampil jika memang hanya admin.

### Aksi
- [ ] Bisa approve/reject izin/cuti/sakit.
- [ ] Tidak bisa approve/reject/hapus pengeluaran jika permission aksi tidak diberikan.
- [ ] Tidak bisa tutup kas kecil jika permission aksi tidak diberikan.
- [ ] Tidak bisa hapus audit log jika permission aksi tidak diberikan.
- [ ] Tidak bisa approve / paid / hapus payroll jika permission aksi tidak diberikan.

## Skenario Cashier
### Menu
- [ ] Menu kasir tampil:
  - `POS Kasir`
  - `Kiosk Absensi`
  - `Makan Karyawan`
  - `Laporan Penjualan`
  - `Pengeluaran Operasional`
  - `Pengaturan Resto`
  - `Ubah Password`

### Tidak Boleh Tampil / Tidak Boleh Masuk
- [ ] `Laporan Keuangan` tidak tampil.
- [ ] `Kas Kecil` tidak tampil.
- [ ] `Pengguna & Hak Akses` tidak tampil.
- [ ] `Payroll` tidak tampil.
- [ ] `Audit Log` tidak tampil.

### Aksi
- [ ] Bisa membuat transaksi POS.
- [ ] Bisa membuat pengajuan pengeluaran operasional.
- [ ] Tidak bisa approve/reject/hapus pengeluaran.
- [ ] Tidak bisa membuka route admin/manager sensitif langsung via URL.

## Skenario Petugas
### Menu
- [ ] Hanya menu berikut yang tampil:
  - `Kiosk Absensi`
  - `Ubah Password`

### Arah Dashboard
- [ ] Login sebagai `petugas` lalu buka `/dashboard` harus diarahkan ke `attendance.kiosk`.

### Tidak Boleh Tampil / Tidak Boleh Masuk
- [ ] Tidak melihat POS.
- [ ] Tidak melihat laporan.
- [ ] Tidak melihat pengeluaran operasional.
- [ ] Tidak melihat payroll.
- [ ] Tidak melihat kas kecil.

## Skenario Custom Permission
Gunakan 1 user uji tambahan. Atur dari menu `Pengguna & Hak Akses`.

### Contoh 1: Manager Boleh Approve Payroll
- [ ] Beri permission:
  - `access.payroll`
  - `action.payroll.approve`
- [ ] Login sebagai user tersebut.
- [ ] Menu payroll tampil.
- [ ] Tombol `Approve` tampil.
- [ ] Tombol `Paid` tidak tampil jika `action.payroll.mark_paid` tidak diberikan.
- [ ] Tombol `Hapus` tidak tampil jika `action.payroll.delete` tidak diberikan.

### Contoh 2: User Bisa Lihat Audit Log Tapi Tidak Bisa Hapus
- [ ] Beri permission:
  - `access.audit_logs`
- [ ] Login sebagai user tersebut.
- [ ] Halaman audit log bisa dibuka.
- [ ] Tombol `Hapus` dan `Hapus Hasil Filter` tidak tampil.

### Contoh 3: User Bisa Lihat Pengeluaran Tapi Tidak Bisa Approve
- [ ] Beri permission:
  - `access.expenses`
- [ ] Login sebagai user tersebut.
- [ ] Halaman pengeluaran bisa dibuka.
- [ ] Tombol `Approve`, `Reject`, dan `Hapus` tidak tampil.

## Uji URL Langsung
Untuk setiap role, coba akses langsung URL sensitif berikut:

- [ ] `/admin/users`
- [ ] `/admin/petty-cash`
- [ ] `/admin/reports/audit-logs`
- [ ] `/admin/payroll`
- [ ] `/admin/expenses`

Ekspektasi:
- jika tidak punya akses, tampil `403` atau diarahkan sesuai mekanisme aplikasi
- halaman tidak boleh terbuka diam-diam

## Keputusan Go-Live
- [ ] Semua role hanya melihat menu yang relevan.
- [ ] Semua aksi sensitif hanya muncul untuk user yang punya permission aksi.
- [ ] Route sensitif tidak bisa dibuka langsung tanpa permission.
- [ ] 1 skenario custom permission berhasil diuji.
- [ ] Checklist ini ditandatangani / disetujui internal sebelum dipakai penuh.
