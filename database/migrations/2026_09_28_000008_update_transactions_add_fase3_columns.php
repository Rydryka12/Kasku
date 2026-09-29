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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
            $table->boolean('is_adjustment')->default(false)->after('type');
            $table->unsignedBigInteger('tax_amount')->default(0)->after('amount');
            $table->string('tax_note')->nullable()->after('tax_amount');
            $table->unsignedBigInteger('recurring_id')->nullable()->after('transfer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['is_adjustment', 'tax_amount', 'tax_note', 'recurring_id']);
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }
};
