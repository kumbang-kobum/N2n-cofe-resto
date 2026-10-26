<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

/**
 * Mencetak nota 80mm langsung ke printer thermal jaringan (ESC/POS via TCP).
 *
 * Dipakai agar cetak tidak bergantung pada AirPrint/Bluetooth di perangkat
 * kasir: perangkat hanya memicu, server yang mengirim ke printer. Tata letak
 * mengikuti resources/views/cashier/receipt.blade.php (nota utama + slip
 * minuman + slip makanan).
 */
class ThermalReceiptPrinter
{
    private int $width;

    public function __construct(
        private string $host,
        private int $port = 9100,
        private int $timeout = 5,
        ?int $width = null,
    ) {
        $this->width = $width && $width > 0 ? $width : 48;
    }

    public static function fromConfig(): self
    {
        return new self(
            host: (string) config('pos.printer.host'),
            port: (int) config('pos.printer.port', 9100),
            timeout: (int) config('pos.printer.timeout', 5),
            width: (int) config('pos.printer.width', 48),
        );
    }

    public static function isConfigured(): bool
    {
        return filled(config('pos.printer.host'));
    }

    /**
     * Cetak 3 rangkap untuk satu transaksi ke printer jaringan.
     */
    public function printSale(Sale $sale): void
    {
        $connector = new NetworkPrintConnector($this->host, $this->port, $this->timeout);
        $printer = new Printer($connector);

        try {
            $this->buildDocument($printer, $sale);
        } finally {
            $printer->close();
        }
    }

    /**
     * Render byte ESC/POS mentah tanpa printer fisik (untuk uji/debug).
     */
    public function preview(Sale $sale): string
    {
        $connector = new DummyPrintConnector();
        $printer = new Printer($connector);
        $this->buildDocument($printer, $sale);
        $data = $connector->getData();
        $printer->close();

        return $data;
    }

    /**
     * Susun 3 rangkap: nota utama, slip minuman, slip makanan.
     */
    private function buildDocument(Printer $printer, Sale $sale): void
    {
        $sale->loadMissing(['lines.product', 'cashier']);
        $settings = Setting::first();

        $drinkLines = $sale->lines
            ->filter(fn ($l) => $l->product?->menu_type === Product::TYPE_DRINK)
            ->values();
        $foodLines = $sale->lines
            ->filter(fn ($l) => $l->product?->menu_type !== Product::TYPE_DRINK)
            ->values();

        $this->printFull($printer, $sale, $settings);
        $printer->cut();

        $this->printCategory($printer, $sale, $settings, 'Slip Minuman', $drinkLines, 'Tidak ada item minuman pada transaksi ini.');
        $printer->cut();

        $this->printCategory($printer, $sale, $settings, 'Slip Makanan', $foodLines, 'Tidak ada item makanan pada transaksi ini.');
        $printer->cut();
    }

    /**
     * Rangkap 1: nota utama lengkap dengan total & pembayaran.
     */
    private function printFull(Printer $printer, Sale $sale, ?Setting $settings): void
    {
        $this->printHeader($printer, $settings);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Struk Pembayaran\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);

        $this->printSaleMeta($printer, $sale, withCashier: true);
        $this->divider($printer);

        foreach ($sale->lines as $line) {
            $qty = $this->qtyFormat($line->qty);
            $sub = $this->money((float) $line->qty * (float) $line->price);
            $this->boldName($printer, (string) $line->product?->name);
            // Baris detail: "  {qty} x {harga}" di kiri, subtotal di kanan.
            $detail = '  ' . $qty . ' x ' . $this->money((float) $line->price);
            $printer->text($this->twoCol($detail, $sub) . "\n");
        }

        $this->divider($printer);

        $taxRate = (float) ($sale->tax_rate ?? config('pos.tax_rate', 0.10));
        $discount = (float) ($sale->discount_amount ?? 0);
        $taxAmount = (float) ($sale->tax_amount ?? 0);
        $taxBase = max(0, (float) $sale->total - $discount);
        $grand = (float) ($sale->grand_total ?? ($taxBase + $taxAmount));

        $printer->text($this->twoCol('Subtotal', $this->money((float) $sale->total)) . "\n");
        $printer->text($this->twoCol('Diskon', $this->money($discount)) . "\n");
        if ($taxRate > 0) {
            $printer->text($this->twoCol('Pajak (' . (int) round($taxRate * 100) . '%)', $this->money($taxAmount)) . "\n");
        }
        $printer->setEmphasis(true);
        $printer->text($this->twoCol('Total', $this->money($grand)) . "\n");
        $printer->setEmphasis(false);
        $printer->text($this->twoCol('Dibayar', $this->money((float) ($sale->paid_amount ?? 0))) . "\n");
        $printer->text($this->twoCol('Kembalian', $this->money((float) ($sale->change_amount ?? 0))) . "\n");

