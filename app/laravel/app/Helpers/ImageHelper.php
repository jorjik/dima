<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    private static ?ImageManager $manager = null;

    private const QUALITY_MAP = [
        'image/png'  => 80,
        'image/jpeg' => 85,
        'image/webp' => 85,
    ];

    /**
     * Resize an image so the longest side does not exceed $maxDimension pixels
     * and recompress to reduce file size.
     * Aspect ratio is preserved. Only scales down (never up).
     * Overwrites the original file.
     *
     * @return int|false Bytes saved, or false on failure
     */
    public static function resizeToMaxWidth(string $fullPath, int $maxDimension = 1900): int|false
    {
        if (! is_file($fullPath)) {
            return false;
        }

        $mime = @mime_content_type($fullPath);
        if ($mime === false || ! str_starts_with((string) $mime, 'image/')) {
            return false;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (in_array($ext, ['svg', 'ico'], true)) {
            return false;
        }

        try {
            if (self::$manager === null) {
                self::$manager = new ImageManager(new Driver());
            }

            $originalSize = filesize($fullPath);
            $image = self::$manager->read($fullPath);
            $width = $image->width();
            $height = $image->height();

            $needsResize = $width > $maxDimension || $height > $maxDimension;
            if ($needsResize) {
                $image->resizeDown(width: $maxDimension, height: $maxDimension);
            }

            $quality = self::QUALITY_MAP[$mime] ?? 85;
            $image->save(quality: $quality);

            clearstatcache(true, $fullPath);
            $newSize = filesize($fullPath);
            $saved = max(0, $originalSize - $newSize);

            if ($saved > 0) {
                Log::info('ImageHelper: compressed', [
                    'path' => basename($fullPath),
                    'mime' => $mime,
                    'before' => $originalSize,
                    'after' => $newSize,
                    'saved' => $saved,
                    'resized' => $needsResize,
                ]);
            }

            return $saved;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}
