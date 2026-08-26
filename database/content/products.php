<?php
declare(strict_types=1);

/**
 * Product catalogue extracted from the supplied raw materials.
 *
 * Provenance: every technical value below comes from one of
 *   - محصولات-1/مشخصات محصولات شرکت رهیافت صنعت.docx   (master specification document)
 *   - the per-product «مشخصات فنی» specification sheets (PNG) in each product folder
 *   - the per-product datasheet PDFs (mini CNC lathe/mill, angle head)
 * and is recorded in each entry's `source_ref`. Nothing here is invented: where
 * the source is silent a field is simply absent, and `needs_review` marks the
 * entries an operator should confirm.
 *
 * Spec rows accept two shapes:
 *   ['label' => '…', 'value' => '…']            identical in every language
 *   ['fa' => [...], 'en' => [...], 'ar' => [...]]  per-language label/value
 * The first is used for the sheets that are already in English in the source,
 * so the engineering vocabulary stays exactly as the company publishes it.
 */

return [

// ===========================================================================
// Metal industry machines
// ===========================================================================

[
    'slug'        => 'cnc-milling-machine-5040',
    'category'    => 'metal-industry-machines',
    'model_code'  => '5040',
    'featured'    => true,
    'cover'       => 'cnc-mill-5040',
    'gallery'     => ['cnc-mill-5040'],
    'source_ref'  => 'محصولات-1/مشخصات محصولات شرکت رهیافت صنعت.docx',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه فرز CNC چهار محور مدل ۵۰۴۰',
            'summary' => 'مرکز ماشین‌کاری عمودی CNC با اسپیندل ۵٫۵ کیلووات، مخزن ابزار کاروسل ۱۰ تایی و امکان افزودن محور چهارم دوار.',
            'description' => "دستگاه فرز CNC مدل ۵۰۴۰ یک مرکز ماشین‌کاری عمودی برای براده‌برداری قطعات فلزی است که در واحد ساخت و تولید شرکت رهیافت صنعت طراحی و ساخته می‌شود.\nاین دستگاه با کورس کاری ۴۰۰ در ۳۰۰ در ۲۵۰ میلیمتر، اسپیندل ۵٫۵ کیلووات با بیشینه دور ۶۰۰۰ دور بر دقیقه و کله‌گی CT/BT 40 عرضه می‌شود و امکان نصب محور چهارم دوار روی میز آن وجود دارد.\nسیستم تعویض ابزار از نوع کاروسل با ظرفیت ۱۰ ابزار است و میز کار با سه شیار T امکان بستن قطعات تا ۲۰۰ کیلوگرم را فراهم می‌کند.",
            'applications' => "ماشین‌کاری قطعات فلزی\nقالب‌سازی\nتولید قطعات صنعتی\nمهندسی معکوس و بازسازی قطعات",
        ],
        'en' => [
            'name'    => '5040 Four-Axis CNC Milling Machine',
            'summary' => 'Vertical CNC machining centre with a 5.5 kW spindle, ten-tool carousel changer and provision for a rotary fourth axis.',
            'description' => "The 5040 CNC milling machine is a vertical machining centre for metal cutting, designed and built in the Rahyaft Sanat production unit.\nIt offers a 400 × 300 × 250 mm working travel, a 5.5 kW spindle running up to 6000 rpm with a CT/BT 40 taper, and can accept a rotary fourth axis mounted on the table.\nTool change is by a carousel magazine holding ten tools, and the three-T-slot table carries workpieces up to 200 kg.",
            'applications' => "Metal part machining\nMould and die making\nIndustrial component production\nReverse engineering and part reconstruction",
        ],
        'ar' => [
            'name'    => 'ماكينة تفريز CNC رباعية المحاور موديل 5040',
            'summary' => 'مركز تشغيل عمودي CNC بمحور دوران 5.5 كيلوواط ومغيّر أدوات دوّار بسعة عشر أدوات وإمكانية إضافة محور رابع دوّار.',
            'description' => "ماكينة التفريز CNC موديل 5040 هي مركز تشغيل عمودي لقطع المعادن، مصمّمة ومصنّعة في وحدة الإنتاج بشركة رهيافت صنعت.\nتوفّر مجال حركة 400 × 300 × 250 مم، ومحور دوران بقدرة 5.5 كيلوواط يصل إلى 6000 دورة في الدقيقة بمخروط CT/BT 40، مع إمكانية تركيب محور رابع دوّار على الطاولة.\nيتم تغيير الأدوات عبر مخزن دوّار يتسع لعشر أدوات، وتتحمّل الطاولة ذات الثلاثة مجاري T قطع عمل تصل إلى 200 كجم.",
            'applications' => "تشغيل القطع المعدنية\nصناعة القوالب\nإنتاج المكوّنات الصناعية\nالهندسة العكسية وإعادة تصنيع القطع",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'کورس حرکتی', 'en' => 'Travels', 'ar' => 'مجال الحركة'],
            'rows'  => [
                ['label' => 'X Axis', 'value' => '400 mm'],
                ['label' => 'Y Axis', 'value' => '300 mm'],
                ['label' => 'Z Axis', 'value' => '250 mm'],
                ['label' => 'Spindle Nose to Table (max)', 'value' => '350 mm'],
                ['label' => 'Spindle Nose to Table (min)', 'value' => '100 mm'],
            ],
        ],
        [
            'title' => ['fa' => 'اسپیندل', 'en' => 'Spindle', 'ar' => 'محور الدوران'],
            'rows'  => [
                ['label' => 'Max Rating',          'value' => '5.5 kW'],
                ['label' => 'Max Speed',           'value' => '6000 rpm'],
                ['label' => 'Max Torque',          'value' => '40 Nm @ 1200 rpm'],
                ['label' => 'Drive System',        'value' => 'Direct Speed, Belt Drive'],
                ['label' => 'Taper',               'value' => 'CT or BT 40'],
                ['label' => 'Bearing Lubrication', 'value' => 'Grease'],
                ['label' => 'Cooling',             'value' => 'Air Cooled'],
            ],
        ],
        [
            'title' => ['fa' => 'میز کار', 'en' => 'Table', 'ar' => 'الطاولة'],
            'rows'  => [
                ['label' => 'Length',                  'value' => '914 mm'],
                ['label' => 'Length (Work Area)',      'value' => '730 mm'],
                ['label' => 'Width',                   'value' => '305 mm'],
                ['label' => 'T-Slot Width',            'value' => '15.90 mm to 16.00 mm'],
                ['label' => 'T-Slot Center Distance',  'value' => '110 mm'],
                ['label' => 'Number of Std T-Slot',    'value' => '3'],
                ['label' => 'Max Weight on Table (evenly distributed)', 'value' => '200 kg'],
            ],
        ],
        [
            'title' => ['fa' => 'سرعت پیشروی', 'en' => 'Feedrate', 'ar' => 'سرعة التغذية'],
            'rows'  => [
                ['label' => 'Max Cutting', 'value' => '12.5 m/min'],
                ['label' => 'Rapid on X',  'value' => '15 m/min'],
                ['label' => 'Rapid on Y',  'value' => '15 m/min'],
                ['label' => 'Rapid on Z',  'value' => '15 m/min'],
            ],
        ],
        [
            'title' => ['fa' => 'تعویض‌کننده ابزار', 'en' => 'Tool Changer', 'ar' => 'مغيّر الأدوات'],
            'rows'  => [
                ['label' => 'Type',                     'value' => 'Carousel'],
                ['label' => 'Capacity',                 'value' => '10'],
                ['label' => 'Max Tool Diameter (full)', 'value' => '89 mm'],
                ['label' => 'Max Tool Weight',          'value' => '5.5 kg'],
            ],
        ],
    ],
],

[
    'slug'       => 'mini-cnc-milling-machine',
    'category'   => 'metal-industry-machines',
    'model_code' => 'Mini Mill CNC',
    'featured'   => true,
    'cover'      => 'mini-cnc-mill',
    'gallery'    => ['mini-cnc-mill', 'mini-cnc-mill-2'],
    'source_ref' => 'محصولات-1/دستگاه مینی سی ان سی فرز/مشخصات فنی دستگاه مینی سی ان سی فرز.pdf',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه مینی سی‌ان‌سی فرز',
            'summary' => 'فرز CNC رومیزی با محفظه ایمنی بسته، اسپیندل BT30 مجهز به تعویض ابزار و امکان افزودن محور چهارم و پنجم.',
            'description' => "دستگاه مینی سی‌ان‌سی فرز برای ماشین‌کاری دقیق قطعات کوچک فلزی طراحی شده و با بدنه و میز چدنی یکپارچه ساخته می‌شود.\nدقت موقعیت‌دهی دستگاه ۰٫۰۲ میلیمتر و تکرارپذیری آن ۰٫۰۱ میلیمتر است. انتقال حرکت از طریق بالسکرو و ریل‌های خطی با دقت بالا انجام می‌شود.\nاین دستگاه به‌صورت اختیاری با محور پنجم قابل ارائه است.",
            'applications' => "ماشین‌کاری دقیق قطعات کوچک\nنمونه‌سازی و ساخت قطعات آزمایشگاهی\nآموزش و کاربردهای دانشگاهی\nقالب‌سازی در ابعاد کوچک",
        ],
        'en' => [
            'name'    => 'Mini CNC Milling Machine',
            'summary' => 'Bench-top CNC mill with a fully enclosed cabin, BT30 tool-changing spindle and an optional fourth and fifth axis.',
            'description' => "The mini CNC milling machine is built for precision machining of small metal parts, with a one-piece cast-iron body and table.\nPositioning accuracy is 0.02 mm and repeatability 0.01 mm; motion is transmitted through ball screws running on high-precision linear guides.\nA fifth axis is available as an option.",
            'applications' => "Precision machining of small parts\nPrototyping and laboratory part production\nEducation and university use\nSmall-scale mould making",
        ],
        'ar' => [
            'name'    => 'ماكينة تفريز CNC مصغّرة',
            'summary' => 'ماكينة تفريز CNC مكتبية بحجيرة مغلقة ومحور دوران BT30 مع تغيير أدوات وإمكانية إضافة محور رابع وخامس.',
            'description' => "صُمّمت ماكينة التفريز CNC المصغّرة للتشغيل الدقيق للقطع المعدنية الصغيرة، بهيكل وطاولة من الحديد الزهر المصبوب قطعة واحدة.\nدقة تحديد الموضع 0.02 مم وقابلية التكرار 0.01 مم، وتنتقل الحركة عبر لوالب كروية على أدلة خطية عالية الدقة.\nيتوفّر محور خامس كخيار إضافي.",
            'applications' => "التشغيل الدقيق للقطع الصغيرة\nصناعة النماذج والقطع المخبرية\nالاستخدام التعليمي والجامعي\nصناعة القوالب الصغيرة",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['label' => 'Positioning accuracy',    'value' => '0.02 mm'],
                ['label' => 'Repeatability accuracy',  'value' => '0.01 mm'],
                ['label' => 'X axis travel',           'value' => '290 mm'],
                ['label' => 'Y axis travel',           'value' => '200 mm'],
                ['label' => 'Z axis travel',           'value' => '280 mm'],
                ['label' => 'Tool Dia. Range',         'value' => '3–20 mm'],
                ['label' => 'Controller',              'value' => 'Radonix'],
                ['label' => 'Motor Type',              'value' => 'Stepper motor'],
                ['label' => 'Hand Wheel',              'value' => '5 axis'],
                ['label' => 'Spindle Motor',           'value' => '2.2 kW'],
                ['label' => 'Spindle Speed',           'value' => '4500 rpm'],
                ['label' => 'Spindle Type',            'value' => 'BT30 – Toolchanger'],
                ['label' => 'Max moving speed',        'value' => '2000 mm/min'],
                ['label' => 'Max feeding speed',       'value' => '1000 mm/min'],
                ['label' => 'Transmission',            'value' => 'Ball screw'],
                ['label' => 'Guide rail',              'value' => 'High precision linear guide'],
                ['label' => 'Table size',              'value' => '440 × 200 mm'],
                ['label' => 'Max load',                'value' => '30 kg'],
                ['label' => 'Table / structure material', 'value' => 'Full cast iron'],
                ['label' => 'Power',                   'value' => '220 V'],
                ['label' => 'Weight (net / gross)',    'value' => '150 kg / 200 kg'],
                ['label' => 'Overall dimension',       'value' => '1030 × 1006 × 772 mm'],
            ],
        ],
        [
            'title' => ['fa' => 'محور پنجم (اختیاری)', 'en' => 'Fifth axis (option)', 'ar' => 'المحور الخامس (اختياري)'],
            'rows'  => [
                ['label' => 'Backlash',               'value' => 'Zero backlash'],
                ['label' => 'Max speed 4th axis',     'value' => '4 rpm'],
                ['label' => 'Max speed 5th axis',     'value' => '6 rpm'],
                ['label' => 'Accuracy 4th axis',      'value' => '±1 arc-min'],
                ['label' => 'Accuracy 5th axis',      'value' => '±1 arc-min'],
                ['label' => 'Repeatability 4th axis', 'value' => '20 arc-sec'],
                ['label' => 'Repeatability 5th axis', 'value' => '20 arc-sec'],
                ['label' => 'Homing',                 'value' => 'B axis'],
                ['label' => 'Limits',                 'value' => '+C, −C'],
                ['label' => 'Travel',                 'value' => 'B axis: +300 to −1250 · C axis: ± infinity'],
            ],
        ],
    ],
],

