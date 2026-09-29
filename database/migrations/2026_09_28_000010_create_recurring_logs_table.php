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
        Schema::create('recurring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recurring_id')->constrained('recurring_transactions')->cascadeOnDelete();
            $table->date('run_date');
            $table->string('status'); // 'success', 'failed'
            $table->text('message')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['recurring_id', 'run_date']);
            $table->index(['user_id', 'run_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_logs');
    }
};
