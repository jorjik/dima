<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable();

            $table->string('media_type', 10); // image | video | audio

            // Path is relative to the `public` disk.
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            // Only for images.
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['post_id']);
            $table->index(['media_type']);
            $table->unique(['post_id', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};

