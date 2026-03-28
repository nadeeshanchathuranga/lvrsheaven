<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_return_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 20)->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('return_date');
            $table->string('reference_no', 100)->nullable();
            $table->enum('reason', ['damaged', 'expired', 'wrong_item', 'overstock', 'other'])->default('other');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_return_notes');
    }
};
