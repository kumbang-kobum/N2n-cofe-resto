<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockMove;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProductConsumptionService
{
    public function __construct(
        protected FefoAllocator $allocator,
        protected UnitConverter $converter,
    ) {
    }

    /**
     * @param iterable<int, object> $lines
     * @return array{cogs: float, items: array<int, array{item_id:int,qty_base:float}>}
     */
    public function consumeProductLines(iterable $lines, string $refType, int $refId, ?string $note = null): array
    {
        $needs = [];

        foreach ($lines as $line) {
            $product = $line->product ?? null;
            $recipe = $product?->recipe ?? null;

            if (! $recipe || $recipe->lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'recipe' => 'Resep belum diatur untuk menu: ' . ($product->name ?? 'Unknown'),
                ]);
            }

            foreach ($recipe->lines as $detail) {
                $item = $detail->item;
                if (! $item || ! $item->base_unit_id) {
                    throw ValidationException::withMessages([
                        'recipe' => 'Item/base unit tidak valid pada resep menu: ' . ($product->name ?? 'Unknown'),
                    ]);
                }

                try {
                    $qtyBase = $this->converter->toBase(
                        (float) $detail->qty,
                        (int) $detail->unit_id,
                        (int) $item->base_unit_id
                    ) * (float) ($line->qty ?? 0);
                } catch (RuntimeException $e) {
                    throw ValidationException::withMessages([
                        'recipe' => 'Konversi unit belum diset untuk item "' . ($item->name ?? 'Unknown') . '" pada resep menu "' . ($product->name ?? 'Unknown') . '".',
                    ]);
                }

                $needs[$item->id] = ($needs[$item->id] ?? 0) + $qtyBase;
            }
        }

        $cogs = 0.0;
        $consumedItems = [];

        foreach ($needs as $itemId => $needBase) {
            $allocations = $this->allocator->allocate((int) $itemId, (float) $needBase);
            $takenTotal = 0.0;

            foreach ($allocations as $allocation) {
                $batch = $allocation['batch'];
                $take = (float) $allocation['take'];

                if ($take <= 0) {
                    continue;
                }

                $takenTotal += $take;

                $batch->qty_on_hand_base = max(0, (float) $batch->qty_on_hand_base - $take);
                if ($batch->qty_on_hand_base <= 0.000001) {
                    $batch->qty_on_hand_base = 0;
                    $batch->status = 'DEPLETED';
                }
                $batch->save();

                StockMove::create([
                    'moved_at' => now(),
                    'item_id' => $itemId,
                    'batch_id' => $batch->id,
                    'qty_base' => -$take,
                    'type' => 'CONSUMPTION',
                    'ref_type' => $refType,
                    'ref_id' => $refId,
                    'created_by' => Auth::id(),
                    'note' => $note ?: strtoupper($refType) . ' #' . $refId,
                ]);

                $cogs += $take * (float) $batch->unit_cost_base;
            }

            if (abs($takenTotal - $needBase) > 0.000001) {
                throw new \RuntimeException('Stok tidak cukup untuk item id: ' . $itemId);
            }

            $consumedItems[] = [
                'item_id' => (int) $itemId,
                'qty_base' => (float) $needBase,
            ];
        }

        return [
            'cogs' => $cogs,
            'items' => $consumedItems,
        ];
    }
}
