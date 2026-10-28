<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ProductoFotoService
{
    /** @return array{foto_path: string, foto_miniatura_path: string} */
    public function store(UploadedFile $file): array
    {
        if (! function_exists('imagewebp')) {
            throw ValidationException::withMessages(['foto' => 'El servidor necesita GD con soporte WebP para procesar fotos.']);
        }

        $source = @imagecreatefromstring($file->get());
        if ($source === false) {
            throw ValidationException::withMessages(['foto' => 'No se pudo leer la foto. Prueba con otra imagen JPG, PNG o WebP.']);
        }

        $paths = [
            'foto_path' => 'productos/'.Str::uuid().'.webp',
            'foto_miniatura_path' => 'productos/'.Str::uuid().'-mini.webp',
        ];

        try {
            $source = $this->orient($source, $file);
            foreach (['foto_path' => 1200, 'foto_miniatura_path' => 320] as $key => $size) {
                $bytes = $this->resize($source, $size);
                if (! Storage::disk('public')->put($paths[$key], $bytes)) {
                    throw new RuntimeException('No se pudo guardar la foto del producto.');
                }
            }
        } catch (Throwable $exception) {
            $this->delete($paths);
            throw $exception;
        } finally {
            imagedestroy($source);
        }

        return $paths;
    }

    private function orient(GdImage $image, UploadedFile $file): GdImage
    {
        if ($file->getMimeType() !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }
        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };
        if ($angle !== 0) {
            $rotated = imagerotate($image, $angle, 0);
            if ($rotated !== false) {
                imagedestroy($image);

                return $rotated;
            }
        }

        return $image;
    }

    private function resize(GdImage $source, int $maximum): string
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maximum / max($width, $height));
        $target = imagecreatetruecolor(max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);

        ob_start();
        try {
            $success = imagewebp($target, null, 78);
            $bytes = ob_get_contents();
        } finally {
            ob_end_clean();
            imagedestroy($target);
        }
        if (! $success || ! is_string($bytes) || $bytes === '') {
            throw new RuntimeException('No se pudo optimizar la foto del producto.');
        }

        return $bytes;
    }

    /** @param array{foto_path?: string|null, foto_miniatura_path?: string|null} $paths */
    public function delete(array $paths): void
    {
        $files = array_values(array_filter($paths));
        if ($files !== []) {
            Storage::disk('public')->delete($files);
        }
    }
}
