<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('micro_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_bank_id')->constrained('customer_banks')->cascadeOnDelete();
            $table->integer('amount_cents');
            $table->string('status')->default('pending'); // pending|matched|failed
            $table->integer('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('micro_deposits');
    }
};
