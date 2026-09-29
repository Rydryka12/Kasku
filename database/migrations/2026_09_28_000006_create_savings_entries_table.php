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
        Schema::create('savings_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->constrained('savings_goals')->cascadeOnDelete();
            $table->foreignId('source_id')->constrained('account_sources')->restrictOnDelete();
            $table->string('type'); // deposit, withdraw
            $table->unsignedBigInteger('amount');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['goal_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_entries');
    }
};
