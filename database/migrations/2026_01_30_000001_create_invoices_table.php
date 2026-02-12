<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->unsignedBigInteger('amount_cents'); // store cents
            $table->string('currency', 3)->default('USD');

            $table->string('status')->default('draft'); // draft|sent|paid|void
            $table->timestamp('due_at')->nullable();
            $table->text('notes')->nullable();

            $table->string('public_token')->unique(); // public invoice token for customer URL

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
