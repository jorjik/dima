<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id')->nullable();

            $table->string('title');
            $table->string('slug')->unique();

            $table->longText('body_markdown')->nullable();

            $table->unsignedBigInteger('cover_media_id')->nullable();

            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['folder_id']);
            $table->index(['cover_media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

