<?php
//
// advanced_watermark, its guide on the help page of the control panel
// Arabic
//
// The guide is built by the admin_help_guides hook in init.php, the texts here can have HTML.
// The keys are the ones of help_en.php, a key that is missing here is shown in English.
//  - names in <strong> are written as the control panel shows them: the words of Kleeja are in lang/ar/,
//    the words of the plugin in advanced_watermark_translations() of init.php
//

return [
    'ADVANCED_WATERMARK_HELP_TITLE' => 'ختم الصور المتقدم',
    'ADVANCED_WATERMARK_HELP_INTRO' =>
        'اختم الصور التي يرفعها زوارك بشعارك أو بسطر من النص. تحل الإضافة محل ختم كليجا، وتحدّد في صفحتها <strong>ختم الصور</strong> نوع الختم وموضعه وخطه وألوانه.',

    //
    // what the plugin does
    //
    'ADVANCED_WATERMARK_HELP_FEATURE_1' => 'ختم بصورة مثل شعارك، أو ختم بنص مثل عنوان موقعك.',
    'ADVANCED_WATERMARK_HELP_FEATURE_2' => 'ستة مواضع: في أعلى الصورة أو أسفلها، على اليسار أو في الوسط أو على اليمين.',
    'ADVANCED_WATERMARK_HELP_FEATURE_3' =>
        'للختم بنص: أربعة خطوط، والحجم، واللون، وخلفية اختيارية خلف النص. ويُكتب النص العربي بحروف متصلة.',
    'ADVANCED_WATERMARK_HELP_FEATURE_4' =>
        'يعرض تبويب <strong>معاينة</strong> الختم على صورة نموذجية، فتتأكد منه قبل أن يرفع زوارك أي صورة.',

    //
    // how to use it
    //
    'ADVANCED_WATERMARK_HELP_STEP_TITLE' => 'إعداد الختم',
    'ADVANCED_WATERMARK_HELP_STEP_1' =>
        'افتح <a href="./?cp=g_users&amp;smt=general">الأعضاء والمجموعات</a>، واضغط <strong>تعديل البيانات</strong> لكل مجموعة تريد ختم صورها، واجعل <strong>تفعيل ختم الصور</strong> على <strong>نعم</strong>.',
    'ADVANCED_WATERMARK_HELP_STEP_2' =>
        'افتح <strong>ختم الصور</strong>، ثم اختر <strong>نوع الختم</strong> و<strong>موضع الختم</strong>.',
    'ADVANCED_WATERMARK_HELP_STEP_3' =>
        'للختم بصورة، اضغط <strong>تغيير</strong> وارفع ملفًا بصيغة PNG أو GIF أو JPG. وللختم بنص، اكتب <strong>نص الختم</strong> واختر خطه وحجمه وألوانه.',
    'ADVANCED_WATERMARK_HELP_STEP_4' =>
        'اضغط <strong>تحديث الإعدادات</strong>، أو افتح تبويب <strong>معاينة</strong> لتحفظ الإعدادات وتراها على صورة نموذجية.',

    //
    // common questions
    //
    'ADVANCED_WATERMARK_HELP_FAQ_Q_1' => 'لماذا لا تُختم الصور المرفوعة؟',
    'ADVANCED_WATERMARK_HELP_FAQ_A_1' =>
        'تأكد أن <strong>تفعيل ختم الصور</strong> مفعّل لمجموعة من رفع الصورة، وأن الإضافة مفعّلة. وتبقى الصورة الأصغر من الختم كما هي، ولا يُضاف الختم بصورة إذا كان ملف صورته مفقودًا.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_2' => 'ما الصور التي تُختم؟',
    'ADVANCED_WATERMARK_HELP_FAQ_A_2' =>
        'صور JPG وPNG وBMP. أما صور GIF فلا تُختم إلا إذا كان امتداد Imagick الخاص بـPHP مثبّتًا على الخادم.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_3' => 'هل تُختم الصور التي رُفعت من قبل؟',
    'ADVANCED_WATERMARK_HELP_FAQ_A_3' =>
        'لا. يُضاف الختم في أثناء رفع الصورة، فلا تُختم إلا الصور التي تُرفع بعد تفعيله.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_4' => 'لماذا لا يظهر الختم على الصور المصغّرة؟',
    'ADVANCED_WATERMARK_HELP_FAQ_A_4' => 'تصنع كليجا الصورة المصغّرة قبل أن تختم الصورة، فتبقى الصور المصغّرة بلا ختم.',
    'ADVANCED_WATERMARK_HELP_FAQ_Q_5' => 'ماذا يحدث عند تعطيل الإضافة؟',
    'ADVANCED_WATERMARK_HELP_FAQ_A_5' =>
        'تعود كليجا إلى ختمها الخاص: صورة الختم التي في مجلد <strong>images</strong>، في الزاوية السفلية اليمنى، للمجموعات نفسها.',

    //
    // beside the guide
    //
    'ADVANCED_WATERMARK_HELP_TIP_1' => 'يظهر الشعار ذو الخلفية الشفافة بصيغة PNG بوضوح على الصور مهما كان لونها.',
    'ADVANCED_WATERMARK_HELP_TIP_2' => 'اجعل الختم صغيرًا مقارنة بصور زوارك، ليميّزها دون أن يحجب ما فيها.',
    'ADVANCED_WATERMARK_HELP_TIP_3' =>
        'فتح تبويب <strong>معاينة</strong> يحفظ الإعدادات أولًا، كما يفعل زر <strong>تحديث الإعدادات</strong>.',
    'ADVANCED_WATERMARK_HELP_TIP_4' =>
        'تحوي خطوط Arial وAmiri وJF Flat حروفًا عربية ولاتينية، أما KacstOffice فحروفه عربية فقط، فلا تظهر به الحروف اللاتينية في النص.',

    'ADVANCED_WATERMARK_HELP_WARNING_1' =>
        'يُكتب الختم في ملف الصورة المرفوعة نفسه، ولا يمكن إزالته بعد ذلك. احتفظ بنسخة خاصة بك من الصور التي تحتاجها بلا ختم.',
];
