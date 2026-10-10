<?php
// Kleeja Plugin
// advanced_watermark
// Version: 1.2
// Developer: Kleeja team

// Prevent illegal run
if (!defined('IN_PLUGINS_SYSTEM')) {
    exit();
}

// Plugin Basic Information
$kleeja_plugin['advanced_watermark']['information'] = [
    // The casual name of this plugin, anything can a human being understands
    'plugin_title' => [
        'en' => 'Advanced Watermark',
        'ar' => 'ختم الصور المتقدم',
    ],
    // Who wrote this plugin?
    'plugin_developer' => 'Kleeja.net',
    // This plugin version
    'plugin_version' => '1.2',
    // Explain what is this plugin, why should I use it?
    'plugin_description' => [
        'en' =>
            'Stamp uploaded images with your own image or text, choose its position, font and colors, and preview the result.',
        'ar' => 'اختم الصور المرفوعة بصورة أو نص من اختيارك، وحدّد موضعه وخطه وألوانه، ثم عاين النتيجة.',
    ],
    //settings page, if there is one (what after ? like cp=j_plugins)
    'settings_page' => 'cp=watermark_settings',
    // Min version of Kleeja that's requiered to run this plugin
    'plugin_kleeja_version_min' => '4.0.0',
    // Max version of Kleeja that support this plugin, use 0 for unlimited
    'plugin_kleeja_version_max' => '4.9',
    // Should this plugin run before others?, 0 is normal, and higher number has high priority
    'plugin_priority' => 0,
];

//after installation message, you can remove it, it's not requiered
$kleeja_plugin['advanced_watermark']['first_run']['ar'] = "
أضاف هذا البرنامج المساعد صفحة «ختم الصور» إلى لوحة التحكم، وفيها تختار ختم الصور المرفوعة بصورة أو بنص،
وتحدّد موضعه وخطه وألوانه، وتعاين النتيجة. يُختم ما ترفعه المجموعات المفعّل لها خيار «تفعيل ختم الصور».
<br>
<a href='./index.php?cp=watermark_settings'>ختم الصور</a> -
<a href='https://github.com/kleeja/advanced_watermark/issues' target='_blank' rel='noopener'>الإبلاغ عن مشكلة</a>
";

$kleeja_plugin['advanced_watermark']['first_run']['en'] = "
This plugin added the Image Watermark page to the control panel, where you choose to stamp uploaded images
with an image or a text, set its position, font and colors, and preview the result.
Images are stamped for the groups that have “Enable image watermark” turned on.
<br>
<a href='./index.php?cp=watermark_settings'>Image Watermark</a> -
<a href='https://github.com/kleeja/advanced_watermark/issues' target='_blank' rel='noopener'>Report a problem</a>
";

// Plugin Installation function
$kleeja_plugin['advanced_watermark']['install'] = function ($plg_id) {
    $options = [];

    foreach (advanced_watermark_default_configs() as $name => $value) {
        $options[$name] = ['value' => $value, 'plg_id' => $plg_id, 'type' => 'advanced_watermark'];
    }

    add_config_r($options);

    foreach (advanced_watermark_translations() as $lang_id => $words) {
        add_olang($words, $lang_id, $plg_id);
    }
};

// the words are written again, so a new version brings its new words (1.2 rewrote all of them),
// with queries of its own: add_olang(), delete_olang(), update_config() and delete_cache() run hooks,
// or need the groups, and the plugins are still loading
$kleeja_plugin['advanced_watermark']['update'] = function ($old_version, $new_version) {
    global $SQL, $dbprefix;

    $result = $SQL->build([
        'SELECT' => 'plg_id',
        'FROM' => "{$dbprefix}plugins",
        'WHERE' => 'plg_name = :name',
        'BIND' => ['name' => 'advanced_watermark'],
    ]);

    $row = $SQL->fetch_array($result);
    $plg_id = (int) ($row['plg_id'] ?? 0);
    $SQL->freeresult($result);

    //a plg_id of 0 would delete the words of the core and of every plugin
    if ($plg_id > 0) {
        $SQL->build([
            'DELETE' => "{$dbprefix}lang",
            'WHERE' => 'plg_id = :plg_id',
            'BIND' => ['plg_id' => $plg_id],
        ]);

        foreach (advanced_watermark_translations() as $lang_id => $words) {
            foreach ($words as $word => $trans) {
                $SQL->build([
                    'INSERT' => 'word, trans, lang_id, plg_id',
                    'INTO' => "{$dbprefix}lang",
                    'VALUES' => ':word, :trans, :lang_id, :plg_id',
                    'BIND' => ['word' => $word, 'trans' => $trans, 'lang_id' => $lang_id, 'plg_id' => $plg_id],
                ]);
            }
        }
    }

    // the text that versions before 1.2 wrote by default, the site of Kleeja is kleeja.net now
    if (version_compare($old_version, '1.2', '<')) {
        $SQL->build([
            'UPDATE' => "{$dbprefix}config",
            'SET' => 'value = :new',
            'WHERE' => 'name = :name AND value = :old',
            'BIND' => ['new' => 'kleeja.net', 'name' => 'watermark_text_content', 'old' => 'kleeja.com'],
        ]);
    }

    // the cached words and settings, and the compiled page of the control panel, which is a Bootstrap 5 one since 1.2;
    // an update doesn't delete the compiled templates, the page is compiled again when it is opened
    $cached = array_merge(
        glob(PATH . 'cache/data_lang*.php') ?: [],
        glob(PATH . 'cache/data_config.php') ?: [],
        glob(PATH . 'cache/tpl_admin_watermark_settings*.php') ?: [],
    );

    foreach ($cached as $file) {
        @unlink($file);
    }
};

