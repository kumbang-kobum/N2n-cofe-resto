<?php

namespace App\Services;

use App\Models\ItemBatch;
use Illuminate\Support\Carbon;
use RuntimeException;

class FefoAllocator
{
    public function allocate(int $itemId, float $qtyBase, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? now();
        $need = $qtyBase;
        $alloc = [];

        $batches = ItemBatch::where('item_id',$itemId)
            ->where('status','ACTIVE')
            ->where('qty_on_hand_base','>',0)
            ->whereDate('expired_at','>=',$asOf->toDateString())
            ->orderBy('expired_at','asc')
            ->orderBy('received_at','asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($need <= 0) break;
            $take = min($need, (float)$batch->qty_on_hand_base);
            if ($take <= 0) continue;

            $alloc[] = ['batch'=>$batch, 'take'=>$take];
            $need -= $take;
        }

        if ($need > 0.000001) {
            throw new RuntimeException("Stok tidak cukup untuk item ID {$itemId}");
        }

        return $alloc;
    }
}