[
    'slug'       => 'mini-cnc-lathe',
    'category'   => 'metal-industry-machines',
    'model_code' => 'Mini Lath CNC',
    'featured'   => true,
    'cover'      => 'mini-cnc-lathe',
    'gallery'    => ['mini-cnc-lathe'],
    'source_ref' => 'محصولات-1/دستگاه مینی سی ان سی تراش/مشخصات فنی سی ان سی تراش.pdf',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه مینی سی‌ان‌سی تراش',
            'summary' => 'تراش CNC رومیزی با بدنه چدنی یکپارچه، سه‌نظام خودمرکز و سیستم تعویض ابزار خودکار شش‌تایی.',
            'description' => "دستگاه مینی سی‌ان‌سی تراش برای تراشکاری دقیق قطعات کوچک و متوسط طراحی شده است.\nقطر تراش روی بستر ۲۶۰ میلیمتر و کورس محورهای X و Z به ترتیب ۱۳۰ و ۲۵۰ میلیمتر است. اسپیندل ۲٫۲ کیلووات با دور ۳۰۰ تا ۴۰۰۰ دور بر دقیقه کار می‌کند و سوراخ عبور آن ۴۰ میلیمتر است.\nدستگاه به سیستم تعویض ابزار خودکار با ظرفیت شش ابزار مجهز است.",
            'applications' => "تراشکاری دقیق قطعات کوچک\nتولید قطعات صنعتی سری\nنمونه‌سازی و ساخت قطعات آزمایشگاهی\nآموزش و کاربردهای دانشگاهی",
        ],
        'en' => [
            'name'    => 'Mini CNC Lathe',
            'summary' => 'Bench-top CNC lathe with a one-piece cast-iron body, self-centring three-jaw chuck and a six-station automatic tool changer.',
            'description' => "The mini CNC lathe is designed for precision turning of small and medium parts.\nTurning diameter over the bed is 260 mm, with X and Z travels of 130 mm and 250 mm. The 2.2 kW spindle runs from 300 to 4000 rpm and has a 40 mm through-hole.\nAn automatic tool changer carries six tools.",
            'applications' => "Precision turning of small parts\nSmall-batch industrial part production\nPrototyping and laboratory part production\nEducation and university use",
        ],
        'ar' => [
            'name'    => 'مخرطة CNC مصغّرة',
            'summary' => 'مخرطة CNC مكتبية بهيكل من الحديد الزهر قطعة واحدة وظرف ثلاثي ذاتي التمركز ومغيّر أدوات آلي بست محطات.',
            'description' => "صُمّمت المخرطة CNC المصغّرة للخراطة الدقيقة للقطع الصغيرة والمتوسطة.\nقطر الخراطة فوق القاعدة 260 مم، ومجال حركة المحورين X وZ هو 130 و250 مم. يعمل محور الدوران بقدرة 2.2 كيلوواط من 300 إلى 4000 دورة في الدقيقة بفتحة مرور 40 مم.\nالماكينة مزوّدة بمغيّر أدوات آلي يتسع لست أدوات.",
            'applications' => "الخراطة الدقيقة للقطع الصغيرة\nإنتاج القطع الصناعية بكميات صغيرة\nصناعة النماذج والقطع المخبرية\nالاستخدام التعليمي والجامعي",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['label' => 'Positioning accuracy',   'value' => '0.02 mm'],
                ['label' => 'Repeatability accuracy', 'value' => '0.01 mm'],
                ['label' => 'Turning diameter over bed', 'value' => '260 mm'],
                ['label' => 'Chuck type',             'value' => 'Three jaw self centering – K11-160'],
                ['label' => 'Chuck diameter',         'value' => '3 mm – 145 mm'],
                ['label' => 'Spindle Speed',          'value' => '300–4000 rpm'],
                ['label' => 'Spindle through hole',   'value' => '40 mm'],
                ['label' => 'Controller',             'value' => 'Radonix'],
                ['label' => 'Motor Type',             'value' => 'Stepper motor'],
                ['label' => 'Hand Wheel',             'value' => '5 axis'],
                ['label' => 'Spindle Motor',          'value' => '2.2 kW'],
                ['label' => 'X/Z axis travel',        'value' => '130 / 250 mm'],
                ['label' => 'Max moving speed',       'value' => '2000 mm/min'],
                ['label' => 'Max feeding speed',      'value' => '1000 mm/min'],
                ['label' => 'Transmission',           'value' => 'Ball screw'],
                ['label' => 'Guide rail',             'value' => 'High precision linear guide'],
                ['label' => 'Automatic tool change system (ATC)', 'value' => '6 tools'],
                ['label' => 'Tool dimension',         'value' => '12 × 12 mm'],
                ['label' => 'Structure material',     'value' => 'Full cast iron'],
                ['label' => 'Power',                  'value' => '220 V'],
                ['label' => 'Weight (net / gross)',   'value' => '180 kg / 230 kg'],
                ['label' => 'Overall dimension',      'value' => '1435 × 1175 × 814 mm'],
            ],
        ],
    ],
],

[
    'slug'       => 'hydraulic-press-60-ton',
    'category'   => 'metal-industry-machines',
    'model_code' => 'H-Frame 60T',
    'featured'   => false,
    'cover'      => 'hydraulic-press-60t',
    'gallery'    => ['hydraulic-press-60t'],
    'source_ref' => 'محصولات-1/پرس 60 تن/مشخصات فنی پرس 60 تن.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'پرس هیدرولیک ۶۰ تن فریم H',
            'summary' => 'پرس هیدرولیک ۶۰ تن با فریم H، سیلندر اصلی متحرک و میز کناری مستقل ۱۵ تن.',
            'description' => "پرس هیدرولیک ۶۰ تن با ساختار فریم H برای عملیات پرس‌کاری، جاسازی و صاف‌کاری قطعات فلزی ساخته شده است.\nسیلندر اصلی با کورس ۳۰۰ میلیمتر و عرض کارگیر ۱۰۵۰ میلیمتر قابلیت جابه‌جایی روی فریم را دارد و یک میز کناری مستقل با ظرفیت ۱۵ تن نیز روی دستگاه تعبیه شده است.\nتوان موتور ۵٫۵ اسب بخار و تغذیه برق سه‌فاز ۲۲۰ ولت است.",
            'applications' => "پرس‌کاری قطعات فلزی\nجاسازی بلبرینگ و بوش\nصاف‌کاری و خم‌کاری\nتعمیرگاه‌های صنعتی",
        ],
        'en' => [
            'name'    => '60-Ton H-Frame Hydraulic Press',
            'summary' => 'Sixty-tonne H-frame hydraulic press with a traversing main cylinder and an independent 15-tonne side table.',
            'description' => "This 60-tonne H-frame hydraulic press is built for pressing, seating and straightening metal parts.\nThe main cylinder has a 300 mm stroke and a 1050 mm working width and can traverse along the frame; an independent side table rated at 15 tonnes is fitted alongside it.\nThe motor is rated at 5.5 hp on a three-phase 220 V supply.",
            'applications' => "Pressing metal parts\nSeating bearings and bushes\nStraightening and bending\nIndustrial repair workshops",
        ],
        'ar' => [
            'name'    => 'مكبس هيدروليكي 60 طن بإطار H',
            'summary' => 'مكبس هيدروليكي بقوة 60 طن بإطار H مع أسطوانة رئيسية متحركة وطاولة جانبية مستقلة بقوة 15 طن.',
            'description' => "صُنع هذا المكبس الهيدروليكي بقوة 60 طن بإطار على شكل H لأعمال الكبس والتركيب وتقويم القطع المعدنية.\nتبلغ شوطة الأسطوانة الرئيسية 300 مم وعرض العمل 1050 مم مع إمكانية تحريكها على الإطار، كما زُوّد المكبس بطاولة جانبية مستقلة بسعة 15 طن.\nقدرة المحرك 5.5 حصان بتغذية ثلاثية الأطوار 220 فولت.",
            'applications' => "كبس القطع المعدنية\nتركيب الرولمان بلي والجلب\nالتقويم والثني\nورش الصيانة الصناعية",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['بیشترین ظرفیت میز اصلی', '۶۰ تن'],           'en' => ['Main table maximum capacity', '60 ton'],          'ar' => ['أقصى سعة للطاولة الرئيسية', '60 طن']],
                ['fa' => ['سرعت حرکت رفت پیستون اصلی', '8 mm/s'],        'en' => ['Main piston advance speed', '8 mm/s'],            'ar' => ['سرعة تقدم المكبس الرئيسي', '8 mm/s']],
                ['fa' => ['سرعت حرکت برگشت پیستون اصلی', '10 mm/s'],     'en' => ['Main piston return speed', '10 mm/s'],            'ar' => ['سرعة رجوع المكبس الرئيسي', '10 mm/s']],
                ['fa' => ['کورس سیلندر اصلی', '300 mm'],                 'en' => ['Main cylinder stroke', '300 mm'],                 'ar' => ['شوط الأسطوانة الرئيسية', '300 mm']],
                ['fa' => ['عرض کارگیر پرس اصلی', '1050 mm'],             'en' => ['Main press working width', '1050 mm'],            'ar' => ['عرض عمل المكبس الرئيسي', '1050 mm']],
                ['fa' => ['قطر سیلندر اصلی', '192 mm'],                  'en' => ['Main cylinder diameter', '192 mm'],               'ar' => ['قطر الأسطوانة الرئيسية', '192 mm']],
                ['fa' => ['قطر پیستون اصلی', '80 mm'],                   'en' => ['Main piston diameter', '80 mm'],                  'ar' => ['قطر المكبس الرئيسي', '80 mm']],
                ['fa' => ['قابلیت حرکت سیلندر اصلی', 'بله'],             'en' => ['Main cylinder traverse', 'Yes'],                  'ar' => ['إمكانية تحريك الأسطوانة الرئيسية', 'نعم']],
                ['fa' => ['ظرفیت میز کناری', '15 ton'],                  'en' => ['Side table capacity', '15 ton'],                  'ar' => ['سعة الطاولة الجانبية', '15 ton']],
                ['fa' => ['سرعت حرکت رفت پیستون کناری', '20 mm/s'],      'en' => ['Side piston advance speed', '20 mm/s'],           'ar' => ['سرعة تقدم المكبس الجانبي', '20 mm/s']],
                ['fa' => ['سرعت حرکت برگشت پیستون کناری', '25 mm/s'],    'en' => ['Side piston return speed', '25 mm/s'],            'ar' => ['سرعة رجوع المكبس الجانبي', '25 mm/s']],
                ['fa' => ['کورس سیلندر کناری', '299 mm'],                'en' => ['Side cylinder stroke', '299 mm'],                 'ar' => ['شوط الأسطوانة الجانبية', '299 mm']],
                ['fa' => ['ابعاد میز کناری', '319 × 360 mm'],            'en' => ['Side table dimensions', '319 × 360 mm'],          'ar' => ['أبعاد الطاولة الجانبية', '319 × 360 mm']],
                ['fa' => ['قطر سیلندر کناری', '100 mm'],                 'en' => ['Side cylinder diameter', '100 mm'],               'ar' => ['قطر الأسطوانة الجانبية', '100 mm']],
                ['fa' => ['قطر پیستون کناری', '50 mm'],                  'en' => ['Side piston diameter', '50 mm'],                  'ar' => ['قطر المكبس الجانبي', '50 mm']],
                ['fa' => ['برق', 'سه فاز – ۲۲۰ ولت'],                    'en' => ['Power supply', '220 V, 3-phase'],                 'ar' => ['التغذية الكهربائية', '220 فولت، ثلاثي الأطوار']],
                ['fa' => ['موتور', '۵٫۵ اسب'],                           'en' => ['Motor', '5.5 hp'],                               'ar' => ['المحرك', '5.5 حصان']],
                ['fa' => ['ابعاد', '1600 × 1118 × 1854 mm'],             'en' => ['Dimensions', '1600 × 1118 × 1854 mm'],            'ar' => ['الأبعاد', '1600 × 1118 × 1854 mm']],
                ['fa' => ['وزن', '400 kg'],                              'en' => ['Weight', '400 kg'],                              'ar' => ['الوزن', '400 kg']],
            ],
        ],
    ],
],

