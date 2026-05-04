# Panduan Reset Data Transaksi Database

## 📋 Overview
Panduan ini menjelaskan cara mengosongkan data transaksi (penjualan, pembelian, stok opname) sambil tetap mempertahankan data master (karyawan, menu, bahan, resep, pengaturan).

---

## ✅ Persyaratan Sebelum Memulai

1. **Backup Database** (WAJIB!)
   ```bash
   # Via MySQL CLI
   mysqldump -u root -p cafferesto > backup_cafferesto_$(date +%Y%m%d_%H%M%S).sql
   
   # Atau via phpMyAdmin > Export
   ```

2. **Pastikan aplikasi dalam keadaan idle** (tidak ada user yang sedang transaksi)

3. **Akses ke terminal/command line** dengan akses ke Laravel project

---

## 🔧 Step-by-Step Eksekusi

### **Method 1: Via Laravel Tinker (RECOMMENDED)**

```bash
# 1. Masuk ke direktori project
cd /path/to/kasir-cafe

# 2. Buka Laravel Tinker
php artisan tinker

# 3. Jalankan query reset
DB::unprepared(file_get_contents('reset_transaction_data.sql'));

# 4. Verifikasi hasil
echo 'Sales: ' . DB::table('sales')->count() . '\n';
echo 'Purchases: ' . DB::table('purchases')->count() . '\n';
echo 'Stock Opnames: ' . DB::table('stock_opnames')->count() . '\n';
echo 'Batches with qty > 0: ' . DB::table('item_batches')->where('qty_on_hand_base', '>', 0)->count() . '\n';

# 5. Exit
exit
```

### **Method 2: Via Command Line**

```bash
cd /path/to/kasir-cafe

# One-liner execution
php artisan tinker --execute="
DB::unprepared(file_get_contents('reset_transaction_data.sql'));
echo 'Reset berhasil!\n';
echo 'Batches with qty > 0: ' . DB::table('item_batches')->where('qty_on_hand_base', '>', 0)->count() . '\n';
"
```

### **Method 3: Via MySQL CLI**

```bash
# Direct MySQL execution
mysql -u root -p cafferesto < reset_transaction_data.sql

# Dengan verifikasi
mysql -u root -p cafferesto -e "SELECT COUNT(*) as sales FROM sales; SELECT COUNT(*) as purchases FROM purchases;"
```

### **Method 4: Via phpMyAdmin**

1. Login ke phpMyAdmin
2. Pilih database `cafferesto`
3. Tab **SQL**
4. Copy-paste isi file `reset_transaction_data.sql`
5. Klik **Execute**

---

## 🔄 Langkah Setelah Eksekusi

### **1. Clear Laravel Cache** (PENTING!)
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### **2. Refresh Browser**
- Buka ulang aplikasi
- Menu "Stok Saat Ini" akan menampilkan semua stok = 0
- Menu "Penjualan" kosong
- Menu "Pembelian" kosong
- Menu "Stok Opname" kosong

### **3. Verifikasi Data Master Masih Ada**
```bash
php artisan tinker --execute="
echo 'Users: ' . DB::table('users')->count() . '\n';
echo 'Items (Bahan): ' . DB::table('items')->count() . '\n';
echo 'Products (Menu): ' . DB::table('products')->count() . '\n';
echo 'Employees (Karyawan): ' . DB::table('employees')->count() . '\n';
echo 'Settings: ' . DB::table('settings')->count() . '\n';
"
```

---

## 📊 Query Explanation

File `reset_transaction_data.sql` melakukan:

| Step | Aksi | Tujuan |
|------|------|--------|
| 1 | DELETE audit_logs | Hapus log audit terkait transaksi |
| 2 | DELETE stock_moves | Hapus semua pergerakan stok |
| 3 | DELETE transaksi | Hapus penjualan, pembelian, stok opname dan child records |
| 3.5 | DELETE orphaned batches | Hapus batch yang tidak punya stock_moves |
| 4 | UPDATE item_batches | Hitung ulang stok = 0, status = DEPLETED |

---

## ✨ Hasil Akhir

Setelah eksekusi, kondisi database:

| Tabel | Sebelum | Sesudah | Status |
|-------|---------|---------|--------|
| **sales** | ribuan | 0 | ✅ Kosong |
| **purchases** | ribuan | 0 | ✅ Kosong |
| **stock_opnames** | ratusan | 0 | ✅ Kosong |
| **stock_moves** | ribuan | 0 | ✅ Kosong |
| **audit_logs** | ribuan | ribuan* | ✅ Tetap ada (non-transaksi) |
| **item_batches** | ada | ada | ✅ Tetap ada (qty=0, DEPLETED) |
| **users** | ada | ada | ✅ Tetap ada |
| **items** | ada | ada | ✅ Tetap ada |
| **products** | ada | ada | ✅ Tetap ada |
| **employees** | ada | ada | ✅ Tetap ada |
| **settings** | ada | ada | ✅ Tetap ada |

*Audit logs non-transaksi tetap terjaga (login history, master data changes, dll)

---

## ⚠️ Important Notes

1. **Irreversible** - Query ini menghapus data permanen. Pastikan backup sudah ada.
2. **No Transactions** - Setelah reset, semua counter/nomor transaksi akan mulai dari 1 lagi.
3. **Batch Remain** - Item batches tetap ada dengan qty=0. Ini normal dan tidak masalah.
4. **Recurring Use** - File ini bisa digunakan berulang kali untuk reset data.

---

## 🆘 Troubleshooting

### Error: "Stok tidak cukup"
- Query telah mengatasi ini dengan menghapus semua stock_moves
- Jika masih error, jalankan manual:
  ```bash
  UPDATE item_batches SET qty_on_hand_base = 0, status = 'DEPLETED';
  ```

### Stok masih terlihat di menu
- Clear cache:
  ```bash
  php artisan cache:clear
  ```
- Refresh browser (Ctrl+F5)

### Query tidak tereksekusi
- Periksa permission database
- Pastikan database connection aktif
- Coba via phpMyAdmin atau MySQL CLI langsung

---

## 📁 File Location

Query file tersimpan di:
```
kasir-cafe/reset_transaction_data.sql
```

Anda bisa membaca atau edit file ini kapan saja.

---

## ✅ Checklist Sebelum Produksi

- [ ] Backup database sudah dibuat
- [ ] Tidak ada user yang sedang transaksi
- [ ] Sudah read permission file `reset_transaction_data.sql`
- [ ] Sudah test di development environment
- [ ] Sudah clear cache setelah reset
- [ ] Sudah verifikasi data master masih ada
- [ ] Sudah inform team bahwa reset dilakukan

---

**Last Updated:** May 4, 2026  
**Version:** 1.0
