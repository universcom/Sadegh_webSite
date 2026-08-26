<?php
declare(strict_types=1);

/**
 * Categories, editorial pages and site settings.
 *
 * Sources:
 *   درباره ما/درباره ما.docx                     — company history and status
 *   صفحه اصلی -1/صفحه اصلی- خانه.docx            — home page copy and the motto
 *   تماس با ما/تماس با ما.docx                   — address, telephones, postcode, map link
 *   محصولات-1/مشخصات محصولات شرکت رهیافت صنعت.docx — engineering & production unit services
 *   محصولات-1/بنر آبی1.jpg                        — brand name, strapline, website, telephones
 *
 * Note: the raw materials contain no e-mail address anywhere. `emails` is left
 * empty on purpose rather than invented; the installer seeds it with the
 * administrator's own address and the operator can correct it in Settings.
 */

return [

// ---------------------------------------------------------------------------
'categories' => [
    [
        'slug'  => 'metal-industry-machines',
        'image' => 'cnc-mill-5040',
        'tr' => [
            'fa' => [
                'name'        => 'دستگاه‌های صنایع فلزی',
                'description' => 'ماشین‌های کنترل عددی و تجهیزات پرس و فرم‌دهی برای براده‌برداری و شکل‌دهی قطعات فلزی.',
            ],
            'en' => [
                'name'        => 'Metal Industry Machines',
                'description' => 'CNC machinery and pressing equipment for cutting and forming metal components.',
            ],
            'ar' => [
                'name'        => 'ماكينات الصناعات المعدنية',
                'description' => 'ماكينات التحكم الرقمي ومعدات الكبس والتشكيل لقطع وتشكيل المكوّنات المعدنية.',
            ],
        ],
    ],
    [
        'slug'  => 'wood-stone-machines',
        'image' => 'cnc-wood-flat',
        'tr' => [
            'fa' => [
                'name'        => 'دستگاه‌های صنایع چوب و سنگ',
                'description' => 'دستگاه‌های CNC تخت و روتاری، مدل‌سازی، سنگ و خراطی برای صنایع چوب، سنگ و دکوراسیون.',
            ],
            'en' => [
                'name'        => 'Wood & Stone Industry Machines',
                'description' => 'Flat and rotary CNC routers, modelling machines, stone machines and lathes for the wood, stone and decoration industries.',
            ],
            'ar' => [
                'name'        => 'ماكينات صناعات الخشب والحجر',
                'description' => 'راوترات CNC مسطحة ودوّارة وماكينات نمذجة وحجر ومخارط لصناعات الخشب والحجر والديكور.',
            ],
        ],
    ],
    [
        'slug'  => 'industrial-equipment',
        'image' => 'screw-compressor',
        'tr' => [
            'fa' => [
                'name'        => 'تجهیزات و متعلقات صنعتی',
                'description' => 'کمپرسور، اسپیندل، محور چهارم، انگل هد و تجهیزات اتوماسیون صنعتی.',
            ],
            'en' => [
                'name'        => 'Industrial Equipment & Accessories',
                'description' => 'Compressors, spindles, fourth axes, angle heads and industrial automation hardware.',
            ],
            'ar' => [
                'name'        => 'المعدات والملحقات الصناعية',
                'description' => 'ضواغط ومحاور دوران ومحاور رابعة ورؤوس زاوية ومعدات أتمتة صناعية.',
            ],
        ],
    ],
],

// ---------------------------------------------------------------------------
'settings' => [
    // Language-neutral values.
    'neutral' => [
        'contact'  => [
            // Every number below appears in the supplied materials:
            // 09132322096 and 37934363 in تماس با ما.docx,
            // 09016114814 and 09368278184 on the printed product banner.
            'phones'      => "09132322096\n09016114814\n09368278184\n37934363",
            'emails'      => '',
            'postal_code' => '8174673344',
            'city'        => 'Isfahan',
            'map_url'     => 'https://maps.app.goo.gl/U7p7yAVdecsVhZD38',
            'map_embed'   => '',
        ],
        'general'  => [
            'website'      => 'https://www.rah-yaft.ir',
            'founded_year' => '1400',
        ],
        'social'   => [
            'social_instagram' => '',
            'social_linkedin'  => '',
            'social_telegram'  => '',
            'social_whatsapp'  => '',
        ],
        'mail'     => [
            'notify_enabled' => '1',
        ],
    ],

    // Per-language values.
    'translated' => [
        'fa' => [
            'site_name'        => 'شرکت رهیافت صنعت',
            'site_tagline'     => 'طراح و تولیدکننده تجهیزات صنعتی و آزمایشگاهی',
            'seo_title'        => 'شرکت رهیافت صنعت | طراحی و ساخت ماشین‌آلات CNC و تجهیزات صنعتی',
            'seo_description'  => 'شرکت رهیافت‌های فناورانه و راهبردی مسائل مهندسی، طراح و سازنده ماشین‌آلات CNC، تجهیزات صنعتی و آزمایشگاهی، مستقر در مرکز رشد دانشگاه اصفهان و عضو شهرک علمی تحقیقاتی اصفهان.',
            'address'          => 'اصفهان، بلوار دانشگاه، دانشگاه اصفهان، روبروی مصلای دانشگاه، مرکز رشد و کارآفرینی، طبقه همکف، واحد شماره ۱۰',
            'working_hours'    => '',
            'footer_about'     => 'طراحی و ساخت ماشین‌آلات کنترل عددی، تجهیزات صنعتی و آزمایشگاهی؛ مستقر در مرکز رشد دانشگاه اصفهان و عضو شهرک علمی تحقیقاتی اصفهان.',
        ],
        'en' => [
            'site_name'        => 'Rahyaft Sanat',
            'site_tagline'     => 'Designer and manufacturer of industrial and laboratory equipment',
            'seo_title'        => 'Rahyaft Sanat | CNC machinery and industrial equipment design and manufacture',
            'seo_description'  => 'Rahyaft Sanat designs and builds CNC machinery, industrial equipment and laboratory instruments. Based at the University of Isfahan incubator and a member of Isfahan Science and Technology Town.',
            'address'          => 'Growth and Entrepreneurship Centre, ground floor, unit 10, University of Isfahan, opposite the university Mosalla, Daneshgah Boulevard, Isfahan, Iran',
            'working_hours'    => '',
            'footer_about'     => 'Design and manufacture of CNC machinery, industrial equipment and laboratory instruments. Based at the University of Isfahan incubator and a member of Isfahan Science and Technology Town.',
        ],
        'ar' => [
            'site_name'        => 'شركة رهيافت صنعت',
            'site_tagline'     => 'مصمّم ومصنّع المعدات الصناعية والمخبرية',
            'seo_title'        => 'شركة رهيافت صنعت | تصميم وتصنيع ماكينات CNC والمعدات الصناعية',
            'seo_description'  => 'تصمّم شركة رهيافت صنعت وتصنّع ماكينات CNC والمعدات الصناعية والأجهزة المخبرية. مقرّها حاضنة جامعة أصفهان وعضو في مدينة أصفهان العلمية والبحثية.',
            'address'          => 'أصفهان، شارع دانشگاه، جامعة أصفهان، مقابل مصلى الجامعة، مركز النمو وريادة الأعمال، الطابق الأرضي، الوحدة رقم 10',
            'working_hours'    => '',
            'footer_about'     => 'تصميم وتصنيع ماكينات CNC والمعدات الصناعية والأجهزة المخبرية؛ مقرّها حاضنة جامعة أصفهان وعضو في مدينة أصفهان العلمية والبحثية.',
        ],
    ],
],

];
