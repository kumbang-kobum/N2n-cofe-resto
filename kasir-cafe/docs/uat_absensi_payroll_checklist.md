# Checklist UAT Absensi + Payroll

Checklist ini dipakai untuk uji lapangan sebelum modul absensi dan payroll dianggap siap dipakai operasional.

## 1. Persiapan Master

- [ ] Minimal 2 shift sudah dibuat di `Master Shift`
- [ ] Minimal 2 rule keterlambatan sudah dibuat di `Rule Keterlambatan`
- [ ] Minimal 3 karyawan aktif tersedia di `Master Karyawan`
- [ ] Setiap karyawan test memiliki `Shift Default`
- [ ] Minimal 2 karyawan test memiliki `Foto Referensi Wajah`
- [ ] `php artisan storage:link` sudah dijalankan jika foto tidak tampil

## 2. Roster / Jadwal

- [ ] Roster bulanan bisa dibuka tanpa error
- [ ] Klik satu sel roster membuka modal set jadwal
- [ ] Set shift manual per tanggal berhasil tersimpan
- [ ] Set `Hari Libur` manual per tanggal berhasil tersimpan
- [ ] Bulk Action bisa menerapkan shift ke beberapa karyawan sekaligus
- [ ] Bulk Action bisa menerapkan libur massal
- [ ] Copy roster dari `minggu lalu` berjalan
- [ ] Copy roster dari `bulan lalu` berjalan
- [ ] Tanggal dengan `cuti / sakit / izin approved` terkunci dan tidak bisa diubah

## 3. Kiosk Absensi

- [ ] Halaman `Absensi Karyawan` bisa diakses tanpa login
- [ ] Kamera tablet/browser bisa aktif
- [ ] Selfie bisa diambil
- [ ] Clock In tersimpan
- [ ] Clock Out tersimpan
- [ ] Foto selfie masuk tampil di halaman `Absensi Karyawan`
- [ ] Foto selfie pulang tampil di halaman `Absensi Karyawan`
- [ ] Jika karyawan punya foto referensi, preview foto referensi tampil di kiosk
- [ ] Sistem memberi status verifikasi ringan
- [ ] Sistem menyimpan skor verifikasi ringan

## 4. Review Wajah

- [ ] Absensi dengan status `REVIEW_REQUIRED` muncul di `Review Wajah Absensi`
- [ ] Admin/manager bisa mengubah status menjadi `FACE_VERIFIED`
- [ ] Admin/manager bisa menyimpan `Catatan Review`
- [ ] Nama reviewer tersimpan
- [ ] Waktu review tersimpan

## 5. Izin / Cuti / Sakit

- [ ] Pengajuan `izin / cuti / sakit` bisa dibuat
- [ ] Approval berjalan
- [ ] Setelah approved, status leave tampil di roster
- [ ] Tanggal leave tidak bisa ditimpa oleh roster manual/bulk/copy

## 6. Rekap Absensi Bulanan

- [ ] Halaman `Rekap Absensi Bulanan` bisa dibuka
- [ ] Filter bulan berjalan
- [ ] Filter per karyawan berjalan
- [ ] Rekap karyawan menampilkan:
  - [ ] hadir
  - [ ] telat
  - [ ] belum lengkap
  - [ ] lembur
  - [ ] face verified
  - [ ] perlu review
  - [ ] cuti
  - [ ] sakit
  - [ ] izin
  - [ ] potongan telat
- [ ] Rekap harian menampilkan data yang konsisten
- [ ] Export Excel dari halaman laporan berhasil

## 7. Payroll

- [ ] Preview `Potongan Telat` muncul otomatis saat pilih periode + karyawan
- [ ] Preview `Potongan Makan` muncul otomatis saat pilih periode + karyawan
- [ ] Rekap absensi bulanan di form payroll tampil benar
- [ ] Data `cuti / sakit / izin` ikut tampil di ringkasan payroll
- [ ] Simpan payroll berhasil
- [ ] Potongan telat masuk ke payroll
- [ ] Potongan makan masuk ke payroll
- [ ] Payroll yang sudah dibuat tidak menarik potongan telat dua kali

## 8. Slip Payroll

- [ ] Slip payroll bisa dibuka
- [ ] Gaji pokok benar
- [ ] Lembur benar
- [ ] Bonus benar
- [ ] Potongan manual benar
- [ ] Potongan telat benar
- [ ] Potongan makan benar
- [ ] Nilai bersih benar

## 9. Skenario Uji Minimal yang Disarankan

### Skenario A: Hadir Normal
- [ ] Karyawan A clock in tepat waktu
- [ ] Karyawan A clock out normal
- [ ] Status tidak telat
- [ ] Tidak ada potongan telat

### Skenario B: Telat
- [ ] Karyawan B clock in melewati toleransi
- [ ] Sistem hitung telat
- [ ] Potongan telat masuk ke rekap payroll

### Skenario C: Face Review
- [ ] Karyawan C selfie dengan pencahayaan buruk
- [ ] Status menjadi `REVIEW_REQUIRED`
- [ ] Admin review dan ubah ke `FACE_VERIFIED`
- [ ] Audit review tercatat

### Skenario D: Leave Approved
- [ ] Karyawan D punya cuti approved
- [ ] Hari itu terkunci di roster
- [ ] Rekap bulanan menambah `Cuti`

### Skenario E: Payroll Final
- [ ] Buat payroll untuk periode uji
- [ ] Cocokkan total potongan telat dengan rekap absensi
- [ ] Cocokkan total potongan makan dengan modul makan karyawan
- [ ] Verifikasi slip payroll

## 10. Catatan Go-Live

- [ ] UAT minimal 3 hari operasional nyata
- [ ] UAT minimal 1 siklus payroll simulasi
- [ ] Backup database sebelum go-live
- [ ] User admin/manager sudah paham alur review wajah
- [ ] User manager sudah paham roster bulanan dan bulk action
- [ ] SOP fallback disiapkan jika kamera gagal

