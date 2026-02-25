<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestimonialPhotoProcessor
{
    public const TARGET_WIDTH = 1280;

    public const TARGET_HEIGHT = 1040;

    public const WEBP_QUALITY = 85;

    public function storeAsWebp(UploadedFile $photoUpload, string $directory = 'testimonials/photos'): string
    {
        if (! function_exists('imagewebp')) {
            return $photoUpload->store($directory, 'public');
        }

        $realPath = $photoUpload->getRealPath();

        if (! is_string($realPath) || $realPath === '') {
            return $photoUpload->store($directory, 'public');
        }

        $imageInfo = getimagesize($realPath);

        if ($imageInfo === false) {
            return $photoUpload->store($directory, 'public');
        }

        $mimeType = $imageInfo['mime'] ?? null;

        if (! is_string($mimeType) || $mimeType === '') {
            return $photoUpload->store($directory, 'public');
        }

        $sourceImage = $this->createImageResource($realPath, $mimeType);

        if (! is_resource($sourceImage) && ! $sourceImage instanceof \GdImage) {
            return $photoUpload->store($directory, 'public');
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $crop = $this->calculateCropArea($sourceWidth, $sourceHeight);

        $destinationImage = imagecreatetruecolor(self::TARGET_WIDTH, self::TARGET_HEIGHT);

        if ($destinationImage === false) {
            imagedestroy($sourceImage);

            return $photoUpload->store($directory, 'public');
        }

        imagealphablending($destinationImage, false);
        imagesavealpha($destinationImage, true);
        $transparent = imagecolorallocatealpha($destinationImage, 255, 255, 255, 127);
        imagefilledrectangle($destinationImage, 0, 0, self::TARGET_WIDTH, self::TARGET_HEIGHT, $transparent);

        imagecopyresampled(
            $destinationImage,
            $sourceImage,
            0,
            0,
            $crop['x'],
            $crop['y'],
            self::TARGET_WIDTH,
            self::TARGET_HEIGHT,
            $crop['width'],
            $crop['height']
        );

        ob_start();
        $wasSaved = imagewebp($destinationImage, null, self::WEBP_QUALITY);
        $webpBinary = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($destinationImage);

        if (! $wasSaved || ! is_string($webpBinary) || $webpBinary === '') {
            return $photoUpload->store($directory, 'public');
        }

        Storage::disk('public')->makeDirectory($directory);
        $filePath = $directory.'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($filePath, $webpBinary);

        return $filePath;
    }

    /**
     * @return array{x: int, y: int, width: int, height: int}
     */
    private function calculateCropArea(int $sourceWidth, int $sourceHeight): array
    {
        $targetRatio = self::TARGET_WIDTH / self::TARGET_HEIGHT;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $cropX = (int) floor(($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) floor(($sourceHeight - $cropHeight) / 2);
        }

        return [
            'x' => $cropX,
            'y' => $cropY,
            'width' => max(1, $cropWidth),
            'height' => max(1, $cropHeight),
        ];
    }

    private function createImageResource(string $realPath, string $mimeType): mixed
    {
        return match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($realPath),
            'image/png' => imagecreatefrompng($realPath),
            'image/webp' => imagecreatefromwebp($realPath),
            default => null,
        };
    }
}
