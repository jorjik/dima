<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('home_hero_title', 200)->nullable()->after('site_background_path');
            $table->text('home_hero_text')->nullable()->after('home_hero_title');
            $table->string('home_hero_background_path')->nullable()->after('home_hero_text');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'home_hero_title',
                'home_hero_text',
                'home_hero_background_path',
            ]);
        });
    }
};