        $this->divider($printer);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Terima kasih\n");
        $printer->text("Nama Wi-Fi: batrisya2\n");
        $printer->text("Password: batrisya02\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->feed(1);
    }

    /**
     * Rangkap 2 & 3: slip per kategori (hanya nama item + qty).
     */
    private function printCategory(Printer $printer, Sale $sale, ?Setting $settings, string $title, Collection $lines, string $emptyMessage): void
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text($this->restaurantName($settings) . "\n");
        $printer->setEmphasis(false);
        $printer->text($title . "\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);

        $this->printSaleMeta($printer, $sale, withCashier: false);
        $this->divider($printer);

        if ($lines->isEmpty()) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text($emptyMessage . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
        } else {
            foreach ($lines as $line) {
                // Nama (bold) di kiri, qty di kanan pada baris terakhir nama.
                $this->printNameWithRight($printer, (string) $line->product?->name, $this->qtyFormat($line->qty));
            }
        }

        $this->divider($printer);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text('Bagian ' . mb_strtolower($title) . "\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->feed(1);
    }

    private function printHeader(Printer $printer, ?Setting $settings): void
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text($this->restaurantName($settings) . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        if (filled($settings?->restaurant_address)) {
            $printer->text($settings->restaurant_address . "\n");
        }
        if (filled($settings?->restaurant_phone)) {
            $printer->text('Telp: ' . $settings->restaurant_phone . "\n");
        }
        $printer->setJustification(Printer::JUSTIFY_LEFT);
    }

    private function printSaleMeta(Printer $printer, Sale $sale, bool $withCashier): void
    {
        $printer->text('No. Nota: ' . ($sale->receipt_no ?? ('#' . $sale->id)) . "\n");
        $printer->text('Tanggal : ' . optional($sale->paid_at)->format('d/m/Y H:i') . "\n");
        if ($sale->table_no || $sale->customer_name) {
            $printer->setEmphasis(true);
            $printer->text('Meja: ' . ($sale->table_no ?? '-'));
            $printer->setEmphasis(false);
            $printer->text(' | Tamu: ' . ($sale->customer_name ?? '-') . "\n");
        }
        if ($withCashier) {
            $printer->text('Kasir   : ' . (optional($sale->cashier)->name ?? '-') . "\n");
            $printer->text('Metode  : ' . mb_strtoupper($sale->payment_method ?? '-') . "\n");
        }
    }

    /* ------------------------------------------------------------------ */
    /* Helper tata letak teks                                             */
    /* ------------------------------------------------------------------ */

    private function restaurantName(?Setting $settings): string
    {
        return $settings?->restaurant_name ?: 'N2N Cafe';
    }

    private function divider(Printer $printer): void
    {
        $printer->text(str_repeat('-', $this->width) . "\n");
    }

    /** Nama item cetak tebal, dipotong per baris selebar kertas. */
    private function boldName(Printer $printer, string $name): void
    {
        $printer->setEmphasis(true);
        foreach ($this->wrapLines($name, $this->width) as $seg) {
            $printer->text($seg . "\n");
        }
        $printer->setEmphasis(false);
    }

    /**
     * Cetak nama (tebal) dengan nilai kanan (mis. qty) yang menempel di baris
     * terakhir nama. Menangani nama panjang dengan pembungkusan baris.
     */
    private function printNameWithRight(Printer $printer, string $name, string $right): void
    {
        $rightLen = mb_strlen($right);
        $nameWidth = max(1, $this->width - $rightLen - 1);
        $segments = $this->wrapLines($name, $nameWidth);
        $last = count($segments) - 1;

        foreach ($segments as $i => $seg) {
            $printer->setEmphasis(true);
            if ($i === $last) {
                $printer->text($this->padRight($seg, $this->width - $rightLen));
                $printer->setEmphasis(false);
                $printer->text($right . "\n");
            } else {
                $printer->text($seg . "\n");
                $printer->setEmphasis(false);
            }
        }
    }

    /** Susun kiri & kanan dalam satu baris selebar kertas. */
    private function twoCol(string $left, string $right): string
    {
        $rightLen = mb_strlen($right);
        $space = $this->width - $rightLen;
        if (mb_strlen($left) > $space - 1) {
            $left = mb_substr($left, 0, max(0, $space - 1));
        }

        return $this->padRight($left, $space) . $right;
    }

    /** @return string[] */
    private function wrapLines(string $text, int $width): array
    {
        $text = trim($text) === '' ? '-' : $text;

        return explode("\n", wordwrap($text, $width, "\n", true));
    }

    private function padRight(string $text, int $width): string
    {
        $pad = $width - mb_strlen($text);

        return $pad > 0 ? $text . str_repeat(' ', $pad) : $text;
    }

    private function money(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function qtyFormat(mixed $qty): string
    {
        return rtrim(rtrim(number_format((float) $qty, 3, '.', ''), '0'), '.');
    }
}
