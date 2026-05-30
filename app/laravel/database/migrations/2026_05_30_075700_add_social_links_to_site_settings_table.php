<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('social_telegram')->nullable()->after('hero_background_opacity');
            $table->string('social_vk')->nullable()->after('social_telegram');
            $table->string('social_instagram')->nullable()->after('social_vk');
            $table->string('social_youtube')->nullable()->after('social_instagram');
            $table->string('social_whatsapp')->nullable()->after('social_youtube');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'social_telegram',
                'social_vk',
                'social_instagram',
                'social_youtube',
                'social_whatsapp'
            ]);
        });
    }
};