// Plugin Uninstallation, function to be called at unistalling
$kleeja_plugin['advanced_watermark']['uninstall'] = function ($plg_id) {
    delete_config(array_keys(advanced_watermark_default_configs()));

    //a plg_id of 0 would delete the words of the core and of every plugin
    if ((int) $plg_id > 0) {
        foreach (array_keys(advanced_watermark_translations()) as $lang_id) {
            delete_olang(null, $lang_id, (int) $plg_id);
        }
    }
};

// Plugin functions
$kleeja_plugin['advanced_watermark']['functions'] = [
    //add to admin menu
    'begin_admin_page' => function ($args) {
        $adm_extensions = $args['adm_extensions'];
        $ext_icons = $args['ext_icons'];

        $adm_extensions[] = 'watermark_settings';
        $ext_icons['watermark_settings'] = 'stamp';

        //the words of the plugin, in English for a language that has no translation of them
        $olang = (array) ($args['olang'] ?? []) + advanced_watermark_translations()['en'];

        return compact('adm_extensions', 'ext_icons', 'olang');
    },

    //add as admin page to reach when click on admin menu item we added.
    'not_exists_watermark_settings' => function () {
        $include_alternative = __DIR__ . '/watermark_settings.php';

        return compact('include_alternative');
    },

    //the watermark of the plugin takes the place of the one of Kleeja
    'helper_watermark_func' => function ($args) {
        include_once __DIR__ . '/watermark.php';

        adv_helper_watermark($args['name'], strtolower($args['ext']));

        return ['return' => true];
    },

    // the guide of the plugin on the help page of the control panel, its words are in language/help_{code}.php
    'admin_help_guides' => function ($args) {
        $help_guides = $args['help_guides'];
        $words = advanced_watermark_help_words();

        // the name of the plugin is the key, so Kleeja knows that the plugin has its guide, and shows its icon,
        // and the help button of the page of the plugin opens it
        $help_guides['advanced_watermark'] = [
            'group' => 'plugins',
            'title' => $words['ADVANCED_WATERMARK_HELP_TITLE'],
            'intro' => $words['ADVANCED_WATERMARK_HELP_INTRO'],
            'page' => 'watermark_settings',
            'link' => './?cp=watermark_settings',
            // tips and warnings are shown beside the others on wide screens
            'sections' => [
                advanced_watermark_help_section($words, 'features', 'FEATURE'),
                advanced_watermark_help_section($words, 'steps', 'STEP'),
                advanced_watermark_help_section($words, 'faq', 'FAQ'),
                advanced_watermark_help_section($words, 'tips', 'TIP'),
                advanced_watermark_help_section($words, 'warnings', 'WARNING'),
            ],
        ];

        return compact('help_guides');
    },
];

/**
 * special functions
 */

