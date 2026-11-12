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
