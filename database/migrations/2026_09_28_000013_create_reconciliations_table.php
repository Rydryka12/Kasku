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
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_source_id')->constrained('account_sources')->cascadeOnDelete();
            $table->bigInteger('app_balance');
            $table->bigInteger('actual_balance');
            $table->bigInteger('difference');
            $table->boolean('adjusted')->default(false);
            $table->foreignId('adjustment_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->date('date');
            $table->timestamps();

            $table->index(['user_id', 'account_source_id']);
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliations');
    }
};