[
    'slug'       => 'tube-bending-machine-rb-6-16',
    'category'   => 'metal-industry-machines',
    'model_code' => 'RB-6-16',
    'featured'   => false,
    'cover'      => null,
    'gallery'    => [],
    'needs_review' => true,
    'source_ref' => 'محصولات-1/مشخصات محصولات شرکت رهیافت صنعت.docx',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه خم لوله مدل RB-6-16',
            'summary' => 'خم‌کن دستی لوله برای قطرهای ۶ تا ۱۶ میلیمتر با غلتک‌های شعاع خم 3D و 5D و قابلیت خم تا ۱۸۰ درجه.',
            'description' => "دستگاه خم لوله مدل RB-6-16 برای خم‌کاری لوله‌های با قطر کوچک طراحی شده و به‌صورت دستی کار می‌کند.\nمجموعه غلتک‌های دستگاه شعاع‌های خم 3D و 5D را برای قطرهای ۶، ۸، ۱۰، ۱۲ و ۱۶ میلیمتر پوشش می‌دهد و دستگاه قابلیت خم تا ۱۸۰ درجه را دارد.\nپایه نگهدارنده محکم و نگهدارنده غلتک از اجزای این دستگاه است.",
            'applications' => "خم‌کاری لوله‌های با قطر کوچک\nساخت مسیرهای هیدرولیک و پنوماتیک\nتعمیرگاه‌ها و کارگاه‌های صنعتی",
            'advantages'   => "مناسب برای خم کردن لوله‌های با قطر کوچک\nقابلیت خم تا ۱۸۰ درجه\nدارای غلتک‌های مناسب برای شعاع خم تا 3D و 5D\nپایه نگهدارنده محکم\nدارای نگهدارنده غلتک",
        ],
        'en' => [
            'name'    => 'RB-6-16 Tube Bending Machine',
            'summary' => 'Manual tube bender for 6–16 mm diameters, with 3D and 5D bend-radius rollers and bends up to 180 degrees.',
            'description' => "The RB-6-16 tube bending machine is designed for bending small-diameter tube and is operated manually.\nIts roller set covers 3D and 5D bend radii for 6, 8, 10, 12 and 16 mm diameters, and the machine bends up to 180 degrees.\nA rigid support stand and a roller holder are part of the machine.",
            'applications' => "Bending small-diameter tube\nBuilding hydraulic and pneumatic runs\nRepair shops and industrial workshops",
            'advantages'   => "Suited to bending small-diameter tube\nBends up to 180 degrees\nRollers for 3D and 5D bend radii\nRigid support stand\nIncludes a roller holder",
        ],
        'ar' => [
            'name'    => 'ماكينة ثني الأنابيب موديل RB-6-16',
            'summary' => 'ثنّاية أنابيب يدوية لأقطار 6 إلى 16 مم، ببكرات نصف قطر ثني 3D و5D وإمكانية الثني حتى 180 درجة.',
            'description' => "صُمّمت ماكينة ثني الأنابيب موديل RB-6-16 لثني الأنابيب صغيرة القطر وتعمل يدوياً.\nتغطي مجموعة البكرات أنصاف أقطار الثني 3D و5D للأقطار 6 و8 و10 و12 و16 مم، وتثني الماكينة حتى 180 درجة.\nتشمل الماكينة قاعدة سند متينة وحاملاً للبكرات.",
            'applications' => "ثني الأنابيب صغيرة القطر\nتنفيذ خطوط هيدروليكية وهوائية\nورش الصيانة والورش الصناعية",
            'advantages'   => "مناسبة لثني الأنابيب صغيرة القطر\nإمكانية الثني حتى 180 درجة\nبكرات لأنصاف أقطار ثني 3D و5D\nقاعدة سند متينة\nمزوّدة بحامل بكرات",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['مشخصه غلتک‌های خم‌کننده', '6mm×3D · 6mm×5D · 8mm×3D · 8mm×5D · 10mm×3D · 12mm×3D · 16mm×3D'],
                 'en' => ['Bending roller sizes', '6mm×3D · 6mm×5D · 8mm×3D · 8mm×5D · 10mm×3D · 12mm×3D · 16mm×3D'],
                 'ar' => ['مقاسات بكرات الثني', '6mm×3D · 6mm×5D · 8mm×3D · 8mm×5D · 10mm×3D · 12mm×3D · 16mm×3D']],
                ['fa' => ['نوع عملکرد', 'دستی'],                          'en' => ['Operation', 'Manual'],                    'ar' => ['طريقة التشغيل', 'يدوي']],
                ['fa' => ['ابعاد دستگاه (طول × عرض × ارتفاع)', '200 × 200 × 800 mm'], 'en' => ['Dimensions (L × W × H)', '200 × 200 × 800 mm'], 'ar' => ['الأبعاد (الطول × العرض × الارتفاع)', '200 × 200 × 800 mm']],
                ['fa' => ['وزن دستگاه', '50 kg'],                         'en' => ['Weight', '50 kg'],                        'ar' => ['الوزن', '50 kg']],
            ],
        ],
    ],
],

// ===========================================================================
// Wood & stone industry machines
// ===========================================================================

[
    'slug'       => 'cnc-modeling-machine',
    'category'   => 'wood-stone-machines',
    'model_code' => null,
    'featured'   => true,
    'cover'      => 'cnc-modeling',
    'gallery'    => ['cnc-modeling', 'cnc-modeling-2'],
    'source_ref' => 'محصولات-1/دستگاه سی ان سی مدلسازی/سی ان سی مدلسازی.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه CNC مدل‌سازی',
            'summary' => 'دستگاه CNC دروازه‌ای با محفظه بزرگ برای مدل‌سازی و ماشین‌کاری قطعات حجیم، مجهز به کنترلر رادونیکس و نرم‌افزار فارسی.',
            'description' => "دستگاه CNC مدل‌سازی برای ساخت مدل‌ها و قطعات حجیم طراحی شده و با سازه دروازه‌ای و محفظه کاری بزرگ ساخته می‌شود.\nسیستم حرکتی دستگاه از دنده‌های مورب، بالسکرو و سروو موتور تشکیل شده و کنترلر آن رادونیکس با نرم‌افزار دو زبانه فارسی و انگلیسی است.\nتعیین طول ابزار به‌صورت خودکار و با ارتفاع‌سنج عمق قطعه کار انجام می‌شود.",
            'applications' => "مدل‌سازی صنعتی\nساخت قالب و مدل قطعات حجیم\nصنایع چوب و کامپوزیت\nنمونه‌سازی سریع",
        ],
        'en' => [
            'name'    => 'CNC Modeling Machine',
            'summary' => 'Gantry CNC machine with a large enclosure for modelling and machining bulky parts, with a Radonix controller and bilingual software.',
            'description' => "The CNC modeling machine is built for producing models and bulky parts, using a gantry structure with a large working enclosure.\nMotion is by helical racks, ball screws and servo motors, and the machine runs a Radonix controller with software in both Persian and English.\nTool length is set automatically using a workpiece depth gauge.",
            'applications' => "Industrial modelling\nMould and large-part model making\nWood and composite industries\nRapid prototyping",
        ],
        'ar' => [
            'name'    => 'ماكينة CNC للنمذجة',
            'summary' => 'ماكينة CNC بهيكل بوابي وحجيرة عمل كبيرة للنمذجة وتشغيل القطع الضخمة، بوحدة تحكم Radonix وبرنامج ثنائي اللغة.',
            'description' => "صُمّمت ماكينة CNC للنمذجة لإنتاج النماذج والقطع الضخمة، بهيكل بوابي وحجيرة عمل كبيرة.\nتعتمد الحركة على تروس حلزونية ولوالب كروية ومحركات سيرفو، وتعمل بوحدة تحكم Radonix ببرنامج بالفارسية والإنجليزية.\nيُضبط طول الأداة آلياً بواسطة مقياس عمق قطعة العمل.",
            'applications' => "النمذجة الصناعية\nصناعة القوالب ونماذج القطع الكبيرة\nصناعات الخشب والمركّبات\nالنمذجة السريعة",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی دستگاه', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['ابعاد کارگیر', '۸۷۰ × ۱۵۰۰ × ۳۵۰۰ میلیمتر'],       'en' => ['Working area', '870 × 1500 × 3500 mm'],            'ar' => ['منطقة العمل', '870 × 1500 × 3500 mm']],
                ['fa' => ['ابعاد خارجی دستگاه', '۳۶۰۰ × ۲۸۰۰ × ۲۳۰۰ میلیمتر'],'en' => ['Overall dimensions', '3600 × 2800 × 2300 mm'],     'ar' => ['الأبعاد الخارجية', '3600 × 2800 × 2300 mm']],
                ['fa' => ['سیستم‌های حرکتی', 'دنده‌های مورب – بالسکرو – سروو موتور'], 'en' => ['Motion system', 'Helical rack – ball screw – servo motor'], 'ar' => ['نظام الحركة', 'تروس حلزونية – لولب كروي – محرك سيرفو']],
                ['fa' => ['ولتاژ مصرفی', '۳ فاز – ۲۲۰ ولت'],                  'en' => ['Power supply', '220 V, 3-phase'],                  'ar' => ['التغذية الكهربائية', '220 فولت، ثلاثي الأطوار']],
                ['fa' => ['کنترلر', 'رادونیکس'],                              'en' => ['Controller', 'Radonix'],                          'ar' => ['وحدة التحكم', 'Radonix']],
                ['fa' => ['زبان نرم‌افزار', 'فارسی – انگلیسی'],               'en' => ['Software language', 'Persian – English'],          'ar' => ['لغة البرنامج', 'الفارسية – الإنجليزية']],
                ['fa' => ['اسپیندل', '۷ کیلووات'],                            'en' => ['Spindle', '7 kW'],                                'ar' => ['محور الدوران', '7 كيلوواط']],
                ['fa' => ['تعیین طول ابزار', 'ارتفاع‌سنج اتوماتیک عمق قطعه کار'], 'en' => ['Tool length setting', 'Automatic workpiece depth gauge'], 'ar' => ['ضبط طول الأداة', 'مقياس عمق آلي لقطعة العمل']],
            ],
        ],
    ],
    'features' => [
        ['fa' => 'سیستم عیب‌یاب اتوماتیک هوشمند فارسی با PLC', 'en' => 'Intelligent Persian-language automatic fault diagnosis via PLC', 'ar' => 'نظام تشخيص أعطال آلي ذكي بالفارسية عبر PLC'],
        ['fa' => 'کنترل از راه دور', 'en' => 'Remote control', 'ar' => 'تحكم عن بُعد'],
        ['fa' => 'تبدیل سریع عکس به طرح', 'en' => 'Fast image-to-toolpath conversion', 'ar' => 'تحويل سريع للصورة إلى مسار قطع'],
        ['fa' => 'نمایش همزمان شکل و طرح', 'en' => 'Simultaneous display of shape and toolpath', 'ar' => 'عرض متزامن للشكل والمسار'],
        ['fa' => 'پرسرعت و ایمن', 'en' => 'Fast and safe operation', 'ar' => 'تشغيل سريع وآمن'],
        ['fa' => 'پذیرش کلیه اشکال و طرح‌ها از Photoshop، ArtCAM، CorelDRAW، AutoCAD و CATIA', 'en' => 'Accepts shapes and designs from Photoshop, ArtCAM, CorelDRAW, AutoCAD and CATIA', 'ar' => 'يقبل الأشكال والتصاميم من Photoshop وArtCAM وCorelDRAW وAutoCAD وCATIA'],
    ],
],

