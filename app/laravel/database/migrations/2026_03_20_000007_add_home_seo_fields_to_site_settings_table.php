<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('home_meta_title', 200)->nullable()->after('home_hero_background_path');
            $table->text('home_meta_description')->nullable()->after('home_meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'home_meta_title',
                'home_meta_description',
            ]);
        });
    }
};
