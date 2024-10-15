<?php
/**
 * @filesource Kotchasan/Image.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Kotchasan;

/**
 * Class Image
 * A class for image manipulation.
 *
 * @see https://www.kotchasan.com/
 */
class Image
{
    /**
     * @var int The image quality (0-100) for JPEG or WEBP images.
     */
    public static $quality = 75;
    /**
     * @var array  Default background color (white)
     */
    public static $backgroundColor = [255, 255, 255];

    /**
     * Crop or resize an image to fit the specified dimensions, with optional watermark,
     * and save as JPEG or WEBP. Supports background color for transparent images.
     *
     * @param string $source The original image file path
     * @param string $target The target file path to save the cropped/resized image
     * @param int $thumbwidth The width of the thumbnail
     * @param int $thumbheight The height of the thumbnail
     * @param string $watermark Optional watermark text to add to the image
     * @param bool $fit If true, resize to fit within the area, if false, crop the image
     * @return bool Success or failure of the image processing
     */
    public static function crop($source, $target, $thumbwidth, $thumbheight, $watermark = '', $fit = false)
    {
        // Load the original image
        $info = getimagesize($source);
        switch ($info['mime']) {
            case 'image/gif':
                $o_im = imageCreateFromGIF($source);
                break;
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
                $o_im = self::orient($source);
                break;
            case 'image/png':
            case 'image/x-png':
                $o_im = imageCreateFromPNG($source);
                break;
            case 'image/webp':
                $o_im = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }

        // Get the original image dimensions
        $o_wd = imagesx($o_im);
        $o_ht = imagesy($o_im);
        $o_ratio = $o_wd / $o_ht;
        $t_ratio = $thumbwidth / $thumbheight;

        // Create the new image with specified dimensions
        $t_im = imageCreateTrueColor($thumbwidth, $thumbheight);

        // Check for transparency in PNG or WEBP images
        if ($info['mime'] == 'image/png' || $info['mime'] == 'image/webp') {
            // Set transparent background for the new image
            imagealphablending($t_im, false);
            imagesavealpha($t_im, true);
            $transparent = imagecolorallocatealpha($t_im, self::$backgroundColor[0], self::$backgroundColor[1], self::$backgroundColor[2], 127);
            imagefill($t_im, 0, 0, $transparent);
        } else {
            // Fill background with the defined color for non-transparent images
            $background_color = imagecolorallocate($t_im, self::$backgroundColor[0], self::$backgroundColor[1], self::$backgroundColor[2]);
            imagefill($t_im, 0, 0, $background_color);
        }

        if ($fit) {
            // Resize the image maintaining aspect ratio, and fill background if needed
            if ($o_ratio > $t_ratio) {
                $new_width = $thumbwidth;
                $new_height = intval($thumbwidth / $o_ratio);
                $x_offset = 0;
                $y_offset = intval(($thumbheight - $new_height) / 2);
            } else {
                $new_height = $thumbheight;
                $new_width = intval($thumbheight * $o_ratio);
                $x_offset = intval(($thumbwidth - $new_width) / 2);
                $y_offset = 0;
            }

            // Resize the image onto the new canvas
            imageCopyResampled($t_im, $o_im, $x_offset, $y_offset, 0, 0, $new_width, $new_height, $o_wd, $o_ht);
        } else {
            // Normal cropping logic
            $wm = $o_wd / $thumbwidth;
            $hm = $o_ht / $thumbheight;
            $h_height = $thumbheight / 2;
            $w_height = $thumbwidth / 2;
            $int_width = 0;
            $int_height = 0;
            $adjusted_width = $thumbwidth;
            $adjusted_height = $thumbheight;

            if ($o_wd > $o_ht) {
                $adjusted_width = ceil($o_wd / $hm);
                $half_width = $adjusted_width / 2;
                $int_width = intval($half_width - $w_height);
                if ($adjusted_width < $thumbwidth) {
                    $adjusted_height = ceil($o_ht / $wm);
                    $half_height = $adjusted_height / 2;
                    $int_height = intval($half_height - $h_height);
                    $adjusted_width = $thumbwidth;
                    $int_width = 0;
                }
            } elseif (($o_wd < $o_ht) || ($o_wd == $o_ht)) {
                $adjusted_height = ceil($o_ht / $wm);
                $half_height = $adjusted_height / 2;
                $int_height = intval($half_height - $h_height);
                if ($adjusted_height < $thumbheight) {
                    $adjusted_width = ceil($o_wd / $hm);
                    $half_width = $adjusted_width / 2;
                    $int_width = intval($half_width - $w_height);
                    $adjusted_height = $thumbheight;
                    $int_height = 0;
                }
            }

            // Resize and crop the image
            imageCopyResampled($t_im, $o_im, -$int_width, -$int_height, 0, 0, $adjusted_width, $adjusted_height, $o_wd, $o_ht);
        }

        // Add watermark if provided
        if (!empty($watermark)) {
            self::watermarkText($t_im, $watermark);
        }

        // Save the resulting image as WEBP or JPEG
        if (preg_match('/.*\.webp$/i', $target)) {
            $result = imagewebp($t_im, $target, self::$quality);
        } else {
            $result = imagejpeg($t_im, $target, self::$quality);
        }

        // Clean up resources
        imageDestroy($t_im);
        imageDestroy($o_im);

        return $result;
    }

