<?php
require 'includes/db_connect.php';

// محاولة جلب برنامج حقيقي من قاعدة البيانات لعرضه في المعاينة (نفضل المعرف 1 أو أول برنامج منشور)
try {
    $stmt = $pdo->prepare("SELECT programs.*, COALESCE(organizers.name, programs.organizer) as organizer FROM programs LEFT JOIN organizers ON programs.organizer_id = organizers.id WHERE programs.status = 'published' ORDER BY programs.id ASC LIMIT 1");
    $stmt->execute();
    $program_db = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $program_db = false;
}

// بيانات بديلة (Fallback) في حال كانت قاعدة البيانات فارغة أو حدث خطأ
if (!$program_db) {
    $program_db = [
        'id' => 1,
        'title' => 'فُلك فَلك',
        'organizer' => 'مؤسسة عبير المسك',
        'description' => 'فُلك فَلك رحلة تبدأ من البحر 🌊 وتنتهي بالنجوم 🪐 💫 برنامج قيمي تربوي ترفيهي حضوري للأطفال وللفتيات',
        'program_type' => 'برنامج(قيمي - تربوي)',
        'Direction' => 'شرق الرياض',
        'location' => 'حي الجزيرة',
        'venue_name' => 'دار مسك',
        'attendance_type' => 'حضوري',
        'start_date' => '2026/07/05',
        'end_date' => '2026/07/22',
        'duration' => 'ثلاثة اسابيع',
        'is_free' => 0,
        'age_group' => 'متوسط, ثانوي',
        'price' => '480.00',
        'price_notes' => 'خصم للمجموعات الشخص 450',
        'registration_link' => 'https://salla.sa/Folk-falak'
    ];
}

// إعداد بيانات البرنامج النشط والبرنامج المنتهي للعرض والمقارنة
$active_program = $program_db;
$active_price = floatval($active_program['price']);
$active_program['price_clean'] = ($active_price == intval($active_price)) ? intval($active_price) : $active_price;

$ended_program = $program_db;
$ended_program['title'] = $program_db['title'] . ' (منتهي)';
$ended_program['start_date'] = '2026/04/01';
$ended_program['end_date'] = '2026/04/20'; // تاريخ في الماضي لمحاكاة الانتهاء
$ended_price = floatval($ended_program['price']);
$ended_program['price_clean'] = ($ended_price == intval($ended_price)) ? intval($ended_price) : $ended_price;

$preview_modes = [
    [
        'id_suffix' => 'active',
        'section_title' => 'معاينة البرامج النشطة (التسجيل مفتوح حالياً)',
        'is_ended' => false,
        'program' => $active_program
    ],
    [
        'id_suffix' => 'ended',
        'section_title' => 'معاينة البرامج المنتهية (تتحول تلقائياً للرمادي بعد انتهاء التاريخ)',
        'is_ended' => true,
        'program' => $ended_program
    ]
];

include 'includes/header.php';
?>

