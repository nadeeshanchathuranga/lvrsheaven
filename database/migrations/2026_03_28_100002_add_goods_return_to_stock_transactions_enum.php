<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'GoodsReturn' to the transaction_type enum
        DB::statement("ALTER TABLE stock_transactions MODIFY COLUMN transaction_type ENUM('Added','Deducted','Sold','Deleted','GRN','GoodsReturn') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_transactions MODIFY COLUMN transaction_type ENUM('Added','Deducted','Sold','Deleted','GRN') NOT NULL");
    }
};
