<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجموعة تكامل - التكامل والتميز المؤسسي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Tajawal for headings, Noto Kufi Arabic for body -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;700&family=Tajawal:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c5aa0;
            --secondary: #d4af37;
            --accent: #8b4513;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --info: #17a2b8;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --gradient: linear-gradient(135deg, var(--primary), #1e3a8a);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Kufi Arabic', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--dark);
            line-height: 1.6;
        }

        html {
            scroll-padding-top: 110px; /* ارتفاع الهيدر + هامش صغير */
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 10px;
        }
        
        /* Header Styles */
        header {
            background: var(--gradient);
            color: white;
            padding: 1rem 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: grid;
            grid-template-rows: auto auto auto;
            gap: 0.5rem;
            position: relative;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .launch-badge {
            position: absolute;
            top: 40px;
            left: 10px;
            background: linear-gradient(135deg, var(--secondary), #e6c12e);
            color: var(--dark);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 10;
            animation: pulse 2s infinite;
        }

        .launch-badge::before {
            content: '🚀';
            margin-left: 8px;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .header-bottom {
            display: flex;
            justify-content: flex-start;
            gap: 20px;
            padding: 0.5rem 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-direction: row; /* logo image then text */
            justify-content: flex-start; /* align to left */
        }
        
        .logo img {
            height: 120px;
            margin-right: 12px;
            margin-left: 0;
            border-radius: 12px;
            background: transparent;
            box-shadow: none;
        }
        
        .logo-text h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #fff;
            font-family: 'Tajawal', Arial, sans-serif;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .logo-text p {
            font-size: 1rem;
            opacity: 0.95;
            color: var(--secondary);
            font-weight: bold;
            margin-top: 30px;
        }
        
        nav {
            width: 100%;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
            justify-content: center;
            flex-wrap: nowrap;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        nav a:hover {
            color: var(--secondary);
            background: rgba(255, 255, 255, 0.1);
        }

        .header-bottom a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .header-bottom a:hover {
            color: var(--secondary);
            background: rgba(255, 255, 255, 0.1);
        }

        .header-bottom a i {
            color: var(--secondary);
        }
        

        
        .announcement {
            background: linear-gradient(135deg, var(--secondary), #e6c12e);
            color: var(--dark);
            padding: 2rem 0;
            text-align: center;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: var(--shadow);
            position: relative;
        }
        
        .close-btn {
            position: absolute;
            top: 10px;
            right: 20px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 20px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .close-btn:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        
        .announcement-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .announcement h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .announcement-details {
            background: rgba(255, 255, 255, 0.8);
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
            box-shadow: var(--shadow);
        }
        
        .announcement-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 10px 0;
            font-size: 1.1rem;
        }
        
        .announcement-icon {
            color: var(--primary);
            font-size: 1.3rem;
        }
        
        .location-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }
        
        .location-link {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .location-link:hover {
            background: #1e3a8a;
            transform: translateY(-3px);
        }
        
        .closing-note {
            font-size: 1.2rem;
            margin-top: 1.5rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 5rem 0;
            text-align: center;
            border-radius: 20px;
            margin-bottom: 3rem;
        }
        
        .hero-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 2rem;
        }
        
        .btn {
            display: inline-block;
            background: var(--secondary);
            color: var(--dark);
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #e6c12e;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        /* Sections Common Styles */
        .section-title {
            text-align: center;
            margin: 3rem 0 1rem;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2rem;
            color: var(--primary);
            display: inline-block;
            padding-bottom: 10px;
            margin-top: 120px; /* حتى لا يغطيه الهيدر الثابت */
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background: var(--secondary);
            bottom: 0;
            right: 50%;
            transform: translateX(50%);
        }
        
        /* Cards Section */
        .cards-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
            margin-bottom: 4rem;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
            flex: 1 1 300px;
            min-width: 300px;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: center;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-body ul {
            list-style: none;
            padding-right: 15px;
        }
        
        .card-body li {
            margin-bottom: 10px;
            position: relative;
            padding-right: 20px;
        }
        
        .card-body li::before {
            content: '✓';
            color: var(--success);
            font-weight: bold;
            position: absolute;
            right: 0;
        }
        
        /* Vision & Mission Section */
        .vision-mission {
            background: var(--light);
            padding: 2rem 0 4rem;
            border-radius: 20px;
            margin-bottom: 3rem;
        }
        
        .vm-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        
        .vm-card {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .vm-card:hover {
            transform: translateY(-5px);
        }
        
        .vm-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .vm-card h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .vm-card p {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        /* Goals Section */
        .goals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 4rem;
            justify-content: center;
        }
        
        .goal-item {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            border-right: 4px solid var(--primary);
        }
        
        /* Achievements Section */
        .achievements-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 4rem;
            justify-content: center;
        }
        
        .achievement-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--secondary);
        }
        
        .achievement-card h4 {
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Timeline Section */
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto 4rem;
        }
        
        .timeline::after {
            content: '';
            position: absolute;
            width: 4px;
            background: var(--primary);
            top: 0;
            bottom: 0;
            right: 50%;
            margin-right: -2px;
        }
        
        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }
        
        .timeline-item:nth-child(odd) {
            right: 0;
        }
        
        .timeline-item:nth-child(even) {
            right: 50%;
        }
        
        .timeline-content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            position: relative;
        }
        
        .timeline-content::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: var(--secondary);
            border-radius: 50%;
            top: 20px;
        }
        
        .timeline-item:nth-child(odd) .timeline-content::after {
            left: -10px;
        }
        
        .timeline-item:nth-child(even) .timeline-content::after {
            right: -10px;
        }
        
        /* Stats Section */
        .stats {
            background: var(--gradient);
            color: white;
            padding: 4rem 0;
            text-align: center;
            border-radius: 20px;
            margin-bottom: 3rem;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .stat-item {
            padding: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        /* This Year Achievements Section */
        #thisyear {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
            padding: 4rem 0;
            border-radius: 20px;
            margin-bottom: 3rem;
            position: relative;
        }
        
        #thisyear::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="%23d4af37" opacity="0.1"/></svg>') repeat;
            border-radius: 20px;
            pointer-events: none;
        }
        
        #thisyear .goal-item {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #d4af37;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(212, 175, 55, 0.2);
            transition: all 0.3s ease;
        }
        
        #thisyear .goal-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(212, 175, 55, 0.3);
            border-color: #b8860b;
        }
        
        #thisyear .goal-item h4 {
            color: #2c5aa0;
            font-weight: bold;
        }
        
        #thisyear .goal-item i {
            color: #d4af37;
        }

        /* Footer -->
        footer {
            background: linear-gradient(135deg, var(--dark) 0%, #1a1a1a 100%);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
            position: relative;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            margin-bottom: 3rem;
        }
        
        .footer-section h3 {
            color: var(--secondary);
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .footer-section h3::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: var(--secondary);
            bottom: 0;
            right: 0;
        }
        
        .footer-section p {
            margin-bottom: 1.2rem;
            line-height: 1.8;
            font-size: 1rem;
        }
        
        .footer-section a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: var(--secondary);
        }
        
        .social-links {
            display: flex;
            gap: 20px;
            margin-top: 25px;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
            font-size: 1.2rem;
        }
        
        .social-links a:hover {
            background: var(--secondary);
            color: var(--dark);
            transform: translateY(-5px);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: #aaa;
        }
        
        /* Mobile Styles */
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .mobile-links {
            display: none;
            justify-content: center;
            gap: 20px;
            padding: 0.5rem 0;
            background: var(--gradient);
            color: white;
        }

        .mobile-links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .side-menu {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            width: 250px;
            height: 100%;
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.5);
            z-index: 1001;
            padding: 2rem 1rem;
        }

        .side-menu ul {
            list-style: none;
            padding: 0;
        }

        .side-menu li {
            margin-bottom: 1rem;
        }

        .side-menu a {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                gap: 0.5rem;
            }

            nav {
                display: none;
            }

            .header-bottom {
                display: none;
            }

            .hamburger {
                display: block;
            }

            .mobile-links {
                display: flex;
            }

            .logo {
                flex-direction: row;
                align-items: center;
                text-align: left;
            }

            .logo img {
                height: 50px;
                margin-right: 10px;
            }

            .logo-text h1 {
                display: none;
            }

            .logo-text p {
                font-size: 0.9rem;
                margin: 0;
            }

            nav ul {
                gap: 15px;
                justify-content: center;
            }

            .hero-content h2 {
                font-size: 2rem;
            }

            .cards-container, .goals-grid, .achievements-container {
                grid-template-columns: 1fr;
            }

            .vm-container {
                grid-template-columns: 1fr;
            }

            .timeline::after {
                right: 31px;
            }

            .timeline-item {
                width: 100%;
                padding-right: 70px;
                padding-left: 25px;
            }

            .timeline-item:nth-child(even) {
                right: 0%;
            }

            .location-links {
                flex-direction: column;
                align-items: center;
            }

            .row-3 {
                display: none;
            }

            .launch-badge {
                top: 20px;
                left: 5px;
                font-size: 0.8rem;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
                <div class="logo">
                <img src="media/logopen-03.png" alt="شعار تكامل" style="margin-right: 12px; margin-left: 0; border-radius: 12px; background: transparent; box-shadow: none;" loading="lazy">
                <div class="logo-text">
                    <p>المجموعة التنسيقية للكينات النسائية العاملة في الرياض</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="#about">عن المجموعة</a></li>
                    <li><a href="#vision">الرؤية والرسالة</a></li>
                    <li><a href="#logos">الجهات الأعضاء</a></li>
                    <li><a href="#integration">مجالات التكامل</a></li>
                    <li><a href="#objectives">الأهداف</a></li>
                    <li><a href="#thisyear">أعمال هذا العام</a></li>
                    <li><a href="#goals">الإنجازات</a></li>
                    <li><a href="#timeline">مراحل التأسيس</a></li>
                    <li><a href="#aspirations">التطلعات</a></li>
                </ul>
            </nav>
            <div class="header-bottom">
                <a href="https://dalil1447.takamulgroup.org/" target="_blank"><i class="fas fa-sun"></i> دليل برامج الصيف</a>
                <a href="media/الدليل الاجرائي لتكامل.pdf" target="_blank"><i class="fas fa-file-alt"></i> الدليل الإجرائي</a>
            </div>
            <div class="launch-badge">الإطلاق الأولي</div>
            <button class="hamburger" onclick="toggleMenu()">&#9776;</button>
        </div>
        <div class="mobile-links">
            <a href="https://dalil1447.takamulgroup.org/" target="_blank">دليل برامج الصيف</a> -
            <a href="media/الدليل الاجرائي لتكامل.pdf" target="_blank">الدليل الإجرائي</a>
        </div>
        <div id="side-menu" class="side-menu">
            <ul>
                <li><a href="#about" onclick="toggleMenu()">عن المجموعة</a></li>
                <li><a href="#vision" onclick="toggleMenu()">الرؤية والرسالة</a></li>
                <li><a href="#logos" onclick="toggleMenu()">الجهات الأعضاء</a></li>
                <li><a href="#integration" onclick="toggleMenu()">مجالات التكامل</a></li>
                <li><a href="#objectives" onclick="toggleMenu()">الأهداف</a></li>
                <li><a href="#thisyear" onclick="toggleMenu()">أعمال هذا العام</a></li>
                <li><a href="#goals" onclick="toggleMenu()">الإنجازات</a></li>
                <li><a href="#timeline" onclick="toggleMenu()">مراحل التأسيس</a></li>
                <li><a href="#aspirations" onclick="toggleMenu()">التطلعات</a></li>
            </ul>
        </div>
    </header>

    <!-- Announcement Section -->
    <section class="announcement" id="announcement">
        <div class="container announcement-content">
            <button class="close-btn" onclick="closeAnnouncement()">&times;</button>
            <h2>اللقاء الثامن عشر لمجموعة تكامل</h2>
            <p>يطيب لأمانة تكامل أن تعلن عن اللقاء الثامن عشر لمجموعة تكامل،<br>باستضافة كريمة من جمعية مكنون</p>
            
            <div class="announcement-details">
                <div class="announcement-item">
                    <i class="fas fa-calendar-alt announcement-icon"></i>
                    <span>🗓 يوم: الثلاثاء</span>
                </div>
                <div class="announcement-item">
                    <i class="fas fa-clock announcement-icon"></i>
                    <span>📆 التاريخ: ٦ جمادى الأولى ١٤٤٧هـ – الموافق ٢٨ أكتوبر ٢٠٢٥م</span>
                </div>
                <div class="announcement-item">
                    <i class="fas fa-clock announcement-icon"></i>
                    <span>⏰ الوقت: بعد صلاة العشاء مباشرة</span>
                </div>
                <div class="announcement-item">
                    <i class="fas fa-map-marker-alt announcement-icon"></i>
                    <span>📍 المكان:</span>
                </div>
                
                <div class="location-links">
                    <a href="https://maps.app.goo.gl/78aHRAfBKUJP1H3b8?g_st=iw" class="location-link" target="_blank">
                        <i class="fas fa-male"></i> الإخوة الرجال
                    </a>
                    <a href="https://maps.app.goo.gl/MTV6mTysntxqRM1L6?g_st=ic" class="location-link" target="_blank">
                        <i class="fas fa-female"></i> الأخوات الكريمات
                    </a>
                </div>
            </div>
            
            <p class="closing-note">فأهلاً وسهلاً بكم في لقاءٍ يتجدد فيه العطاء والتكامل ✨</p>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <h2>مجموعة تكامل - التكامل والتميز المؤسسي</h2>
            <p>مجموعة تنسيقية تضم 30 جهة تعمل في المجال التنموي النسائي بمدينة الرياض</p>
            <a href="#about" class="btn">تعرف على المزيد</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="container">
        <div class="section-title">
            <h2>عن مجموعة تكامل</h2>
        </div>
        <div class="cards-container">
            <div class="card">
                <div class="card-header">
                    <h3>التعريف بالمجموعة</h3>
                </div>
                <div class="card-body">
                    <p>مجموعة تكامل هي مجموعة تنسيقية تضم (30) جهة تعمل في المجال التنموي النسائي بمدينة الرياض.</p>
                    <p><strong>تاريخ النشأة:</strong> 10/03/1442هـ الموافق: 27/10/2020م</p>
                </div>
            </div>
            
            
            <div class="card">
                <div class="card-header">
                    <h3>النموذج الاحترافي</h3>
                </div>
                <div class="card-body">
                    <p>تمثل مجموعة تكامل نموذجاً احترافياً في العمل التنسيقي المشترك بين الجهات التنموية النسائية في الرياض.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission Section -->
    <section id="vision" class="vision-mission">
        <div class="container">
            <div class="section-title">
                <h2>الرؤية والرسالة</h2>
            </div>
            <div class="vm-container">
                <div class="vm-card">
                    <div class="vm-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 style="color: var(--secondary);">الرؤية</h3>
                    <p>نسعى للتكامل وتبادل الخبرات، من خلال إقامة برامج نوعية مشتركة، وتأهيل وتدريب مشترك وتطوير لبيئة العمل لتحقيق التميز المؤسسي.</p>
                </div>
                <div class="vm-card">
                    <div class="vm-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 style="color: var(--secondary);">الرسالة</h3>
                    <p>تعزيز العمل التنسيقي بين الجهات النسائية التنموية في الرياض لتحقيق التكامل وتبادل الخبرات والموارد لخدمة المجتمع.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Member Logos Section -->
    <section id="logos" class="member-logos">
        <div class="container">
            <div class="section-title">
                <h2>الجهات الأعضاء</h2>
            </div>
            
            <!-- السطر الأول: من اليمين لليسار → -->
            <div class="logo-row row-1">
                <div class="logo-slider-track">
                    <div class="logo-slide"><img src="./media/logos/إثراء المعرفة.png" alt="إثراء المعرفة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية إيراق.png" alt="جمعية إيراق" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/دعوتها.png" alt="دعوتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/شعار رسالتها.png" alt="رسالتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/عالم غراس.png" alt="عالم غراس" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مجمع نورين.png" alt="مجمع نورين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وابل.jpg" alt="مركز وابل" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وارث.png" alt="مركز وارث" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكنون.jpg" alt="مكنون" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكين.jpeg" alt="مكين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/همة.png" alt="همة" class="logo-image"></div>
                    <!-- نسخة مكررة للحلقة المستمرة -->
                    <div class="logo-slide"><img src="./media/logos/إثراء المعرفة.png" alt="إثراء المعرفة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية إيراق.png" alt="جمعية إيراق" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/دعوتها.png" alt="دعوتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/شعار رسالتها.png" alt="رسالتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/عالم غراس.png" alt="عالم غراس" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مجمع نورين.png" alt="مجمع نورين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وابل.jpg" alt="مركز وابل" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وارث.png" alt="مركز وارث" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكنون.jpg" alt="مكنون" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكين.jpeg" alt="مكين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/همة.png" alt="همة" class="logo-image"></div>
                </div>
            </div>

            <!-- السطر الثاني: من اليسار لليمين ← (عكس) -->
            <div class="logo-row row-2">
                <div class="logo-slider-track">
                    <div class="logo-slide"><img src="./media/logos/إثراء المعرفة.png" alt="إثراء المعرفة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية إيراق.png" alt="جمعية إيراق" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/دعوتها.png" alt="دعوتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/شعار رسالتها.png" alt="رسالتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/عالم غراس.png" alt="عالم غراس" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مجمع نورين.png" alt="مجمع نورين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وابل.jpg" alt="مركز وابل" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وارث.png" alt="مركز وارث" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكنون.jpg" alt="مكنون" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكين.jpeg" alt="مكين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/همة.png" alt="همة" class="logo-image"></div>
                    <!-- نسخة مكررة -->
                    <div class="logo-slide"><img src="./media/logos/إثراء المعرفة.png" alt="إثراء المعرفة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية إيراق.png" alt="جمعية إيراق" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/دعوتها.png" alt="دعوتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/شعار رسالتها.png" alt="رسالتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/عالم غراس.png" alt="عالم غراس" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مجمع نورين.png" alt="مجمع نورين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وابل.jpg" alt="مركز وابل" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وارث.png" alt="مركز وارث" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكنون.jpg" alt="مكنون" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكين.jpeg" alt="مكين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/همة.png" alt="همة" class="logo-image"></div>
                </div>
            </div>

            <!-- السطر الثالث: من اليمين لليسار → (مثل الأول) -->
            <div class="logo-row row-3">
                <div class="logo-slider-track">
                    <div class="logo-slide"><img src="./media/logos/إثراء المعرفة.png" alt="إثراء المعرفة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية إيراق.png" alt="جمعية إيراق" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/دعوتها.png" alt="دعوتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/شعار رسالتها.png" alt="رسالتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/عالم غراس.png" alt="عالم غراس" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مجمع نورين.png" alt="مجمع نورين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وابل.jpg" alt="مركز وابل" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وارث.png" alt="مركز وارث" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكنون.jpg" alt="مكنون" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكين.jpeg" alt="مكين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/همة.png" alt="همة" class="logo-image"></div>
                    <!-- نسخة مكررة -->
                    <div class="logo-slide"><img src="./media/logos/إثراء المعرفة.png" alt="إثراء المعرفة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية إيراق.png" alt="جمعية إيراق" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/دعوتها.png" alt="دعوتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/شعار رسالتها.png" alt="رسالتها" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/عالم غراس.png" alt="عالم غراس" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مجمع نورين.png" alt="مجمع نورين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وابل.jpg" alt="مركز وابل" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مركز وارث.png" alt="مركز وارث" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكنون.jpg" alt="مكنون" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/مكين.jpeg" alt="مكين" class="logo-image"></div>
                    <div class="logo-slide"><img src="./media/logos/همة.png" alt="همة" class="logo-image"></div>
                </div>
            </div>
        </div>
    </section>

<style>
    .member-logos {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        padding: 4rem 0;
        margin: 4rem 0;
        border-radius: 30px;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.03);
    }

    .logo-row {
        overflow: hidden;
        position: relative;
        width: 100%;
        direction: ltr;
        margin-bottom: 20px;
    }

    .logo-row:last-child {
        margin-bottom: 0;
    }

    .logo-slider-track {
        display: flex;
        width: fit-content;
        gap: 30px;
    }

    /* السطر الأول: من اليمين لليسار */
    .row-1 .logo-slider-track {
        animation: scrollLeft 60s linear infinite;
    }

    /* السطر الثاني: من اليسار لليمين (عكس) */
    .row-2 .logo-slider-track {
        animation: scrollRight 60s linear infinite;
    }

    /* السطر الثالث: من اليمين لليسار لكن يبدأ من النصف */
    .row-3 .logo-slider-track {
        animation: scrollLeft 60s linear infinite;
        transform: translateX(-25%);
    }

    .logo-slider-track:hover {
        animation-play-state: paused;
    }

    .logo-slide {
        flex-shrink: 0;
        min-width: 170px;
        max-width: 210px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 10px 0 rgba(44, 62, 80, 0.07);
        transition: box-shadow 0.3s, transform 0.3s;
    }

    .logo-slide:hover {
        box-shadow: 0 12px 36px 0 rgba(44, 62, 80, 0.18);
        transform: scale(1.13);
        z-index: 2;
    }

    .logo-image {
        max-width: 90%;
        max-height: 90px;
        object-fit: contain;
        filter: none;
        transition: transform 0.3s;
    }

    /* حركة من اليمين لليسار */
    @keyframes scrollLeft {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    /* حركة من اليسار لليمين */
    @keyframes scrollRight {
        0% {
            transform: translateX(-50%);
        }
        100% {
            transform: translateX(0);
        }
    }

    @media (max-width: 900px) {
        .logo-slide {
            min-width: 100px;
            max-width: 120px;
            height: 60px;
        }
        .logo-image {
            max-height: 40px;
        }
    }
</style>
    <!-- نهاية قسم الشعارات -->

    <!-- Integration Areas Section -->
    <section id="integration" class="container">
        <div class="section-title">
            <h2>مجالات التكامل</h2>
        </div>
        <div class="goals-grid">
            <div class="goal-item">
                <h4><i class="fas fa-cogs"></i> المساهمة في تطوير وتحسين أداء الكيانات</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-calendar-alt"></i> إقامة برامج مشتركة</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-users"></i> مشاركة الكوادر البشرية</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-graduation-cap"></i> التدريب والتطوير المشترك</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-exchange-alt"></i> تبادل المعرفة</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-handshake"></i> خدمات مساندة تشاركية</h4>
            </div>
        </div>
    </section>

    <!-- Objectives Section -->
    <section id="objectives" class="container">
        <div class="section-title">
            <h2>أهداف مجموعة تكامل التنسيقية</h2>
        </div>
        <div class="goals-grid">
            <div class="goal-item">
                <h4><i class="fas fa-handshake"></i> تحقيق التكامل والتنسيق والشراكات وتبادل الخبرات بين الكيانات المهتمة بالفتيات</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-graduation-cap"></i> تقديم برامج مشتركة للتطوير والتدريب</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-heart"></i> تعزيز أواصر الترابط وبناء الثقة بين الكيانات</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-star"></i> إقامة برامج نوعية مشتركة</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-share-alt"></i> التشارك في الخدمات</h4>
            </div>
        </div>
    </section>

    <!-- This Year Achievements Section -->
    <section id="thisyear" class="container">
        <div class="section-title">
            <h2>أعمال تم إنجازها هذا العام</h2>
        </div>
        <div class="goals-grid">
            <div class="goal-item">
                <h4><i class="fas fa-check-circle"></i> الانتهاء من نمذجة عمل مجموعة تكامل</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-map-marker-alt"></i> زيارة الكيانات الشبيهة في المنطقة الشرقية</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-book"></i> إصدار دليل البرامج الصيفية لصيف عام 1447هـ لجميع جهات تكامل</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-globe"></i> إطلاق موقع خاص بدليل برامج الصيف</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-lightbulb"></i> التخطيط للقاء المؤسسات المانحة بطريقة مختلفة عن اللقاءات السابقة</h4>
            </div>
            <div class="goal-item">
                <h4><i class="fas fa-users"></i> تدشين برنامج تدريب 20 مديرة تنفيذية من جهات تكامل</h4>
            </div>
        </div>
    </section>

    </section>

    <!-- Goals Section -->
    <section id="goals" class="container">
        <div class="section-title">
            <h2>إنجازات مجموعة تكامل</h2>
        </div>
        <div class="goals-grid">
            <div class="goal-item">
                <h4><i class="fas fa-users"></i> الاجتماعات واللقاءات</h4>
                <ul>
                    <li>عقد 17 اجتماع لأعضاء المجموعة</li>
                    <li>إقامة 5 لقاءات اجتماعية للمجموعة للرجال وللنساء</li>
                    <li>عقد لقاءين لجهات تكامل مع المؤسسات المانحة لعرض المشاريع (بحضور 13 – 18 مؤسسة)</li>
                </ul>
            </div>
            
            <div class="goal-item">
                <h4><i class="fas fa-graduation-cap"></i> التدريب والتطوير</h4>
                <ul>
                    <li>تقديم دورة تدريبية عن صناعة المشاريع المميزة قبل لقاء المؤسسات المانحة</li>
                    <li>إقامة سبعة دورات تدريبية مشتركة على مستوى القيادات والفريق التنفيذي</li>
                    <li>الانتهاء من تدريب 8 مدراء تنفيذيين – دورة إدارة المشاريع – دورة السكرتارية ..الخ</li>
                </ul>
            </div>
            
            <div class="goal-item">
                <h4><i class="fas fa-handshake"></i> الشراكات والتبادل</h4>
                <ul>
                    <li>عقد أكثر من 100 شراكة بين جهات تكامل</li>
                    <li>إقامة عدد من الزيارات البينية بين جهات تكامل لتبادل الخبرات والمعلومات</li>
                    <li>زيارة جهات تكامل الى سبعة جهات مشابهة في مكة المكرمة وجدة</li>
                    <li>زيارات بعض الجهات النسائية من خارج الرياض لمجموعة تكامل لتفعيل الشراكة والاستفادة من التجربة</li>
                </ul>
            </div>
            
            <div class="goal-item">
                <h4><i class="fas fa-cogs"></i> الخدمات والدعم</h4>
                <ul>
                    <li>تقديم العديد من الخدمات لبعض جهات تكامل (تصاميم الهويات، انشاء الخطط، الاستفادة من المقرات ..الخ)</li>
                    <li>اصدار دليل البرامج الصيفية لصيف عام 1444هـ - 1445هـ لجميع جهات تكامل</li>
                    <li>تكريم المديرات والمشرفات والمتطوعات على البرامج الصيفية من قبل أمانة تكامل للعام 1445هـ (بعدد 600)</li>
                </ul>
            </div>
            
            <div class="goal-item">
                <h4><i class="fas fa-sync-alt"></i> التبادل والتعاون</h4>
                <ul>
                    <li>الاستفادة من المتطوعات من بعض الجهات التابعة لتكامل</li>
                    <li>تبادل الخطط السنوية لبعض الجهات والاستفادة منها</li>
                    <li>توقيع عقد اتفاقية مع جهة متخصصة بالتصاميم والهويات</li>
                    <li>استفادة اكثر من 15 جهة من المقرات ووسائل النقل</li>
                    <li>الاستفادة من البرامج المحاسبية لدى بعض الجهات</li>
                </ul>
            </div>
            
            <div class="goal-item">
                <h4><i class="fas fa-chart-line"></i> التطوير المؤسسي</h4>
                <ul>
                    <li>العمل على نمذجة عمل مجموعة تكامل</li>
                    <li>تعيين مساعد للمدير التنفيذي</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section id="timeline" class="container">
        <div class="section-title">
            <h2>مراحل التأسيس وقبل الانطلاق</h2>
        </div>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>وضع الأهداف والرؤية</h4>
                    <p>وضع أهداف ورسالة ورؤية مشتركة للمجموعة</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>تحديد الجهات</h4>
                    <p>تحديد الجهات ذات العلاقة من بين الجمعيات</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>دعوة الجهات</h4>
                    <p>مخاطبة الجهات ودعوتهم للانضمام للمجموعة</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>الاجتماع التعريفي</h4>
                    <p>عقد الاجتماع التعريفي الأول مع الجمعيات المنظمة</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>التكليف الرسمي</h4>
                    <p>اصدار تكليف رسمي للمجموعة من قبل اللجنة التنسيقية</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>تعيين المدير التنفيذي</h4>
                    <p>تعيين المدير التنفيذي للمجموعة</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>الخطة التنفيذية</h4>
                    <p>رسم خطة تنفيذية سنوية مع موازنتها التشغيلية</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>بدء التنفيذ</h4>
                    <p>البدء بتنفيذ الخطة</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements Section -->
    <section id="achievements" class="container">
        <div class="section-title">
            <h2>إنجازات مجموعة تكامل</h2>
        </div>
        <div class="achievements-container">
            <div class="achievement-card">
                <h4><i class="fas fa-handshake"></i> الشراكات</h4>
                <p>عقد أكثر من 100 شراكة بين جهات تكامل</p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-graduation-cap"></i> التدريب</h4>
                <p>الانتهاء من تدريب 8 مدراء تنفيذيين في دورات متخصصة</p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-sun"></i> البرامج الصيفية</h4>
                <p>اصدار دليل البرامج الصيفية لصيف عام 1444هـ - 1445هـ</p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-award"></i> التكريم</h4>
                <p>تكريم 600 مديرة ومشرفة ومتطوعة على البرامج الصيفية</p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-exchange-alt"></i> التبادل</h4>
                <p>تبادل الخطط السنوية والاستفادة المتبادلة بين الجهات</p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-building"></i> المرافق</h4>
                <p>استفادة أكثر من 15 جهة من المقرات ووسائل النقل</p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-palette"></i> التصاميم</h4>
                <p>توقيع اتفاقية مع جهة متخصصة بالتصاميم والهويات</p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-chart-bar"></i> النمذجة</h4>
                <p>إصدار الديل الإجرائي  ـ نمذجة تكامل </p>
            </div>
            
            <div class="achievement-card">
                <h4><i class="fas fa-user-tie"></i> الهيكلة</h4>
                <p>تعيين مساعد للمدير التنفيذي</p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container stats-container">
            <div class="stat-item">
                <div class="stat-number">30</div>
                <div class="stat-text">جهة تنموية نسائية</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100+</div>
                <div class="stat-text">شراكة مبرمة</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">600</div>
                <div class="stat-text">مكرمة ومتطوعة</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">15</div>
                <div class="stat-text">دورة تدريبية</div>
            </div>
        </div>
    </section>

    <!-- Aspirations Section -->
    <section id="aspirations" class="container">
        <div class="aspirations-section-title">
            <h2>تطلعاتنا المستقبلية</h2>
            <p class="aspirations-desc">نطمح في اللجنة التنسيقية إلى تحقيق قفزات نوعية في العمل التنموي النسائي عبر رؤى عصرية وشراكات مبتكرة.</p>
        </div>
        <div class="aspirations-flex">
            <div class="aspiration-card">
                <div class="asp-icon"><i class="fas fa-network-wired"></i></div>
                <div class="asp-text">إنشاء منظومة متكاملة للتعاون بين الجمعيات والمؤسسات النسائية والمجتمع المحلي لتوحيد الجهود وتجنب التكرار</div>
            </div>
            <div class="aspiration-card">
                <div class="asp-icon"><i class="fas fa-handshake"></i></div>
                <div class="asp-text">تعزيز الشراكات بين مختلف الجهات</div>
            </div>
            <div class="aspiration-card">
                <div class="asp-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="asp-text">تقديم مبادرات مبتكرة تدعم الاكتفاء الذاتي والاندماج المجتمعي</div>
            </div>
            <div class="aspiration-card">
                <div class="asp-icon"><i class="fas fa-file-alt"></i></div>
                <div class="asp-text">إعداد لوائح تنظيمية واضحة لعمل اللجنة ومبادراتها</div>
            </div>
            <div class="aspiration-card">
                <div class="asp-icon"><i class="fas fa-laptop"></i></div>
                <div class="asp-text">استخدام التكنولوجيا في تحسين إدارة الموارد ومتابعة الأداء</div>
            </div>
        </div>
        <style>
        /* Aspirations Section Modern Style */
        #aspirations {
            background: linear-gradient(120deg, #f7fafc 60%, #e6e9f0 100%);
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(44, 62, 80, 0.07);
            margin-bottom: 3rem;
            padding: 3rem 1.5rem 2.5rem;
        }
        .aspirations-section-title {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .aspirations-section-title h2 {
            color: var(--primary);
            font-size: 2.1rem;
            margin-bottom: 0.7rem;
        }
        .aspirations-desc {
            color: #555;
            font-size: 1.1rem;
        }
        .aspirations-flex {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }
        .aspiration-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px 0 rgba(44, 62, 80, 0.08);
            padding: 2rem 1.2rem 1.2rem;
            width: 270px;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .aspiration-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 8px 32px 0 rgba(44, 62, 80, 0.13);
        }
        .asp-icon {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            margin-bottom: 1.2rem;
            box-shadow: 0 2px 8px 0 rgba(44, 62, 80, 0.10);
        }
        .asp-text {
            color: #333;
            font-size: 1.08rem;
            text-align: center;
            line-height: 1.8;
        }
        @media (max-width: 900px) {
            .aspirations-flex {
                gap: 18px;
            }
            .aspiration-card {
                width: 90vw;
                min-width: 220px;
                max-width: 350px;
            }
        }
        @media (max-width: 600px) {
            #aspirations {
                padding: 2rem 0.2rem 1.5rem;
            }
            .aspirations-section-title h2 {
                font-size: 1.3rem;
            }
            .aspiration-card {
                padding: 1.2rem 0.5rem 0.8rem;
                min-height: 140px;
            }
            .asp-icon {
                width: 44px;
                height: 44px;
                font-size: 1.3rem;
            }
        }
        </style>
    </section>

    </section>

    <!-- Footer -->
    <footer class="modern-footer">
        <!-- موجة علوية للفوتر -->
        <div class="footer-wave">
            <svg viewBox="0 0 1440 90" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,60 C360,120 1080,0 1440,60 L1440,90 L0,90 Z" fill="#232946"/></svg>
        </div>
        <div class="footer-main">
            <div class="footer-col about">
                <div class="footer-logo"><img src="media/logopen-03.png" alt="شعار تكامل" class="footer-logo-img"></div>
                <div>
                    <h3>تكامل</h3>
                    <p>المجموعة التنسيقية للكيانات النسائية العاملة في الرياض</p>
                </div>
            </div>
            <div class="footer-col contact">
                <h4><i class="fas fa-map-marker-alt"></i> الرياض، السعودية</h4>
                <p><i class="fas fa-phone"></i> <a href="tel:0560341046">0560341046</a></p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:takamul15@gmail.com">takamul15@gmail.com</a></p>
            </div>
            <div class="footer-col links">
                <h4><i class="fas fa-link"></i> روابط مهمة</h4>
                <ul>
                    <li><a href="https://majlis-ngos.org/" target="_blank">اللجنة التنسيقية للجمعيات النسائية</a></li>
                    <li><a href="https://ccsa.org.sa/" target="_blank">مجلس الجمعيات الأهلية</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-copyright">
            جميع الحقوق محفوظة &copy; 2025 مجموعة تكامل
        </div>
        <style>
        .modern-footer {
            background: linear-gradient(120deg, #232946 80%, #1a1a2e 100%);
            color: #fff;
            position: relative;
            margin-top: 4rem;
            font-family: inherit;
        }
        .footer-wave {
            position: relative;
            top: -1px;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }
        .footer-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 1.2rem;
            flex-wrap: wrap;
        }
        .footer-col {
            flex: 1 1 260px;
            min-width: 220px;
            margin-bottom: 1.5rem;
        }
        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 0.7rem;
        }
        .footer-logo-img {
            height: 70px;
            border-radius: 8px;
            object-fit: contain;
            margin-right: 12px;
            background: transparent; /* do not force white box */
            padding: 0; /* remove extra padding that made a visible square */
            display: block;
        }
        .footer-col.about h3 {
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
            font-family: 'Tajawal', Arial, sans-serif;
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .footer-col.about p {
            color: var(--secondary);
            font-size: 1.05rem;
            margin-bottom: 1.2rem;
            font-weight: bold;
        }
        .footer-col.contact h4, .footer-col.links h4 {
            color: var(--primary);
            font-size: 1.1rem;
            margin-bottom: 1.1rem;
        }
        .footer-col.contact p, .footer-col.links ul {
            font-size: 1rem;
            margin-bottom: 0.7rem;
        }
        .footer-col.contact a, .footer-col.links a {
            color: #eaeaea;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-col.contact a:hover, .footer-col.links a:hover {
            color: var(--secondary);
        }
        .footer-col.links ul {
            list-style: none;
            padding: 0;
        }
        .footer-col.links li {
            margin-bottom: 0.5rem;
        }
        .footer-copyright {
            text-align: center;
            padding: 1.2rem 0 0.5rem;
            color: #bdbdbd;
            font-size: 0.98rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin: 0 2rem;
        }
        @media (max-width: 900px) {
            .footer-main {
                flex-direction: column;
                align-items: center;
                gap: 18px;
            }
            .footer-col {
                min-width: 0;
                width: 100%;
                text-align: center;
            }
            .footer-logo {
                margin: 0 auto 0.7rem;
            }
        }
        @media (max-width: 600px) {
            .footer-main {
                padding: 1.2rem 0.2rem 0.5rem;
            }
            .footer-copyright {
                font-size: 0.85rem;
                margin: 0 0.2rem;
            }
        }
        </style>
    </footer>

    <script>
        // Close announcement function
        function closeAnnouncement() {
            const announcement = document.getElementById('announcement');
            announcement.style.display = 'none';
        }

        // Toggle side menu
        function toggleMenu() {
            const menu = document.getElementById('side-menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        // Simple scroll animation for timeline
        document.addEventListener('DOMContentLoaded', function() {
            const timelineItems = document.querySelectorAll('.timeline-item');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            timelineItems.forEach(item => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(item);
            });

            // ...existing code...
        });
    </script>
</body>
</html>
