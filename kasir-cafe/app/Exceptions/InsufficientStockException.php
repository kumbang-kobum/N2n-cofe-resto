<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public int $itemId;
    public float $shortageBase;

    public function __construct(int $itemId, float $shortageBase)
    {
        parent::__construct("Stok tidak cukup untuk item_id={$itemId} (kurang {$shortageBase}).");
        $this->itemId = $itemId;
        $this->shortageBase = $shortageBase;
    }
}
