<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('shift_number', 30)->unique()->nullable()->after('id');
            $table->string('status', 10)->default('open')->after('cash_in_drawer'); // open | closed
            $table->decimal('opening_float', 12, 2)->default(0)->after('status');
            $table->decimal('closing_float', 12, 2)->nullable()->after('opening_float');
            $table->text('notes')->nullable()->after('closing_float');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['shift_number', 'status', 'opening_float', 'closing_float', 'notes']);
        });
    }
};
