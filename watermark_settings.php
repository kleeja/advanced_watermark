<?php
//
// advanced_watermark, its page in the control panel: ?cp=watermark_settings
//

// not for directly open
if (!defined('IN_ADMIN')) {
    exit();
}

//current case
$current_case = g('case', 'str', 'view');

//current template
$stylee = 'admin_watermark_settings';
$styleePath = __DIR__;

// template variables
$action = basename(ADMIN_PATH) . '?cp=watermark_settings';
$H_FORM_KEYS = kleeja_add_form_key('adm_watermark_settings');
$generated_preview_path = false;

include_once __DIR__ . '/watermark.php';

$watermark_choices = advanced_watermark_choices();

switch ($current_case) {
    /**
     * upload a new watermark image
     */
    case 'upload':
        if ((int) $userinfo['founder'] !== 1) {
            kleeja_admin_err($lang['HV_NOT_PRVLG_ACCESS'], $action);
        }

        if (!kleeja_check_form_key('adm_watermark_settings', 1000)) {
            kleeja_admin_err($lang['INVALID_FORM_KEY'], $action);
        }

        $new_image = $_FILES['newimage'] ?? [];

        if (
            empty($new_image['tmp_name']) ||
            !is_string($new_image['tmp_name']) ||
            !is_uploaded_file($new_image['tmp_name'])
        ) {
            kleeja_admin_err($lang['CHOSE_F'], $action);
        }

        $ext = strtolower(pathinfo((string) $new_image['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['png', 'gif', 'jpg', 'jpeg'])) {
            kleeja_admin_err(sprintf($lang['FORBID_EXT'], kleeja_html_encode($ext)), $action);
        }

        // the file has to be an image of that kind, not only be named like one
        $image_info = @getimagesize($new_image['tmp_name']);

        if (empty($image_info) || !in_array($image_info[2], [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_JPEG], true)) {
            kleeja_admin_err($olang['WATERMARK_NOT_IMAGE'], $action);
        }

        $old_image_path = $config['watermark_image_path'] ?? '';
        $uploads_folder = trim($config['foldername'], '/');
        $image_path = $uploads_folder . '/watermark' . mt_rand() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);

        if (!move_uploaded_file($new_image['tmp_name'], PATH . $image_path)) {
            kleeja_admin_err($lang['ERROR_TRY_AGAIN'], $action);
        }

        update_config('watermark_image_path', $image_path);

        // the image that was uploaded here before it, the image of Kleeja in images/ stays
        if (
            $old_image_path !== $image_path &&
            preg_match('/^' . preg_quote($uploads_folder, '/') . '\/watermark\d+\.(png|gif|jpg)$/', $old_image_path) &&
            file_exists(PATH . $old_image_path)
        ) {
            kleeja_unlink(PATH . $old_image_path);
        }

        kleeja_admin_info(sprintf($lang['ITEM_UPDATED'], $olang['WATERMARK_IMAGE_PATH']), $action);

        break;

    /**
     * save the settings, and show them on a sample image when the preview tab was clicked
     */
    case 'update':
        if (!kleeja_check_form_key('adm_watermark_settings', 1000)) {
            kleeja_admin_err($lang['INVALID_FORM_KEY'], $action);
        }

        $choose = function ($name, $list) {
            $value = p($name);

            return isset($list[$value]) ? $value : (string) array_key_first($list);
        };

        // the text is saved as it is written, update_config() encodes it once, and it has to fit in the column
        $text =
            isset($_POST['watermark_text_content']) && is_string($_POST['watermark_text_content'])
                ? trim(preg_replace('/\s+/u', ' ', $_POST['watermark_text_content']) ?? '')
                : '';

        while (mb_strlen(kleeja_html_encode($text)) > 255) {
            $text = mb_substr($text, 0, -1);
        }

        $size = p('watermark_text_size', 'int');

        $settings = [
            'watermark_type' => $choose('watermark_type', $watermark_choices['type']),
            'watermark_position' => $choose('watermark_position', $watermark_choices['position']),
            'watermark_text_content' => $text,
            'watermark_text_size' => (string) ($size > 0 ? min($size, 500) : 15),
            'watermark_text_color' => adv_watermark_hex_color(p('watermark_text_color'), '#ffffff'),
            'watermark_text_background' => adv_watermark_hex_color(p('watermark_text_background'), '#cccccc'),
            'watermark_text_background_enable' => ip('watermark_text_background_enable') ? '1' : '0',
            'watermark_text_font' => $choose('watermark_text_font', $watermark_choices['font']),
        ];

        foreach ($settings as $name => $value) {
            update_config($name, $value);
        }

        if (!ip('preview')) {
            kleeja_admin_info($lang['CONFIGS_UPDATED'], $action);
        }

        $preview_file = trim($config['foldername'], '/') . '/preview_watermark.jpg';

        if (!copy(__DIR__ . '/preview.jpg', PATH . $preview_file)) {
            kleeja_admin_err($lang['ERROR_TRY_AGAIN'], $action);
        }

        adv_helper_watermark(PATH . $preview_file, 'jpg');

        // the file keeps its name, the time makes the browser load the new one
        $generated_preview_path = PATH . $preview_file . '?' . time();

        break;

    /**
     * the settings
     */
    default:
        break;
}

//
// the settings, for the form
//
$watermark_lists = [];

foreach (['type', 'position', 'font'] as $choice) {
    $watermark_lists[$choice] = [];

    foreach ($watermark_choices[$choice] as $value => $title) {
        $watermark_lists[$choice][] = [
            'value' => $value,
            'title' => $olang[$title] ?? $title,
            'selected' => ($config['watermark_' . ($choice === 'font' ? 'text_font' : $choice)] ?? '') === $value,
        ];
    }
}

$watermark_types = $watermark_lists['type'];
$watermark_positions = $watermark_lists['position'];
$watermark_fonts = $watermark_lists['font'];

$watermark_is_text = ($config['watermark_type'] ?? '') === 'text';

// the image of the watermark, it is a path from the root of Kleeja
$watermark_image_path = $config['watermark_image_path'] ?? '';
$watermark_image_exists =
    $watermark_image_path !== '' && is_file(PATH . ltrim(htmlspecialchars_decode($watermark_image_path), '/'));
$watermark_image_url = PATH . ltrim($watermark_image_path, '/');

// the values are saved encoded, so they are printed as they are
$watermark_text_content = $config['watermark_text_content'] ?? '';
$watermark_text_size = (int) ($config['watermark_text_size'] ?? 0) > 0 ? (int) $config['watermark_text_size'] : 15;
$watermark_text_background_enable = !empty($config['watermark_text_background_enable']);

// a color input takes #rrggbb only
$watermark_text_color = adv_watermark_hex_color($config['watermark_text_color'] ?? '', '#ffffff');
$watermark_text_background = adv_watermark_hex_color($config['watermark_text_background'] ?? '', '#cccccc');
