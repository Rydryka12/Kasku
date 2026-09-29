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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_source_id')->constrained('account_sources')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('type'); // 'income', 'expense'
            $table->bigInteger('amount'); // Satuan rupiah integer > 0
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('transfer_id')->nullable()->constrained('transfers')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type', 'date']);
            $table->index(['account_source_id']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
