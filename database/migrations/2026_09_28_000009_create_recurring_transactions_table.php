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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_source_id')->constrained('account_sources')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('type'); // 'income', 'expense'
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->string('tax_note')->nullable();
            $table->text('description')->nullable();
            $table->string('frequency'); // 'daily', 'weekly', 'monthly', 'yearly'
            $table->date('start_date');
            $table->date('next_run');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // 'active', 'paused', 'ended'
            $table->timestamps();

            $table->index(['status', 'next_run']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
