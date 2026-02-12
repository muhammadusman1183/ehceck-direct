<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');

            $table->string('processor')->nullable();
            $table->string('processor_payout_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('payouts');
    }
};