[
    'slug'       => 'cnc-wood-router-r250-390',
    'category'   => 'wood-stone-machines',
    'model_code' => 'R250-390',
    'featured'   => true,
    'cover'      => 'cnc-wood-390',
    'gallery'    => ['cnc-wood-390', 'cnc-wood-flat', 'cnc-wood-flat-front'],
    'source_ref' => 'محصولات-1/دستگاه سی ان سی چوب مدل 250-390/مشخصات فنی سی ان سی 250 در 390.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه CNC چوب مدل R250-390',
            'summary' => 'CNC چوب تخت و روتاری با ابعاد کارگیر ۱۹۰۰ در ۳۹۰۰ میلیمتر، مناسب برای ورق‌های بزرگ MDF.',
            'description' => "دستگاه CNC چوب مدل R250-390 بزرگ‌ترین مدل تخت و روتاری این خانواده است و برای کار روی ورق‌های بزرگ MDF طراحی شده است.\nمیز تخت دستگاه ابعاد ۱۹۰۰ در ۳۹۰۰ میلیمتر دارد و محور روتاری آن قطر ۳۰۰ و طول ۷۰۰ میلیمتر را پوشش می‌دهد.\nنصب قطعه کار با دو عدد پمپ وکیوم آبی با تقسیم‌بندی مناسب ۲۵۰ متر مکعب انجام می‌شود.",
            'applications' => "برش و حکاکی ورق‌های MDF\nتولید درب و کابینت\nصنایع دکوراسیون چوبی\nمنبت و حکاکی روتاری",
        ],
        'en' => [
            'name'    => 'R250-390 CNC Wood Router',
            'summary' => 'Flat-and-rotary CNC wood router with a 1900 × 3900 mm working area, sized for large MDF panels.',
            'description' => "The R250-390 is the largest flat-and-rotary model in this family, designed for working large MDF panels.\nThe flat table measures 1900 × 3900 mm and the rotary axis covers 300 mm diameter by 700 mm length.\nWorkpieces are held by two water-ring vacuum pumps with a well-divided 250 m³ capacity.",
            'applications' => "Cutting and engraving MDF panels\nDoor and cabinet production\nWooden interior decoration\nRotary carving and engraving",
        ],
        'ar' => [
            'name'    => 'راوتر CNC للخشب موديل R250-390',
            'summary' => 'راوتر CNC للخشب مسطح ودوّار بمنطقة عمل 1900 × 3900 مم، مخصص لألواح MDF الكبيرة.',
            'description' => "الموديل R250-390 هو الأكبر في عائلة الماكينات المسطحة والدوّارة، وقد صُمّم للعمل على ألواح MDF الكبيرة.\nتبلغ أبعاد الطاولة المسطحة 1900 × 3900 مم، ويغطي المحور الدوّار قطر 300 مم بطول 700 مم.\nيتم تثبيت قطعة العمل بمضختي تفريغ مائيتين بسعة 250 متراً مكعباً بتقسيم مناسب.",
            'applications' => "قص وحفر ألواح MDF\nإنتاج الأبواب والخزائن\nصناعات الديكور الخشبي\nالحفر والنقش الدوّار",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی دستگاه', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['ابعاد کارگیر تخت', '۱۹۰۰ × ۳۹۰۰ میلیمتر'],           'en' => ['Flat working area', '1900 × 3900 mm'],             'ar' => ['منطقة العمل المسطحة', '1900 × 3900 mm']],
                ['fa' => ['ابعاد کارگیر روتاری', '۳۰۰ میلیمتر قطر – ۷۰۰ میلیمتر طول'], 'en' => ['Rotary working area', '300 mm diameter – 700 mm length'], 'ar' => ['منطقة العمل الدوّارة', 'قطر 300 مم – طول 700 مم']],
                ['fa' => ['ابعاد خارجی دستگاه', '۲۶۳۲ × ۵۲۰۰ × ۲۰۰۰ میلیمتر'],  'en' => ['Overall dimensions', '2632 × 5200 × 2000 mm'],     'ar' => ['الأبعاد الخارجية', '2632 × 5200 × 2000 mm']],
                ['fa' => ['سیستم‌های حرکتی', 'دنده‌های مورب – بالسکرو – سروو موتور'], 'en' => ['Motion system', 'Helical rack – ball screw – servo motor'], 'ar' => ['نظام الحركة', 'تروس حلزونية – لولب كروي – محرك سيرفو']],
                ['fa' => ['ولتاژ مصرفی', '۳ فاز – ۲۲۰ ولت'],                    'en' => ['Power supply', '220 V, 3-phase'],                  'ar' => ['التغذية الكهربائية', '220 فولت، ثلاثي الأطوار']],
                ['fa' => ['کنترلر', 'رادونیکس'],                                'en' => ['Controller', 'Radonix'],                          'ar' => ['وحدة التحكم', 'Radonix']],
                ['fa' => ['زبان نرم‌افزار', 'فارسی – انگلیسی'],                 'en' => ['Software language', 'Persian – English'],          'ar' => ['لغة البرنامج', 'الفارسية – الإنجليزية']],
                ['fa' => ['اسپیندل', '۷ کیلووات'],                              'en' => ['Spindle', '7 kW'],                                'ar' => ['محور الدوران', '7 كيلوواط']],
                ['fa' => ['نحوه نصب قطعه کار', 'دو عدد پمپ وکیوم آبی با تقسیم‌بندی مناسب ۲۵۰ متر مکعب'], 'en' => ['Workholding', 'Two water-ring vacuum pumps, 250 m³, well divided'], 'ar' => ['تثبيت قطعة العمل', 'مضختا تفريغ مائيتان بسعة 250 م³ بتقسيم مناسب']],
            ],
        ],
    ],
],

[
    'slug'       => 'cnc-wood-router-r130-250',
    'category'   => 'wood-stone-machines',
    'model_code' => 'R130-250',
    'featured'   => true,
    'cover'      => 'cnc-wood-rotary',
    'gallery'    => ['cnc-wood-rotary'],
    'source_ref' => 'محصولات-1/دستگاه سی ان سی چوب مدل 130-250/مشخصات  سی ان سی 130 -250.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه CNC چوب مدل R130-250',
            'summary' => 'CNC چوب تخت و روتاری با میز ۱۳۰۰ در ۲۵۰۰ میلیمتر و محور روتاری بلند ۲۲۰۰ میلیمتری.',
            'description' => "دستگاه CNC چوب مدل R130-250 نسخه مجهز به محور روتاری از خانواده ۱۳۰-۲۵۰ است.\nمیز تخت دستگاه ابعاد ۱۳۰۰ در ۲۵۰۰ میلیمتر دارد و محور روتاری آن قطر ۳۰۰ و طول ۲۲۰۰ میلیمتر را پوشش می‌دهد که برای منبت و حکاکی روی قطعات بلند مانند ستون و نرده مناسب است.\nنصب قطعه کار با پمپ وکیوم آبی با تقسیم‌بندی مناسب ۲۵۰ متر مکعب انجام می‌شود.",
            'applications' => "برش و حکاکی ورق MDF\nمنبت روی ستون و نرده چوبی\nصنایع دکوراسیون چوبی\nتولید درب و کابینت",
        ],
        'en' => [
            'name'    => 'R130-250 CNC Wood Router',
            'summary' => 'Flat-and-rotary CNC wood router with a 1300 × 2500 mm table and a long 2200 mm rotary axis.',
            'description' => "The R130-250 is the rotary-equipped version of the 130-250 family.\nIts flat table measures 1300 × 2500 mm and the rotary axis covers 300 mm diameter by 2200 mm length, which suits carving and engraving long pieces such as columns and balusters.\nWorkholding is by a water-ring vacuum pump with a well-divided 250 m³ capacity.",
            'applications' => "Cutting and engraving MDF panels\nCarving wooden columns and balusters\nWooden interior decoration\nDoor and cabinet production",
        ],
        'ar' => [
            'name'    => 'راوتر CNC للخشب موديل R130-250',
            'summary' => 'راوتر CNC للخشب مسطح ودوّار بطاولة 1300 × 2500 مم ومحور دوّار طويل بطول 2200 مم.',
            'description' => "الموديل R130-250 هو النسخة المزوّدة بمحور دوّار من عائلة 130-250.\nتبلغ أبعاد الطاولة المسطحة 1300 × 2500 مم، ويغطي المحور الدوّار قطر 300 مم بطول 2200 مم، ما يناسب حفر ونقش القطع الطويلة كالأعمدة والدرابزين.\nيتم التثبيت بمضخة تفريغ مائية بسعة 250 متراً مكعباً بتقسيم مناسب.",
            'applications' => "قص وحفر ألواح MDF\nنقش الأعمدة والدرابزين الخشبي\nصناعات الديكور الخشبي\nإنتاج الأبواب والخزائن",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی دستگاه', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['ابعاد کارگیر تخت', '۱۳۰۰ × ۲۵۰۰ میلیمتر'],           'en' => ['Flat working area', '1300 × 2500 mm'],             'ar' => ['منطقة العمل المسطحة', '1300 × 2500 mm']],
                ['fa' => ['ابعاد کارگیر روتاری', '۳۰۰ میلیمتر قطر – ۲۲۰۰ میلیمتر طول'], 'en' => ['Rotary working area', '300 mm diameter – 2200 mm length'], 'ar' => ['منطقة العمل الدوّارة', 'قطر 300 مم – طول 2200 مم']],
                ['fa' => ['ابعاد خارجی دستگاه', '۲۳۳۱ × ۳۶۰۰ × ۲۰۰۰ میلیمتر'],  'en' => ['Overall dimensions', '2331 × 3600 × 2000 mm'],     'ar' => ['الأبعاد الخارجية', '2331 × 3600 × 2000 mm']],
                ['fa' => ['سیستم‌های حرکتی', 'دنده‌های مورب – بالسکرو – سروو موتور'], 'en' => ['Motion system', 'Helical rack – ball screw – servo motor'], 'ar' => ['نظام الحركة', 'تروس حلزونية – لولب كروي – محرك سيرفو']],
                ['fa' => ['ولتاژ مصرفی', '۳ فاز – ۲۲۰ ولت'],                    'en' => ['Power supply', '220 V, 3-phase'],                  'ar' => ['التغذية الكهربائية', '220 فولت، ثلاثي الأطوار']],
                ['fa' => ['کنترلر', 'رادونیکس'],                                'en' => ['Controller', 'Radonix'],                          'ar' => ['وحدة التحكم', 'Radonix']],
                ['fa' => ['زبان نرم‌افزار', 'فارسی – انگلیسی'],                 'en' => ['Software language', 'Persian – English'],          'ar' => ['لغة البرنامج', 'الفارسية – الإنجليزية']],
                ['fa' => ['اسپیندل', '۷ کیلووات'],                              'en' => ['Spindle', '7 kW'],                                'ar' => ['محور الدوران', '7 كيلوواط']],
                ['fa' => ['نحوه نصب قطعه کار', 'پمپ وکیوم آبی با تقسیم‌بندی مناسب ۲۵۰ متر مکعب'], 'en' => ['Workholding', 'Water-ring vacuum pump, 250 m³, well divided'], 'ar' => ['تثبيت قطعة العمل', 'مضخة تفريغ مائية بسعة 250 م³ بتقسيم مناسب']],
            ],
        ],
    ],
],

