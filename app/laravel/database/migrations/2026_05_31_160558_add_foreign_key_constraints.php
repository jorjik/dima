<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->cascadeOnDelete();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('folder_id')
                ->references('id')
                ->on('folders')
                ->nullOnDelete();

            $table->foreign('cover_media_id')
                ->references('id')
                ->on('media_files')
                ->nullOnDelete();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->foreign('background_media_id')
                ->references('id')
                ->on('media_files')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['cover_media_id']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['background_media_id']);
        });
    }
};
