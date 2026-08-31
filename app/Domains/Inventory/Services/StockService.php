<?php

namespace App\Domains\Inventory\Services;

use App\Domains\Inventory\Models\BranchStock;
use App\Domains\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Tambah / kurangi stok untuk satu produk di satu cabang.
     */
    public function adjust(
        string $branchId,
        string $productId,
        int    $delta,          // Positif = masuk, negatif = keluar
        string $type,           // 'in' | 'out' | 'transfer_in' | 'transfer_out' | 'adjustment'
        string $reference = '',
        string $notes     = '',
        ?string $userId   = null,
    ): BranchStock {
        return DB::transaction(function () use ($branchId, $productId, $delta, $type, $reference, $notes, $userId) {
            $stock = BranchStock::firstOrCreate(
                ['branch_id' => $branchId, 'product_id' => $productId],
                ['quantity' => 0, 'min_stock' => 5]
            );

            $before = $stock->quantity;
            $stock->increment('quantity', $delta);
            $stock->refresh();

            StockMovement::create([
                'branch_id'    => $branchId,
                'product_id'   => $productId,
                'type'         => $type,
                'quantity'     => $delta,
                'stock_before' => $before,
                'stock_after'  => $stock->quantity,
                'reference'    => $reference,
                'notes'        => $notes,
                'created_by'   => $userId,
            ]);

            return $stock;
        });
    }

    /**
     * Cek produk yang stoknya di bawah minimum (low stock alert).
     */
    public function getLowStockItems(?string $branchId = null)
    {
        $query = BranchStock::with('product', 'branch')
            ->whereColumn('quantity', '<=', 'min_stock');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('quantity')->get();
    }
}