<!-- تنسيقات صفحة العرض المخصصة والمعزولة تماماً لمنع التداخل مع بقية الصفحات -->
<style>
    .showcase-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        direction: rtl;
    }

    .showcase-intro {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 45px;
        box-shadow: 0 10px 30px rgba(138, 43, 226, 0.05);
        border-right: 6px solid var(--primary);
        position: relative;
        overflow: hidden;
    }

    .showcase-intro::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(138, 43, 226, 0.05) 0%, transparent 70%);
        border-radius: 50%;
    }

    .showcase-intro h2 {
        color: var(--primary);
        margin-bottom: 12px;
        font-size: 2rem;
        font-weight: 800;
    }

    .showcase-intro p {
        color: #555;
        font-size: 1.1rem;
        line-height: 1.8;
    }

    .showcase-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
        background: #fff;
        padding: 20px 25px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        flex-wrap: wrap;
        gap: 15px;
    }

    .controls-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .controls-title i {
        color: var(--primary);
    }

    .bg-switcher {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .bg-btn {
        border: 2px solid #e2e8f0;
        background: #ffffff;
        padding: 8px 18px;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4a5568;
    }

    .bg-btn:hover {
        background: #f7fafc;
        border-color: #cbd5e0;
    }

    .bg-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 10px rgba(138, 43, 226, 0.25);
    }

    /* عناوين الفئات */
    .section-preview-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #2d3748;
        margin: 50px 0 25px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s, border-color 0.3s;
    }

    .section-preview-title::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 24px;
        background: var(--primary);
        border-radius: 3px;
    }

    /* شبكة العرض والتحكم */
    .showcase-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 40px;
        padding: 30px;
        border-radius: 24px;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        background: transparent;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .showcase-grid {
            grid-template-columns: 1fr;
            gap: 35px;
            padding: 15px;
        }
    }

    .showcase-card-wrapper {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .showcase-label {
        font-size: 1.15rem;
        font-weight: 800;
        color: #2d3748;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 5px;
        transition: color 0.3s;
    }

    .showcase-label-title {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .showcase-label-title i {
        color: var(--primary);
    }

    .showcase-label-badge {
        background: #efe9ff;
        color: var(--primary);
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
        transition: all 0.3s;
    }

    /* وضعيات الخلفيات لشبكة المعاينة */
    .bg-default {
        background: transparent;
    }

    .bg-white {
        background: #ffffff !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.03) inset, 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }

    .bg-dark {
        background: #11141a !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.4) inset;
        border: 1px solid #1a1e26;
    }

    .bg-gradient-color {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2) inset;
    }

    /* تعديل نصوص الشارات والبطاقات عند تفعيل الخلفيات الداكنة */
    .dark-preview-labels .showcase-label {
        color: #ffffff;
    }
    .dark-preview-labels .showcase-label-badge {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
    }
    .dark-preview-labels .showcase-label-title i {
        color: #c5a880;
    }
    .dark-preview-labels .section-preview-title {
        color: #ffffff;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
    .dark-preview-labels .section-preview-title::before {
        background: #c5a880;
    }

    /* تنسيقات إضافية للأسعار والملاحظات على النموذج الحالي (0) */
    .program-fee-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    .program-fee-notes {
        font-size: 0.75rem;
        color: #777;
        font-weight: 500;
        max-width: 180px;
    }
    .dark-preview .program-fee-notes {
        color: #cbd5e0;
    }

    /* ==========================================================================
       نموذج 3: التصميم الأنيق الفاخر (Elegant Premium Theme)
       ========================================================================== */
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

    /* تعديلات الفاخر الأنيق في الوضع الداكن */
    .dark-preview .card-premium {
        background: #16141c;
        border-color: rgba(255, 255, 255, 0.05);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }
    .dark-preview .card-premium-title {
        color: #ffffff;
    }
    .dark-preview .card-premium-description {
        color: #a09eab;
    }
    .dark-preview .premium-detail-item {
        color: #a09eab;
        border-color: rgba(255, 255, 255, 0.1);
    }
    .dark-preview .premium-detail-item i {
        color: #c5a880;
    }
    .dark-preview .card-premium-badge {
        background: rgba(197, 168, 128, 0.1);
        border-color: rgba(197, 168, 128, 0.25);
        color: #c5a880;
    }
    .dark-preview .premium-price-value {
        color: #ffffff;
    }
    .dark-preview .premium-price-notes {
        color: #a09eab;
    }
    .dark-preview .premium-btn {
        background: #c5a880;
        border-color: #c5a880;
        color: #1d0f3a !important;
    }
    .dark-preview .premium-btn:hover {
        background: #ffffff;
        border-color: #ffffff;
        color: #1d0f3a !important;
    }

    /* ==========================================================================
       تنسيقات البرامج المنتهية الصلاحية (Grayscale & Ended Overlay)
       ========================================================================== */
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

    /* شارة الانتهاء لبطاقة النيوبوتاليزم */
    .card-brutal.ended .ended-badge {
        border: 2px solid #000;
        background: #e53e3e;
        color: #fff;
        border-radius: 0;
        box-shadow: 2px 2px 0px #000;
        top: 10px;
        left: 10px;
    }

    /* شارة الانتهاء للبطاقة الفاخرة */
    .card-premium.ended .ended-badge {
        background: rgba(229, 62, 62, 0.1);
        border: 1px solid rgba(229, 62, 62, 0.3);
        color: #e53e3e;
        border-radius: 4px;
        box-shadow: none;
    }
</style>

<div class="showcase-container">
    <!-- مقدمة الصفحة التوضيحية -->
    <div class="showcase-intro">
        <h2>معرض مقارنة بطاقات البرامج الصيفية (محدثة)</h2>
        <p>بناءً على طلبك ومراجعة البيانات، قمنا بإجراء التحسينات التالية على نماذج البطاقات:</p>
        <ul style="margin-right: 20px; margin-top: 10px; line-height: 1.8; color: #555;">
            <li><strong>تاريخ الانتهاء:</strong> تم إدراج تاريخ بدء وانتهاء البرنامج بوضوح في جميع النماذج.</li>
            <li><strong>إزالة الهللات:</strong> تم تنظيف وعرض السعر بدون أصفار كسرية (مثال: 480 بدلاً من 480.00).</li>
            <li><strong>ملاحظات السعر:</strong> إظهار الملاحظات (مثل خصومات المجموعات) بخط صغير وأنيق مباشرة تحت السعر.</li>
            <li><strong>البرنامج المنتهي (الرمادي):</strong> إذا انتهت فترة البرنامج (تاريخ اليوم تجاوز تاريخ الانتهاء)، يتم تطبيق مرشح رمادي (Grayscale) لتعطيل البطاقة وإظهار شارة "انتهى التسجيل" حمراء مميزة.</li>
        </ul>
    </div>

    <!-- لوحة التحكم بالخلفية الفورية -->
    <div class="showcase-controls">
        <div class="controls-title">
            <i class="fas fa-magic"></i>
            <span>تغيير خلفية المعاينة لرؤية تأثير الشفافية والبروز للرمادي والنشط:</span>
        </div>
        <div class="bg-switcher">
            <button class="bg-btn active" data-bg="bg-default">
                <i class="fas fa-image"></i> الافتراضية
            </button>
            <button class="bg-btn" data-bg="bg-white">
                <i class="fas fa-square" style="color: #fff; border: 1px solid #ccc; border-radius: 2px;"></i> بيضاء نقية
            </button>
            <button class="bg-btn" data-bg="bg-dark">
                <i class="fas fa-square" style="color: #111; border-radius: 2px;"></i> داكنة فاخرة
            </button>
            <button class="bg-btn" data-bg="bg-gradient-color">
                <i class="fas fa-palette"></i> تدرج لوني حيوي
            </button>
        </div>
    </div>

    <?php foreach ($preview_modes as $mode): 
        $program = $mode['program'];
        $is_ended = $mode['is_ended'];
        $is_free = (isset($program['is_free']) && $program['is_free'] == 1) || ($program['price'] == '0' || in_array(strtolower(trim($program['price'])), ['مجاناً', 'مجاني'], true));
        $price_text = $is_free ? 'مجاني' : $program['price_clean'] . ' ريال';
    ?>
        <!-- عنوان القسم -->
        <h3 class="section-preview-title"><?php echo $mode['section_title']; ?></h3>

        <!-- شبكة المقارنة الرئيسية للقسم -->
        <div class="showcase-grid bg-default">

            <!-- النموذج 0: التصميم الحالي للموقع بعد التعديلات -->
            <div class="showcase-card-wrapper">
                <div class="showcase-label">
                    <div class="showcase-label-title">
                        <i class="fas fa-history"></i>
                        <span>التصميم الحالي للموقع</span>
                    </div>
                    <span class="showcase-label-badge">النموذج 0</span>
                </div>
                
                <div class="program-card <?php echo $is_ended ? 'ended' : ''; ?>">
                    <?php if ($is_ended): ?>
                        <div class="ended-badge"><i class="fas fa-lock"></i> انتهى التسجيل</div>
                    <?php endif; ?>
                    <div class="card-header">
                        <h3 class="program-title"><?php echo htmlspecialchars($program['title']); ?></h3>
                        <div class="organization">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($program['organizer']); ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="program-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['location']); ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['duration']); ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar detail-icon"></i>
                                <div class="detail-text">البدء: <?php echo htmlspecialchars($program['start_date']); ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar-check detail-icon"></i>
                                <div class="detail-text">الانتهاء: <?php echo htmlspecialchars($program['end_date']); ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-user-friends detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['age_group']); ?></div>
                            </div>
                        </div>
                        <?php 
                            $description = htmlspecialchars($program['description']);
                            $words = explode(' ', $description);
                            $short_desc = implode(' ', array_slice($words, 0, 30));
                        ?>
                        <p class="program-description">
                            <span><?php echo $short_desc; ?>...</span>
                        </p>
                    </div>
                    <div class="card-footer">
                        <div class="program-fee-wrapper">
                            <div class="program-fee <?php echo $is_free ? 'free-badge' : ''; ?>">
                                <?php echo $price_text; ?>
                            </div>
                            <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                <span class="program-fee-notes"><?php echo htmlspecialchars($program['price_notes']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="#" class="register-btn"><?php echo $is_ended ? 'مغلق' : 'سجل الآن'; ?></a>
                    </div>
                </div>
            </div>


            <!-- النموذج 1: التصميم الزجاجي العصري (Modern Glassmorphism) -->
            <div class="showcase-card-wrapper">
                <div class="showcase-label">
                    <div class="showcase-label-title">
                        <i class="fas fa-glass-martini-alt"></i>
                        <span>التصميم الزجاجي العصري (Glassmorphism)</span>
                    </div>
                    <span class="showcase-label-badge">النموذج 1</span>
                </div>

                <div class="card-glass <?php echo $is_ended ? 'ended' : ''; ?>">
                    <?php if ($is_ended): ?>
                        <div class="ended-badge"><i class="fas fa-lock"></i> انتهى التسجيل</div>
                    <?php endif; ?>
                    <div class="card-glass-header">
                        <div class="card-glass-organizer">
                            <i class="fas fa-building"></i>
                            <span><?php echo htmlspecialchars($program['organizer']); ?></span>
                        </div>
                        <h3 class="card-glass-title"><?php echo htmlspecialchars($program['title']); ?></h3>
                    </div>
                    
                    <div class="card-glass-body">
                        <div class="card-glass-details">
                            <div class="glass-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="glass-detail-text" title="<?php echo htmlspecialchars($program['location']); ?>"><?php echo htmlspecialchars($program['location']); ?></span>
                            </div>
                            <div class="glass-detail-item">
                                <i class="fas fa-clock"></i>
                                <span class="glass-detail-text" title="<?php echo htmlspecialchars($program['duration']); ?>"><?php echo htmlspecialchars($program['duration']); ?></span>
                            </div>
                            <div class="glass-detail-item">
                                <i class="fas fa-calendar"></i>
                                <span class="glass-detail-text" title="البدء: <?php echo htmlspecialchars($program['start_date']); ?>">البدء: <?php echo htmlspecialchars($program['start_date']); ?></span>
                            </div>
                            <div class="glass-detail-item">
                                <i class="fas fa-calendar-check"></i>
                                <span class="glass-detail-text" title="الانتهاء: <?php echo htmlspecialchars($program['end_date']); ?>">الانتهاء: <?php echo htmlspecialchars($program['end_date']); ?></span>
                            </div>
                            <div class="glass-detail-item" style="grid-column: span 2;">
                                <i class="fas fa-user-friends"></i>
                                <span class="glass-detail-text" title="<?php echo htmlspecialchars($program['age_group']); ?>"><?php echo htmlspecialchars($program['age_group']); ?></span>
                            </div>
                        </div>
                        <p class="card-glass-description">
                            <?php echo htmlspecialchars($program['description']); ?>
                        </p>
                        <div class="card-glass-footer">
                            <div class="glass-price-box">
                                <span class="glass-price-label">الرسوم</span>
                                <?php
                                    $price_val = $is_free ? 'مجاني' : $program['price_clean'] . ' ريال';
                                ?>
                                <span class="glass-price-value <?php echo $is_free ? 'free' : ''; ?>"><?php echo $price_val; ?></span>
                                <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                    <span class="glass-price-notes"><?php echo htmlspecialchars($program['price_notes']); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="#" class="glass-btn"><?php echo $is_ended ? 'مغلق' : 'سجل الآن'; ?> <i class="fas fa-arrow-left" style="margin-right: 5px; font-size: 0.8rem;"></i></a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- النموذج 2: التصميم التفاعلي الجريء (Bold & Playful Neo-brutalism) -->
            <div class="showcase-card-wrapper">
                <div class="showcase-label">
                    <div class="showcase-label-title">
                        <i class="fas fa-bolt"></i>
                        <span>التصميم التفاعلي الجريء (Neo-brutalism)</span>
                    </div>
                    <span class="showcase-label-badge">النموذج 2</span>
                </div>

                <div class="card-brutal <?php echo $is_ended ? 'ended' : ''; ?>">
                    <?php if ($is_ended): ?>
                        <div class="ended-badge"><i class="fas fa-lock"></i> انتهى التسجيل</div>
                    <?php endif; ?>
                    <div class="card-brutal-header">
                        <span class="card-brutal-organizer">
                            <i class="fas fa-building" style="margin-left: 3px;"></i>
                            <?php echo htmlspecialchars($program['organizer']); ?>
                        </span>
                        <h3 class="card-brutal-title"><?php echo htmlspecialchars($program['title']); ?></h3>
                    </div>
                    
                    <div class="card-brutal-body">
                        <div class="card-brutal-details">
                            <div class="brutal-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="brutal-detail-text"><?php echo htmlspecialchars($program['location']); ?></span>
                            </div>
                            <div class="brutal-detail-item">
                                <i class="fas fa-clock"></i>
                                <span class="brutal-detail-text"><?php echo htmlspecialchars($program['duration']); ?></span>
                            </div>
                            <div class="brutal-detail-item">
                                <i class="fas fa-calendar"></i>
                                <span class="brutal-detail-text">البدء: <?php echo htmlspecialchars($program['start_date']); ?></span>
                            </div>
                            <div class="brutal-detail-item">
                                <i class="fas fa-calendar-check"></i>
                                <span class="brutal-detail-text">الانتهاء: <?php echo htmlspecialchars($program['end_date']); ?></span>
                            </div>
                            <div class="brutal-detail-item" style="grid-column: span 2;">
                                <i class="fas fa-user-friends"></i>
                                <span class="brutal-detail-text"><?php echo htmlspecialchars($program['age_group']); ?></span>
                            </div>
                        </div>
                        <p class="card-brutal-description">
                            <?php echo htmlspecialchars($program['description']); ?>
                        </p>
                        <div class="card-brutal-footer">
                            <div class="brutal-price-box">
                                <span class="brutal-price-label">الرسوم</span>
                                <?php
                                    $price_brutal = $is_free ? 'مجاني' : $program['price_clean'] . ' ريال';
                                ?>
                                <span class="brutal-price-value <?php echo $is_free ? 'free' : ''; ?>"><?php echo $price_brutal; ?></span>
                                <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                    <span class="brutal-price-notes"><?php echo htmlspecialchars($program['price_notes']); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="#" class="brutal-btn"><?php echo $is_ended ? 'مغلق' : 'سجل الآن'; ?></a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- النموذج 3: التصميم الأنيق الفاخر (Elegant Premium Theme) -->
            <div class="showcase-card-wrapper">
                <div class="showcase-label">
                    <div class="showcase-label-title">
                        <i class="fas fa-crown"></i>
                        <span>التصميم الأنيق الفاخر (Elegant Premium)</span>
                    </div>
                    <span class="showcase-label-badge">النموذج 3</span>
                </div>

                <div class="card-premium <?php echo $is_ended ? 'ended' : ''; ?>">
                    <?php if ($is_ended): ?>
                        <div class="ended-badge"><i class="fas fa-lock"></i> انتهى التسجيل</div>
                    <?php endif; ?>
                    <div class="card-premium-top">
                        <span class="card-premium-organizer"><?php echo htmlspecialchars($program['organizer']); ?></span>
                        <span class="card-premium-badge"><?php echo htmlspecialchars($program['attendance_type'] ?? 'حضوري'); ?></span>
                    </div>
                    
                    <h3 class="card-premium-title"><?php echo htmlspecialchars($program['title']); ?></h3>
                    
                    <div class="card-premium-body">
                        <p class="card-premium-description">
                            <?php echo htmlspecialchars($program['description']); ?>
                        </p>
                        
                        <div class="card-premium-details">
                            <div class="premium-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($program['location']); ?></span>
                            </div>
                            <div class="premium-detail-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo htmlspecialchars($program['duration']); ?></span>
                            </div>
                            <div class="premium-detail-item">
                                <i class="fas fa-calendar"></i>
                                <span>البدء: <?php echo htmlspecialchars($program['start_date']); ?></span>
                            </div>
                            <div class="premium-detail-item">
                                <i class="fas fa-calendar-check"></i>
                                <span>الانتهاء: <?php echo htmlspecialchars($program['end_date']); ?></span>
                            </div>
                            <div class="premium-detail-item">
                                <i class="fas fa-user-friends"></i>
                                <span><?php echo htmlspecialchars($program['age_group']); ?></span>
                            </div>
                        </div>
                        
                        <div class="card-premium-footer">
                            <div class="premium-price-container">
                                <span class="premium-price-label">الاستثمار</span>
                                <?php
                                    $price_premium = $is_free ? 'مجاني' : $program['price_clean'] . ' ريال';
                                ?>
                                <span class="premium-price-value <?php echo $is_free ? 'free' : ''; ?>"><?php echo $price_premium; ?></span>
                                <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                    <span class="premium-price-notes"><?php echo htmlspecialchars($program['price_notes']); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="#" class="premium-btn"><?php echo $is_ended ? 'مغلق' : 'سجل الآن'; ?></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('.bg-btn').click(function() {
            // إزالة الكلاس النشط من جميع الأزرار وإضافته للزر المحدد
            $('.bg-btn').removeClass('active');
            $(this).addClass('active');
            
            // تحديد فئة الخلفية المستهدفة
            var bgClass = $(this).data('bg');
            
            // تهيئة شبكة العرض
            $('.showcase-grid').removeClass('bg-default bg-white bg-dark bg-gradient-color');
            $('.showcase-grid').addClass(bgClass);
            
            // التحكم في تفعيل الوضع الداكن بناءً على الخلفية المختارة لتغيير ألوان النصوص التوضيحية
            if (bgClass === 'bg-dark' || bgClass === 'bg-gradient-color') {
                $('.showcase-container').addClass('dark-preview-labels');
                $('.showcase-grid').addClass('dark-preview');
            } else {
                $('.showcase-container').removeClass('dark-preview-labels');
                $('.showcase-grid').removeClass('dark-preview');
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
