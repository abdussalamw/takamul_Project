<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أمانة تكامل - التكامل والتميز المؤسسي</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #003d5c;
            --secondary: #c9a961;
            --background: #ffffff;
            --foreground: #1a1a1a;
            --muted: #f5f5f5;
            --border: #e0e0e0;
            --accent: #f0f0f0;
        }

        body {
            font-family: 'Cairo', sans-serif;
            color: var(--foreground);
            background-color: var(--background);
            line-height: 1.6;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        @media (min-width: 768px) {
            .container {
                padding: 0 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .container {
                padding: 0 2rem;
            }
        }

        /* Navigation */
        nav {
            background-color: var(--background);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: none;
            list-style: none;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: flex;
            }
        }

        .nav-links a {
            text-decoration: none;
            color: var(--foreground);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .cta-button {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: box-shadow 0.3s;
        }

        .cta-button:hover {
            box-shadow: 0 8px 16px rgba(0, 61, 92, 0.3);
        }

        /* Hero Section */
        .hero {
            padding: 3rem 0;
            background: linear-gradient(135deg, rgba(0, 61, 92, 0.05), rgba(201, 169, 97, 0.05));
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: center;
        }

        @media (min-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr 1fr;
            }
        }

        .hero-text h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--primary);
            line-height: 1.2;
        }

        @media (min-width: 768px) {
            .hero-text h1 {
                font-size: 3.5rem;
            }
        }

        .hero-text p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            box-shadow: 0 8px 16px rgba(0, 61, 92, 0.3);
        }

        .btn-secondary {
            border: 2px solid var(--primary);
            color: var(--primary);
            background-color: transparent;
        }

        .btn-secondary:hover {
            background-color: rgba(0, 61, 92, 0.05);
        }

        .hero-image {
            display: none;
        }

        @media (min-width: 1024px) {
            .hero-image {
                display: block;
                border-radius: 1rem;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                width: 100%;
                height: auto;
            }
        }

        /* Statistics Section */
        .stats {
            padding: 2rem 0;
            background-color: var(--muted);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s;
        }

        .stat-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-number.secondary {
            color: var(--secondary);
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        /* Integration Fields Section */
        .integration-fields {
            padding: 3rem 0;
            background: linear-gradient(135deg, rgba(0, 61, 92, 0.05), rgba(201, 169, 97, 0.05));
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .section-header p {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Carousel */
        .carousel {
            position: relative;
            background-color: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .carousel-slides {
            position: relative;
            height: 400px;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
            background: linear-gradient(135deg, rgba(0, 61, 92, 0.1), rgba(201, 169, 97, 0.1));
            opacity: 0;
            transition: opacity 0.7s ease-in-out;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .slide h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .slide p {
            font-size: 1rem;
            color: #666;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .carousel-buttons {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 1rem;
            transform: translateY(-50%);
            pointer-events: none;
            z-index: 10;
        }

        .carousel-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(0, 61, 92, 0.2);
            border: none;
            color: var(--primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: all;
            transition: all 0.3s;
            font-size: 1.2rem;
        }

        .carousel-btn:hover {
            background-color: rgba(0, 61, 92, 0.4);
            transform: scale(1.1);
        }

        .carousel-indicators {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            padding: 1.5rem 0;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(0, 61, 92, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .indicator.active {
            width: 32px;
            background-color: var(--primary);
            border-radius: 6px;
        }

        .carousel-counter {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        /* About Section */
        .about {
            padding: 3rem 0;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .about-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .about-card {
            padding: 2rem;
            background-color: white;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            transition: all 0.3s;
        }

        .about-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .about-card-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .about-card h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .about-card p {
            color: #666;
            line-height: 1.8;
        }

        .about-info {
            background: linear-gradient(135deg, rgba(0, 61, 92, 0.1), rgba(201, 169, 97, 0.1));
            border-radius: 0.75rem;
            padding: 2rem;
        }

        .about-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .info-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }

        .info-item-icon {
            color: var(--primary);
            font-size: 1.3rem;
            flex-shrink: 0;
            margin-top: 0.2rem;
        }

        .info-item-content p:first-child {
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 0.25rem;
        }

        .info-item-content p:last-child {
            color: #666;
            font-size: 0.95rem;
        }

        /* Objectives Section */
        .objectives {
            padding: 3rem 0;
            background-color: var(--muted);
        }

        .objectives-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .objective-card {
            padding: 1.5rem;
            background-color: white;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            transition: all 0.3s;
        }

        .objective-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .objective-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .objective-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .objective-card p {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.6;
        }

        /* Achievements Section */
        .achievements {
            padding: 3rem 0;
        }

        .achievements-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .achievements-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .achievement-card {
            padding: 2rem;
            background-color: white;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            transition: all 0.3s;
        }

        .achievement-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .achievement-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .achievement-list {
            list-style: none;
        }

        .achievement-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: flex-start;
        }

        .achievement-item-icon {
            color: var(--secondary);
            font-size: 1.3rem;
            flex-shrink: 0;
            margin-top: 0.2rem;
        }

        .achievement-item-text {
            color: #666;
            font-size: 0.95rem;
        }

        /* Future Aspirations Section */
        .aspirations {
            padding: 3rem 0;
            background: linear-gradient(135deg, var(--primary), rgba(0, 61, 92, 0.8));
            color: white;
        }

        .aspirations .section-header h2,
        .aspirations .section-header p {
            color: white;
        }

        .aspirations .section-header p {
            color: rgba(255, 255, 255, 0.8);
        }

        .aspirations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .aspiration-item {
            padding: 1.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.75rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .aspiration-icon {
            color: var(--secondary);
            font-size: 1.5rem;
            flex-shrink: 0;
            margin-top: 0.2rem;
        }

        .aspiration-text {
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Contact Section */
        .contact {
            padding: 3rem 0;
        }

        .contact-form {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--foreground);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-family: 'Cairo', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 61, 92, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .form-group button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: box-shadow 0.3s;
        }

        .form-group button:hover {
            box-shadow: 0 8px 16px rgba(0, 61, 92, 0.3);
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-item {
            text-align: center;
        }

        .contact-item p:first-child {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .contact-item p:last-child {
            font-weight: 600;
            color: var(--primary);
        }

        /* Footer */
        footer {
            background-color: var(--foreground);
            color: white;
            padding: 2rem 0;
            text-align: center;
        }

        footer p {
            margin-bottom: 0.5rem;
        }

        footer .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        footer a {
            color: var(--secondary);
            text-decoration: none;
            transition: color 0.3s;
        }

        footer a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 2rem;
            }

            .section-header h2 {
                font-size: 1.8rem;
            }

            .slide h3 {
                font-size: 1.5rem;
            }

            .slide p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <div class="nav-container">
                <a href="#" class="logo">
                    <span>ت</span>
                    تكامل
                </a>
                <ul class="nav-links">
                    <li><a href="#home">الرئيسية</a></li>
                    <li><a href="#about">من نحن</a></li>
                    <li><a href="#fields">مجالات التكامل</a></li>
                    <li><a href="#objectives">الأهداف</a></li>
                    <li><a href="#achievements">الإنجازات</a></li>
                    <li><a href="#contact">التواصل</a></li>
                </ul>
                <button class="cta-button">تواصل معنا</button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div style="display: inline-block; padding: 0.5rem 1rem; background-color: rgba(201, 169, 97, 0.2); border-radius: 2rem; margin-bottom: 1rem;">
                        <span style="color: var(--primary); font-weight: 600; font-size: 0.9rem;">مرحباً بك في تكامل</span>
                    </div>
                    <h1>التكامل والتميز المؤسسي</h1>
                    <p>مجموعة تنسيقية تضم 30 جهة تعمل في المجال التنموي النسائي بمدينة الرياض، نسعى للتكامل وتبادل الخبرات من خلال إقامة برامج نوعية مشتركة.</p>
                    <div class="hero-buttons">
                        <a href="#fields" class="btn btn-primary">استكشف مجالات التكامل</a>
                        <a href="#about" class="btn btn-secondary">تعرف علينا أكثر</a>
                    </div>
                </div>
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'%3E%3Crect fill='%23003d5c' width='400' height='400'/%3E%3Cpath fill='%23c9a961' d='M100 100 L150 100 L150 150 L100 150 Z M200 100 L250 100 L250 150 L200 150 Z M150 150 L200 150 L200 200 L150 200 Z M100 200 L150 200 L150 250 L100 250 Z M250 200 L300 200 L300 250 L250 250 Z M200 250 L250 250 L250 300 L200 300 Z'/%3E%3C/svg%3E" alt="Hero Illustration" class="hero-image">
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">30</div>
                    <div class="stat-label">جهة عضو</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number secondary">100+</div>
                    <div class="stat-label">شراكة مبرمة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">600</div>
                    <div class="stat-label">مكرمة ومتطوعة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number secondary">15+</div>
                    <div class="stat-label">دورة تدريبية</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integration Fields Section -->
    <section id="fields" class="integration-fields">
        <div class="container">
            <div class="section-header">
                <h2>مجالات التكامل</h2>
                <p>ستة مجالات أساسية نعمل من خلالها على تحقيق التكامل والتنسيق بين الجهات الأعضاء.</p>
            </div>

            <div class="carousel">
                <div class="carousel-slides">
                    <div class="slide active">
                        <div>
                            <div class="slide-icon">📈</div>
                            <h3>تطوير وتحسين الأداء</h3>
                            <p>المساهمة في تطوير وتحسين أداء الكيانات من خلال تبادل الخبرات والممارسات الفضلى</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div>
                            <div class="slide-icon">🎯</div>
                            <h3>البرامج المشتركة</h3>
                            <p>إقامة برامج مشتركة نوعية تخدم المجتمع وتحقق أهداف التنمية المستدامة</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div>
                            <div class="slide-icon">👥</div>
                            <h3>مشاركة الكوادر البشرية</h3>
                            <p>تبادل الموارد البشرية والكفاءات بين الجهات لتعزيز الكفاءة والفعالية</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div>
                            <div class="slide-icon">🎓</div>
                            <h3>التدريب والتطوير</h3>
                            <p>برامج تدريب مشتركة وتطوير مستمر للقيادات والفريق التنفيذي</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div>
                            <div class="slide-icon">💡</div>
                            <h3>تبادل المعرفة</h3>
                            <p>نقل الخبرات والمعارف بين الجهات لتحسين الممارسات والعمليات</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div>
                            <div class="slide-icon">🤝</div>
                            <h3>الخدمات المساندة</h3>
                            <p>تقديم خدمات تشاركية مثل التصاميم والمقرات والموارد المختلفة</p>
                        </div>
                    </div>
                </div>

                <div class="carousel-buttons">
                    <button class="carousel-btn" id="prevBtn">‹</button>
                    <button class="carousel-btn" id="nextBtn">›</button>
                </div>

                <div class="carousel-indicators">
                    <button class="indicator active" data-slide="0"></button>
                    <button class="indicator" data-slide="1"></button>
                    <button class="indicator" data-slide="2"></button>
                    <button class="indicator" data-slide="3"></button>
                    <button class="indicator" data-slide="4"></button>
                    <button class="indicator" data-slide="5"></button>
                </div>

                <div class="carousel-counter">
                    <span id="slideCounter">1</span> / 6
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <h2>من نحن</h2>
                <p>مجموعة تنسيقية احترافية تعمل على تحقيق التكامل والتميز المؤسسي.</p>
            </div>

            <div class="about-grid">
                <div class="about-card">
                    <div class="about-card-icon">🎯</div>
                    <h3>الرؤية</h3>
                    <p>نسعى للتكامل وتبادل الخبرات، من خلال إقامة برامج نوعية مشتركة، وتأهيل وتدريب مشترك وتطوير لبيئة العمل لتحقيق التميز المؤسسي.</p>
                </div>

                <div class="about-card">
                    <div class="about-card-icon">❤️</div>
                    <h3>الرسالة</h3>
                    <p>تعزيز العمل التنسيقي بين الجهات النسائية التنموية في الرياض لتحقيق التكامل وتبادل الخبرات والموارد لخدمة المجتمع.</p>
                </div>
            </div>

            <div class="about-info">
                <h3>معلومات عن المجموعة</h3>
                <div class="info-item">
                    <div class="info-item-icon">✓</div>
                    <div class="info-item-content">
                        <p>تاريخ النشأة</p>
                        <p>10/03/1442هـ الموافق: 27/10/2020م</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-icon">✓</div>
                    <div class="info-item-content">
                        <p>عدد الجهات الأعضاء</p>
                        <p>30 جهة تنموية نسائية</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-icon">✓</div>
                    <div class="info-item-content">
                        <p>الموقع</p>
                        <p>مدينة الرياض، المملكة العربية السعودية</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-icon">✓</div>
                    <div class="info-item-content">
                        <p>النموذج</p>
                        <p>نموذج احترافي في العمل التنسيقي المشترك</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Objectives Section -->
    <section id="objectives" class="objectives">
        <div class="container">
            <div class="section-header">
                <h2>أهدافنا</h2>
                <p>مجموعة من الأهداف الاستراتيجية التي نسعى لتحقيقها.</p>
            </div>

            <div class="objectives-grid">
                <div class="objective-card">
                    <div class="objective-icon">👥</div>
                    <h3>التكامل والتنسيق</h3>
                    <p>تحقيق التكامل والتنسيق والشراكات وتبادل الخبرات بين الكيانات</p>
                </div>
                <div class="objective-card">
                    <div class="objective-icon">🎯</div>
                    <h3>البرامج المشتركة</h3>
                    <p>تقديم برامج مشتركة للتطوير والتدريب والتنمية</p>
                </div>
                <div class="objective-card">
                    <div class="objective-icon">❤️</div>
                    <h3>بناء الثقة</h3>
                    <p>تعزيز أواصر الترابط وبناء الثقة بين الكيانات</p>
                </div>
                <div class="objective-card">
                    <div class="objective-icon">🏆</div>
                    <h3>البرامج النوعية</h3>
                    <p>إقامة برامج نوعية مشتركة ذات تأثير مجتمعي</p>
                </div>
                <div class="objective-card">
                    <div class="objective-icon">🤝</div>
                    <h3>التشارك في الخدمات</h3>
                    <p>التشارك في الخدمات والموارد والمقرات</p>
                </div>
                <div class="objective-card">
                    <div class="objective-icon">📈</div>
                    <h3>التطوير المستمر</h3>
                    <p>تطوير العمليات والممارسات بشكل مستمر</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements Section -->
    <section id="achievements" class="achievements">
        <div class="container">
            <div class="section-header">
                <h2>إنجازاتنا</h2>
                <p>إنجازات ملموسة حققتها المجموعة في مختلف المجالات.</p>
            </div>

            <div class="achievements-grid">
                <div class="achievement-card">
                    <h3>الاجتماعات واللقاءات</h3>
                    <ul class="achievement-list">
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">عقد 17 اجتماع لأعضاء المجموعة</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">إقامة 5 لقاءات اجتماعية للمجموعة</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">عقد لقاءين مع المؤسسات المانحة</div>
                        </li>
                    </ul>
                </div>

                <div class="achievement-card">
                    <h3>التدريب والتطوير</h3>
                    <ul class="achievement-list">
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">تقديم دورات تدريبية متخصصة</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">تدريب 8 مدراء تنفيذيين</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">سبعة دورات تدريبية مشتركة</div>
                        </li>
                    </ul>
                </div>

                <div class="achievement-card">
                    <h3>الشراكات والتبادل</h3>
                    <ul class="achievement-list">
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">عقد أكثر من 100 شراكة</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">زيارات بينية بين الجهات</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">زيارات لجهات مشابهة في مكة وجدة</div>
                        </li>
                    </ul>
                </div>

                <div class="achievement-card">
                    <h3>الخدمات والدعم</h3>
                    <ul class="achievement-list">
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">تقديم خدمات متعددة للجهات</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">إصدار دليل البرامج الصيفية</div>
                        </li>
                        <li class="achievement-item">
                            <div class="achievement-item-icon">✓</div>
                            <div class="achievement-item-text">تكريم 600 مديرة ومتطوعة</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Future Aspirations Section -->
    <section class="aspirations">
        <div class="container">
            <div class="section-header">
                <h2>تطلعاتنا المستقبلية</h2>
                <p>نطمح إلى تحقيق قفزات نوعية في العمل التنموي النسائي.</p>
            </div>

            <div class="aspirations-grid">
                <div class="aspiration-item">
                    <div class="aspiration-icon">✓</div>
                    <div class="aspiration-text">إنشاء منظومة متكاملة للتعاون بين الجمعيات والمؤسسات النسائية</div>
                </div>
                <div class="aspiration-item">
                    <div class="aspiration-icon">✓</div>
                    <div class="aspiration-text">تعزيز الشراكات بين مختلف الجهات والمؤسسات</div>
                </div>
                <div class="aspiration-item">
                    <div class="aspiration-icon">✓</div>
                    <div class="aspiration-text">تقديم مبادرات مبتكرة تدعم الاكتفاء الذاتي</div>
                </div>
                <div class="aspiration-item">
                    <div class="aspiration-icon">✓</div>
                    <div class="aspiration-text">إعداد لوائح تنظيمية واضحة للعمل</div>
                </div>
                <div class="aspiration-item">
                    <div class="aspiration-icon">✓</div>
                    <div class="aspiration-text">استخدام التكنولوجيا في تحسين إدارة الموارد</div>
                </div>
                <div class="aspiration-item">
                    <div class="aspiration-icon">✓</div>
                    <div class="aspiration-text">تطوير بيئة عمل مشجعة للابتكار والإبداع</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>تواصل معنا</h2>
                <p>نحن هنا للاستماع إليك والإجابة على أسئلتك.</p>
            </div>

            <form class="contact-form" onsubmit="handleSubmit(event)">
                <div class="form-group">
                    <label for="name">الاسم</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="subject">الموضوع</label>
                    <input type="text" id="subject" name="subject" required>
                </div>

                <div class="form-group">
                    <label for="message">الرسالة</label>
                    <textarea id="message" name="message" required></textarea>
                </div>

                <div class="form-group">
                    <button type="submit">إرسال الرسالة</button>
                </div>
            </form>

            <div class="contact-info">
                <div class="contact-item">
                    <p>البريد الإلكتروني</p>
                    <p><a href="mailto:takamul15@gmail.com" style="color: var(--primary); text-decoration: none;">takamul15@gmail.com</a></p>
                </div>
                <div class="contact-item">
                    <p>الهاتف</p>
                    <p><a href="tel:0560341046" style="color: var(--primary); text-decoration: none;">0560341046</a></p>
                </div>
                <div class="contact-item">
                    <p>الموقع</p>
                    <p>الرياض، السعودية</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-links">
                <a href="#">اللجنة التنسيقية للجمعيات النسائية</a>
                <a href="#">مجلس الجمعيات الأهلية</a>
            </div>
            <p>جميع الحقوق محفوظة © 2025 مجموعة تكامل</p>
            <p>المجموعة التنسيقية للكيانات النسائية العاملة في الرياض</p>
        </div>
    </footer>

    <script>
        // Carousel functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;
        const slideCounter = document.getElementById('slideCounter');

        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));

            slides[n].classList.add('active');
            indicators[n].classList.add('active');
            slideCounter.textContent = n + 1;
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(currentSlide);
        }

        // Event listeners
        document.getElementById('nextBtn').addEventListener('click', nextSlide);
        document.getElementById('prevBtn').addEventListener('click', prevSlide);

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Auto-play carousel
        setInterval(nextSlide, 6000);

        // Form submission
        function handleSubmit(event) {
            event.preventDefault();
            alert('شكراً لتواصلك معنا! سنرد عليك قريباً.');
            event.target.reset();
        }

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
