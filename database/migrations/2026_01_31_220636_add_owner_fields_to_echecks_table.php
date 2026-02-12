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
        Schema::table('echecks', function (Blueprint $table) {
            $table->string('account_holder_name')->nullable()->after('customer_phone');
            $table->string('account_holder_address1')->nullable()->after('account_holder_name');
            $table->string('account_holder_address2')->nullable()->after('account_holder_address1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('echecks', function (Blueprint $table) {
            $table->dropColumn([
                'account_holder_name',
                'account_holder_address1',
                'account_holder_address2',
            ]);
        });
    }
};
