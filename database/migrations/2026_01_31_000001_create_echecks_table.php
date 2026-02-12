<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schema::create('echecks', function (Blueprint $table) {
        //     $table->id();

        //     // IMPORTANT: must match merchants.id type (Laravel default is big int)
        //     $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();

        //     // Customer info
        //     $table->string('customer_name');
        //     $table->string('customer_email')->nullable();
        //     $table->string('customer_phone')->nullable();

        //     // eCheck amount
        //     $table->decimal('amount', 12, 2);

        //     // Plaid + bank info
        //     $table->string('plaid_item_id')->nullable();
        //     $table->string('plaid_access_token')->nullable(); // For demo only (encrypt or store securely later)
        //     $table->string('plaid_account_id')->nullable();

        //     $table->string('bank_name')->nullable();
        //     $table->string('bank_mask')->nullable();
        //     $table->string('account_subtype')->nullable();

        //     $table->string('routing_number')->nullable();
        //     $table->string('account_last4')->nullable();

        //     // status lifecycle
        //     $table->enum('status', ['pending','processing','cleared','rejected'])->default('pending');
        //     $table->json('verification_json')->nullable();

        //     $table->timestamps();
        // });
        Schema::create('echecks', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('merchant_id')
                  ->constrained('merchants')
                  ->cascadeOnDelete();
        
            // Customer
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
        
            // Amount
            $table->decimal('amount', 12, 2);
            $table->string('amount_words')->nullable();
        
            // Check metadata
            $table->string('check_number')->nullable();
            $table->date('authorization_date')->nullable();
        
            // Plaid (verification only)
            $table->string('plaid_item_id')->nullable();
            $table->string('plaid_access_token')->nullable(); // encrypt later
            $table->string('plaid_account_id')->nullable();
        
            // Bank
            $table->string('bank_name')->nullable();
            $table->string('bank_mask')->nullable();
            $table->string('account_subtype')->nullable();
        
            $table->string('routing_number_encrypted')->nullable();
            $table->string('account_last4')->nullable();
        
            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'cleared',
                'rejected'
            ])->default('pending');
        
            $table->json('verification_json')->nullable();
        
            $table->timestamps();
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('echecks');
    }
};
