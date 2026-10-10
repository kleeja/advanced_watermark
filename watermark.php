<?php
/**
 *
 * @package advanced_watermark
 * @copyright (c) 2007-2026 Kleeja.net
 * @license ./docs/license.txt
 *
 */

//no for directly open
if (!defined('IN_COMMON')) {
    exit();
}

/**
 * This helper is used to make a watermark on a given image,
 * return nothing because if it work then ok , and if not then ok too :)
 *
 * @param string $name the path of the image
 * @param string $ext  its extension
 */
function adv_helper_watermark($name, $ext)
{
    global $config;

    //is this file really exsits ?
    if (!file_exists($name)) {
        return;
    }

    $is_image_type = ($config['watermark_type'] ?? 'image') !== 'text';
    $path_to_watermark = PATH . ltrim(htmlspecialchars_decode($config['watermark_image_path'] ?? ''), '/');

    if ($is_image_type) {
        $src_logo = false;

        if (is_file($path_to_watermark)) {
            $watermark_ext = str_replace('jpg', 'jpeg', strtolower(pathinfo($path_to_watermark, PATHINFO_EXTENSION)));

            if (in_array($watermark_ext, ['gif', 'png', 'jpeg'])) {
                $func = "imagecreatefrom{$watermark_ext}";
                $src_logo = @$func($path_to_watermark);
            }
        }

        //no watermark pic
        if (!$src_logo) {
            return;
        }
    }

    //if there is imagick lib, then we should use it
    if (extension_loaded('imagick') || class_exists('Imagick')) {
        adv_helper_watermark_imagick($name, $ext, $path_to_watermark);

        return;
    }

    //now, lets work and detect our image extension
    if (stripos($ext, 'jp') !== false) {
        $src_img = @imagecreatefromjpeg($name);
    } elseif (stripos($ext, 'png') !== false) {
        $src_img = @imagecreatefrompng($name);
    } elseif (stripos($ext, 'bmp') !== false) {
        $src_img = @imagecreatefrombmp($name);
    } else {
        //gif needs imagick, GD would keep its first frame only
        return;
    }

    if (!$src_img) {
        return;
    }

    //detect width, height for the image
    $bwidth = imagesx($src_img);
    $bheight = imagesy($src_img);

    if ($is_image_type) {
        //get width, height for the watermark image
        $lwidth = imagesx($src_logo);
        $lheight = imagesy($src_logo);

        //image is not big enough to watermark it
        if ($bwidth <= $lwidth + 15 || $bheight <= $lheight + 15) {
            return;
        }

        //where exactly do we have to make the watermark ..
        [$src_x, $src_y] = adv_get_watermark_position($bwidth, $bheight, $lwidth, $lheight, 'image');

        //make it now, watermark it
        imagealphablending($src_img, true);
        imagecopy($src_img, $src_logo, $src_x, $src_y, 0, 0, $lwidth, $lheight);
    } else {
        $text = adv_watermark_text();

        if ($text === '') {
            return;
        }

        $font_path = adv_watermark_font_path();
        $text_size = (int) ($config['watermark_text_size'] ?? 0) > 0 ? (int) $config['watermark_text_size'] : 15;

        $ttfsize = @imagettfbbox($text_size, 0, $font_path, $text);

        if (!$ttfsize) {
            return;
        }

        $lwidth = abs($ttfsize[4] - $ttfsize[0]);
        $lheight = abs($ttfsize[5] - $ttfsize[1]);

        //image is not big enough to watermark it
        if ($bwidth <= $lwidth + 10 || $bheight <= $lheight + 15) {
            return;
        }

        //where exactly do we have to make the watermark ..
        [$src_x, $src_y] = adv_get_watermark_position($bwidth, $bheight, $lwidth, $lheight);

        //color
        $color = adv_convert_color($config['watermark_text_color'] ?? '', [255, 255, 255]);
        $font_color = imagecolorallocate($src_img, $color[0], $color[1], $color[2]);

        //background
        if (!empty($config['watermark_text_background_enable'])) {
            $background = adv_convert_color($config['watermark_text_background'] ?? '', [204, 204, 204]);
            $font_bkg = imagecolorallocate($src_img, $background[0], $background[1], $background[2]);

            // 0, 0 is the top left corner of the image.
            imagefilledrectangle($src_img, $src_x, $src_y - $lheight - 10, $src_x + $lwidth + 5, $src_y, $font_bkg);
        }

        //text
        imagettftext($src_img, $text_size, 0 /** angle */, $src_x + 2, $src_y - 6, $font_color, $font_path, $text);
    }

    if (stripos($ext, 'jp') !== false) {
        //no compression, same quality
        @imagejpeg($src_img, $name, 100);
    } elseif (stripos($ext, 'png') !== false) {
        //PNG is lossless, the default compression keeps the same quality without making the file many times bigger,
        //and the transparent parts stay transparent
        imagesavealpha($src_img, true);
        @imagepng($src_img, $name);
    } elseif (stripos($ext, 'bmp') !== false) {
        @imagebmp($src_img, $name);
    }
}

