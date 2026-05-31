<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('site_settings', 'social_tiktok')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->string('social_tiktok')->nullable()->after('social_whatsapp');
            });
        }
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('social_tiktok');
        });
    }
};
