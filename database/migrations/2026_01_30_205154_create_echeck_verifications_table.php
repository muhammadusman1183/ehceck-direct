<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('echeck_verifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();

            $table->unsignedBigInteger('merchant_id');

            // Customer + amount being verified
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('amount', 10, 2);

            // Plaid fields (customer bank)
            $table->string('plaid_item_id')->nullable();
            $table->text('plaid_access_token')->nullable(); // encrypt later if needed
            $table->string('plaid_account_id')->nullable();

            // Auth numbers (store minimal)
            $table->string('routing_number')->nullable();
            $table->string('account_last4')->nullable();

            // Balance signals
            $table->decimal('available_balance', 12, 2)->nullable();
            $table->decimal('current_balance', 12, 2)->nullable();

            $table->string('status')->default('pending'); // pending|verified|failed
            $table->json('result_json')->nullable();

            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('echeck_verifications');
    }
};
