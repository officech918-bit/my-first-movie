<?php 
function create_thumbnail($source, $destination, $thumb_width) {
    // 1. VALIDATION
    if (!file_exists($source)) {
        error_log("Thumbnail creation failed: Source file does not exist at '{$source}'");
        return false;
    }
    if (!is_readable($source)) {
        error_log("Thumbnail creation failed: Source file is not readable at '{$source}'");
        return false;
    }

    $size = @getimagesize($source);
    if ($size === false) {
        error_log("Thumbnail creation failed: Could not get image size. '{$source}' may be corrupt or not a valid image.");
        return false;
    }

    list($width, $height, $image_type) = $size;

    // 2. IMAGE LOADING
    $image = null;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = @imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_GIF:
            $image = @imagecreatefromgif($source);
            break;
        case IMAGETYPE_PNG:
            $image = @imagecreatefrompng($source);
            break;
        default:
            error_log("Thumbnail creation failed: Unsupported image type ('" . image_type_to_mime_type($image_type) . "') for file '{$source}'");
            return false;
    }

    if (!$image) {
        error_log("Thumbnail creation failed: imagecreatefromjpeg/gif/png failed for '{$source}'. The file may be corrupt.");
        return false;
    }

    // 3. CROPPING & RESIZING
    $x = 0;
    $y = 0;
    if ($width > $height) {
        $x = ceil(($width - $height) / 2);
        $width = $height;
    } elseif ($height > $width) {
        $y = ceil(($height - $width) / 2);
        $height = $width;
    }

    $new_image = imagecreatetruecolor($thumb_width, $thumb_width);
    if (!$new_image) {
        error_log("Thumbnail creation failed: imagecreatetruecolor() failed.");
        imagedestroy($image);
        return false;
    }

    imagecopyresampled($new_image, $image, 0, 0, $x, $y, $thumb_width, $thumb_width, $width, $height);

    // 4. SAVING THE THUMBNAIL
    $success = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($new_image, $destination, 90); // Quality 90
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($new_image, $destination);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($new_image, $destination, 9); // Compression level 9
            break;
    }

    if (!$success) {
        error_log("Thumbnail creation failed: Could not save the new image to '{$destination}'. Check directory permissions.");
    }

    // 5. CLEANUP
    imagedestroy($image);
    imagedestroy($new_image);

    return $success;
}

//get image extension
function get_image_extension($name)	{
	$name = strtolower($name);
	$i = strrpos($name, ".");
	if (!$i){ return ""; }
		$l = strlen($name) - $i;
	$extension = substr($name, $i+1, $l);
	return $extension;	
}
?>