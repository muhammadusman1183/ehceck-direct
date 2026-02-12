<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('echecks', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('verification_json');
            $table->string('image_path')->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('echecks', function (Blueprint $table) {
            $table->dropColumn(['pdf_path','image_path']);
        });
    }
};
