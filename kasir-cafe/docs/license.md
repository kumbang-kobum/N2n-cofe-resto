# Lisensi (Serial Tervalidasi)

## 1. Set Master Key
Isi di `.env`:
```
LICENSE_MASTER_KEY=isi_kunci_rahasia_kamu
```

## 2. Dapatkan Installation Code
Admin → **Pengaturan Resto** → copy **Installation Code**.

## 3. Generate License Key
Di mesin kamu:
```
php scripts/generate_license.php INSTALLATION_CODE MASTER_KEY
```

Hasilnya adalah **license key** yang harus diinput di menu **Pengaturan Resto**.

## 4. Validasi
Saat license key sudah benar, aplikasi aktif tanpa batas waktu.
Jika tidak ada license key, aplikasi hanya bisa dipakai 30 hari sejak pertama kali diakses.
