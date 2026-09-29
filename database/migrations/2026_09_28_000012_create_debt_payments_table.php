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
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('debt_id')->constrained('debts')->cascadeOnDelete();
            $table->foreignId('account_source_id')->constrained('account_sources')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('debt_id');
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};
