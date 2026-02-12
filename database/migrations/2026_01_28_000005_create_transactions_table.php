<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');

            $table->enum('status', ['pending', 'processed', 'failed', 'returned'])->default('pending');
            $table->string('reference')->unique();
            $table->string('memo')->nullable();

            $table->string('processor')->nullable(); // e.g., dwolla
            $table->string('processor_transfer_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('transactions');
    }
};
