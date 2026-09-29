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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // null = template default
            $table->string('name');
            $table->string('type'); // 'income', 'expense'
            $table->string('color', 7)->default('#2B3A8C');
            $table->string('icon')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