[
    'slug'       => 'cnc-wood-router-130-250',
    'category'   => 'wood-stone-machines',
    'model_code' => '130-250',
    'featured'   => false,
    'cover'      => 'cnc-wood-flat',
    'gallery'    => ['cnc-wood-flat', 'cnc-wood-flat-front'],
    'source_ref' => 'محصولات-1/مشخصات محصولات شرکت رهیافت صنعت.docx',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه CNC چوب مدل 130-250',
            'summary' => 'CNC چوب با میز تخت ۱۳۰۰ در ۲۵۰۰ میلیمتر، نسخه بدون محور روتاری با ابعاد خارجی جمع‌وجورتر.',
            'description' => "دستگاه CNC چوب مدل ۱۳۰-۲۵۰ نسخه تخت این خانواده است و برای برش و حکاکی ورق‌های MDF کوچک و متوسط به کار می‌رود.\nابعاد کارگیر ۱۳۰۰ در ۲۵۰۰ میلیمتر و ابعاد خارجی دستگاه ۲۰۳۲ در ۳۶۰۰ در ۲۰۰۰ میلیمتر است.\nنصب قطعه کار با پمپ وکیوم آبی با تقسیم‌بندی مناسب ۲۵۰ متر مکعب انجام می‌شود.",
            'applications' => "برش و حکاکی ورق MDF\nتولید درب و کابینت\nصنایع دکوراسیون چوبی",
        ],
        'en' => [
            'name'    => '130-250 CNC Wood Router',
            'summary' => 'CNC wood router with a 1300 × 2500 mm flat table — the non-rotary version, with a more compact footprint.',
            'description' => "The 130-250 is the flat-table version of this family, used for cutting and engraving small and medium MDF panels.\nThe working area is 1300 × 2500 mm and the machine measures 2032 × 3600 × 2000 mm overall.\nWorkholding is by a water-ring vacuum pump with a well-divided 250 m³ capacity.",
            'applications' => "Cutting and engraving MDF panels\nDoor and cabinet production\nWooden interior decoration",
        ],
        'ar' => [
            'name'    => 'راوتر CNC للخشب موديل 130-250',
            'summary' => 'راوتر CNC للخشب بطاولة مسطحة 1300 × 2500 مم — النسخة بدون محور دوّار وبمساحة أصغر.',
            'description' => "الموديل 130-250 هو النسخة ذات الطاولة المسطحة من هذه العائلة، ويُستخدم لقص وحفر ألواح MDF الصغيرة والمتوسطة.\nمنطقة العمل 1300 × 2500 مم والأبعاد الخارجية 2032 × 3600 × 2000 مم.\nيتم التثبيت بمضخة تفريغ مائية بسعة 250 متراً مكعباً بتقسيم مناسب.",
            'applications' => "قص وحفر ألواح MDF\nإنتاج الأبواب والخزائن\nصناعات الديكور الخشبي",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی دستگاه', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['ابعاد کارگیر (ورق MDF)', '۱۳۰۰ × ۲۵۰۰ میلیمتر'],   'en' => ['Working area (MDF panel)', '1300 × 2500 mm'],      'ar' => ['منطقة العمل (لوح MDF)', '1300 × 2500 mm']],
                ['fa' => ['ابعاد خارجی دستگاه', '۲۰۳۲ × ۳۶۰۰ × ۲۰۰۰ میلیمتر'],'en' => ['Overall dimensions', '2032 × 3600 × 2000 mm'],     'ar' => ['الأبعاد الخارجية', '2032 × 3600 × 2000 mm']],
                ['fa' => ['سیستم‌های حرکتی', 'دنده‌های مورب – سروو موتور'],    'en' => ['Motion system', 'Helical rack – servo motor'],     'ar' => ['نظام الحركة', 'تروس حلزونية – محرك سيرفو']],
                ['fa' => ['ولتاژ مصرفی', 'تک‌فاز و ۳ فاز به سفارش مشتری'],     'en' => ['Power supply', 'Single-phase or three-phase, to order'], 'ar' => ['التغذية الكهربائية', 'أحادي أو ثلاثي الأطوار حسب الطلب']],
                ['fa' => ['کنترلر', 'رادونیکس'],                              'en' => ['Controller', 'Radonix'],                          'ar' => ['وحدة التحكم', 'Radonix']],
                ['fa' => ['زبان نرم‌افزار', 'فارسی – انگلیسی'],               'en' => ['Software language', 'Persian – English'],          'ar' => ['لغة البرنامج', 'الفارسية – الإنجليزية']],
                ['fa' => ['توان اسپیندل', '۷ کیلووات'],                       'en' => ['Spindle power', '7 kW'],                          'ar' => ['قدرة محور الدوران', '7 كيلوواط']],
                ['fa' => ['تعیین طول ابزار', 'ارتفاع‌سنج اتوماتیک عمق قطعه کار'], 'en' => ['Tool length setting', 'Automatic workpiece depth gauge'], 'ar' => ['ضبط طول الأداة', 'مقياس عمق آلي لقطعة العمل']],
                ['fa' => ['نحوه نصب قطعه کار', 'پمپ وکیوم آبی با تقسیم‌بندی مناسب ۲۵۰ متر مکعب'], 'en' => ['Workholding', 'Water-ring vacuum pump, 250 m³, well divided'], 'ar' => ['تثبيت قطعة العمل', 'مضخة تفريغ مائية بسعة 250 م³ بتقسيم مناسب']],
            ],
        ],
    ],
],

[
    'slug'       => 'cnc-flat-rotary-10-head',
    'category'   => 'wood-stone-machines',
    'model_code' => null,
    'featured'   => false,
    'cover'      => 'cnc-rotary-10head',
    'gallery'    => ['cnc-rotary-10head'],
    'source_ref' => 'محصولات-1/دستگاه روتاری 10کله/مشخصات تخت و روتاری 10 هد.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه CNC تخت و روتاری ۱۰ کله',
            'summary' => 'دستگاه CNC چند اسپیندله با ۱۰ هد موازی برای تولید انبوه قطعات منبت و حکاکی‌شده.',
            'description' => "دستگاه CNC تخت و روتاری ۱۰ کله برای تولید انبوه طراحی شده و با ده اسپیندل موازی کار می‌کند؛ هر اسپیندل یک قطعه یکسان را همزمان ماشین‌کاری می‌کند.\nابعاد کارگیر روتاری برای هر اسپیندل ۴۰ در ۲۵۰ سانتیمتر است و مجموعه دستگاه ابعاد خارجی ۴ در ۶ متر با ارتفاع ۲٫۵ متر دارد.\nتوان اسپیندل‌ها بین ۴٫۵ تا ۷ کیلووات است و وزن کل دستگاه ۳۵۰۰ کیلوگرم است.",
            'applications' => "تولید انبوه قطعات منبت‌کاری‌شده\nتولید سری پایه، ستون و نرده چوبی\nصنایع مبلمان",
        ],
        'en' => [
            'name'    => 'Ten-Head Flat & Rotary CNC Machine',
            'summary' => 'Multi-spindle CNC machine with ten parallel heads for volume production of carved and engraved parts.',
            'description' => "The ten-head flat and rotary CNC machine is built for volume production, running ten spindles in parallel so that ten identical parts are machined at once.\nThe rotary working area is 40 × 250 cm per spindle, and the complete machine measures 4 × 6 m with a height of 2.5 m.\nSpindle power is between 4.5 and 7 kW and the machine weighs 3500 kg.",
            'applications' => "Volume production of carved parts\nBatch production of wooden legs, columns and balusters\nFurniture industry",
        ],
        'ar' => [
            'name'    => 'ماكينة CNC مسطحة ودوّارة بعشرة رؤوس',
            'summary' => 'ماكينة CNC متعددة المحاور بعشرة رؤوس متوازية للإنتاج الكمي للقطع المحفورة والمنقوشة.',
            'description' => "صُمّمت الماكينة المسطحة والدوّارة بعشرة رؤوس للإنتاج الكمي، وتعمل بعشرة محاور دوران متوازية تُشغّل عشر قطع متطابقة في آن واحد.\nمنطقة العمل الدوّارة لكل محور 40 × 250 سم، وتبلغ أبعاد الماكينة الكاملة 4 × 6 أمتار بارتفاع 2.5 متر.\nقدرة المحاور بين 4.5 و7 كيلوواط ووزن الماكينة 3500 كجم.",
            'applications' => "الإنتاج الكمي للقطع المحفورة\nالإنتاج المتسلسل للأرجل والأعمدة والدرابزين الخشبي\nصناعة الأثاث",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی دستگاه', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['ابعاد کارگیر روتاری برای هر اسپیندل (هد)', '۴۰ سانتیمتر در ۲۵۰ سانتیمتر'], 'en' => ['Rotary working area per spindle (head)', '40 cm × 250 cm'], 'ar' => ['منطقة العمل الدوّارة لكل محور (رأس)', '40 سم × 250 سم']],
                ['fa' => ['ابعاد خارجی دستگاه', '۴ متر در ۶ متر و ۲٫۵ متر ارتفاع'], 'en' => ['Overall dimensions', '4 m × 6 m, 2.5 m high'],   'ar' => ['الأبعاد الخارجية', '4 م × 6 م بارتفاع 2.5 م']],
                ['fa' => ['سیستم‌های حرکتی', 'دنده‌های مورب – بالسکرو – سروو موتور'], 'en' => ['Motion system', 'Helical rack – ball screw – servo motor'], 'ar' => ['نظام الحركة', 'تروس حلزونية – لولب كروي – محرك سيرفو']],
                ['fa' => ['ولتاژ مصرفی', '۳ فاز – ۲۲۰ ولت'],                    'en' => ['Power supply', '220 V, 3-phase'],                  'ar' => ['التغذية الكهربائية', '220 فولت، ثلاثي الأطوار']],
                ['fa' => ['کنترلر', 'رادونیکس'],                                'en' => ['Controller', 'Radonix'],                          'ar' => ['وحدة التحكم', 'Radonix']],
                ['fa' => ['زبان نرم‌افزار', 'فارسی – انگلیسی'],                 'en' => ['Software language', 'Persian – English'],          'ar' => ['لغة البرنامج', 'الفارسية – الإنجليزية']],
                ['fa' => ['اسپیندل', '۱۰ اسپیندل ۴٫۵ تا ۷ کیلووات'],           'en' => ['Spindles', '10 spindles, 4.5–7 kW'],               'ar' => ['محاور الدوران', '10 محاور بقدرة 4.5–7 كيلوواط']],
                ['fa' => ['وزن', '۳۵۰۰ کیلوگرم'],                              'en' => ['Weight', '3500 kg'],                              'ar' => ['الوزن', '3500 كجم']],
            ],
        ],
    ],
],

