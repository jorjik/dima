<?php

namespace Database\Factories;

use App\Models\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaFile>
 */
class MediaFileFactory extends Factory
{
    protected $model = MediaFile::class;

    public function definition(): array
    {
        return [
            'post_id' => null,
            'media_type' => MediaFile::TYPE_IMAGE,
            'path' => 'photos/' . fake()->uuid() . '.jpg',
            'original_name' => fake()->word() . '.jpg',
            'mime' => 'image/jpeg',
            'size_bytes' => fake()->numberBetween(1000, 5000000),
            'width' => fake()->optional()->numberBetween(200, 4000),
            'height' => fake()->optional()->numberBetween(200, 4000),
            'sort' => 0,
        ];
    }
}
