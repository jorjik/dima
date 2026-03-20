<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('header_title', 200)->default('Альбом жизни');
            $table->text('header_tagline')->nullable();
            $table->string('header_background_path')->nullable();
            $table->timestamps();
        });

        // Ensure there's always a single settings row with ID=1.
        if (! DB::table('site_settings')->where('id', 1)->exists()) {
            DB::table('site_settings')->insert([
                'id' => 1,
                'header_title' => 'Альбом жизни',
                'header_tagline' => null,
                'header_background_path' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