[
    'slug'       => 'cnc-stone-machine-1225',
    'category'   => 'wood-stone-machines',
    'model_code' => '1225',
    'featured'   => true,
    'cover'      => 'cnc-stone',
    'gallery'    => ['cnc-stone'],
    'source_ref' => 'محصولات-1/دستگاه سی ان سی سنگ/مشخصات فنی سی ان سی سنگ.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه CNC سنگ مدل ۱۲۲۵',
            'summary' => 'CNC سنگ با محفظه کاملاً بسته، ابعاد کارگیر ۲۵۰۰ در ۱۵۰۰ در ۳۵۰ میلیمتر و اسپیندل ۷٫۵ کیلووات.',
            'description' => "دستگاه CNC سنگ مدل ۱۲۲۵ برای برش، حکاکی و فرم‌دهی سنگ طراحی شده و دارای محفظه کاملاً بسته برای مهار پاشش آب و گرد و غبار است.\nابعاد کارگیر دستگاه ۲۵۰۰ در ۱۵۰۰ در ۳۵۰ میلیمتر و ابعاد خارجی آن ۴۶۷۹ در ۲۵۰۰ در ۲۰۷۵ میلیمتر است.\nسیستم حرکتی از دنده‌های مورب، بالسکرو و سروو موتور تشکیل شده و کنترلر آن رادونیکس با نرم‌افزار دوزبانه است.",
            'applications' => "برش و حکاکی سنگ ساختمانی\nساخت سنگ قبر و کتیبه\nتولید نمای سنگی\nصنایع تزئینی سنگ",
        ],
        'en' => [
            'name'    => 'Model 1225 CNC Stone Machine',
            'summary' => 'Fully enclosed CNC stone machine with a 2500 × 1500 × 350 mm working area and a 7.5 kW spindle.',
            'description' => "The model 1225 CNC stone machine is designed for cutting, engraving and shaping stone, with a fully enclosed cabin that contains water spray and dust.\nThe working area is 2500 × 1500 × 350 mm and the machine measures 4679 × 2500 × 2075 mm overall.\nMotion is by helical racks, ball screws and servo motors, under a Radonix controller with bilingual software.",
            'applications' => "Cutting and engraving building stone\nHeadstone and inscription work\nStone façade production\nDecorative stonework",
        ],
        'ar' => [
            'name'    => 'ماكينة CNC للحجر موديل 1225',
            'summary' => 'ماكينة CNC للحجر بحجيرة مغلقة تماماً ومنطقة عمل 2500 × 1500 × 350 مم ومحور دوران 7.5 كيلوواط.',
            'description' => "صُمّمت ماكينة CNC للحجر موديل 1225 لقص ونقش وتشكيل الحجر، بحجيرة مغلقة تماماً تحتوي رذاذ الماء والغبار.\nمنطقة العمل 2500 × 1500 × 350 مم والأبعاد الخارجية 4679 × 2500 × 2075 مم.\nتعتمد الحركة على تروس حلزونية ولوالب كروية ومحركات سيرفو بوحدة تحكم Radonix ببرنامج ثنائي اللغة.",
            'applications' => "قص ونقش حجر البناء\nصناعة شواهد القبور والنقوش\nإنتاج الواجهات الحجرية\nالأعمال الحجرية الزخرفية",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی دستگاه', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['ابعاد کارگیر', '۲۵۰۰ × ۱۵۰۰ × ۳۵۰ میلیمتر'],        'en' => ['Working area', '2500 × 1500 × 350 mm'],            'ar' => ['منطقة العمل', '2500 × 1500 × 350 mm']],
                ['fa' => ['ابعاد خارجی دستگاه', '۴۶۷۹ × ۲۵۰۰ × ۲۰۷۵ میلیمتر'], 'en' => ['Overall dimensions', '4679 × 2500 × 2075 mm'],     'ar' => ['الأبعاد الخارجية', '4679 × 2500 × 2075 mm']],
                ['fa' => ['سیستم‌های حرکتی', 'دنده‌های مورب – بالسکرو – سروو موتور'], 'en' => ['Motion system', 'Helical rack – ball screw – servo motor'], 'ar' => ['نظام الحركة', 'تروس حلزونية – لولب كروي – محرك سيرفو']],
                ['fa' => ['ولتاژ مصرفی', '۳ فاز – ۲۲۰ ولت'],                    'en' => ['Power supply', '220 V, 3-phase'],                  'ar' => ['التغذية الكهربائية', '220 فولت، ثلاثي الأطوار']],
                ['fa' => ['کنترلر', 'رادونیکس'],                                'en' => ['Controller', 'Radonix'],                          'ar' => ['وحدة التحكم', 'Radonix']],
                ['fa' => ['زبان نرم‌افزار', 'فارسی – انگلیسی'],                 'en' => ['Software language', 'Persian – English'],          'ar' => ['لغة البرنامج', 'الفارسية – الإنجليزية']],
                ['fa' => ['توان اسپیندل', '۷٫۵ کیلووات'],                       'en' => ['Spindle power', '7.5 kW'],                        'ar' => ['قدرة محور الدوران', '7.5 كيلوواط']],
            ],
        ],
    ],
],

[
    'slug'       => 'manual-wood-turning-lathe',
    'category'   => 'wood-stone-machines',
    'model_code' => null,
    'featured'   => false,
    'cover'      => 'wood-lathe',
    'gallery'    => ['wood-lathe'],
    'source_ref' => 'محصولات-1/دستگاه خراطی/مشخصات فنی خراطی.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'دستگاه خراطی دستی',
            'summary' => 'خراطی چوب دستی با ظرفیت قطر ۵۰۰ و طول ۷۵۰ میلیمتر، اسپیندل ۳ کیلووات و دور متغیر ۶۰ تا ۳۵۰۰ دور بر دقیقه.',
            'description' => "دستگاه خراطی دستی برای تراش و فرم‌دهی قطعات چوبی گرد طراحی شده است.\nظرفیت کارگیر دستگاه ۵۰۰ میلیمتر قطر و ۷۵۰ میلیمتر طول است و موتور آن با دور متغیر از ۶۰ تا ۳۵۰۰ دور بر دقیقه کار می‌کند.\nمرغک دستگاه از نوع دنباله مورس شماره ۲ است و تغذیه برق آن تک‌فاز ۲۲۰ ولت است.",
            'applications' => "تراش پایه و ستون چوبی\nساخت قطعات گرد تزئینی\nصنایع مبلمان\nکارگاه‌های نجاری",
        ],
        'en' => [
            'name'    => 'Manual Wood Turning Lathe',
            'summary' => 'Manual wood lathe taking 500 mm diameter by 750 mm length, with a 3 kW spindle and variable 60–3500 rpm.',
            'description' => "The manual wood turning lathe is built for turning and shaping round wooden parts.\nIt takes work up to 500 mm in diameter and 750 mm long, and the motor runs at a variable 60 to 3500 rpm.\nThe tailstock takes a No. 2 Morse taper, and the machine runs on a single-phase 220 V supply.",
            'applications' => "Turning wooden legs and columns\nMaking decorative round parts\nFurniture industry\nJoinery workshops",
        ],
        'ar' => [
            'name'    => 'مخرطة خشب يدوية',
            'summary' => 'مخرطة خشب يدوية تستوعب قطر 500 مم بطول 750 مم، بمحور دوران 3 كيلوواط وسرعة متغيرة 60–3500 دورة/دقيقة.',
            'description' => "صُنعت مخرطة الخشب اليدوية لخراطة وتشكيل القطع الخشبية الدائرية.\nتستوعب قطعاً حتى 500 مم قطراً و750 مم طولاً، ويعمل المحرك بسرعة متغيرة من 60 إلى 3500 دورة في الدقيقة.\nالمسند الخلفي بمخروط مورس رقم 2، والتغذية أحادية الطور 220 فولت.",
            'applications' => "خراطة الأرجل والأعمدة الخشبية\nصناعة القطع الدائرية الزخرفية\nصناعة الأثاث\nورش النجارة",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['ابعاد کارگیر', '۵۰۰ میلیمتر قطر و ۷۵۰ میلیمتر طول'], 'en' => ['Working capacity', '500 mm diameter × 750 mm length'], 'ar' => ['سعة العمل', 'قطر 500 مم × طول 750 مم']],
                ['fa' => ['ابعاد خارجی دستگاه', '۶۱۰ × ۱۲۵۰ × ۱۶۵۰ میلیمتر'],  'en' => ['Overall dimensions', '610 × 1250 × 1650 mm'],      'ar' => ['الأبعاد الخارجية', '610 × 1250 × 1650 mm']],
                ['fa' => ['ولتاژ مصرفی', 'تک‌فاز – ۲۲۰ ولت'],                   'en' => ['Power supply', '220 V, single-phase'],             'ar' => ['التغذية الكهربائية', '220 فولت، أحادي الطور']],
                ['fa' => ['دور موتور', 'از ۶۰ تا ۳۵۰۰ دور بر دقیقه'],           'en' => ['Motor speed', '60 to 3500 rpm'],                   'ar' => ['سرعة المحرك', 'من 60 إلى 3500 دورة/دقيقة']],
                ['fa' => ['اسپیندل', '۳ کیلووات'],                              'en' => ['Spindle', '3 kW'],                                'ar' => ['محور الدوران', '3 كيلوواط']],
                ['fa' => ['مرغک', 'دنباله مورس شماره ۲'],                       'en' => ['Tailstock', 'No. 2 Morse taper'],                  'ar' => ['المسند الخلفي', 'مخروط مورس رقم 2']],
            ],
        ],
    ],
],

// ===========================================================================
// Industrial equipment & accessories
// ===========================================================================

[
    'slug'       => 'screw-air-compressor',
    'category'   => 'industrial-equipment',
    'model_code' => null,
    'featured'   => true,
    'cover'      => 'screw-compressor',
    'gallery'    => ['screw-compressor', 'screw-compressor-unit', 'screw-compressor-panel', 'screw-compressor-cut'],
    'source_ref' => 'محصولات-1/مشخصات محصولات شرکت رهیافت صنعت.docx',
    'tr' => [
        'fa' => [
            'name'    => 'کمپرسور باد اسکرو',
            'summary' => 'کمپرسور باد دورانی اسکرو با موتور ۲۲ کیلووات (۳۰ اسب)، دبی خروجی ۲٫۸ متر مکعب بر دقیقه و فشار کاری ۶ تا ۱۰ بار.',
            'description' => "کمپرسور باد اسکرو رهیافت صنعت برای تأمین هوای فشرده پیوسته در کارگاه‌ها و خطوط تولید صنعتی ساخته می‌شود.\nموتور دستگاه ۲۲ کیلووات معادل ۳۰ اسب بخار با دور ۳۰۰۰ دور بر دقیقه است و بیشترین دبی خروجی باد ۲٫۸ متر مکعب بر دقیقه در فشار ۶ تا ۱۰ بار را تأمین می‌کند.\nدستگاه در بدنه‌ای عایق‌بندی‌شده با تابلو کنترل شامل گیج فشار، کلید استارت و استپ، شستی اضطراری و هشدار دما عرضه می‌شود.",
            'applications' => "تأمین هوای فشرده خطوط تولید\nابزارهای پنوماتیک کارگاهی\nخطوط رنگ و سندبلاست\nصنایع فلزی و چوب",
        ],
        'en' => [
            'name'    => 'Rotary Screw Air Compressor',
            'summary' => 'Rotary screw air compressor with a 22 kW (30 hp) motor, 2.8 m³/min free air delivery and a 6–10 bar working pressure.',
            'description' => "The Rahyaft Sanat screw air compressor supplies continuous compressed air for workshops and industrial production lines.\nIts motor is rated at 22 kW (30 hp) at 3000 rpm and delivers up to 2.8 m³/min of air at 6 to 10 bar.\nThe unit is supplied in an insulated enclosure with a control panel carrying a pressure gauge, start and stop buttons, an emergency stop and a temperature warning lamp.",
            'applications' => "Compressed air for production lines\nWorkshop pneumatic tools\nPainting and sandblasting lines\nMetal and wood industries",
        ],
        'ar' => [
            'name'    => 'ضاغط هواء لولبي',
            'summary' => 'ضاغط هواء لولبي دوّار بمحرك 22 كيلوواط (30 حصان) وتصريف هواء 2.8 م³/دقيقة وضغط تشغيل 6–10 بار.',
            'description' => "يوفّر ضاغط الهواء اللولبي من رهيافت صنعت هواءً مضغوطاً مستمراً للورش وخطوط الإنتاج الصناعية.\nمحركه بقدرة 22 كيلوواط (30 حصاناً) عند 3000 دورة في الدقيقة، ويصرّف حتى 2.8 م³/دقيقة عند ضغط 6 إلى 10 بار.\nتُورَّد الوحدة داخل هيكل معزول مع لوحة تحكم تضم مقياس ضغط ومفتاحي تشغيل وإيقاف وزر طوارئ ومصباح تحذير الحرارة.",
            'applications' => "تغذية خطوط الإنتاج بالهواء المضغوط\nالأدوات الهوائية في الورش\nخطوط الدهان والسفع الرملي\nصناعات المعادن والخشب",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['fa' => ['تکنولوژی کمپرسور', 'Rotary-Screw'],                'en' => ['Compressor technology', 'Rotary screw'],           'ar' => ['تقنية الضاغط', 'لولبي دوّار']],
                ['fa' => ['موتور', '22 kW / 30 Hp – 3000 RPM'],               'en' => ['Motor', '22 kW / 30 hp – 3000 rpm'],               'ar' => ['المحرك', '22 kW / 30 Hp – 3000 RPM']],
                ['fa' => ['جریان ورودی', '25 A'],                             'en' => ['Input current', '25 A'],                           'ar' => ['تيار الدخل', '25 A']],
                ['fa' => ['ولتاژ', '220 V – 3 Ph'],                           'en' => ['Voltage', '220 V – 3 Ph'],                         'ar' => ['الجهد', '220 V – 3 Ph']],
                ['fa' => ['بیشترین دبی خروجی باد', '2.8 m³/min'],             'en' => ['Max free air delivery', '2.8 m³/min'],              'ar' => ['أقصى تصريف هواء', '2.8 m³/min']],
                ['fa' => ['فشار باد', '6–10 bar (70–140 psi)'],               'en' => ['Air pressure', '6–10 bar (70–140 psi)'],            'ar' => ['ضغط الهواء', '6–10 bar (70–140 psi)']],
                ['fa' => ['ظرفیت مخزن پمپ', '16 لیتر'],                       'en' => ['Pump reservoir capacity', '16 litres'],             'ar' => ['سعة خزان المضخة', '16 لتراً']],
                ['fa' => ['ابعاد', '900 × 1400 × 900 mm'],                    'en' => ['Dimensions', '900 × 1400 × 900 mm'],                'ar' => ['الأبعاد', '900 × 1400 × 900 mm']],
                ['fa' => ['وزن', '700 kg'],                                   'en' => ['Weight', '700 kg'],                                'ar' => ['الوزن', '700 kg']],
            ],
        ],
    ],
],