//
// generate watermarked images by imagick
//
function adv_helper_watermark_imagick($name, $ext, $logo)
{
    global $config;

    $im = new Imagick($name);

    if (($config['watermark_type'] ?? 'image') !== 'text') {
        $watermark = new Imagick($logo);
        $wWidth = $watermark->getImageWidth();
        $wHeight = $watermark->getImageHeight();
        $iWidth = $im->getImageWidth();
        $iHeight = $im->getImageHeight();
        [$x, $y] = adv_get_watermark_position($iWidth, $iHeight, $wWidth, $wHeight, 'image');
    } else {
        $text = adv_watermark_text();

        if ($text === '') {
            return;
        }

        // Create a new drawing palette
        $draw = new ImagickDraw();

        $draw->setFont(adv_watermark_font_path());
        $draw->setFontSize((int) ($config['watermark_text_size'] ?? 0) > 0 ? (int) $config['watermark_text_size'] : 15);
        $draw->setFillColor(
            new ImagickPixel(adv_watermark_hex_color($config['watermark_text_color'] ?? '', '#ffffff')),
        );

        // the corner or the side of the image where the text is written
        $positions = [
            'tl' => Imagick::GRAVITY_NORTHWEST,
            'tc' => Imagick::GRAVITY_NORTH,
            'tr' => Imagick::GRAVITY_NORTHEAST,
            'bl' => Imagick::GRAVITY_SOUTHWEST,
            'bc' => Imagick::GRAVITY_SOUTH,
            'br' => Imagick::GRAVITY_SOUTHEAST,
        ];
        $draw->setGravity($positions[$config['watermark_position'] ?? ''] ?? Imagick::GRAVITY_SOUTHEAST);

        if (!empty($config['watermark_text_background_enable'])) {
            $draw->setTextUnderColor(
                new ImagickPixel(adv_watermark_hex_color($config['watermark_text_background'] ?? '', '#cccccc')),
            );
        }

        $draw->setTextEncoding('UTF-8');
    }

    //an exception for gif image
    //generating thumb with 10 frames only, big gif is a devil
    if ($ext === 'gif') {
        $i = 0;

        foreach ($im as $frame) {
            if (isset($watermark)) {
                $frame->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);
            } else {
                $frame->annotateImage($draw, 0, 0, 0, $text);
            }

            if ($i >= 10) {
                // more than 10 frames, quit it
                break;
            }
            $i++;
        }
        $im->writeImages($name, true);

        return;
    }

    if (isset($watermark)) {
        $im->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);
    } else {
        $im->annotateImage($draw, 10, 10, 0, ' ' . $text . ' ');
    }

    $im->writeImages($name, false);
}

/**
 * the text of the watermark, ready to be drawn: it is saved HTML-encoded,
 * and Arabic letters are joined, as GD and Imagick draw each letter on its own
 * @return string
 */
function adv_watermark_text()
{
    global $config;

    $text = trim(
        preg_replace('/\s+/u', ' ', htmlspecialchars_decode($config['watermark_text_content'] ?? '', ENT_QUOTES)) ?? '',
    );

    //if contains arabic letters, the text stays on one line, Glyphs breaks it after 50 letters by default
    if (preg_match('/\p{Arabic}/u', $text)) {
        include_once __DIR__ . '/Glyphs.php';
        $text = (new I18N_Arabic_Glyphs())->utf8Glyphs($text, 255);
    }

    return $text;
}

/**
 * the font file of the watermark text, the Arial of Kleeja when no other font is chosen
 * @return string
 */
function adv_watermark_font_path()
{
    global $config;

    $font = $config['watermark_text_font'] ?? 'default';

    if (
        $font !== 'default' &&
        isset(advanced_watermark_choices()['font'][$font]) &&
        is_file(__DIR__ . "/{$font}.ttf")
    ) {
        return __DIR__ . "/{$font}.ttf";
    }

    return PATH . 'includes/arial.ttf';
}

/**
 * a saved color as #rrggbb, the versions before 1.2 saved #fff and #ccc by default
 * @param  string $hex
 * @param  string $default
 * @return string
 */
function adv_watermark_hex_color($hex, $default)
{
    $hex = strtolower(trim((string) $hex));

    if (preg_match('/^#([0-9a-f])([0-9a-f])([0-9a-f])$/', $hex, $m)) {
        $hex = "#{$m[1]}{$m[1]}{$m[2]}{$m[2]}{$m[3]}{$m[3]}";
    }

    return preg_match('/^#[0-9a-f]{6}$/', $hex) ? $hex : $default;
}

/**
 * a saved color as [red, green, blue]
 * @param  string $hex
 * @param  array  $default
 * @return array
 */
function adv_convert_color($hex, $default = [255, 255, 255])
{
    $hex = adv_watermark_hex_color($hex, '');

    if ($hex === '') {
        return $default;
    }

    return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
}

/**
 * where the watermark goes: the top left corner of the image,
 * or the bottom left corner of the text with its background
 * @return array [x, y]
 */
function adv_get_watermark_position($bwidth, $bheight, $lwidth, $lheight, $type = 'text')
{
    global $config;

    $position = $config['watermark_position'] ?? '';

    if (!in_array($position, ['tl', 'tc', 'tr', 'bl', 'bc', 'br'])) {
        $position = 'br';
    }

    //left, center or right
    $src_x = [
        'l' => 10,
        'c' => ($bwidth - $lwidth) / 2,
        'r' => $bwidth - ($lwidth + 10),
    ][$position[1]];

    //top or bottom
    if ($position[0] === 't') {
        $src_y = ($type === 'image' ? 0 : $lheight) + 15;
    } else {
        $src_y = $type === 'image' ? $bheight - ($lheight + 5) : $bheight - 5;
    }

    return [(int) round($src_x), (int) round($src_y)];
}
