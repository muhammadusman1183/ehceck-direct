<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();

            $table->string('reference')->unique();

            // customer-side plaid item
            $table->string('plaid_item_id')->nullable();
            $table->text('plaid_access_token')->nullable();
            $table->string('plaid_account_id')->nullable();

            // balance verification snapshot
            $table->string('balance_status')->default('not_checked'); // not_checked|insufficient|sufficient|unavailable
            $table->json('balance_json')->nullable();

            // payment lifecycle
            $table->string('status')->default('pending'); // pending|cleared|rejected
            $table->string('decision_reason')->nullable();

            $table->unsignedBigInteger('amount_cents');
            $table->string('currency', 3)->default('USD');

            $table->timestamps();

            $table->index(['merchant_id','status']);
            $table->index(['invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
