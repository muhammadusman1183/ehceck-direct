<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('plaid_item_id')->nullable();
            $table->text('plaid_access_token')->nullable();
            $table->string('plaid_account_id')->index();

            $table->string('name')->nullable();
            $table->string('mask')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_subtype')->nullable();

            $table->text('routing_number')->nullable();
            $table->text('account_number')->nullable();

            $table->timestamps();

            $table->unique(['customer_id','plaid_account_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('customer_banks');
    }
};