[
    'slug'       => 'cnc-fourth-axis',
    'category'   => 'industrial-equipment',
    'model_code' => 'FA100 / FA160',
    'featured'   => false,
    'cover'      => 'cnc-4th-axis',
    'gallery'    => ['cnc-4th-axis'],
    'source_ref' => 'محصولات-1/مشخصات محصولات شرکت رهیافت صنعت.docx',
    'tr' => [
        'fa' => [
            'name'    => 'محور چهارم CNC',
            'summary' => 'میز دوار محور چهارم برای مراکز ماشین‌کاری CNC، در دو مدل FA100 و FA160 با نسبت دنده ۶۰ به ۱.',
            'description' => "محور چهارم CNC یک میز دوار است که روی میز مراکز ماشین‌کاری نصب می‌شود و امکان ماشین‌کاری چهار محوره را فراهم می‌کند.\nاین محصول در دو مدل عرضه می‌شود: FA100 با قطر پیشانی ۱۰۰ میلیمتر برای قطعات سبک‌تر و سریع‌تر، و FA160 با قطر پیشانی ۱۶۰ میلیمتر برای قطعات سنگین‌تر و گشتاور بالاتر.\nهر دو مدل با نسبت دنده ۶۰ به ۱ و ترمز پنوماتیک کار می‌کنند.",
            'applications' => "ماشین‌کاری چهار محوره\nتراش و فرزکاری قطعات دوار\nقالب‌سازی\nتولید قطعات صنعتی پیچیده",
        ],
        'en' => [
            'name'    => 'CNC Fourth Axis',
            'summary' => 'Rotary table fourth axis for CNC machining centres, in FA100 and FA160 models with a 60:1 gear ratio.',
            'description' => "The CNC fourth axis is a rotary table that mounts on a machining-centre table to enable four-axis machining.\nTwo models are available: the FA100, with a 100 mm faceplate, for lighter and faster work, and the FA160, with a 160 mm faceplate, for heavier parts and higher torque.\nBoth run a 60:1 gear ratio with a pneumatic brake.",
            'applications' => "Four-axis machining\nTurning and milling rotational parts\nMould and die making\nComplex industrial component production",
        ],
        'ar' => [
            'name'    => 'المحور الرابع CNC',
            'summary' => 'طاولة دوّارة كمحور رابع لمراكز التشغيل CNC، بموديلين FA100 وFA160 بنسبة تروس 60:1.',
            'description' => "المحور الرابع CNC عبارة عن طاولة دوّارة تُركّب على طاولة مركز التشغيل لإتاحة التشغيل رباعي المحاور.\nيتوفر موديلان: FA100 بقطر وجه 100 مم للأعمال الخفيفة والأسرع، وFA160 بقطر وجه 160 مم للقطع الأثقل وعزم أعلى.\nيعمل الموديلان بنسبة تروس 60:1 مع فرامل هوائية.",
            'applications' => "التشغيل رباعي المحاور\nخراطة وتفريز القطع الدوّارة\nصناعة القوالب\nإنتاج المكوّنات الصناعية المعقدة",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مدل FA100', 'en' => 'Model FA100', 'ar' => 'موديل FA100'],
            'rows'  => [
                ['fa' => ['قطر پیشانی دستگاه', '۱۰۰ میلیمتر'],           'en' => ['Faceplate diameter', '100 mm'],            'ar' => ['قطر الوجه', '100 مم']],
                ['fa' => ['بیشترین وزن قابل تحمل', '۱۳ کیلوگرم'],        'en' => ['Maximum load', '13 kg'],                   'ar' => ['أقصى حمل', '13 كجم']],
                ['fa' => ['بیشترین سرعت', '۳۰۰ درجه بر ثانیه'],          'en' => ['Maximum speed', '300 °/s'],                'ar' => ['أقصى سرعة', '300 درجة/ثانية']],
                ['fa' => ['بیشترین گشتاور', '۴۵ نیوتن متر'],             'en' => ['Maximum torque', '45 Nm'],                 'ar' => ['أقصى عزم', '45 نيوتن·متر']],
                ['fa' => ['ارتفاع محور تا کف میز', '۸۹ میلیمتر'],        'en' => ['Centre height above table', '89 mm'],      'ar' => ['ارتفاع المحور عن الطاولة', '89 مم']],
                ['fa' => ['گشتاور ترمز در فشار ۷ بار', '۳۵ نیوتن متر'],  'en' => ['Brake torque at 7 bar', '35 Nm'],          'ar' => ['عزم الفرملة عند 7 بار', '35 نيوتن·متر']],
                ['fa' => ['نسبت دنده', '۶۰ به ۱'],                       'en' => ['Gear ratio', '60:1'],                      'ar' => ['نسبة التروس', '60:1']],
                ['fa' => ['وزن', '۲۰ کیلوگرم'],                          'en' => ['Weight', '20 kg'],                         'ar' => ['الوزن', '20 كجم']],
            ],
        ],
        [
            'title' => ['fa' => 'مدل FA160', 'en' => 'Model FA160', 'ar' => 'موديل FA160'],
            'rows'  => [
                ['fa' => ['قطر پیشانی دستگاه', '۱۶۰ میلیمتر'],            'en' => ['Faceplate diameter', '160 mm'],            'ar' => ['قطر الوجه', '160 مم']],
                ['fa' => ['بیشترین وزن قابل تحمل', '۴۰ کیلوگرم'],         'en' => ['Maximum load', '40 kg'],                   'ar' => ['أقصى حمل', '40 كجم']],
                ['fa' => ['بیشترین سرعت', '۱۳۰ درجه بر ثانیه'],           'en' => ['Maximum speed', '130 °/s'],                'ar' => ['أقصى سرعة', '130 درجة/ثانية']],
                ['fa' => ['بیشترین گشتاور', '۲۰۰ نیوتن متر'],             'en' => ['Maximum torque', '200 Nm'],                'ar' => ['أقصى عزم', '200 نيوتن·متر']],
                ['fa' => ['ارتفاع محور تا کف میز', '۱۲۷ میلیمتر'],        'en' => ['Centre height above table', '127 mm'],     'ar' => ['ارتفاع المحور عن الطاولة', '127 مم']],
                ['fa' => ['گشتاور ترمز در فشار ۷ بار', '۱۳۶ نیوتن متر'],  'en' => ['Brake torque at 7 bar', '136 Nm'],         'ar' => ['عزم الفرملة عند 7 بار', '136 نيوتن·متر']],
                ['fa' => ['نسبت دنده', '۶۰ به ۱'],                        'en' => ['Gear ratio', '60:1'],                      'ar' => ['نسبة التروس', '60:1']],
                ['fa' => ['وزن', '۶۴ کیلوگرم'],                           'en' => ['Weight', '64 kg'],                         'ar' => ['الوزن', '64 كجم']],
            ],
        ],
    ],
],

[
    'slug'       => 'angle-head-iso40',
    'category'   => 'industrial-equipment',
    'model_code' => 'Angle Head ISO40',
    'featured'   => false,
    'cover'      => 'angle-head',
    'gallery'    => ['angle-head'],
    'source_ref' => 'محصولات-1/Angle Head-BT40/مشخصات فنی انگل هد بی تی 40.pdf',
    'tr' => [
        'fa' => [
            'name'    => 'انگل هد ISO40',
            'summary' => 'کله‌گی زاویه‌دار با کله‌گی ISO40، کولت ER25، گشتاور ۲۴ نیوتن متر و بیشینه دور ۸۰۰۰ دور بر دقیقه.',
            'description' => "انگل هد ISO40 تجهیزی جانبی برای مراکز ماشین‌کاری است که امکان ماشین‌کاری در راستای عمود بر محور اسپیندل را فراهم می‌کند.\nاین تجهیز با نسبت دنده ۱ به ۱ کار می‌کند، گشتاور ۲۴ نیوتن متر را منتقل می‌کند و بار محوری تا ۵۱۰ نیوتن را تحمل می‌کند.\nخروجی آن از نوع کولت ER25 و وزن مجموعه ۴٫۵ کیلوگرم است.",
            'applications' => "ماشین‌کاری زاویه‌دار و جانبی\nسوراخ‌کاری و قلاویزکاری عمود بر محور\nقالب‌سازی\nماشین‌کاری قطعات با دسترسی محدود",
        ],
        'en' => [
            'name'    => 'ISO40 Angle Head',
            'summary' => 'Right-angle head with an ISO40 taper, ER25 collet, 24 Nm torque and up to 8000 rpm.',
            'description' => "The ISO40 angle head is an accessory for machining centres that allows cutting perpendicular to the spindle axis.\nIt runs a 1:1 gear ratio, transmits 24 Nm of torque and withstands an axial load of up to 510 N.\nThe output takes an ER25 collet and the assembly weighs 4.5 kg.",
            'applications' => "Angled and side machining\nCross-axis drilling and tapping\nMould and die making\nMachining parts with restricted access",
        ],
        'ar' => [
            'name'    => 'رأس زاوي ISO40',
            'summary' => 'رأس زاوي بمخروط ISO40 وكولّيت ER25 وعزم 24 نيوتن·متر وسرعة تصل إلى 8000 دورة/دقيقة.',
            'description' => "الرأس الزاوي ISO40 ملحق لمراكز التشغيل يتيح القطع عمودياً على محور الدوران.\nيعمل بنسبة تروس 1:1 وينقل عزماً مقداره 24 نيوتن·متر ويتحمّل حملاً محورياً حتى 510 نيوتن.\nمخرجه يقبل كولّيت ER25 ويزن المجموع 4.5 كجم.",
            'applications' => "التشغيل الزاوي والجانبي\nالثقب واللولبة العمودية على المحور\nصناعة القوالب\nتشغيل القطع ذات الوصول المحدود",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی', 'en' => 'Technical specifications', 'ar' => 'المواصفات الفنية'],
            'rows'  => [
                ['label' => 'Taper',          'value' => 'ISO40'],
                ['label' => 'Collet',         'value' => 'ER25 ⌀1/16'],
                ['label' => 'Output',         'value' => '⌀16'],
                ['label' => 'Shaft Output',   'value' => 'ER'],
                ['label' => 'Gear Ratio',     'value' => '1:1'],
                ['label' => 'Torque',         'value' => '24 Nm'],
                ['label' => 'Axial Load',     'value' => '510 N'],
                ['label' => 'Tapping',        'value' => 'M12'],
                ['label' => 'Rotation Speed', 'value' => '8000 rpm'],
                ['label' => 'Weight',         'value' => '4.5 kg'],
            ],
        ],
    ],
],