if (!function_exists('advanced_watermark_default_configs')) {
    /**
     * the settings of the plugin, with the values that it is installed with
     * @return array
     */
    function advanced_watermark_default_configs()
    {
        return [
            'watermark_type' => 'image',
            'watermark_position' => 'br',
            'watermark_image_path' => 'images/watermark.gif',
            'watermark_text_content' => 'kleeja.net',
            'watermark_text_size' => '15',
            'watermark_text_color' => '#ffffff',
            'watermark_text_background' => '#cccccc',
            'watermark_text_background_enable' => '1',
            'watermark_text_font' => 'default',
        ];
    }

    /**
     * the values that the settings of a list can have, with the language keys of their titles,
     * the fonts are the .ttf files of the plugin, "default" is the Arial of Kleeja
     * @return array
     */
    function advanced_watermark_choices()
    {
        return [
            'type' => [
                'image' => 'WATERMARK_TYPE_IMAGE',
                'text' => 'WATERMARK_TYPE_TEXT',
            ],
            'position' => [
                'tl' => 'WATERMARK_POSITION_TL',
                'tc' => 'WATERMARK_POSITION_TC',
                'tr' => 'WATERMARK_POSITION_TR',
                'bl' => 'WATERMARK_POSITION_BL',
                'bc' => 'WATERMARK_POSITION_BC',
                'br' => 'WATERMARK_POSITION_BR',
            ],
            'font' => [
                'default' => 'WATERMARK_TEXT_FONT_DEFAULT',
                'amiri' => 'Amiri',
                'kacstoffice' => 'WATERMARK_TEXT_FONT_KACSTOFFICE',
                'flat' => 'JF Flat',
            ],
        ];
    }

    /**
     * the words of the guide on the help page, in the language of the admin,
     * a word that the translation misses is shown in English
     * @return array
     */
    function advanced_watermark_help_words()
    {
        global $config;

        $words = (array) require __DIR__ . '/language/help_en.php';
        $language = preg_replace('/[^a-z0-9_-]/i', '', (string) ($config['language'] ?? ''));
        $translation = __DIR__ . "/language/help_{$language}.php";

        if ($language !== '' && $language !== 'en' && file_exists($translation)) {
            // in its own line, Prettier drops the brackets of (require $translation) + $words
            $translated = require $translation;
            $words = (array) $translated + $words;
        }

        return $words;
    }

    /**
     * a section of the guide from its numbered words, like ADVANCED_WATERMARK_HELP_TIP_1, ADVANCED_WATERMARK_HELP_TIP_2 ..
     * its title, when it has its own, is ADVANCED_WATERMARK_HELP_TIP_TITLE
     * @param  array  $words
     * @param  string $type  how Kleeja shows it: features, steps, tips, warnings or faq
     * @param  string $name  the name of its words, ADVANCED_WATERMARK_HELP_{name}_1
     * @return array
     */
    function advanced_watermark_help_section($words, $type, $name)
    {
        $prefix = 'ADVANCED_WATERMARK_HELP_' . $name;
        $section = ['type' => $type, 'title' => $words[$prefix . '_TITLE'] ?? '', 'items' => []];

        for ($n = 1; isset($words[$prefix . ($type === 'faq' ? '_Q_' : '_') . $n]); $n++) {
            $section['items'][] =
                $type === 'faq'
                    ? ['q' => $words[$prefix . '_Q_' . $n], 'a' => $words[$prefix . '_A_' . $n] ?? '']
                    : $words[$prefix . '_' . $n];
        }

        return $section;
    }

    /**
     * the words of the plugin, they are added to the language table when it is installed or updated
     * @return array
     */
    function advanced_watermark_translations()
    {
        return [
            'en' => [
                'R_WATERMARK_SETTINGS' => 'Image Watermark',
                'WATERMARK_TYPE' => 'Watermark type',
                'WATERMARK_TYPE_IMAGE' => 'Image',
                'WATERMARK_TYPE_TEXT' => 'Text',
                'WATERMARK_POSITION' => 'Position',
                'WATERMARK_POSITION_TL' => 'Top left',
                'WATERMARK_POSITION_TC' => 'Top center',
                'WATERMARK_POSITION_TR' => 'Top right',
                'WATERMARK_POSITION_BL' => 'Bottom left',
                'WATERMARK_POSITION_BC' => 'Bottom center',
                'WATERMARK_POSITION_BR' => 'Bottom right',
                'WATERMARK_IMAGE_SETTINGS' => 'Image watermark',
                'WATERMARK_IMAGE_PATH' => 'Watermark image',
                'WATERMARK_IMAGE_HINT' =>
                    'A PNG, GIF or JPG file. A PNG with a transparent background gives the cleanest result.',
                'WATERMARK_IMAGE_MISSING' =>
                    'The watermark image file was not found, so images are not stamped. Upload a new image.',
                'WATERMARK_IMAGE_UPLOAD' => 'Upload a watermark image',
                'WATERMARK_NOT_IMAGE' => 'The uploaded file is not a valid PNG, GIF or JPG image.',
                'WATERMARK_TEXT_SETTINGS' => 'Text watermark',
                'WATERMARK_TEXT_CONTENT' => 'Watermark text',
                'WATERMARK_TEXT_FONT' => 'Font',
                'WATERMARK_TEXT_FONT_DEFAULT' => 'Arial (default)',
                'WATERMARK_TEXT_FONT_KACSTOFFICE' => 'KacstOffice (Arabic letters only)',
                'WATERMARK_TEXT_SIZE' => 'Font size',
                'WATERMARK_TEXT_COLOR' => 'Text color',
                'WATERMARK_TEXT_BACKGROUND' => 'Background color',
                'WATERMARK_TEXT_BACKGROUND_ENABLE' => 'Show a background behind the text',
                'WATERMARK_NOT_ENABLED_NOTE' =>
                    'Images are stamped only when the group of the uploader has <strong>Enable image watermark</strong> turned on. Turn it on for each group in <a href="./?cp=g_users&amp;smt=general">Users &amp; Groups</a>, under <strong>Edit data</strong>.',
                'WATERMARK_PREVIEW' => 'Preview',
                'WATERMARK_PREVIEW_NOTE' =>
                    'Your settings were saved. This sample image shows how the watermark appears on uploaded images.',
                'WATERMARK_PREVIEW_CREDIT' => 'Sample photo by',
            ],
            'ar' => [
                'R_WATERMARK_SETTINGS' => 'ختم الصور',
                'WATERMARK_TYPE' => 'نوع الختم',
                'WATERMARK_TYPE_IMAGE' => 'صورة',
                'WATERMARK_TYPE_TEXT' => 'نص',
                'WATERMARK_POSITION' => 'موضع الختم',
                'WATERMARK_POSITION_TL' => 'أعلى اليسار',
                'WATERMARK_POSITION_TC' => 'أعلى الوسط',
                'WATERMARK_POSITION_TR' => 'أعلى اليمين',
                'WATERMARK_POSITION_BL' => 'أسفل اليسار',
                'WATERMARK_POSITION_BC' => 'أسفل الوسط',
                'WATERMARK_POSITION_BR' => 'أسفل اليمين',
                'WATERMARK_IMAGE_SETTINGS' => 'الختم بصورة',
                'WATERMARK_IMAGE_PATH' => 'صورة الختم',
                'WATERMARK_IMAGE_HINT' => 'ملف بصيغة PNG أو GIF أو JPG، وتعطي صورة PNG بخلفية شفافة أفضل نتيجة.',
                'WATERMARK_IMAGE_MISSING' => 'لم يُعثر على ملف صورة الختم، لذا لا تُختم الصور. ارفع صورة جديدة.',
                'WATERMARK_IMAGE_UPLOAD' => 'رفع صورة الختم',
                'WATERMARK_NOT_IMAGE' => 'الملف المرفوع ليس صورة صالحة بصيغة PNG أو GIF أو JPG.',
                'WATERMARK_TEXT_SETTINGS' => 'الختم بنص',
                'WATERMARK_TEXT_CONTENT' => 'نص الختم',
                'WATERMARK_TEXT_FONT' => 'الخط',
                'WATERMARK_TEXT_FONT_DEFAULT' => 'Arial (الافتراضي)',
                'WATERMARK_TEXT_FONT_KACSTOFFICE' => 'KacstOffice (حروف عربية فقط)',
                'WATERMARK_TEXT_SIZE' => 'حجم الخط',
                'WATERMARK_TEXT_COLOR' => 'لون النص',
                'WATERMARK_TEXT_BACKGROUND' => 'لون الخلفية',
                'WATERMARK_TEXT_BACKGROUND_ENABLE' => 'إظهار خلفية خلف النص',
                'WATERMARK_NOT_ENABLED_NOTE' =>
                    'لا تُختم الصور إلا إذا كان خيار <strong>تفعيل ختم الصور</strong> مفعّلًا لمجموعة من رفعها. فعّله لكل مجموعة من <a href="./?cp=g_users&amp;smt=general">الأعضاء والمجموعات</a>، في <strong>تعديل البيانات</strong>.',
                'WATERMARK_PREVIEW' => 'معاينة',
                'WATERMARK_PREVIEW_NOTE' =>
                    'حُفظت إعداداتك. تُظهر هذه الصورة النموذجية كيف يبدو الختم على الصور المرفوعة.',
                'WATERMARK_PREVIEW_CREDIT' => 'الصورة النموذجية من تصوير',
            ],
        ];
    }
}
