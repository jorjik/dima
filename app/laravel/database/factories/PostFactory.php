<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(4),
            'body_markdown' => fake()->optional()->paragraphs(3, true),
            'cover_media_id' => null,
            'sort' => 0,
        ];
    }
}
