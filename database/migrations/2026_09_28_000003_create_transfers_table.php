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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_source_id')->constrained('account_sources')->restrictOnDelete();
            $table->foreignId('to_source_id')->constrained('account_sources')->restrictOnDelete();
            $table->bigInteger('amount'); // Satuan rupiah integer > 0
            $table->bigInteger('admin_fee')->default(0); // Satuan rupiah integer >= 0
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['from_source_id']);
            $table->index(['to_source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