    /**
     * Flip an image horizontally.
     *
     * This method flips the specified image horizontally.
     *
     * @param \GdImage $imgsrc The source image resource.
     *
     * @return \GdImage The flipped image resource.
     */
    public static function flip($imgsrc)
    {
        $width = imagesx($imgsrc);
        $height = imagesy($imgsrc);
        $src_x = $width - 1;
        $src_y = 0;
        $src_width = -$width;
        $src_height = $height;
        $imgdest = imagecreatetruecolor($width, $height);
        imageCopyResampled($imgdest, $imgsrc, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height);
        return $imgsrc;
    }

    /**
     * Retrieve image information.
     *
     * This method retrieves the width, height, and MIME type of an image using the `getimagesize()` function.
     *
     * @param string $source The path and filename of the image.
     *
     * @return array|bool An array containing the image properties (width, height, and mime) on success,
     *                    or false if the information cannot be obtained.
     */
    public static function info($source)
    {
        $info = getimagesize($source);
        if (!$info) {
            return false;
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
            'mime' => $info['mime']
        ];
    }

    /**
     * @param $source
     * @return mixed
     */
    public static function orient($source)
    {
        $im = imageCreateFromJPEG($source);

        if (!$im) {
            return false;
        }

        try {
            $exif = exif_read_data($source);
        } catch (\Throwable $th) {
            // exif error
            return $im;
        }

        $orientation = empty($exif['Orientation']) ? 0 : $exif['Orientation'];

        switch ($orientation) {
            case 2:
                // horizontal flip
                $im = self::flip($im);
                break;
            case 3:
                // 180 rotate left
                $im = self::rotateImage($im, 180);
                break;
            case 4:
                // vertical flip
                $im = self::flipImage($im);
                break;
            case 5:
                // vertical flip + 90 rotate left
                $im = self::flipImage($im);
                $im = self::rotateImage($im, -90);
                break;
            case 6:
                // 90 rotate right
                $im = self::rotateImage($im, 90);
                break;
            case 7:
                // horizontal flip + 90 rotate right
                $im = self::flipImage($im);
                $im = self::rotateImage($im, 90);
                break;
            case 8:
                // 90 rotate left
                $im = self::rotateImage($im, -90);
                break;
        }

        return $im;
    }

    /**
     * @param $image
     * @param $angle
     */
    private static function rotateImage($image, $angle)
    {
        return imagerotate($image, $angle, 0);
    }

    /**
     * @param $image
     */
    private static function flipImage($image)
    {
        return self::rotateImage($image, 180);
    }

