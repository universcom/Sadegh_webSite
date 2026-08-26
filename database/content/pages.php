<?php
declare(strict_types=1);

/**
 * Editorial pages and their modular sections.
 *
 * Persian copy is taken verbatim (or lightly tidied for the web) from the
 * supplied Word documents; English and Arabic are translations of that same
 * copy. Section `body` fields that feed a list use one item per line, and the
 * features/stats blocks use "Title | description" per line.
 */

return [

// ===========================================================================
'home' => [
    'system' => true,
    'tr' => [
        'fa' => ['title' => 'خانه',        'subtitle' => 'طراحی و ساخت ماشین‌آلات کنترل عددی و تجهیزات صنعتی'],
        'en' => ['title' => 'Home',        'subtitle' => 'Design and manufacture of CNC machinery and industrial equipment'],
        'ar' => ['title' => 'الرئيسية',    'subtitle' => 'تصميم وتصنيع ماكينات التحكم الرقمي والمعدات الصناعية'],
    ],
    'sections' => [
        [
            'type'  => 'hero',
            'media' => 'hero-machining',
            'tr' => [
                'fa' => [
                    'heading'    => 'مهندسی، طراحی و ساخت ماشین‌آلات صنعتی',
                    'subheading' => 'طراح و تولیدکننده تجهیزات صنعتی و آزمایشگاهی',
                    'body'       => 'شرکت رهیافت صنعت با تکیه بر توان علمی اعضای خود، ماشین‌های کنترل عددی و تجهیزات صنعتی را از مرحله طراحی تا ساخت و راه‌اندازی به سرانجام می‌رساند.',
                    'cta_label'  => 'مشاهده محصولات',
                ],
                'en' => [
                    'heading'    => 'Engineering, design and manufacture of industrial machinery',
                    'subheading' => 'Designer and manufacturer of industrial and laboratory equipment',
                    'body'       => 'Drawing on the scientific strength of its members, Rahyaft Sanat takes CNC machinery and industrial equipment from design through to manufacture and commissioning.',
                    'cta_label'  => 'Explore products',
                ],
                'ar' => [
                    'heading'    => 'هندسة وتصميم وتصنيع الماكينات الصناعية',
                    'subheading' => 'مصمّم ومصنّع المعدات الصناعية والمخبرية',
                    'body'       => 'اعتماداً على القدرات العلمية لأعضائها، تأخذ شركة رهيافت صنعت ماكينات التحكم الرقمي والمعدات الصناعية من التصميم إلى التصنيع والتشغيل.',
                    'cta_label'  => 'استعرض المنتجات',
                ],
            ],
        ],
        [
            'type'  => 'image_text',
            'media' => 'engineering-desk',
            'tr' => [
                'fa' => [
                    'heading'    => 'از ایده تا محصول، در یک مجموعه',
                    'subheading' => 'درباره شرکت',
                    'body'       => "شرکت رهیافت‌های فناورانه و راهبردی مسائل مهندسی در راستای اهداف خویش اقدام به تولید انواع دستگاه‌ها و تجهیزات صنعتی نموده است. از این میان می‌توان به دستگاه‌های صنایع چوب و دستگاه‌ها و تجهیزات صنعتی نام برد.\nبهینه کردن برخی از دستگاه‌های موجود به سفارش مشتری یا اتوماتیک کردن این دستگاه‌ها، همچنین به‌روز کردن اتوماسیون صنعتی و تابلو برق‌های قدیمی، از کارکردهای این شرکت می‌باشد.",
                    'cta_label'  => 'درباره ما',
                ],
                'en' => [
                    'heading'    => 'From idea to product, under one roof',
                    'subheading' => 'About the company',
                    'body'       => "In pursuit of its goals, the Technological and Strategic Approaches to Engineering Problems Company has taken up the production of a range of industrial machines and equipment, among them wood-industry machinery and general industrial equipment.\nOptimising existing machines to customer order or automating them, along with modernising industrial automation and older electrical panels, is also part of the company's work.",
                    'cta_label'  => 'About us',
                ],
                'ar' => [
                    'heading'    => 'من الفكرة إلى المنتج تحت سقف واحد',
                    'subheading' => 'عن الشركة',
                    'body'       => "سعياً وراء أهدافها، شرعت شركة المقاربات التقنية والاستراتيجية لمسائل الهندسة في إنتاج مجموعة من الماكينات والمعدات الصناعية، من بينها ماكينات صناعة الخشب والمعدات الصناعية العامة.\nكما يشمل عمل الشركة تحسين بعض الماكينات القائمة حسب طلب العميل أو أتمتتها، وتحديث الأتمتة الصناعية ولوحات الكهرباء القديمة.",
                    'cta_label'  => 'من نحن',
                ],
            ],
        ],
        [
            'type' => 'features',
            'tr' => [
                'fa' => [
                    'heading'    => 'خدمات واحد امور مهندسی و واحد ساخت و تولید',
                    'subheading' => 'توانمندی‌ها',
                    'body'       => "مهندسی معکوس و بازطراحی | بازطراحی قطعات و تجهیزات صنعتی بر پایه نمونه موجود.\nطراحی سفارشی تجهیزات | طراحی تجهیزات بر اساس نیاز و سفارش مشتری.\nاسکن و پرینت سه‌بعدی | اسکن سه‌بعدی از قطعات و پرینت سه‌بعدی قطعات.\nنقشه‌کشی صنعتی | تهیه نقشه‌های ساخت و کارگاهی.\nمشاوره و اسناد پروژه | مشاوره تعیین قیمت پروژه‌ها، تهیه اسناد مناقصه و مشاوره عقد قراردادهای صنعتی.\nساخت و تولید | تهیه و تأمین کلیه متریال ساخت و انجام برشکاری، ماشین‌کاری، مونتاژ، جوشکاری، سنگزنی، سندبلاست و رنگ‌آمیزی.",
                ],
                'en' => [
                    'heading'    => 'Engineering and production unit services',
                    'subheading' => 'Capabilities',
                    'body'       => "Reverse engineering and redesign | Redesigning industrial parts and equipment from an existing sample.\nCustom equipment design | Designing equipment to the customer's requirements and order.\n3D scanning and printing | Three-dimensional scanning of parts and 3D printing of components.\nIndustrial draughting | Preparation of manufacturing and workshop drawings.\nConsultancy and project documents | Project pricing consultancy, tender document preparation and advice on industrial contracts.\nManufacturing and production | Supply of all fabrication material and the execution of cutting, machining, assembly, welding, grinding, sandblasting and painting.",
                ],
                'ar' => [
                    'heading'    => 'خدمات وحدة الشؤون الهندسية ووحدة التصنيع والإنتاج',
                    'subheading' => 'القدرات',
                    'body'       => "الهندسة العكسية وإعادة التصميم | إعادة تصميم القطع والمعدات الصناعية انطلاقاً من نموذج قائم.\nتصميم المعدات حسب الطلب | تصميم المعدات وفق احتياجات العميل وطلبه.\nالمسح والطباعة ثلاثية الأبعاد | مسح ثلاثي الأبعاد للقطع وطباعتها ثلاثية الأبعاد.\nالرسم الهندسي الصناعي | إعداد رسومات التصنيع والورشة.\nالاستشارات ووثائق المشاريع | استشارات تسعير المشاريع وإعداد وثائق المناقصات والاستشارة في العقود الصناعية.\nالتصنيع والإنتاج | توفير كل مواد التصنيع وتنفيذ القص والتشغيل والتجميع واللحام والجلخ والسفع الرملي والدهان.",
                ],
            ],
        ],
        [
            'type' => 'quote',
            'tr' => [
                'fa' => [
                    'heading' => 'مولوی',
                    'body'    => "عقل را با عقل یاری یار کن\nاَمرَهم شوری بخوان و کار کن",
                ],
                'en' => [
                    'heading' => 'Rumi',
                    'body'    => "Let reason be aided by reason;\nread «their affairs are by counsel» — and get to work.",
                ],
                'ar' => [
                    'heading' => 'مولوي',
                    'body'    => "أعِنِ العقلَ بالعقلِ\nواقرأ «وأمرُهم شورى بينهم» ثمّ اعمل.",
                ],
            ],
        ],
        [
            'type' => 'cta',
            'tr' => [
                'fa' => [
                    'heading'   => 'پروژه‌ای در دست دارید؟',
                    'body'      => 'برای مشاوره فنی، استعلام قیمت یا سفارش ساخت با کارشناسان ما در ارتباط باشید.',
                    'cta_label' => 'تماس با ما',
                ],
                'en' => [
                    'heading'   => 'Have a project in mind?',
                    'body'      => 'Talk to our engineers about technical advice, a quotation or a build to order.',
                    'cta_label' => 'Contact us',
                ],
                'ar' => [
                    'heading'   => 'لديك مشروع؟',
                    'body'      => 'تواصل مع مهندسينا للحصول على استشارة فنية أو عرض سعر أو طلب تصنيع.',
                    'cta_label' => 'اتصل بنا',
                ],
            ],
        ],
    ],
],

// ===========================================================================
'about' => [
    'system' => true,
    'tr' => [
        'fa' => [
            'title'    => 'درباره ما',
            'subtitle' => 'یک هسته فنی و عملیاتی، برآمده از دانشگاه',
            'body'     => "شرکت رهیافت‌های فناورانه و راهبردی مسائل مهندسی در سال ۱۴۰۰، در قالب یک هسته فنی و عملیاتی، با تلاش گروهی و تیمی برخی از دانشجویان دانشگاه صنعتی اصفهان، دانشگاه اصفهان و دانشگاه صنعتی خواجه نصیرالدین طوسی، با هدف ایجاد یک بستر علمی و فنی برای استفاده مؤثر از نیروهای توانمند در حوزه صنعت، کشاورزی و هوش مصنوعی، در مرکز رشد دانشگاه اصفهان شروع به فعالیت نمود.\nهدف ما جذب دانشجویان مستعد و به‌کارگیری آن‌ها در تخصص‌هایی بوده که به آن‌ها علاقه‌مند هستند و فکر می‌کنند در صورت وجود یک بستر فنی، قادر به رشد و تعالی علمی و ایجاد یک تغییر بزرگ در زندگی هستند.\nاین شرکت در سال ۱۴۰۲ به عضویت شهرک علمی تحقیقاتی اصفهان در آمده است و همچنین با تعدادی از شرکت‌های بزرگ دانش‌بنیان مشغول به همکاری شده است.",
        ],
        'en' => [
            'title'    => 'About Us',
            'subtitle' => 'A technical and operational core, born out of the university',
            'body'     => "The Technological and Strategic Approaches to Engineering Problems Company began work in 2021 as a technical and operational core, through the collective effort of students from Isfahan University of Technology, the University of Isfahan and K. N. Toosi University of Technology. It was founded at the University of Isfahan incubator with the aim of creating a scientific and technical platform for making effective use of capable people in industry, agriculture and artificial intelligence.\nOur aim has been to attract talented students and engage them in the specialisms they care about — people who believe that, given a technical platform, they can grow scientifically and make a real change in their lives.\nIn 2023 the company became a member of Isfahan Science and Technology Town, and it also began collaborating with a number of large knowledge-based companies.",
        ],
        'ar' => [
            'title'    => 'من نحن',
            'subtitle' => 'نواة تقنية وتشغيلية انبثقت من الجامعة',
            'body'     => "بدأت شركة المقاربات التقنية والاستراتيجية لمسائل الهندسة عملها عام 2021 كنواة تقنية وتشغيلية، بجهد جماعي من طلاب جامعة أصفهان للتكنولوجيا وجامعة أصفهان وجامعة خواجة نصير الدين الطوسي للتكنولوجيا. تأسست في حاضنة جامعة أصفهان بهدف إنشاء منصة علمية وتقنية للاستفادة الفعّالة من الكفاءات في مجالات الصناعة والزراعة والذكاء الاصطناعي.\nهدفنا استقطاب الطلاب الموهوبين وإشراكهم في التخصصات التي يهتمون بها، ممن يرون أنهم قادرون — متى توفّرت لهم منصة تقنية — على النمو العلمي وإحداث تغيير كبير في حياتهم.\nوفي عام 2023 أصبحت الشركة عضواً في مدينة أصفهان العلمية والبحثية، كما بدأت التعاون مع عدد من الشركات الكبرى القائمة على المعرفة.",
        ],
    ],
    'sections' => [
        [
            'type'  => 'image_text',
            'media' => 'hero-factory',
            'tr' => [
                'fa' => [
                    'heading'    => 'واحد ساخت و تولید',
                    'subheading' => 'زیرساخت تولید',
                    'body'       => "تهیه و تأمین کلیه متریال ساخت، و انجام فعالیت‌های ساخت شامل برشکاری، ماشین‌کاری، مونتاژ، جوشکاری، سنگزنی، سندبلاست و رنگ‌آمیزی، در واحد ساخت و تولید شرکت انجام می‌شود.",
                ],
                'en' => [
                    'heading'    => 'Production unit',
                    'subheading' => 'Manufacturing infrastructure',
                    'body'       => "The company's production unit supplies all fabrication material and carries out the manufacturing work itself: cutting, machining, assembly, welding, grinding, sandblasting and painting.",
                ],
                'ar' => [
                    'heading'    => 'وحدة التصنيع والإنتاج',
                    'subheading' => 'البنية الإنتاجية',
                    'body'       => "توفّر وحدة التصنيع والإنتاج في الشركة كل مواد التصنيع وتنفّذ أعمال التصنيع بنفسها: القص والتشغيل والتجميع واللحام والجلخ والسفع الرملي والدهان.",
                ],
            ],
        ],
        [
            'type'  => 'image_text',
            'media' => 'engineering-desk',
            'tr' => [
                'fa' => [
                    'heading'    => 'واحد امور مهندسی',
                    'subheading' => 'نرم‌افزارها و ابزارها',
                    'body'       => "کارشناسان این واحد با تکیه بر سال‌ها تجربه و با استفاده از نرم‌افزارهای طراحی و محاسباتی مطرح و به‌روز دنیا نظیر SAFE، SAP، ABAQUS، ANSYS، SOLIDWORKS و INVENTOR خدمات مهندسی شرکت را ارائه می‌دهند.\nطراحی شبکه‌های آب و خطوط انتقال با نرم‌افزار WATER GEMS و طراحی فضای سبز با نرم‌افزار REAL TIME نیز از خدمات این واحد است.",
                ],
                'en' => [
                    'heading'    => 'Engineering unit',
                    'subheading' => 'Software and tools',
                    'body'       => "Drawing on years of experience, the specialists in this unit deliver the company's engineering services using leading, current design and analysis software such as SAFE, SAP, ABAQUS, ANSYS, SOLIDWORKS and INVENTOR.\nThe unit also designs water networks and transmission lines with WATER GEMS, and landscaping with REAL TIME.",
                ],
                'ar' => [
                    'heading'    => 'وحدة الشؤون الهندسية',
                    'subheading' => 'البرمجيات والأدوات',
                    'body'       => "اعتماداً على سنوات من الخبرة، يقدّم اختصاصيو هذه الوحدة خدمات الشركة الهندسية باستخدام برمجيات التصميم والحساب الرائدة والحديثة مثل SAFE وSAP وABAQUS وANSYS وSOLIDWORKS وINVENTOR.\nكما تصمّم الوحدة شبكات المياه وخطوط النقل ببرنامج WATER GEMS، والمساحات الخضراء ببرنامج REAL TIME.",
                ],
            ],
        ],
        [
            'type' => 'features',
            'tr' => [
                'fa' => [
                    'heading'    => 'جایگاه و همکاری‌ها',
                    'subheading' => 'اعتبارات',
                    'body'       => "مستقر در مرکز رشد دانشگاه اصفهان | فعالیت شرکت از سال ۱۴۰۰ در مرکز رشد و کارآفرینی دانشگاه اصفهان آغاز شده است.\nعضو شهرک علمی تحقیقاتی اصفهان | این شرکت در سال ۱۴۰۲ به عضویت شهرک علمی تحقیقاتی اصفهان درآمده است.\nهمکاری با شرکت‌های دانش‌بنیان | همکاری با تعدادی از شرکت‌های بزرگ دانش‌بنیان در دست اجراست.",
                ],
                'en' => [
                    'heading'    => 'Standing and collaborations',
                    'subheading' => 'Credentials',
                    'body'       => "Based at the University of Isfahan incubator | The company has operated from the University of Isfahan Growth and Entrepreneurship Centre since 2021.\nMember of Isfahan Science and Technology Town | The company became a member in 2023.\nWorking with knowledge-based companies | Collaboration with a number of large knowledge-based companies is under way.",
                ],
                'ar' => [
                    'heading'    => 'المكانة والتعاون',
                    'subheading' => 'الاعتمادات',
                    'body'       => "مقرّها حاضنة جامعة أصفهان | تعمل الشركة من مركز النمو وريادة الأعمال بجامعة أصفهان منذ عام 2021.\nعضو في مدينة أصفهان العلمية والبحثية | انضمت الشركة إلى العضوية عام 2023.\nالتعاون مع الشركات القائمة على المعرفة | يجري التعاون مع عدد من الشركات الكبرى القائمة على المعرفة.",
                ],
            ],
        ],
        [
            'type' => 'cta',
            'tr' => [
                'fa' => ['heading' => 'با ما همکاری کنید', 'body' => 'برای همکاری، استخدام یا سفارش پروژه با ما در تماس باشید.', 'cta_label' => 'تماس با ما'],
                'en' => ['heading' => 'Work with us',      'body' => 'Get in touch about collaboration, joining the team or commissioning a project.', 'cta_label' => 'Contact us'],
                'ar' => ['heading' => 'تعاون معنا',        'body' => 'تواصل معنا بشأن التعاون أو الانضمام إلى الفريق أو طلب مشروع.', 'cta_label' => 'اتصل بنا'],
            ],
        ],
    ],
],

// ===========================================================================
'research' => [
    'system' => true,
    'tr' => [
        'fa' => [
            'title'    => 'تحقیق و توسعه',
            'subtitle' => 'پژوهش، طراحی و توسعه فناوری در حوزه‌های هوافضا، مکانیک سیالات و الکترومکانیک',
            'body'     => "تحقیق و توسعه (R&D) به یک واحد اساسی در بسیاری از سازمان‌های انتفاعی و غیرانتفاعی تبدیل شده است. هر بخش از علوم آکادمیک و کاربردی، از داروسازی و فیزیک گرفته تا مهندسی و بیوتکنولوژی، به دانشمندان حرفه‌ای در زمینه تحقیق و توسعه نیاز دارد. افرادی که در این حوزه فعالیت می‌کنند، به انجام پژوهش‌های نوآورانه می‌پردازند، دانش و درک خود از اصول علمی را گسترش می‌دهند و سپس محصولات جدید، فرآیندهای پیشگامانه و سایر دستاوردهای شگفت‌انگیزی را توسعه می‌دهند که جهان را برای همیشه تغییر خواهند داد.\nدر رهیافت صنعت، هر فرد بر اساس علایق و توانمندی‌های خود در زمینه‌ای تخصصی فعالیت می‌کند. با تکیه بر دانش عمیق در رشته‌های علمی و مهندسی، و انگیزه ساخت فردایی بهتر، اعضای ما برای خلق دستاوردهای برجسته آماده هستند.\nشرکت ما نه تنها بستری برای رشد حرفه‌ای پژوهشگران و مهندسان است، بلکه فضایی برای طراحی و توسعه فناوری‌های تحول‌آفرین نیز فراهم می‌کند. ما به دنبال پرورش و جذب استعدادهایی هستیم که رؤیای ساختن فردایی بهتر را در سر دارند — از زیست‌فناوران و داروسازان گرفته تا فیزیکدانان، شیمی‌دانان و مهندسان نوآور.\nآینده در دستان امروز ماست.",
        ],
        'en' => [
            'title'    => 'Research & Development',
            'subtitle' => 'Research, design and technology development across aerospace, fluid mechanics and electromechanics',
            'body'     => "Research and development has become a fundamental unit in many organisations, commercial and non-commercial alike. Every branch of academic and applied science — from pharmacy and physics to engineering and biotechnology — needs professional scientists working in research and development. The people who work in this field carry out innovative research, extend their knowledge and understanding of scientific principles, and then develop new products, pioneering processes and other remarkable achievements that change the world for good.\nAt Rahyaft Sanat, each person works in a specialist area according to their own interests and abilities. Drawing on deep knowledge in the sciences and engineering, and on the motivation to build a better tomorrow, our members are ready to create outstanding work.\nOur company is not only a platform for the professional growth of researchers and engineers; it also provides a space for designing and developing transformative technologies. We seek to nurture and attract the talents who dream of building a better tomorrow — from biotechnologists and pharmacists to physicists, chemists and innovative engineers.\nThe future is in the hands of our today.",
        ],
        'ar' => [
            'title'    => 'البحث والتطوير',
            'subtitle' => 'البحث والتصميم وتطوير التقنيات في مجالات الفضاء الجوي وميكانيكا الموائع والكهروميكانيك',
            'body'     => "أصبح البحث والتطوير وحدة أساسية في كثير من المؤسسات الربحية وغير الربحية. فكل فرع من فروع العلوم الأكاديمية والتطبيقية — من الصيدلة والفيزياء إلى الهندسة والتقنية الحيوية — يحتاج إلى علماء محترفين في مجال البحث والتطوير. والعاملون في هذا المجال يجرون أبحاثاً مبتكرة، ويوسّعون معرفتهم وفهمهم للمبادئ العلمية، ثم يطوّرون منتجات جديدة وعمليات رائدة وإنجازات مذهلة تغيّر العالم إلى الأبد.\nفي رهيافت صنعت، يعمل كل فرد في مجال تخصصي وفق اهتماماته وقدراته. واعتماداً على معرفة عميقة بالعلوم والهندسة، وبدافع بناء غدٍ أفضل، فإن أعضاءنا مستعدون لتحقيق إنجازات بارزة.\nشركتنا ليست منصة للنمو المهني للباحثين والمهندسين فحسب، بل توفّر أيضاً فضاءً لتصميم وتطوير تقنيات تحويلية. ونسعى إلى رعاية واستقطاب المواهب التي تحلم ببناء غدٍ أفضل — من علماء التقنية الحيوية والصيادلة إلى الفيزيائيين والكيميائيين والمهندسين المبتكرين.\nالمستقبل بين أيدي يومنا هذا.",
        ],
    ],
    'sections' => [],
],

// ===========================================================================
'contact' => [
    'system' => true,
    'tr' => [
        'fa' => ['title' => 'تماس با ما', 'subtitle' => 'برای مشاوره فنی، استعلام قیمت یا همکاری با ما در ارتباط باشید.'],
        'en' => ['title' => 'Contact Us', 'subtitle' => 'Get in touch for technical advice, a quotation or partnership enquiries.'],
        'ar' => ['title' => 'اتصل بنا',   'subtitle' => 'تواصل معنا للاستشارة الفنية أو طلب عرض سعر أو للتعاون.'],
    ],
    'sections' => [],
],

];