[
    'slug'       => 'bt30-air-cooled-spindle',
    'category'   => 'industrial-equipment',
    'model_code' => 'BT30',
    'featured'   => false,
    'cover'      => 'bt30-spindle',
    'gallery'    => ['bt30-spindle', 'bt30-spindle-drawing'],
    'source_ref' => 'محصولات-1/اسپیندل بی تی 30/مشخصات فنی اسپیندل.PNG',
    'tr' => [
        'fa' => [
            'name'    => 'اسپیندل هوا خنک BT30',
            'summary' => 'اسپیندل هوا خنک با کله‌گی BT30، تعویض ابزار پنوماتیک و دور تا ۴۰۰۰ دور بر دقیقه.',
            'description' => "اسپیندل هوا خنک BT30 برای نصب روی مراکز ماشین‌کاری و دستگاه‌های فرز CNC ساخته می‌شود.\nاین اسپیندل از بلبرینگ‌های سرعت بالا با عملکرد عالی بهره می‌برد و به سیستم تعویض ابزار پنوماتیکی مجهز است.\nطول کل مجموعه ۲۹ سانتیمتر و قطر فلنج آن ۱۲۷ میلیمتر است و روانکاری آن با گریس انجام می‌شود.",
            'applications' => "مراکز ماشین‌کاری CNC\nدستگاه‌های فرز CNC\nبازسازی و ارتقای دستگاه‌های موجود",
        ],
        'en' => [
            'name'    => 'BT30 Air-Cooled Spindle',
            'summary' => 'Air-cooled spindle with a BT30 taper, pneumatic tool change and speeds up to 4000 rpm.',
            'description' => "The BT30 air-cooled spindle is built for fitting to machining centres and CNC milling machines.\nIt runs on high-speed bearings with excellent performance and is fitted with pneumatic tool change.\nOverall length is 29 cm with a 127 mm flange diameter, and lubrication is by grease.",
            'applications' => "CNC machining centres\nCNC milling machines\nRetrofitting and upgrading existing machines",
        ],
        'ar' => [
            'name'    => 'محور دوران BT30 مبرّد بالهواء',
            'summary' => 'محور دوران مبرّد بالهواء بمخروط BT30 وتغيير أدوات هوائي وسرعة تصل إلى 4000 دورة/دقيقة.',
            'description' => "صُنع محور الدوران BT30 المبرّد بالهواء للتركيب على مراكز التشغيل وماكينات التفريز CNC.\nيعمل على محامل عالية السرعة بأداء ممتاز، ومزوّد بنظام تغيير أدوات هوائي.\nالطول الكلي 29 سم وقطر الفلنجة 127 مم، والتشحيم بالشحم.",
            'applications' => "مراكز التشغيل CNC\nماكينات التفريز CNC\nتحديث وترقية الماكينات القائمة",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'مشخصات فنی اسپیندل', 'en' => 'Spindle specifications', 'ar' => 'مواصفات محور الدوران'],
            'rows'  => [
                ['fa' => ['نوع اسپیندل', 'هوا خنک BT30'],                          'en' => ['Spindle type', 'Air-cooled BT30'],                       'ar' => ['نوع محور الدوران', 'مبرّد بالهواء BT30']],
                ['fa' => ['بلبرینگ', 'بلبرینگ‌های سرعت بالا با عملکرد عالی'],      'en' => ['Bearings', 'High-speed bearings with excellent performance'], 'ar' => ['المحامل', 'محامل عالية السرعة بأداء ممتاز']],
                ['fa' => ['تعویض ابزار', 'پنوماتیکی'],                             'en' => ['Tool change', 'Pneumatic'],                              'ar' => ['تغيير الأدوات', 'هوائي']],
                ['fa' => ['سرعت', 'تا ۴۰۰۰ دور بر دقیقه'],                         'en' => ['Speed', 'Up to 4000 rpm'],                               'ar' => ['السرعة', 'حتى 4000 دورة/دقيقة']],
                ['fa' => ['طول کل', '۲۹ سانتیمتر'],                                'en' => ['Overall length', '29 cm'],                               'ar' => ['الطول الكلي', '29 سم']],
                ['fa' => ['قطر فلنج', '۱۲۷ میلیمتر'],                              'en' => ['Flange diameter', '127 mm'],                             'ar' => ['قطر الفلنجة', '127 مم']],
                ['fa' => ['روانکاری', 'با گریس'],                                  'en' => ['Lubrication', 'Grease'],                                 'ar' => ['التشحيم', 'بالشحم']],
            ],
        ],
    ],
],

[
    'slug'       => 'industrial-relay-card',
    'category'   => 'industrial-equipment',
    'model_code' => null,
    'featured'   => false,
    'cover'      => 'relay-card',
    'gallery'    => ['relay-card'],
    'source_ref' => 'محصولات-1/کارت رله/photo18116996646.jpg',
    'tr' => [
        'fa' => [
            'name'    => 'کارت رله صنعتی',
            'summary' => 'کارت رله ریلی در سه مدل ۴، ۸ و ۱۲ کاناله با رله‌های مستقل OMRON یا HELISHUN و تحمل جریان تا ۱۶ آمپر.',
            'description' => "کارت رله برای ایزوله کردن خروجی‌های PLC رله‌ای از مصرف‌کننده به کار می‌رود تا اگر اتصالی یا مشکلی در مصرف‌کننده به وجود آمد، به خروجی PLC صدمه نزند.\nهمچنین اگر قرار باشد یک خروجی ترانزیستوری یک مصرف‌کننده با آمپر بالا مانند کنتاکتور یا المنت را وصل کند، برای این موارد حتماً نیاز به استفاده از رله است.\nتحریک ورودی کارت‌های رله‌ای با ۲۴ ولت AC یا DC است و ورودی کارت بدون پلاریته است؛ یعنی در برابر جابه‌جا بودن مثبت و منفی سیم‌های برق مقاوم است.",
            'applications' => "ایزوله کردن خروجی PLC\nراه‌اندازی کنتاکتور و المنت\nتابلوهای برق صنعتی\nاتوماسیون صنعتی",
            'advantages'   => "مجهز به LED نمایشگر وضعیت رله‌ها\nرله‌ها از یکدیگر مستقل هستند؛ در صورت خرابی یک رله، فقط همان رله جایگزین می‌شود و نیازی به تعویض کل کارت نیست\nقابلیت نصب روی ریل\nرله‌ها از برند OMRON یا HELISHUN و با تحمل جریان تا ۱۶ آمپر\nتحریک بدون در نظر گرفتن پلاریته مثبت و منفی، به دلیل داشتن پل دیودی سر راه ورودی\nمجهز به کنتاکت نرمال باز و نرمال بسته\nقیمت مناسب، کیفیت بسیار بالا و طراحی منحصر به فرد",
        ],
        'en' => [
            'name'    => 'Industrial Relay Card',
            'summary' => 'DIN-rail relay card in 4, 8 and 12-channel versions with independent OMRON or HELISHUN relays rated to 16 A.',
            'description' => "The relay card isolates relay-type PLC outputs from the load, so that a short or fault in the load cannot damage the PLC output.\nIt is also required where a transistor output has to switch a high-current load such as a contactor or a heating element.\nThe card is triggered by 24 V AC or DC, and its input is polarity-free, so reversed positive and negative wiring does no harm.",
            'applications' => "Isolating PLC outputs\nSwitching contactors and heating elements\nIndustrial control panels\nIndustrial automation",
            'advantages'   => "LED status indicator for every relay\nRelays are independent — a failed relay is replaced on its own, without changing the whole card\nDIN-rail mountable\nOMRON or HELISHUN relays rated up to 16 A\nPolarity-free triggering, thanks to a diode bridge on the input\nNormally-open and normally-closed contacts\nCompetitive price, very high quality and a distinctive design",
        ],
        'ar' => [
            'name'    => 'بطاقة مرحّلات صناعية',
            'summary' => 'بطاقة مرحّلات لسكة DIN بإصدارات 4 و8 و12 قناة بمرحّلات مستقلة OMRON أو HELISHUN تتحمّل حتى 16 أمبير.',
            'description' => "تُستخدم بطاقة المرحّلات لعزل مخارج PLC من نوع المرحّل عن الحمل، بحيث لا يؤدي أي قصر أو عطل في الحمل إلى إتلاف مخرج PLC.\nكما أنها ضرورية عندما يتعيّن على مخرج ترانزستوري تشغيل حمل عالي التيار مثل كونتاكتور أو عنصر تسخين.\nتُحفَّز البطاقة بجهد 24 فولت تيار متردد أو مستمر، ومدخلها عديم القطبية فلا يضرّه عكس أسلاك الموجب والسالب.",
            'applications' => "عزل مخارج PLC\nتشغيل الكونتاكتورات وعناصر التسخين\nلوحات التحكم الصناعية\nالأتمتة الصناعية",
            'advantages'   => "مؤشر LED لحالة كل مرحّل\nالمرحّلات مستقلة — يُستبدل المرحّل التالف وحده دون تغيير البطاقة بأكملها\nقابلة للتركيب على سكة DIN\nمرحّلات OMRON أو HELISHUN تتحمّل حتى 16 أمبير\nتحفيز عديم القطبية بفضل جسر ثنائيات على المدخل\nمزوّدة بتماس مفتوح عادةً ومغلق عادةً\nسعر مناسب وجودة عالية جداً وتصميم مميّز",
        ],
    ],
    'specs' => [
        [
            'title' => ['fa' => 'تحریک ورودی و جریان خروجی', 'en' => 'Input trigger and output current', 'ar' => 'تحفيز الدخل وتيار الخرج'],
            'rows'  => [
                ['fa' => ['تحریک ورودی', '۲۴ ولت AC یا DC'],          'en' => ['Input trigger', '24 V AC or DC'],          'ar' => ['تحفيز الدخل', '24 فولت AC أو DC']],
                ['fa' => ['پلاریته ورودی', 'بدون پلاریته'],            'en' => ['Input polarity', 'Polarity-free'],         'ar' => ['قطبية الدخل', 'عديمة القطبية']],
                ['fa' => ['جریان خروجی رله‌ها', '۱۶ آمپر'],            'en' => ['Relay output current', '16 A'],            'ar' => ['تيار خرج المرحّلات', '16 أمبير']],
                ['fa' => ['برند رله', 'OMRON یا HELISHUN'],            'en' => ['Relay brand', 'OMRON or HELISHUN'],        'ar' => ['ماركة المرحّل', 'OMRON أو HELISHUN']],
            ],
        ],
        [
            'title' => ['fa' => 'ابعاد کارت رله', 'en' => 'Relay card dimensions', 'ar' => 'أبعاد بطاقة المرحّلات'],
            'rows'  => [
                ['fa' => ['۴ کانال', 'ارتفاع ۷۰ – عرض ۹۰ – ضخامت ۶۵ میلیمتر'],   'en' => ['4 channel', 'Height 70 · Width 90 · Thickness 65 mm'],  'ar' => ['4 قنوات', 'ارتفاع 70 · عرض 90 · سماكة 65 مم']],
                ['fa' => ['۸ کانال', 'ارتفاع ۱۴۰ – عرض ۹۰ – ضخامت ۶۵ میلیمتر'],  'en' => ['8 channel', 'Height 140 · Width 90 · Thickness 65 mm'], 'ar' => ['8 قنوات', 'ارتفاع 140 · عرض 90 · سماكة 65 مم']],
                ['fa' => ['۱۲ کانال', 'ارتفاع ۲۱۰ – عرض ۹۰ – ضخامت ۶۵ میلیمتر'], 'en' => ['12 channel', 'Height 210 · Width 90 · Thickness 65 mm'],'ar' => ['12 قناة', 'ارتفاع 210 · عرض 90 · سماكة 65 مم']],
            ],
        ],
    ],
],

];
