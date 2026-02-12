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
            Schema::table('echecks', function (Blueprint $table) {
                if (!Schema::hasColumn('echecks', 'account_number')) {
                    $table->string('account_number')->nullable()->after('routing_number');
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('echecks', function (Blueprint $table) {
            if (Schema::hasColumn('echecks', 'account_number')) {
                $table->dropColumn('account_number');
            }
        });
    }
};
