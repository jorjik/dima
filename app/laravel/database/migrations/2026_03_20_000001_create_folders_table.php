<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('background_media_id')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['background_media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};

