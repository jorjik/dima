<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageHelper
{
    private static ?ImageManager $manager = null;

    /**
     * Resize an image so the longest side does not exceed $maxDimension pixels.
     * Aspect ratio is preserved. Only scales down (never up).
     * Overwrites the original file.
     */
    public static function resizeToMaxWidth(string $fullPath, int $maxDimension = 1900): void
    {
        if (! is_file($fullPath)) {
            return;
        }

        $mime = @mime_content_type($fullPath);
        if ($mime === false || ! str_starts_with((string) $mime, 'image/')) {
            return;
        }

        // Skip non-raster images (SVG, etc.)
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (in_array($ext, ['svg', 'ico'], true)) {
            return;
        }

        try {
            if (self::$manager === null) {
                self::$manager = new ImageManager(new Driver());
            }

            $image = self::$manager->read($fullPath);
            $width = $image->width();
            $height = $image->height();

            // Only resize if the longest side exceeds the max dimension
            if ($width <= $maxDimension && $height <= $maxDimension) {
                return;
            }

            $image->resizeDown(width: $maxDimension, height: $maxDimension);
            $image->save();
        } catch (\Throwable $e) {
            // Fail silently — don't crash the upload if image processing fails
            report($e);
        }
    }
}
