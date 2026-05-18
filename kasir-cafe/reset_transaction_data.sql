-- ====================================================================
-- Query untuk mengosongkan data transaksi (penjualan, pembelian, stok opname)
-- Data master tetap dipertahankan (karyawan, menu, bahan, resep, pengaturan)
-- Stok batch dihitung ulang dari stock_moves yang tersisa
-- ====================================================================

START TRANSACTION;

-- 1. Hapus audit log yang terkait transaksi
DELETE FROM audit_logs
WHERE auditable_type IN (
    'App\\Models\\Sale',
    'App\\Models\\SaleLine',
    'App\\Models\\SaleRefund',
    'App\\Models\\SaleRefundLine',
    'App\\Models\\Purchase',
    'App\\Models\\PurchaseLine',
    'App\\Models\\StockOpname',
    'App\\Models\\StockOpnameLine'
);

-- 2. Hapus SEMUA pergerakan stok (untuk mengosongkan semua stok)
DELETE FROM stock_moves;

-- 3. Hapus semua transaksi dengan cascade delete untuk child records
DELETE FROM stock_opname_lines;
DELETE FROM stock_opnames;
DELETE FROM purchase_lines;
DELETE FROM purchases;
DELETE FROM sale_refund_lines;
DELETE FROM sale_refunds;
DELETE FROM sale_lines;
DELETE FROM sales;

-- 3.5. Hapus batches yang orphaned (tidak ada stock_moves terkait)
DELETE FROM item_batches
WHERE id NOT IN (
    SELECT DISTINCT batch_id FROM stock_moves
)
AND qty_on_hand_base = 0;

-- 4. Hitung ulang stok batch dari stock_moves yang tersisa
UPDATE item_batches b
LEFT JOIN (
    SELECT batch_id, SUM(qty_base) AS qty_total
    FROM stock_moves
    GROUP BY batch_id
) sm ON sm.batch_id = b.id
SET
    b.qty_on_hand_base = GREATEST(COALESCE(sm.qty_total, 0), 0),
    b.status = CASE
        WHEN b.status = 'EXPIRED' THEN 'EXPIRED'
        WHEN COALESCE(sm.qty_total, 0) <= 0.000001 THEN 'DEPLETED'
        ELSE 'ACTIVE'
    END;

COMMIT;

-- ====================================================================
-- Cara menjalankan:
-- ====================================================================
-- 1. Via phpMyAdmin: Copy-paste query ini ke tab SQL
-- 2. Via MySQL CLI: mysql -u username -p database_name < reset_transaction_data.sql
-- 3. Via Laravel Tinker:
--    php artisan tinker
--    DB::unprepared(file_get_contents('reset_transaction_data.sql'));
-- ====================================================================
