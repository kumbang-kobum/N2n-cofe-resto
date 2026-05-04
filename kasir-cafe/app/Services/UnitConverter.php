<?php

namespace App\Services;

use App\Models\UnitConversion;
use RuntimeException;

class UnitConverter
{
    public function toBase(float $qty, int $fromUnitId, int $baseUnitId): float
    {
        if (! is_finite($qty) || $qty < 0) {
            throw new RuntimeException("Qty unit tidak valid.");
        }

        if ($fromUnitId === $baseUnitId) return $qty;

        $conv = UnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $baseUnitId)
            ->first();

        if (!$conv) {
            throw new RuntimeException("Konversi unit tidak ditemukan (from {$fromUnitId} -> base {$baseUnitId}).");
        }

        $multiplier = (float) $conv->multiplier;
        if (! is_finite($multiplier) || $multiplier <= 0) {
            throw new RuntimeException("Konversi unit tidak valid (from {$fromUnitId} -> base {$baseUnitId}).");
        }

        $result = $qty * $multiplier;
        if (! is_finite($result)) {
            throw new RuntimeException("Hasil konversi unit tidak valid (from {$fromUnitId} -> base {$baseUnitId}).");
        }

        return $result;
    }

    public function costToBase(float $unitCost, int $fromUnitId, int $baseUnitId): float
    {
        if (! is_finite($unitCost) || $unitCost < 0) {
            throw new RuntimeException("Harga unit tidak valid.");
        }

        if ($fromUnitId === $baseUnitId) return $unitCost;

        $conv = UnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $baseUnitId)
            ->first();

        if (!$conv) {
            throw new RuntimeException("Konversi unit (cost) tidak ditemukan (from {$fromUnitId} -> base {$baseUnitId}).");
        }

        $multiplier = (float) $conv->multiplier;
        if (! is_finite($multiplier) || $multiplier <= 0) {
            throw new RuntimeException("Konversi unit (cost) tidak valid (from {$fromUnitId} -> base {$baseUnitId}).");
        }

        // contoh: cost per kg -> cost per g = cost/kg dibagi 1000
        $result = $unitCost / $multiplier;
        if (! is_finite($result)) {
            throw new RuntimeException("Hasil konversi unit (cost) tidak valid (from {$fromUnitId} -> base {$baseUnitId}).");
        }

        return $result;
    }
}
