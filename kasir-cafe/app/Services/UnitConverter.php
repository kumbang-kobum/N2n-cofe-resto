<?php

namespace App\Services;

use App\Models\UnitConversion;
use RuntimeException;

class UnitConverter
{
    public function toBase(float $qty, int $fromUnitId, int $baseUnitId): float
    {
        if ($fromUnitId === $baseUnitId) return $qty;

        $conv = UnitConversion::where('from_unit_id',$fromUnitId)
            ->where('to_unit_id',$baseUnitId)
            ->first();

        if (!$conv) throw new RuntimeException("Konversi unit tidak ditemukan.");

        return $qty * (float)$conv->multiplier;
    }

    public function costToBase(float $unitCost, int $fromUnitId, int $baseUnitId): float
    {
        if ($fromUnitId === $baseUnitId) return $unitCost;

        $conv = UnitConversion::where('from_unit_id',$fromUnitId)
            ->where('to_unit_id',$baseUnitId)
            ->first();

        if (!$conv) throw new RuntimeException("Konversi unit untuk cost tidak ditemukan.");

        // cost per kg -> cost per g = cost/kg ÷ 1000
        return $unitCost / (float)$conv->multiplier;
    }
}