<?php
//
// advanced_watermark, its guide on the help page of the control panel
// English
//
// The guide is built by the admin_help_guides hook in init.php, the texts here can have HTML.
//  - a list is numbered: ADVANCED_WATERMARK_HELP_TIP_1, ADVANCED_WATERMARK_HELP_TIP_2 ... it ends at the first missing number,
//    so an item can be added or removed without touching the code
//  - ADVANCED_WATERMARK_HELP_{LIST}_TITLE is the title of the list, a list without it takes the title that Kleeja gives its type
//  - questions and answers are ADVANCED_WATERMARK_HELP_FAQ_Q_1 and ADVANCED_WATERMARK_HELP_FAQ_A_1
//  - names in <strong> are written as the control panel shows them: the words of Kleeja are in lang/en/,
//    the words of the plugin in advanced_watermark_translations() of init.php
//

return [
    'ADVANCED_WATERMARK_HELP_TITLE' => 'Advanced Watermark',
    'ADVANCED_WATERMARK_HELP_INTRO' =>
        'Stamp the images that your visitors upload with your logo or a line of text. The plugin replaces the watermark of Kleeja, and its <strong>Image Watermark</strong> page sets the type, the position, the font and the colors of the watermark.',

    //
    // what the plugin does
    //
    'ADVANCED_WATERMARK_HELP_FEATURE_1' =>
        'An image watermark, such as your logo, or a text watermark, such as the address of your site.',
    'ADVANCED_WATERMARK_HELP_FEATURE_2' =>
        'Six positions: at the top or the bottom of the image, on the left, in the center or on the right.',
    'ADVANCED_WATERMARK_HELP_FEATURE_3' =>
        'For a text watermark: four fonts, the size, the color and an optional background behind the text. Arabic text is drawn with its letters joined.',
    'ADVANCED_WATERMARK_HELP_FEATURE_4' =>
        'The <strong>Preview</strong> tab shows the watermark on a sample photo, so you can check it before your visitors upload anything.',

    //
    // how to use it
    //
    'ADVANCED_WATERMARK_HELP_STEP_TITLE' => 'Set up the watermark',
    'ADVANCED_WATERMARK_HELP_STEP_1' =>
        'Open <a href="./?cp=g_users&amp;smt=general">Users &amp; Groups</a>, click <strong>Edit data</strong> for each group whose images you want to stamp, and set <strong>Enable image watermark</strong> to <strong>Yes</strong>.',
    'ADVANCED_WATERMARK_HELP_STEP_2' =>
        'Open <strong>Image Watermark</strong>, then choose the <strong>Watermark type</strong> and its <strong>Position</strong>.',
    'ADVANCED_WATERMARK_HELP_STEP_3' =>
        'For an image watermark, click <strong>Change</strong> and upload a PNG, GIF or JPG file. For a text watermark, write the <strong>Watermark text</strong> and choose its font, size and colors.',
    'ADVANCED_WATERMARK_HELP_STEP_4' =>
        'Click <strong>Update Settings</strong>, or open the <strong>Preview</strong> tab to save the settings and see them on a sample photo.',

    //
    // common questions
    //
    'ADVANCED_WATERMARK_HELP_FAQ_Q_1' => 'Why are the uploaded images not stamped?',
    'ADVANCED_WATERMARK_HELP_FAQ_A_1' =>
        'Check that <strong>Enable image watermark</strong> is turned on for the group of the uploader, and that the plugin is enabled. An image that is too small for the watermark is left as it is, and an image watermark whose file is missing stamps nothing.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_2' => 'Which images are stamped?',
    'ADVANCED_WATERMARK_HELP_FAQ_A_2' =>
        'JPG, PNG and BMP images. GIF images are stamped only when the server has the Imagick extension of PHP.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_3' => 'Are the images that were uploaded before stamped too?',
    'ADVANCED_WATERMARK_HELP_FAQ_A_3' =>
        'No. The watermark is added while an image is uploaded, so only the images uploaded after you turn it on are stamped.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_4' => 'Why don\'t the thumbnails show the watermark?',
    'ADVANCED_WATERMARK_HELP_FAQ_A_4' =>
        'Kleeja makes the thumbnail of an image before it stamps the image, so thumbnails stay without the watermark.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_5' => 'What happens when the plugin is disabled?',
    'ADVANCED_WATERMARK_HELP_FAQ_A_5' =>
        'Kleeja goes back to its own watermark: the watermark image in its <strong>images</strong> folder, in the bottom right corner, for the same groups.',

    //
    // beside the guide
    //
    'ADVANCED_WATERMARK_HELP_TIP_1' => 'A PNG logo with a transparent background looks good on photos of any color.',
    'ADVANCED_WATERMARK_HELP_TIP_2' =>
        'Keep the watermark small compared with the images of your visitors, so it marks them without hiding what they show.',
    'ADVANCED_WATERMARK_HELP_TIP_3' =>
        'Opening the <strong>Preview</strong> tab saves the settings first, as <strong>Update Settings</strong> does.',
    'ADVANCED_WATERMARK_HELP_TIP_4' =>
        'Arial, Amiri and JF Flat have Arabic and Latin letters. KacstOffice has Arabic letters only, so Latin letters in the text don\'t appear with it.',

    'ADVANCED_WATERMARK_HELP_WARNING_1' =>
        'The watermark is written into the uploaded file itself, and it can\'t be removed later. Keep your own copy of the images that you need without it.',
];
