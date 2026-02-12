<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('echecks', function (Blueprint $table) {
            if (!Schema::hasColumn('echecks', 'routing_number')) {
                $table->string('routing_number')->nullable()->after('account_subtype');
            }

            if (!Schema::hasColumn('echecks', 'account_last4')) {
                $table->string('account_last4')->nullable()->after('routing_number');
            }

            if (!Schema::hasColumn('echecks', 'verification_json')) {
                $table->json('verification_json')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('echecks', function (Blueprint $table) {
            if (Schema::hasColumn('echecks', 'routing_number')) {
                $table->dropColumn('routing_number');
            }
            if (Schema::hasColumn('echecks', 'account_last4')) {
                $table->dropColumn('account_last4');
            }
            if (Schema::hasColumn('echecks', 'verification_json')) {
                $table->dropColumn('verification_json');
            }
        });
    }
};
