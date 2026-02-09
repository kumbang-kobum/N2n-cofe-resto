<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemBatch;
use App\Models\StockMove;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpiredController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $expiredBatches = ItemBatch::query()
            ->with('item')
            ->where('status', 'ACTIVE') // ← INI YANG FIX
            ->where('qty_on_hand_base', '>', 0)
            ->whereDate('expired_at', '<', now()->toDateString())
            ->when($q, function ($query) use ($q) {
                $query->whereHas('item', function ($iq) use ($q) {
                    $iq->where('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('expired_at', 'asc')
            ->paginate(30)
            ->withQueryString();

        return view('admin.expired.index', compact('expiredBatches', 'q'));
    }

    public function dispose(Request $request, int $batchId)
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($batchId, $request) {
            $batch = ItemBatch::lockForUpdate()->with('item')->findOrFail($batchId);

            if ($batch->status !== 'ACTIVE') {
                abort(400, 'Batch tidak aktif.');
            }

            if ($batch->qty_on_hand_base <= 0) {
                abort(400, 'Stok batch sudah habis.');
            }

            if ($batch->expired_at >= now()->toDateString()) {
                abort(400, 'Batch belum expired.');
            }

            $qtyToDispose = (float) $batch->qty_on_hand_base;

            StockMove::create([
                'moved_at' => now(),
                'item_id' => $batch->item_id,
                'batch_id' => $batch->id,
                'qty_base' => -$qtyToDispose,
                'type' => 'EXPIRED_DISPOSAL',
                'ref_type' => 'expired_disposal',
                'ref_id' => $batch->id,
                'created_by' => auth()->id(),
                'note' => $request->note,
            ]);

            AuditLog::log(auth()->id(), 'STOCK_EXPIRED_DISPOSAL', $batch, [
                'item_id' => $batch->item_id,
                'item_name' => $batch->item?->name,
                'qty_base' => -$qtyToDispose,
                'reason' => 'expired_disposal',
                'note' => $request->note,
            ]);

            $batch->qty_on_hand_base = 0;
            $batch->status = 'EXPIRED';
            $batch->save();
        });

        return redirect()
            ->route('admin.expired.index')
            ->with('status', 'Barang expired berhasil dibuang.');
    }
}
