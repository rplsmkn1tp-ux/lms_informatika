<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoService
{
    /**
     * Auto-trim / crop transparent or white/solid background around a logo image.
     * Leaves approximately 4% margin around the emblem/content.
     *
     * @param string $sourcePath Full path to source image
     * @param string|null $destinationPath Full path to save trimmed image (optional)
     * @param float $marginPercent Margin percentage (default 0.04 = 4%)
     * @return string Path to the trimmed image file
     */
    public function trimImage(string $sourcePath, ?string $destinationPath = null, float $marginPercent = 0.04): string
    {
        if (!file_exists($sourcePath)) {
            return $sourcePath;
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        // SVG files should not be modified
        if ($extension === 'svg') {
            if ($destinationPath && $destinationPath !== $sourcePath) {
                copy($sourcePath, $destinationPath);
                return $destinationPath;
            }
            return $sourcePath;
        }

        if (!extension_loaded('gd')) {
            return $sourcePath;
        }

        $imageContent = file_get_contents($sourcePath);
        if (!$imageContent) {
            return $sourcePath;
        }

        $im = @imagecreatefromstring($imageContent);
        if (!$im) {
            return $sourcePath;
        }

        // Convert palette/indexed images to truecolor
        if (!imageistruecolor($im)) {
            imagepalettetotruecolor($im);
        }

        imagealphablending($im, false);
        imagesavealpha($im, true);

        $width = imagesx($im);
        $height = imagesy($im);

        if ($width <= 10 || $height <= 10) {
            return $sourcePath;
        }

        // Sample corner colors
        $corners = [
            $this->getPixelRgba($im, 0, 0),
            $this->getPixelRgba($im, $width - 1, 0),
            $this->getPixelRgba($im, 0, $height - 1),
            $this->getPixelRgba($im, $width - 1, $height - 1),
        ];

        // Determine background type
        $cornersTransparent = true;
        $cornersWhite = true;
        foreach ($corners as $c) {
            if ($c['alpha'] < 110) {
                $cornersTransparent = false;
            }
            if (!($c['r'] > 240 && $c['g'] > 240 && $c['b'] > 240 && $c['alpha'] < 20)) {
                $cornersWhite = false;
            }
        }

        // Scan pixels to find bounding box of foreground content
        $top = null;
        $bottom = null;
        $left = null;
        $right = null;

        // Step size for speed on very high-res images (adaptive: 1px for <= 500px, 2px for > 500px, 4px for > 2000px)
        $step = max(1, min(4, (int) round(max($width, $height) / 600)));

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $p = $this->getPixelRgba($im, $x, $y);

                $isBg = false;

                if ($p['alpha'] >= 110) {
                    // Transparent pixel
                    $isBg = true;
                } elseif ($p['r'] > 242 && $p['g'] > 242 && $p['b'] > 242 && $p['alpha'] < 20) {
                    // White or near-white pixel
                    $isBg = true;
                } elseif (!$cornersTransparent && !$cornersWhite) {
                    // Check against corner colors (e.g. solid or gradient background)
                    if ($this->matchesCorners($p, $corners)) {
                        $isBg = true;
                    }
                }

                if (!$isBg) {
                    if ($top === null) $top = $y;
                    $bottom = $y;
                    if ($left === null || $x < $left) $left = $x;
                    if ($right === null || $x > $right) $right = $x;
                }
            }
        }

        // If no content found or content covers whole image (within 2%), return original
        if ($top === null || $left === null || $right === null || $bottom === null) {
            return $sourcePath;
        }

        $cw = $right - $left + 1;
        $ch = $bottom - $top + 1;

        // If already filling > 96% of width and height, minimal trimming needed
        if ($cw >= $width * 0.96 && $ch >= $height * 0.96) {
            return $sourcePath;
        }

        // Add margin (~4%)
        $margin = max(4, (int) round(max($cw, $ch) * $marginPercent));

        // Create square crop bounding box centered on content
        $targetDim = max($cw, $ch) + ($margin * 2);
        
        $cropX = (int) round($left - $margin - max(0, ($targetDim - ($cw + $margin * 2)) / 2));
        $cropY = (int) round($top - $margin - max(0, ($targetDim - ($ch + $margin * 2)) / 2));

        // Create a new square canvas with transparent background
        $destCanvas = imagecreatetruecolor($targetDim, $targetDim);
        imagealphablending($destCanvas, false);
        imagesavealpha($destCanvas, true);

        // If the original has a non-transparent background (e.g. solid/gradient), fill with corner color or sample background
        if (!$cornersTransparent && !$cornersWhite) {
            $avgR = (int) round(($corners[0]['r'] + $corners[1]['r'] + $corners[2]['r'] + $corners[3]['r']) / 4);
            $avgG = (int) round(($corners[0]['g'] + $corners[1]['g'] + $corners[2]['g'] + $corners[3]['g']) / 4);
            $avgB = (int) round(($corners[0]['b'] + $corners[1]['b'] + $corners[2]['b'] + $corners[3]['b']) / 4);
            $bgColor = imagecolorallocatealpha($destCanvas, $avgR, $avgG, $avgB, 0);
        } else {
            $bgColor = imagecolorallocatealpha($destCanvas, 0, 0, 0, 127);
        }

        imagefilledrectangle($destCanvas, 0, 0, $targetDim, $targetDim, $bgColor);
        imagealphablending($destCanvas, true);

        // Copy cropped source region into canvas
        $srcX = max(0, $cropX);
        $srcY = max(0, $cropY);
        $srcW = min($width - $srcX, $targetDim);
        $srcH = min($height - $srcY, $targetDim);

        $dstX = max(0, -$cropX);
        $dstY = max(0, -$cropY);

        imagecopy($destCanvas, $im, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH);

        // Determine destination file path
        if (!$destinationPath) {
            $dir = dirname($sourcePath);
            $filename = pathinfo($sourcePath, PATHINFO_FILENAME) . '_trimmed.png';
            $destinationPath = $dir . DIRECTORY_SEPARATOR . $filename;
        }

        // Save as PNG to preserve transparency
        imagealphablending($destCanvas, false);
        imagesavealpha($destCanvas, true);
        imagepng($destCanvas, $destinationPath, 8);

        // GdImage is automatically freed by PHP garbage collection
        return $destinationPath;
    }

    /**
     * Handle logo upload from Livewire:
     * Saves original file to logos/original/ and trimmed file to logos/
     *
     * @param UploadedFile $uploadedFile
     * @return array ['trimmed' => string, 'original' => string] relative storage paths
     */
    public function processUploadedLogo(UploadedFile $uploadedFile): array
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $filename = Str::random(40);

        // 1. Save original file
        $originalName = "{$filename}_original.{$extension}";
        $originalRelativePath = $uploadedFile->storeAs('logos/original', $originalName, 'public');
        $originalFullPath = Storage::disk('public')->path($originalRelativePath);

        // If SVG, trimmed is identical to original
        if ($extension === 'svg') {
            $trimmedRelativePath = "logos/{$filename}.svg";
            Storage::disk('public')->copy($originalRelativePath, $trimmedRelativePath);
            return [
                'trimmed' => $trimmedRelativePath,
                'original' => $originalRelativePath,
            ];
        }

        // 2. Trim image
        $trimmedName = "{$filename}.png";
        $trimmedRelativePath = "logos/{$trimmedName}";
        $trimmedFullPath = Storage::disk('public')->path($trimmedRelativePath);

        // Ensure directory exists
        if (!file_exists(dirname($trimmedFullPath))) {
            mkdir(dirname($trimmedFullPath), 0755, true);
        }

        $this->trimImage($originalFullPath, $trimmedFullPath, 0.04);

        return [
            'trimmed' => $trimmedRelativePath,
            'original' => $originalRelativePath,
        ];
    }

    /**
     * Synchronize public favicon files (favicon.ico, favicon.svg, apple-touch-icon.png)
     * with the newly uploaded/trimmed custom logo.
     *
     * @param string $trimmedRelativePath
     * @return void
     */
    public function syncPublicFavicons(string $trimmedRelativePath): void
    {
        try {
            $fullPath = Storage::disk('public')->path($trimmedRelativePath);
            if (!file_exists($fullPath)) {
                return;
            }

            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            if ($extension === 'svg') {
                @copy($fullPath, public_path('favicon.svg'));
                return;
            }

            if (!extension_loaded('gd')) {
                return;
            }

            $content = file_get_contents($fullPath);
            if (!$content) {
                return;
            }

            $sourceIm = @imagecreatefromstring($content);
            if (!$sourceIm) {
                return;
            }

            // 1. Apple Touch Icon (180x180 PNG)
            $im180 = $this->resizeCanvasSquare($sourceIm, 180);
            if ($im180) {
                @imagepng($im180, public_path('apple-touch-icon.png'));
            }

            // 2. Favicon ICO (16, 32, 48 multi-size)
            $this->generateIcoFromGd($sourceIm, public_path('favicon.ico'), [16, 32, 48]);

            // 3. SVG wrapper for favicon.svg embedding the high-res PNG
            $base64Data = base64_encode($content);
            $mimeType = $extension === 'jpg' || $extension === 'jpeg' ? 'image/jpeg' : ($extension === 'webp' ? 'image/webp' : 'image/png');
            $svgWrapper = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128">
    <image width="128" height="128" href="data:{$mimeType};base64,{$base64Data}" preserveAspectRatio="xMidYMid meet"/>
</svg>
SVG;
            @file_put_contents(public_path('favicon.svg'), $svgWrapper);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to sync public favicons: ' . $e->getMessage());
        }
    }

    /**
     * Restore public favicon files to default Ruang Informatika branding.
     *
     * @return void
     */
    public function restoreDefaultFavicons(): void
    {
        try {
            $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128">
    <defs>
        <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#2A52E2"/>
            <stop offset="100%" stop-color="#1B38A6"/>
        </linearGradient>
    </defs>
    <rect width="128" height="128" rx="28" fill="url(#bgGrad)"/>
    <!-- Left curly brace { -->
    <path d="M 46 36 C 40 36 36 40 36 46 L 36 56 C 36 60 33 63 29 64 C 33 65 36 68 36 72 L 36 82 C 36 88 40 92 46 92 L 48 92 C 50.2 92 52 90.2 52 88 C 52 85.8 50.2 84 48 84 L 46 84 C 44 84 43 83 43 81 L 43 71 C 43 65.5 38.5 64 35 64 C 38.5 64 43 62.5 43 57 L 43 47 C 43 45 44 44 46 44 L 48 44 C 50.2 44 52 42.2 52 40 C 52 37.8 50.2 36 48 36 Z" fill="#FFFFFF"/>
    <!-- Right curly brace } -->
    <path d="M 82 36 C 88 36 92 40 92 46 L 92 56 C 92 60 95 63 99 64 C 95 65 92 68 92 72 L 92 82 C 92 88 88 92 82 92 L 80 92 C 77.8 92 76 90.2 76 88 C 76 85.8 77.8 84 80 84 L 82 84 C 84 84 85 83 85 81 L 85 71 C 85 65.5 89.5 64 93 64 C 89.5 64 85 62.5 85 57 L 85 47 C 85 45 84 44 82 44 L 80 44 C 77.8 44 76 42.2 76 40 C 76 37.8 77.8 36 80 36 Z" fill="#FFFFFF"/>
</svg>
SVG;
            @file_put_contents(public_path('favicon.svg'), $svgContent);

            if (!extension_loaded('gd')) {
                return;
            }

            $brandIm = $this->createDefaultBrandImage(180);
            if ($brandIm) {
                @imagepng($brandIm, public_path('apple-touch-icon.png'));
                $this->generateIcoFromGd($brandIm, public_path('favicon.ico'), [16, 32, 48]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to restore default favicons: ' . $e->getMessage());
        }
    }

    /**
     * Create default brand icon GD image
     */
    public function createDefaultBrandImage(int $size)
    {
        $img = imagecreatetruecolor($size, $size);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        imagealphablending($img, true);

        $blue = imagecolorallocate($img, 35, 71, 196);
        $white = imagecolorallocate($img, 255, 255, 255);
        $radius = (int) round($size * 0.22);

        imagefilledrectangle($img, $radius, 0, $size - 1 - $radius, $size - 1, $blue);
        imagefilledrectangle($img, 0, $radius, $size - 1, $size - 1 - $radius, $blue);
        imagefilledellipse($img, $radius, $radius, $radius * 2, $radius * 2, $blue);
        imagefilledellipse($img, $size - 1 - $radius, $radius, $radius * 2, $radius * 2, $blue);
        imagefilledellipse($img, $radius, $size - 1 - $radius, $radius * 2, $radius * 2, $blue);
        imagefilledellipse($img, $size - 1 - $radius, $size - 1 - $radius, $radius * 2, $radius * 2, $blue);

        $fontFile = 'C:\Windows\Fonts\consola.ttf';
        if (!file_exists($fontFile)) {
            $fontFile = 'C:\Windows\Fonts\arialbd.ttf';
        }

        if (file_exists($fontFile) && function_exists('imagettftext')) {
            $fontSize = (int) round($size * 0.46);
            $bbox = imagettfbbox($fontSize, 0, $fontFile, '{ }');
            $textWidth = abs($bbox[4] - $bbox[0]);
            $textHeight = abs($bbox[5] - $bbox[1]);
            $x = (int) round(($size - $textWidth) / 2) - $bbox[0];
            $y = (int) round(($size + $textHeight) / 2) - 2;
            imagettftext($img, $fontSize, 0, $x, $y, $white, $fontFile, '{ }');
        } else {
            $str = "{ }";
            $font = 5;
            $tw = imagefontwidth($font) * strlen($str);
            $th = imagefontheight($font);
            $x = (int)(($size - $tw) / 2);
            $y = (int)(($size - $th) / 2);
            imagestring($img, $font, $x, $y, $str, $white);
        }

        return $img;
    }

    /**
     * Resamples an image onto a square canvas with transparent background.
     */
    protected function resizeCanvasSquare($sourceIm, int $targetDim)
    {
        $sw = imagesx($sourceIm);
        $sh = imagesy($sourceIm);

        $dest = imagecreatetruecolor($targetDim, $targetDim);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transparent);
        imagealphablending($dest, true);

        $scale = min($targetDim / $sw, $targetDim / $sh);
        $dw = (int) round($sw * $scale);
        $dh = (int) round($sh * $scale);
        $dx = (int) round(($targetDim - $dw) / 2);
        $dy = (int) round(($targetDim - $dh) / 2);

        imagecopyresampled($dest, $sourceIm, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);

        return $dest;
    }

    /**
     * Generate multi-resolution ICO file from GD image
     */
    protected function generateIcoFromGd($sourceIm, string $outputPath, array $sizes = [16, 32, 48]): void
    {
        $images = [];
        foreach ($sizes as $s) {
            $resized = $this->resizeCanvasSquare($sourceIm, $s);
            if (!$resized) continue;

            ob_start();
            imagepng($resized);
            $pngData = ob_get_clean();
            $images[] = [
                'width' => $s,
                'height' => $s,
                'data' => $pngData,
                'size' => strlen($pngData),
            ];
        }

        if (empty($images)) return;

        // ICO header (6 bytes)
        $ico = pack('vvv', 0, 1, count($images));
        $offset = 6 + (count($images) * 16);

        foreach ($images as $img) {
            $w = $img['width'] >= 256 ? 0 : $img['width'];
            $h = $img['height'] >= 256 ? 0 : $img['height'];
            $ico .= pack('CCCCvvVV', $w, $h, 0, 0, 1, 32, $img['size'], $offset);
            $offset += $img['size'];
        }

        foreach ($images as $img) {
            $ico .= $img['data'];
        }

        @file_put_contents($outputPath, $ico);
    }

    /**
     * Helper to get RGBA color values of a pixel
     */
    private function getPixelRgba($im, int $x, int $y): array
    {
        $rgba = imagecolorat($im, $x, $y);
        return [
            'r' => ($rgba >> 16) & 0xFF,
            'g' => ($rgba >> 8) & 0xFF,
            'b' => $rgba & 0xFF,
            'alpha' => ($rgba >> 24) & 0x7F,
        ];
    }

    /**
     * Check if a pixel matches any corner colors or background gradient
     */
    private function matchesCorners(array $pixel, array $corners, int $tolerance = 40): bool
    {
        foreach ($corners as $c) {
            $dr = abs($pixel['r'] - $c['r']);
            $dg = abs($pixel['g'] - $c['g']);
            $db = abs($pixel['b'] - $c['b']);
            $da = abs($pixel['alpha'] - $c['alpha']);

            if ($dr <= $tolerance && $dg <= $tolerance && $db <= $tolerance && $da <= 25) {
                return true;
            }
        }

        // Check if pixel is within the bounding color box of the 4 corners (gradient interpolation)
        $minR = min(array_column($corners, 'r')) - 15;
        $maxR = max(array_column($corners, 'r')) + 15;
        $minG = min(array_column($corners, 'g')) - 15;
        $maxG = max(array_column($corners, 'g')) + 15;
        $minB = min(array_column($corners, 'b')) - 15;
        $maxB = max(array_column($corners, 'b')) + 15;

        if ($pixel['r'] >= $minR && $pixel['r'] <= $maxR &&
            $pixel['g'] >= $minG && $pixel['g'] <= $maxG &&
            $pixel['b'] >= $minB && $pixel['b'] <= $maxB) {
            return true;
        }

        return false;
    }
}
