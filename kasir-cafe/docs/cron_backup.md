# Cron Backup 8 Jam

Jalankan backup otomatis setiap 8 jam dan simpan maksimal 20 file terakhir.

1. Edit crontab:
```bash
crontab -e
```

2. Tambahkan baris:
```bash
0 */8 * * * /bin/bash /path/to/kasir-cafe/scripts/db_backup.sh >> /path/to/kasir-cafe/storage/logs/backup.log 2>&1
```

Catatan:
- Ganti `/path/to/kasir-cafe` sesuai lokasi project.
- Script sudah otomatis membersihkan backup lama (menyimpan 20 file terbaru).
