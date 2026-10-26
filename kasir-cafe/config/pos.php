<?php

return [
    // Pajak dibebankan ke pembeli (10% dari subtotal sebelum diskon).
    'tax_rate' => 0.10,

    // Printer thermal jaringan (LAN). Server mengirim ESC/POS langsung ke
    // printer via TCP, sehingga cetak tidak bergantung pada AirPrint/Bluetooth
    // di perangkat kasir (iPad, Android, dsb).
    'printer' => [
        // Kosongkan (null) untuk menonaktifkan cetak jaringan; tombol akan
        // otomatis kembali memakai dialog cetak browser (window.print()).
        'host' => env('PRINTER_HOST'),
        'port' => (int) env('PRINTER_PORT', 9100),
        // Timeout koneksi ke printer (detik).
        'timeout' => (int) env('PRINTER_TIMEOUT', 5),
        // Lebar karakter kertas 80mm Font A (umumnya 48; sebagian unit 42).
        'width' => (int) env('PRINTER_WIDTH', 48),
    ],
];
