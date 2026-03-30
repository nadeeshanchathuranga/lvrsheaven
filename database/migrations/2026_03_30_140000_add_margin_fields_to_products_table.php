<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('margin_percentage', 8, 2)->default(0)->after('cost_price');
            $table->decimal('margin_price', 10, 2)->default(0)->after('margin_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['margin_percentage', 'margin_price']);
        });
    }
};