    /**
     * Resizes an image, applies an optional watermark, and saves it in the specified format.
     *
     * This function takes an image from the source path, resizes it to the specified width while maintaining
     * the aspect ratio, applies a text watermark if provided, and saves the resized image to the target directory
     * with the specified name. The image format can be either JPEG or WebP based on the file extension.
     *
     * @param string $source The full path to the source image file.
     * @param string $target The directory where the resized image will be saved.
     * @param string $name The desired name of the resized image file.
     * @param int $width Optional. The desired width of the resized image in pixels. If set to 0, the image retains its original dimensions.
     * @param string $watermark Optional. A text string to be applied as a watermark on the image. If not provided, no watermark is applied.
     * @param bool $forceConvert Optional. Whether to force the conversion of the image to JPEG or WebP even if no resizing is needed. Default is true.
     *
     * @return array|bool Returns an array with the resized image's details (name, width, height, mime type) if successful, or false on failure.
     */
    public static function resize($source, $target, $name, $width = 0, $watermark = '', $forceConvert = true)
    {
        // Convert the filename to lowercase
        $newname = strtolower($name);
        $ext = '.jpg'; // Default extension
        $mime = 'image/jpeg'; // Default MIME type

        // Check if the file extension is .webp
        if (preg_match('/(.*)\.([a-z]{3,})$/', $newname, $match)) {
            $newname = $match[1];
            if ($match[2] === 'webp') {
                $ext = '.webp';
                $mime = 'image/webp';
            }
        }

        // Get the image info
        $info = getimagesize($source);
        $o_im = null;
        $o_ext = '';

        // Load the image into memory based on its MIME type
        switch ($info['mime']) {
            case 'image/gif':
                $o_im = imagecreatefromgif($source);
                $o_ext = '.gif';
                break;
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
                $o_im = self::orient($source);
                $o_ext = '.jpg';
                break;
            case 'image/png':
            case 'image/x-png':
                $o_im = imagecreatefrompng($source);
                $o_ext = '.png';
                break;
            case 'image/webp':
                $o_im = imagecreatefromwebp($source);
                $o_ext = '.webp';
                break;
            default:
                // Unsupported image type
                return false;
        }

        // Calculate new dimensions if a width is specified
        if ($width > 0 && ($info[0] > $width || $info[1] > $width)) {
            $o_wd = imagesx($o_im);
            $o_ht = imagesy($o_im);
            if ($o_wd <= $o_ht) {
                $h = $width;
                $w = round($h * $o_wd / $o_ht);
            } else {
                $w = $width;
                $h = round($w * $o_ht / $o_wd);
            }
        } elseif (!$forceConvert && $watermark === '' && in_array($info['mime'], ['image/gif', 'image/png', 'image/x-png'])) {
            // No resizing or conversion needed, just copy the file
            copy($source, $target.$newname.$o_ext);
            return [
                'name' => $newname.$o_ext,
                'width' => $info[0],
                'height' => $info[1],
                'mime' => $info['mime']
            ];
        } else {
            // Use the original dimensions
            $w = $info[0];
            $h = $info[1];
            $o_wd = $info[0];
            $o_ht = $info[1];
        }

        // Create a new true color image
        $t_im = ImageCreateTrueColor($w, $h);

        // Check for transparency in PNG or WEBP images
        if ($info['mime'] == 'image/png' || $info['mime'] == 'image/webp') {
            // Set transparent background for the new image
            imagealphablending($t_im, false);
            imagesavealpha($t_im, true);
            $transparent = imagecolorallocatealpha($t_im, 255, 255, 255, 127);
            imagefill($t_im, 0, 0, $transparent);
        } else {
            // Fill background with the defined color for non-transparent images
            $background_color = imagecolorallocate($t_im, self::$backgroundColor[0], self::$backgroundColor[1], self::$backgroundColor[2]);
            imagefill($t_im, 0, 0, $background_color);
        }

        // Resize the image
        imageCopyResampled($t_im, $o_im, 0, 0, 0, 0, $w + 1, $h + 1, $o_wd, $o_ht);

        // Apply watermark if specified
        if ($watermark != '') {
            $t_im = self::watermarkText($t_im, $watermark);
        }

        // Add the file extension to the new name
        $newname .= $ext;
        $result = false;

        // Save the image based on its MIME type
        if ($mime == 'image/webp') {
            $result = imagewebp($t_im, $target.$newname, self::$quality);
        } else {
            $result = imagejpeg($t_im, $target.$newname, self::$quality);
        }

        // Destroy the image resources to free memory
        imageDestroy($o_im);
        imageDestroy($t_im);

        // Return the new image details or false if failed
        if ($result) {
            return [
                'name' => $newname,
                'width' => $w,
                'height' => $h,
                'mime' => $mime
            ];
        }
        return false;
    }

    /**
     * Add a text watermark to an image.
     *
     * This method adds a text watermark to the specified image. The watermark text, position,
     * color, font size, and opacity can be customized.
     *
     * @param \GdImage $imgsrc The source image resource.
     * @param string $text The text to be used as the watermark.
     * @param string $pos The position of the watermark. Valid values: 'center', 'bottom', 'left', 'right' (default: '').
     * @param string $color The color of the watermark in hexadecimal format (default: 'CCCCCC').
     * @param int $font_size The font size of the watermark (default: 20).
     * @param int $opacity The opacity of the watermark (0-100) (default: 50).
     *
     * @return \GdImage The modified image resource with the added watermark.
     */
    public static function watermarkText($imgsrc, $text, $pos = '', $color = 'CCCCCC', $font_size = 20, $opacity = 50)
    {
        $font = ROOT_PATH.'skin/fonts/leelawad.ttf';
        $offset = 5;
        $alpha_color = imagecolorallocatealpha($imgsrc, hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)), 127 * (100 - $opacity) / 100);
        $box = imagettfbbox($font_size, 0, $font, $text);

        if (preg_match('/center/i', $pos)) {
            $y = $box[1] + (imagesy($imgsrc) / 2) - ($box[5] / 2);
        } elseif (preg_match('/bottom/i', $pos)) {
            $y = imagesy($imgsrc) - $offset;
        } else {
            $y = $box[1] - $box[5] + $offset;
        }

        if (preg_match('/center/i', $pos)) {
            $x = $box[0] + (imagesx($imgsrc) / 2) - ($box[4] / 2);
        } elseif (preg_match('/right/i', $pos)) {
            $x = $box[0] - $box[4] + imagesx($imgsrc) - $offset;
        } else {
            $x = $offset;
        }

        imagettftext($imgsrc, $font_size, 0, $x, $y, $alpha_color, $font, $text);

        return $imgsrc;
    }

    /**
     * Set the image quality for JPEG images.
     *
     * This method sets the quality level (0-100) for JPEG images. Higher quality values
     * result in larger file sizes but better image quality. The default value is 75.
     *
     * @param int $quality The image quality level (0-100).
     */
    public static function setQuality($quality)
    {
        self::$quality = max(0, min($quality, 100));
    }
}
