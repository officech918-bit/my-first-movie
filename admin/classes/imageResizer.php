<?php
declare(strict_types=1);

class ImageResizer
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/gif', 'image/png'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    public function createThumbnail(string $source, string $destination, int $thumb_width): bool
    {
        if (!file_exists($source) || filesize($source) > self::MAX_FILE_SIZE) {
            throw new \RuntimeException("File does not exist or is too large.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($source);

        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new \RuntimeException("Invalid file type.");
        }

        [$width, $height] = getimagesize($source);
        $x = 0;
        $y = 0;

        if ($width > $height) {
            $x = (int)ceil(($width - $height) / 2);
            $width = $height;
        } elseif ($height > $width) {
            $y = (int)ceil(($height - $width) / 2);
            $height = $width;
        }

        $new_image = imagecreatetruecolor($thumb_width, $thumb_width);
        if ($new_image === false) {
            throw new \RuntimeException('Cannot initialize new GD image stream');
        }

        $image = $this->createImageFromSource($source, $mime);

        imagecopyresampled($new_image, $image, 0, 0, $x, $y, $thumb_width, $thumb_width, $width, $height);

        $this->saveImage($new_image, $destination, $mime);

        imagedestroy($new_image);
        imagedestroy($image);

        return true;
    }

    private function createImageFromSource(string $source, string $mime)
    {
        switch ($mime) {
            case 'image/jpeg':
                return imagecreatefromjpeg($source);
            case 'image/gif':
                return imagecreatefromgif($source);
            case 'image/png':
                return imagecreatefrompng($source);
            default:
                throw new \RuntimeException("Unsupported image type.");
        }
    }

    private function saveImage($image, string $destination, string $mime): void
    {
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($image, $destination);
                break;
            case 'image/gif':
                imagegif($image, $destination);
                break;
            case 'image/png':
                imagepng($image, $destination);
                break;
        }
    }
}
?>