<?php

namespace Tests\Feature;

use App\Models\MediaFile;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCoverUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_cover_url_returns_null_when_no_media(): void
    {
        $post = Post::factory()->create();
        $this->assertNull($post->cover_url);
    }

    public function test_cover_url_returns_cover_media_url_when_set(): void
    {
        $post = Post::factory()->create();

        $cover = MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'covers/photo.jpg',
        ]);

        $post->update(['cover_media_id' => $cover->id]);
        $post->load('cover');

        $expected = $cover->url;
        $this->assertEquals($expected, $post->cover_url);
    }

    public function test_cover_url_falls_back_to_first_image_when_cover_is_video(): void
    {
        $post = Post::factory()->create();

        $videoCover = MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_VIDEO,
            'path' => 'videos/clip.mp4',
        ]);

        $firstImage = MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'photos/first.jpg',
            'sort' => 0,
        ]);

        MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'photos/second.jpg',
            'sort' => 1,
        ]);

        $post->update(['cover_media_id' => $videoCover->id]);
        $post->load('cover', 'images');

        $this->assertEquals($firstImage->url, $post->cover_url);
    }

    public function test_cover_url_falls_back_to_first_image_when_cover_belongs_to_different_post(): void
    {
        $post = Post::factory()->create();
        $otherPost = Post::factory()->create();

        $coverFromOtherPost = MediaFile::factory()->create([
            'post_id' => $otherPost->id,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'photos/other.jpg',
        ]);

        $firstImage = MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'photos/mine.jpg',
            'sort' => 0,
        ]);

        $post->update(['cover_media_id' => $coverFromOtherPost->id]);
        $post->load('cover', 'images');

        $this->assertEquals($firstImage->url, $post->cover_url);
    }

    public function test_cover_url_returns_null_when_only_videos_exist(): void
    {
        $post = Post::factory()->create();

        MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_VIDEO,
            'path' => 'videos/clip.mp4',
        ]);

        $post->load('cover', 'images');

        $this->assertNull($post->cover_url);
    }

    public function test_cover_url_returns_image_url_when_cover_is_correct(): void
    {
        $post = Post::factory()->create();

        $cover = MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'covers/cover.jpg',
            'sort' => 0,
        ]);

        // Additional image that should not be picked
        MediaFile::factory()->create([
            'post_id' => $post->id,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'photos/other.jpg',
            'sort' => 1,
        ]);

        $post->update(['cover_media_id' => $cover->id]);
        $post->load('cover', 'images');

        $this->assertEquals($cover->url, $post->cover_url);
    }
}
