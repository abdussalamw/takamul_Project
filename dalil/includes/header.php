<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<html lang="ar" dir="rtl"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
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

        /* View Toggling Controls - Modern Segmented Tabs */
        .view-controls {
            display: inline-flex;
            background-color: rgba(138, 43, 226, 0.05);
            padding: 5px;
            border-radius: 40px;
            border: 1px solid rgba(138, 43, 226, 0.1);
            margin-bottom: 20px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .view-controls .view-toggle-btn {
            padding: 10px 24px;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #5c1d9c;
            border-radius: 30px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Tajawal', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .view-controls .view-toggle-btn:not(.active):hover {
            background-color: rgba(138, 43, 226, 0.08);
            color: var(--primary);
        }
        .view-controls .view-toggle-btn.active {
            background: linear-gradient(135deg, var(--primary) 0%, #6f1ab6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(138, 43, 226, 0.25);
        }
        
        @media (max-width: 768px) {
            .view-controls {
                display: flex;
                width: 100%;
                justify-content: space-between;
                padding: 4px;
                border-radius: 30px;
                box-sizing: border-box;
                margin-top: 10px;
            }
            .view-controls .view-toggle-btn {
                flex: 1;
                justify-content: center;
                padding: 8px 10px;
                font-size: 0.8rem;
                gap: 4px;
            }
            .view-controls .view-toggle-btn i {
                font-size: 0.95rem;
            }
        }

        /* Pulsating Map Markers */
        .pulsating-icon-container {
            position: relative;
            width: 40px;
            height: 40px;
            background: transparent !important;
            border: none !important;
        }

        .pulse-dot {
            position: absolute;
            top: 13px;
            left: 13px;
            width: 14px;
            height: 14px;
            background-color: var(--primary); /* اللون البنفسجي */
            border: 2px solid white;
            border-radius: 50%;
            box-shadow: 0 0 6px rgba(138, 43, 226, 0.6);
            z-index: 2;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .pulsating-icon-container:hover .pulse-dot {
            background-color: var(--secondary); /* اللون المرجاني عند التحويم */
            transform: scale(1.25);
        }

        .pulse-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 40px;
            height: 40px;
            border: 3px solid var(--primary);
            border-radius: 50%;
            background-color: rgba(138, 43, 226, 0.15);
            animation: pulse-animation 1.6s infinite ease-out;
            z-index: 1;
            opacity: 0;
            box-sizing: border-box;
        }

        @keyframes pulse-animation {
            0% {
                transform: scale(0.2);
                opacity: 0.8;
            }
            50% {
                opacity: 0.4;
            }
            100% {
                transform: scale(1.2);
                opacity: 0;
            }
        }

        /* Public Programs Table Styles */
        .direction-group {
            margin-bottom: 2.5rem;
        }
        .direction-heading {
            text-align: right;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-responsive-wrapper {
            overflow-x: auto;
            border: 1px solid rgba(138, 43, 226, 0.15);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
            background: white;
        }
        .programs-table-public {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
            min-width: 800px;
        }
        .programs-table-public th, .programs-table-public td {
            padding: 14px 16px;
            text-align: right;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .programs-table-public th {
            background-color: rgba(138, 43, 226, 0.04);
            color: #5c1d9c;
            font-weight: 700;
            white-space: nowrap;
        }
        .programs-table-public td {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .programs-table-public tr:nth-child(even) {
            background-color: #fbfbfd;
        }
        .programs-table-public tr:hover {
            background-color: rgba(138, 43, 226, 0.02);
        }
        .register-btn-table {
            color: #fff;
            background: linear-gradient(135deg, var(--secondary) 0%, #e55a5a 100%);
            padding: 7px 14px;
            border-radius: 20px;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.2);
            transition: all 0.3s ease;
        }
        .register-btn-table:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(255, 107, 107, 0.35);
        }
        
        /* ==========================================================================
           أنماط بطاقات البرامج الجديدة والمحدثة (تم دمجها ديناميكياً)
           ========================================================================== */
        
        /* 1. التصميم الزجاجي العصري (Modern Glassmorphism) */
        .card-glass {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            text-align: right;
        }

        .card-glass::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #8a2be2, #ff6b6b);
            opacity: 0.8;
        }

        .card-glass:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px 0 rgba(31, 38, 135, 0.12);
            border-color: rgba(255, 255, 255, 0.75);
            background: rgba(255, 255, 255, 0.55);
        }

        .card-glass-header {
            margin-bottom: 18px;
        }

        .card-glass-organizer {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #6a1b9a;
            background: rgba(138, 43, 226, 0.08);
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-glass-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #1a1d24;
            line-height: 1.45;
        }

        .card-glass-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-glass-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }

        .glass-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.6);
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .glass-detail-item i {
            color: #8a2be2;
            font-size: 0.95rem;
            width: 14px;
            text-align: center;
        }

        .glass-detail-text {
            font-size: 0.85rem;
            color: #4a5568;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .card-glass-description {
            font-size: 0.9rem;
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 20px;
            flex: 1;
        }

        .card-glass-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding-top: 16px;
        }

        .glass-price-box {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .glass-price-label {
            font-size: 0.75rem;
            color: #718096;
            margin-bottom: 2px;
        }

        .glass-price-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #8a2be2;
        }

        .glass-price-value.free {
            color: #28a745;
        }

        .glass-price-notes {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 4px;
            font-weight: 500;
            max-width: 160px;
        }

        .glass-btn {
            background: linear-gradient(135deg, #8a2be2, #6a1b9a);
            color: white !important;
            padding: 10px 22px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
            transition: all 0.3s ease;
            border: none;
            text-align: center;
        }

        .glass-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.3);
            background: linear-gradient(135deg, #9b41f7, #7b27b3);
        }

        /* 2. التصميم التفاعلي الجريء (Bold & Playful Neo-brutalism) */
        .card-brutal {
            background: #ffffff;
            border: 3px solid #000000;
            border-radius: 0px;
            padding: 24px;
            box-shadow: 8px 8px 0px #000000;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.2s cubic-bezier(0.19, 1, 0.22, 1);
            position: relative;
            box-sizing: border-box;
            text-align: right;
        }

        .card-brutal:hover {
            transform: translate(-4px, -4px);
            box-shadow: 12px 12px 0px #000000;
        }

        .card-brutal:active {
            transform: translate(2px, 2px);
            box-shadow: 4px 4px 0px #000000;
        }

        .card-brutal-header {
            border-bottom: 3px solid #000000;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .card-brutal-organizer {
            font-size: 0.85rem;
            font-weight: 800;
            color: #000000;
            background: #f1c40f;
            border: 2px solid #000000;
            padding: 4px 12px;
            display: inline-block;
            margin-bottom: 10px;
            box-shadow: 2px 2px 0px #000000;
        }

        .card-brutal-title {
            font-size: 1.4rem;
            font-weight: 900;
            color: #000000;
            line-height: 1.35;
        }

        .card-brutal-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-brutal-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }

        .brutal-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 2px solid #000000;
            padding: 6px 10px;
            background: #eef2f7;
            font-weight: 700;
        }

        .brutal-detail-item i {
            color: #000000;
            font-size: 0.9rem;
        }

        .brutal-detail-text {
            font-size: 0.8rem;
            color: #000000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-brutal-description {
            font-size: 0.9rem;
            color: #2d3748;
            line-height: 1.6;
            margin-bottom: 20px;
            font-weight: 600;
            flex: 1;
        }

        .card-brutal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 3px solid #000000;
            padding-top: 16px;
            margin-top: auto;
        }

        .brutal-price-box {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .brutal-price-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: #718096;
            text-transform: uppercase;
        }

        .brutal-price-value {
            font-size: 1.35rem;
            font-weight: 900;
            color: #e74c3c;
        }

        .brutal-price-value.free {
            color: #2ecc71;
        }

        .brutal-price-notes {
            font-size: 0.7rem;
            color: #4a5568;
            margin-top: 2px;
            font-weight: 800;
            max-width: 150px;
        }

        .brutal-btn {
            background: #ff6b6b;
            color: #000000 !important;
            padding: 10px 22px;
            border: 3px solid #000000;
            font-weight: 900;
            text-decoration: none;
            font-size: 0.9rem;
            box-shadow: 3px 3px 0px #000000;
            transition: all 0.1s ease;
            text-align: center;
        }

        .brutal-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0px #000000;
            background: #ff8585;
        }

        .brutal-btn:active {
            transform: translate(1px, 1px);
            box-shadow: 2px 2px 0px #000000;
        }

        /* 3. التصميم الأنيق الفاخر (Elegant Premium Theme) */
        .card-premium {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(29, 15, 58, 0.02);
            border: 1px solid rgba(29, 15, 58, 0.06);
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            position: relative;
            box-sizing: border-box;
            text-align: right;
        }

        .card-premium:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(29, 15, 58, 0.08);
            border-color: rgba(197, 168, 128, 0.35);
        }

        .card-premium-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .card-premium-organizer {
            font-size: 0.8rem;
            font-weight: 700;
            color: #c5a880;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-premium-badge {
            background: #faf6f0;
            border: 1px solid #ebdcb9;
            color: #b08d48;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .card-premium-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1d0f3a;
            line-height: 1.4;
            margin-bottom: 14px;
        }

        .card-premium-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-premium-description {
            font-size: 0.92rem;
            color: #5d5a68;
            line-height: 1.75;
            margin-bottom: 24px;
            flex: 1;
        }

        .card-premium-details {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            row-gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px dashed #e2e0e6;
        }

        .premium-detail-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #6c6978;
            padding: 0 10px;
        }

        .premium-detail-item i {
            color: #c5a880;
            font-size: 0.9rem;
        }

        /* فواصل التفاصيل بشكل مناسب للـ RTL */
        [dir="rtl"] .premium-detail-item {
            border-right: 1px solid #e2e0e6;
        }
        [dir="rtl"] .premium-detail-item:first-child {
            border-right: none;
            padding-right: 0;
        }

        .card-premium-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .premium-price-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .premium-price-label {
            font-size: 0.75rem;
            color: #8f8c9c;
            margin-bottom: 2px;
        }

        .premium-price-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1d0f3a;
        }

        .premium-price-value.free {
            color: #27ae60;
        }

        .premium-price-notes {
            font-size: 0.75rem;
            color: #8f8c9c;
            margin-top: 4px;
            font-style: italic;
            max-width: 170px;
        }

        .premium-btn {
            background: #1d0f3a;
            color: #ffffff !important;
            border: 1px solid #1d0f3a;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            text-align: center;
        }

        .premium-btn:hover {
            background: #c5a880;
            border-color: #c5a880;
            color: #1d0f3a !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(197, 168, 128, 0.3);
        }

        /* 4. تنسيقات البرامج المنتهية الصلاحية (Grayscale & Ended Overlay) */
        .program-card.ended,
        .card-glass.ended,
        .card-brutal.ended,
        .card-premium.ended {
            position: relative;
            filter: grayscale(0.95) contrast(0.85) brightness(0.92);
            transition: all 0.3s;
            pointer-events: none; /* تعطيل التحويم والتفاعل بالكامل */
            user-select: none;
        }

        .ended-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #e53e3e;
            color: white;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            z-index: 20;
            box-shadow: 0 4px 10px rgba(229, 62, 62, 0.2);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card-brutal.ended .ended-badge {
            border: 2px solid #000;
            background: #e53e3e;
            color: #fff;
            border-radius: 0;
            box-shadow: 2px 2px 0px #000;
            top: 10px;
            left: 10px;
        }

        .card-premium.ended .ended-badge {
            background: rgba(229, 62, 62, 0.1);
            border: 1px solid rgba(229, 62, 62, 0.3);
            color: #e53e3e;
            border-radius: 4px;
            box-shadow: none;
        }

        /* ==========================================================================
           أنماط الجداول المتوافقة مع النماذج (Table Style Variations)
           ========================================================================== */
        
        /* نمط الزجاجي العصري للجدول (Style 1) */
        .table-responsive-wrapper.style-1 {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
        }
        .table-responsive-wrapper.style-1 .programs-table-public {
            background: transparent;
        }
        .table-responsive-wrapper.style-1 .programs-table-public th {
            background-color: rgba(138, 43, 226, 0.08);
            color: #6a1b9a;
            border-bottom: 2px solid rgba(138, 43, 226, 0.15);
        }
        .table-responsive-wrapper.style-1 .programs-table-public td {
            border-bottom: 1px solid rgba(138, 43, 226, 0.08);
            color: #4a5568;
        }
        .table-responsive-wrapper.style-1 .programs-table-public tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.25);
        }
        .table-responsive-wrapper.style-1 .programs-table-public tr:hover {
            background-color: rgba(138, 43, 226, 0.04);
        }
        .table-responsive-wrapper.style-1 .register-btn-table {
            background: linear-gradient(135deg, #8a2be2, #6a1b9a);
            box-shadow: 0 2px 5px rgba(138, 43, 226, 0.2);
        }

        /* نمط النيوبوتاليزم للجدول (Style 2) */
        .table-responsive-wrapper.style-2 {
            border: 3px solid #000000;
            box-shadow: 6px 6px 0px #000000;
            border-radius: 0px;
        }
        .table-responsive-wrapper.style-2 .programs-table-public th {
            background-color: #f1c40f;
            color: #000000;
            font-weight: 900;
            border-bottom: 3px solid #000000;
            border-left: 2px solid #000000;
        }
        .table-responsive-wrapper.style-2 .programs-table-public td {
            border-bottom: 2px solid #000000;
            border-left: 2px solid #000000;
            color: #000000;
            font-weight: 700;
        }
        .table-responsive-wrapper.style-2 .programs-table-public th:last-child,
        .table-responsive-wrapper.style-2 .programs-table-public td:last-child {
            border-left: none;
        }
        .table-responsive-wrapper.style-2 .programs-table-public tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .table-responsive-wrapper.style-2 .programs-table-public tr:hover {
            background-color: #edf2f7;
        }
        .table-responsive-wrapper.style-2 .register-btn-table {
            background: #ff6b6b;
            color: #000000;
            border: 2px solid #000000;
            box-shadow: 2px 2px 0px #000000;
            border-radius: 0;
            font-weight: 900;
        }
        .table-responsive-wrapper.style-2 .register-btn-table:hover {
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px #000000;
            background: #ff8585;
        }

        /* نمط الفاخر الأنيق للجدول (Style 3) */
        .table-responsive-wrapper.style-3 {
            border: 1px solid rgba(29, 15, 58, 0.08);
            box-shadow: 0 10px 30px rgba(29, 15, 58, 0.02);
            border-radius: 12px;
        }
        .table-responsive-wrapper.style-3 .programs-table-public th {
            background-color: #1d0f3a;
            color: #c5a880;
            font-weight: 700;
            border-bottom: 2px solid #c5a880;
        }
        .table-responsive-wrapper.style-3 .programs-table-public td {
            border-bottom: 1px solid #e2e0e6;
            color: #5d5a68;
        }
        .table-responsive-wrapper.style-3 .programs-table-public tr:nth-child(even) {
            background-color: #fafafc;
        }
        .table-responsive-wrapper.style-3 .programs-table-public tr:hover {
            background-color: #f5f4f8;
        }
        .table-responsive-wrapper.style-3 .register-btn-table {
            background: #1d0f3a;
            color: #ffffff;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: none;
        }
        .table-responsive-wrapper.style-3 .register-btn-table:hover {
            background: #c5a880;
            color: #1d0f3a;
            transform: translateY(-1px);
        }

        /* ==========================================================================
           أنماط منبثقات الخريطة المتوافقة مع النماذج (Map Popup Variations)
           ========================================================================== */
        
        .map-popup-card {
            text-align: right;
            direction: rtl;
            font-family: 'Tajawal', sans-serif;
            padding: 5px;
            box-sizing: border-box;
        }
        .map-popup-card .popup-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .map-popup-card .popup-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 5px 0;
            color: #212529;
            line-height: 1.4;
        }
        .map-popup-card .popup-organizer {
            font-size: 0.85rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .map-popup-card .popup-organizer i {
            color: var(--primary);
        }
        .map-popup-card .popup-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 0.8rem;
            margin-bottom: 8px;
        }
        .map-popup-card .popup-detail-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #555;
        }
        .map-popup-card .popup-detail-item i {
            color: var(--primary);
            font-size: 0.85rem;
        }
        .map-popup-card .popup-description {
            font-size: 0.85rem;
            color: #555;
            line-height: 1.5;
            margin: 0 0 10px 0;
        }
        .map-popup-card .popup-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
        .map-popup-card .popup-price-box {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .map-popup-card .popup-price-label {
            font-size: 0.7rem;
            color: #777;
        }
        .map-popup-card .popup-price-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
        }
        .map-popup-card .popup-price-value.free {
            color: var(--success);
        }
        .map-popup-card .popup-price-notes {
            font-size: 0.7rem;
            color: #777;
            margin-top: 2px;
        }
        .map-popup-card .popup-register-btn {
            background: var(--primary);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .map-popup-card .popup-register-btn:hover {
            background: #7a1fc2;
        }

        /* منبثق نمط الزجاجي العصري (Style 1) */
        .map-popup-card.style-1 .popup-organizer {
            color: #6a1b9a;
            background: rgba(138, 43, 226, 0.08);
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-flex;
        }
        .map-popup-card.style-1 .popup-detail-item i {
            color: #8a2be2;
        }
        .map-popup-card.style-1 .popup-price-value {
            color: #8a2be2;
        }
        .map-popup-card.style-1 .popup-register-btn {
            background: linear-gradient(135deg, #8a2be2, #6a1b9a);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(138, 43, 226, 0.2);
        }
        .map-popup-card.style-1 .popup-register-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(138, 43, 226, 0.3);
        }

        /* منبثق نمط النيوبوتاليزم (Style 2) */
        .map-popup-card.style-2 {
            border: 2px solid #000;
            padding: 10px;
            box-shadow: 4px 4px 0px #000;
        }
        .map-popup-card.style-2 .popup-header {
            border-bottom: 2px solid #000;
        }
        .map-popup-card.style-2 .popup-title {
            font-weight: 900;
        }
        .map-popup-card.style-2 .popup-organizer {
            background: #f1c40f;
            border: 1px solid #000;
            padding: 1px 6px;
            color: #000;
            font-weight: 800;
            box-shadow: 1px 1px 0px #000;
            display: inline-flex;
        }
        .map-popup-card.style-2 .popup-detail-item {
            border: 1px solid #000;
            background: #eef2f7;
            padding: 3px 6px;
            font-weight: 700;
        }
        .map-popup-card.style-2 .popup-detail-item i {
            color: #000;
        }
        .map-popup-card.style-2 .popup-description {
            font-weight: 600;
        }
        .map-popup-card.style-2 .popup-footer {
            border-top: 2px solid #000;
        }
        .map-popup-card.style-2 .popup-price-value {
            color: #e74c3c;
            font-weight: 900;
        }
        .map-popup-card.style-2 .popup-price-value.free {
            color: #2ecc71;
        }
        .map-popup-card.style-2 .popup-price-notes {
            color: #000;
            font-weight: 800;
        }
        .map-popup-card.style-2 .popup-register-btn {
            background: #ff6b6b;
            color: #000;
            border: 2px solid #000;
            border-radius: 0;
            box-shadow: 2px 2px 0px #000;
            font-weight: 900;
        }
        .map-popup-card.style-2 .popup-register-btn:hover {
            transform: translate(-1px, -1px);
            box-shadow: 3px 3px 0px #000;
            background: #ff8585;
        }

        /* منبثق نمط الفاخر الأنيق (Style 3) */
        .map-popup-card.style-3 .popup-organizer {
            color: #c5a880;
            font-weight: 700;
            text-transform: uppercase;
        }
        .map-popup-card.style-3 .popup-detail-item i {
            color: #c5a880;
        }
        .map-popup-card.style-3 .popup-price-value {
            color: #1d0f3a;
        }
        .map-popup-card.style-3 .popup-register-btn {
            background: #1d0f3a;
            border-radius: 6px;
            font-weight: 600;
        }
        .map-popup-card.style-3 .popup-register-btn:hover {
            background: #c5a880;
            color: #1d0f3a;
            transform: translateY(-1px);
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
