<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8a2be2;
            --secondary: #ff6b6b;
            --accent: #4ecdc4;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: var(--dark);
            line-height: 1.6;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(120deg, var(--primary), #5c1d9c);
            color: white;
            padding: 1.2rem 0; /* زيادة طفيفة في ارتفاع الشريط العلوي */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo-image {
            width: 80px;
            height: 80px;
            object-fit: contain; /* للحفاظ على أبعاد الصورة وعدم تشويهها */
            border-radius: 8px;
        }
        
        .logo-text {
            font-size: 1.8rem; /* الخط الكبير العريض */
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        
        .logo-subtext {
            font-size: 0.9rem; /* الخط الأصغر الخفيف */
            font-weight: 400;
            opacity: 0.9;
            line-height: 1.2;
            margin-top: 12px; /* تمت زيادة المسافة بين السطرين بشكل أكبر */
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 20px; /* زيادة المسافة قليلاً لتناسب الأيقونات الأكبر */
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px; /* زيادة الحشوة لتناسب الأيقونات الأكبر */
            border-radius: 30px;
            transition: all 0.3s ease;
            display: flex; /* لجعل الأيقونة والنص في نفس السطر بشكل جيد */
            align-items: center; /* لمحاذاة الأيقونة والنص عمودياً */
            gap: 12px; /* زيادة المسافة بين الأيقونة والنص */
        }

        nav a i { /* استهداف الأيقونات داخل روابط التنقل */
            font-size: 1.6rem; /* زيادة حجم الأيقونة */
        }
        
        nav a:hover, nav a.active {
            background: rgba(255, 255, 255, 0.15);
        }

        .logo-link {
            text-decoration: none;
            color: inherit;
        }
        
        .hero {
            background: linear-gradient(rgba(78, 205, 196, 0.9), rgba(78, 205, 196, 0.7)), url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            padding: 5rem 2rem;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            color: white;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.95;
            color: white;
        }
        
        .search-box {
            max-width: 700px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 5px;
            display: flex;
            backdrop-filter: blur(5px);
        }
        
        .search-box input {
            flex: 1;
            border: none;
            background: white;
            padding: 15px 25px;
            border-radius: 0 50px 50px 0; /* Adjusted for RTL: input is on the right */
            font-size: 1.1rem;
            outline: none;
        }
        
        .search-box button {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px 0 0 50px; /* Adjusted for RTL: button is on the left */
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-box button:hover {
            background: #ff5252;
        }
        
        /* Filters Section */
        .filters-section {
            background: white;
            padding: 1.5rem; /* Reduced padding */
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            max-width: 1200px;
            margin: -50px auto 30px;
            position: relative;
            z-index: 10;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary);
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .filter-group {
            margin-bottom: 1.5rem;
        }
        
        .filter-group h3 {
            margin-bottom: 12px;
            color: var(--dark);
            font-size: 1rem; /* Reduced font size */
            display: flex; /* Align icon and text */
            align-items: center;
        }

        .filter-group h3 i {
            color: var(--primary); /* Icon color */
            margin-left: 8px; /* Space between icon and text (for RTL) */
        }
        
        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .filter-btn {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .filter-group select {
            width: 100%;
            padding: 8px 12px; /* Reduced padding */
            border-radius: 30px;
            border: 2px solid #e0e0e0;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.85rem; /* Reduced font size */
            outline: none;
            background-color: white;
        }
        
        .filter-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            margin-bottom: 1.5rem;
        }
        
        .action-btn {
            padding: 8px 20px; /* Reduced padding */
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem; /* Reduced font size */
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .apply-btn {
            background: var(--primary);
            color: white;
            border: 2px solid var(--primary);
        }
        
        .reset-btn {
            background: white;
            color: var(--dark);
            border: 2px solid #e0e0e0;
        }
        
        .apply-btn:hover {
            background: #7a1fc2;
            border-color: #7a1fc2;
        }
        
        .reset-btn:hover {
            background: #f5f5f5;
        }

        /* أنماط جديدة لمربع البحث داخل مجموعة الفلترة */
        .filter-group.search-filter-group .search-box {
            max-width: none; /* إلغاء تحديد العرض الأقصى لمربع البحث في قسم الهيرو */
            margin: 0; /* إلغاء الهوامش */
            background: white; /* خلفية بيضاء لتتناسق مع حقول الفلترة الأخرى */
            border: 2px solid #e0e0e0; /* حدود لتتناسق مع حقول الفلترة الأخرى */
            border-radius: 30px; /* حواف دائرية */
            padding: 0; /* لا يوجد حشوة داخلية للمحتوى */
            display: flex;
            backdrop-filter: none; /* إزالة تأثير التمويه */
            overflow: hidden; /* لضمان أن الحواف الدائرية تقص المحتوى الزائد */
        }

        .filter-group.search-filter-group .search-box input {
            flex: 1; /* ليأخذ المساحة المتاحة */
            border: none; /* إزالة الحدود الفردية */
            background: transparent; /* خلفية شفافة لإظهار خلفية العنصر الأب */
            padding: 8px 15px; /* حشوة داخلية تتناسق مع قوائم الاختيار */
            border-radius: 0; /* لا توجد حواف دائرية فردية، الأب هو من يتحكم */
            font-size: 0.85rem; /* حجم خط يتناسق مع قوائم الاختيار */
            outline: none;
        }

        .filter-group.search-filter-group .search-box button {
            background: var(--primary); /* استخدام اللون الأساسي */
            color: white;
            border: none;
            padding: 8px 15px; /* حشوة داخلية تتناسق مع قوائم الاختيار */
            border-radius: 0; /* لا توجد حواف دائرية فردية، الأب هو من يتحكم */
            font-size: 0.85rem; /* حجم خط يتناسق مع قوائم الاختيار */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-group.search-filter-group .search-box button:hover {
            background: #7a1fc2; /* لون أغمق عند التحويم */
        }
        
        /* Programs Section */
        .programs-section {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 20px;
        }
        
        .programs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .results-count {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .sort-options select {
            padding: 10px 15px;
            border-radius: 30px;
            border: 2px solid #e0e0e0;
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            outline: none;
        }
        
        .programs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .program-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .program-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            padding: 20px 20px 15px;
            background: linear-gradient(120deg, var(--accent), #3daaa4);
            color: white;
        }
        
        .program-title {
            font-size: 1.4rem;
            margin-bottom: 5px;
        }
        
        .organization {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 20px;
            flex: 1;
        }
        
        .program-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .detail-icon {
            color: var(--primary);
            font-size: 1.1rem;
            margin-top: 3px;
        }
        
        .detail-text {
            font-size: 0.95rem;
        }
        
        .program-description {
            margin: 20px 0;
            font-size: 0.95rem;
            line-height: 1.7;
            color: #555;
        }
        
        .card-footer {
            padding: 0 20px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .program-fee {
            background: #f0f7ff;
            color: var(--primary);
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.95rem;
        }
        
        .free-badge {
            background: var(--success);
            color: white;
        }
        
        .register-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95rem;
        }
        
        .register-btn:hover {
            background: #7a1fc2;
        }
        
        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 3rem 0 2rem;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .footer-col h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-col h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--accent);
            padding-right: 5px;
        }

        .footer-col .footer-links li {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-col .footer-links li > i {
            font-size: 1.6rem; 
            width: 24px; 
            text-align: center; 
            color: var(--accent);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid #444;
            font-size: 0.9rem;
            color: #aaa;
        }

        .footer-col ul.footer-header-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-col ul.footer-header-nav li {
            margin-bottom: 12px;
        }
        .footer-col ul.footer-header-nav a {
            color: #ddd;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 0; 
            transition: color 0.3s ease, padding-right 0.3s ease;
        }
        .footer-col ul.footer-header-nav a i {
            font-size: 1.6rem; 
            width: 24px; 
            text-align: center; 
            color: var(--accent);
        }
        .footer-col ul.footer-header-nav a:hover {
            color: white; 
            padding-right: 8px;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            nav ul {
                gap: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: stretch;
                padding: 0 10px;
                gap: 5px;
            }

            header {
                padding: 5px 0;
                max-height: 20vh;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .logo {
                gap: 8px;
                flex-shrink: 1;
                min-width: 0;
                align-items: center; 
            }

            .logo-image {
                width: 35px;
                height: 35px; 
            }

            .logo-text {
                font-size: 1rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .logo-subtext {
                font-size: 0.65rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .hero {
                padding: 3rem 1rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .filters-section {
                padding: 1.5rem;
                margin: -30px auto 30px;
            }
            
            .programs-grid {
                grid-template-columns: 1fr;
            }

            nav {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
            }

            nav ul {
                flex-direction: row;
                flex-wrap: nowrap;
                align-items: center;
                padding: 0;
                margin: 0;
                gap: 5px;
                justify-content: center;
            }

            nav a {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 5px 3px;
                gap: 2px;
                font-size: 0.7rem;
            }

            nav a i {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            nav ul {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
            
            .search-box {
                flex-direction: column;
                background: transparent;
                gap: 10px;
            }
            
            .search-box input, .search-box button {
                width: 100%;
                border-radius: 30px;
            }
            
            .filter-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo-link">
                <div class="logo">
                    <img src="<?php echo htmlspecialchars($site_settings['logo_path'] ?? 'assets/img/default-logo.png'); ?>" alt="شعار" class="logo-image">
                    <div>
                        <div class="logo-text"><?php echo htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية'); ?></div>
                        <?php if (!empty($site_settings['guide_subtitle'])): ?>
                            <div class="logo-subtext"><?php echo htmlspecialchars($site_settings['guide_subtitle']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <nav>
                <ul>
                    <?php if (!empty($site_settings['whatsapp_channel_header_enabled']) && !empty($site_settings['whatsapp_channel_url'])): ?>
                        <li><a href="<?php echo htmlspecialchars($site_settings['whatsapp_channel_url']); ?>" target="_blank"><i class="fab fa-whatsapp"></i> قناة الواتساب</a></li>
                    <?php endif; ?>
                    <?php if (!empty($site_settings['telegram_channel_header_enabled']) && !empty($site_settings['telegram_channel_url'])): ?>
                        <li><a href="<?php echo htmlspecialchars($site_settings['telegram_channel_url']); ?>" target="_blank"><i class="fab fa-telegram"></i> قناة التليجرام</a></li>
                    <?php endif; ?>
                    <?php if (!empty($site_settings['guide_pdf_header_enabled']) && !empty($site_settings['guide_pdf_path'])): ?>
                        <li><a href="<?php echo htmlspecialchars($site_settings['guide_pdf_path']); ?>" target="_blank"><i class="fas fa-file-pdf"></i> تحميل الدليل</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
