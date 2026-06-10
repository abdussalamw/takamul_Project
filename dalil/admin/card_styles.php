<?php
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'تصميم بطاقات البرامج';
$adminController->requirePermission('can_manage_settings', 'dashboard.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF).");
    } else {
        if (isset($_POST['active_style'])) {
            $style = intval($_POST['active_style']);
            if ($style >= 0 && $style <= 3) {
                // تحديث الخيار في قاعدة البيانات
                $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('active_card_style', ?) ON DUPLICATE KEY UPDATE setting_value = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$style, $style]);
                $adminController->setSuccessMessage("تم تفعيل نمط البطاقة المختار بنجاح! ✅");
            } else {
                $adminController->setErrorMessage("نمط غير صالح.");
            }
        }
        header('Location: card_styles.php');
        exit;
    }
}

// جلب النمط النشط الحالي
$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'active_card_style'");
$active_style = $stmt->fetchColumn();
if ($active_style === false) {
    $active_style = '0'; // الافتراضي
}

// محاولة جلب برنامج حقيقي من قاعدة البيانات لعرضه في المعاينة
try {
    $stmt = $pdo->prepare("SELECT programs.*, COALESCE(organizers.name, programs.organizer) as organizer FROM programs LEFT JOIN organizers ON programs.organizer_id = organizers.id WHERE programs.status = 'published' ORDER BY programs.id ASC LIMIT 1");
    $stmt->execute();
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $program = false;
}

if (!$program) {
    $program = [
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
        'price_notes' => 'خصم للمجموعات الشخص 450'
    ];
}

$price_val = floatval($program['price']);
$program['price_clean'] = ($price_val == intval($price_val)) ? intval($price_val) : $price_val;

$is_free = (isset($program['is_free']) && $program['is_free'] == 1) || ($program['price'] == '0' || in_array(strtolower(trim($program['price'])), ['مجاناً', 'مجاني'], true));
$price_text = $is_free ? 'مجاني' : $program['price_clean'] . ' ريال';

$description = htmlspecialchars($program['description']);
$words = explode(' ', $description);
$short_desc = implode(' ', array_slice($words, 0, 30));

$adminController->renderHeader($page_title);
$adminController->renderMessages();
?>

<!-- تنسيقات معاينة الأنماط والبطاقات معزولة لصفحة الإدارة -->
<style>
    .admin-showcase-container {
        direction: rtl;
    }

    .admin-showcase-intro {
        background: #fff;
        border-radius: 12px;
        padding: 20px 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        border-right: 5px solid var(--primary-color, #8a2be2);
    }

    .admin-showcase-intro h3 {
        color: #333;
        margin-bottom: 8px;
        font-size: 1.3rem;
    }

    .admin-showcase-intro p {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .admin-showcase-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 35px;
        margin-bottom: 40px;
    }

    @media (max-width: 992px) {
        .admin-showcase-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
    }

    .admin-card-wrapper {
        background: #fdfdfd;
        border: 1px solid #e2e8f0;
        padding: 24px;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.01);
        transition: all 0.3s;
    }

    .admin-card-wrapper.active-style {
        border-color: #28a745;
        background: #fbfdfb;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.05);
    }

    .style-label-area {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #edf2f7;
        padding-bottom: 12px;
    }

    .style-title-text {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
    }

    .style-num-badge {
        background: #edf2f7;
        color: #4a5568;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .admin-card-wrapper.active-style .style-num-badge {
        background: #e6f4ea;
        color: #28a745;
    }

    .activation-area {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        border-top: 1px solid #edf2f7;
        padding-top: 15px;
        margin-top: auto;
    }

    .activate-btn {
        background: var(--primary-color, #8a2be2);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 700;
        font-family: 'Tajawal', sans-serif;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(138, 43, 226, 0.15);
    }

    .activate-btn:hover {
        background: #761ec7;
        transform: translateY(-1px);
    }

    .active-badge {
        background: #28a745;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.15);
    }

    /* ==========================================================================
       تنسيقات البطاقة الحالية (النموذج 0) لمحاكاة العرض في لوحة التحكم
       ========================================================================== */
    .program-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid #eee;
        display: flex;
        flex-direction: column;
        height: 100%;
        text-align: right;
    }
    
    .card-header {
        padding: 20px 20px 15px;
        background: linear-gradient(120deg, #4ecdc4, #3daaa4);
        color: white;
    }
    
    .program-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .organization {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        opacity: 0.95;
    }
    
    .card-body {
        padding: 20px;
        flex: 1;
    }
    
    .program-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 15px;
    }
    
    .detail-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    
    .detail-icon {
        color: #8a2be2;
        font-size: 1rem;
        margin-top: 3px;
    }
    
    .detail-text {
        font-size: 0.85rem;
        color: #444;
    }
    
    .program-description {
        margin: 15px 0 0;
        font-size: 0.85rem;
        line-height: 1.6;
        color: #666;
    }
    
    .card-footer {
        padding: 0 20px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .program-fee {
        background: #f0f7ff;
        color: #8a2be2;
        padding: 6px 15px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.9rem;
    }
    
    .free-badge {
        background: #28a745;
        color: white;
    }
    
    .register-btn {
        background: #8a2be2;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 30px;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .program-fee-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    .program-fee-notes {
        font-size: 0.7rem;
        color: #888;
        font-weight: 500;
        max-width: 150px;
    }

    /* ==========================================================================
       تنسيقات البطاقة الزجاجية (النموذج 1)
       ========================================================================== */
    .card-glass {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(138, 43, 226, 0.15);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
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

    .card-glass-header {
        margin-bottom: 15px;
    }

    .card-glass-organizer {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: #6a1b9a;
        background: rgba(138, 43, 226, 0.06);
        padding: 4px 10px;
        border-radius: 50px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .card-glass-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1a1d24;
        line-height: 1.4;
    }

    .card-glass-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-glass-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 15px;
    }

    .glass-detail-item {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f7fafc;
        padding: 6px 10px;
        border-radius: 10px;
        border: 1px solid #edf2f7;
    }

    .glass-detail-item i {
        color: #8a2be2;
        font-size: 0.9rem;
    }

    .glass-detail-text {
        font-size: 0.8rem;
        color: #4a5568;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
    }

    .card-glass-description {
        font-size: 0.85rem;
        color: #4a5568;
        line-height: 1.6;
        margin-bottom: 15px;
        flex: 1;
    }

    .card-glass-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        border-top: 1px solid #edf2f7;
        padding-top: 15px;
    }

    .glass-price-box {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .glass-price-label {
        font-size: 0.7rem;
        color: #718096;
        margin-bottom: 2px;
    }

    .glass-price-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: #8a2be2;
    }

    .glass-price-value.free {
        color: #28a745;
    }

    .glass-price-notes {
        font-size: 0.7rem;
        color: #718096;
        margin-top: 2px;
        font-weight: 500;
        max-width: 140px;
    }

    .glass-btn {
        background: linear-gradient(135deg, #8a2be2, #6a1b9a);
        color: white !important;
        padding: 8px 18px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.85rem;
        border: none;
    }

    /* ==========================================================================
       تنسيقات البطاقة النيوبوتاليزم (النموذج 2)
       ========================================================================== */
    .card-brutal {
        background: #ffffff;
        border: 3px solid #000000;
        border-radius: 0px;
        padding: 24px;
        box-shadow: 6px 6px 0px #000000;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        text-align: right;
    }

    .card-brutal-header {
        border-bottom: 3px solid #000000;
        padding-bottom: 12px;
        margin-bottom: 15px;
    }

    .card-brutal-organizer {
        font-size: 0.8rem;
        font-weight: 800;
        color: #000000;
        background: #f1c40f;
        border: 2px solid #000000;
        padding: 3px 10px;
        display: inline-block;
        margin-bottom: 8px;
        box-shadow: 2px 2px 0px #000000;
    }

    .card-brutal-title {
        font-size: 1.25rem;
        font-weight: 900;
        color: #000000;
        line-height: 1.3;
    }

    .card-brutal-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-brutal-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 15px;
    }

    .brutal-detail-item {
        display: flex;
        align-items: center;
        gap: 6px;
        border: 2px solid #000000;
        padding: 5px 8px;
        background: #eef2f7;
        font-weight: 700;
    }

    .brutal-detail-item i {
        color: #000000;
        font-size: 0.85rem;
    }

    .brutal-detail-text {
        font-size: 0.75rem;
        color: #000000;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card-brutal-description {
        font-size: 0.85rem;
        color: #2d3748;
        line-height: 1.5;
        margin-bottom: 15px;
        font-weight: 600;
        flex: 1;
    }

    .card-brutal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 3px solid #000000;
        padding-top: 15px;
        margin-top: auto;
    }

    .brutal-price-box {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .brutal-price-label {
        font-size: 0.7rem;
        font-weight: 800;
        color: #718096;
    }

    .brutal-price-value {
        font-size: 1.25rem;
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
        max-width: 140px;
    }

    .brutal-btn {
        background: #ff6b6b;
        color: #000000 !important;
        padding: 8px 18px;
        border: 2px solid #000000;
        font-weight: 900;
        text-decoration: none;
        font-size: 0.85rem;
        box-shadow: 2px 2px 0px #000000;
    }

    /* ==========================================================================
       تنسيقات البطاقة الفاخرة (النموذج 3)
       ========================================================================== */
    .card-premium {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.01);
        border: 1px solid rgba(29, 15, 58, 0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
        text-align: right;
    }

    .card-premium-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .card-premium-organizer {
        font-size: 0.75rem;
        font-weight: 700;
        color: #c5a880;
        text-transform: uppercase;
    }

    .card-premium-badge {
        background: #faf6f0;
        border: 1px solid #ebdcb9;
        color: #b08d48;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .card-premium-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1d0f3a;
        line-height: 1.4;
        margin-bottom: 12px;
    }

    .card-premium-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-premium-description {
        font-size: 0.85rem;
        color: #5d5a68;
        line-height: 1.6;
        margin-bottom: 15px;
        flex: 1;
    }

    .card-premium-details {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        row-gap: 10px;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e2e0e6;
    }

    .premium-detail-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.8rem;
        color: #6c6978;
        padding: 0 8px;
        border-right: 1px solid #e2e0e6;
    }

    .premium-detail-item:first-child {
        border-right: none;
        padding-right: 0;
    }

    .premium-detail-item i {
        color: #c5a880;
        font-size: 0.85rem;
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
        font-size: 0.7rem;
        color: #8f8c9c;
        margin-bottom: 2px;
    }

    .premium-price-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1d0f3a;
    }

    .premium-price-value.free {
        color: #27ae60;
    }

    .premium-price-notes {
        font-size: 0.7rem;
        color: #8f8c9c;
        margin-top: 2px;
        font-style: italic;
        max-width: 150px;
    }

    .premium-btn {
        background: #1d0f3a;
        color: #ffffff !important;
        border: 1px solid #1d0f3a;
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        text-align: center;
    }
</style>

<div class="admin-showcase-container">
    <div class="admin-showcase-intro">
        <h3><i class="fas fa-palette"></i> تفعيل وتخصيص بطاقات البرامج للموقع</h3>
        <p>اختر أحد الأنماط الجمالية المصممة أدناه لتفعيلها على بطاقات البرامج الصيفية في الواجهة الرئيسية للموقع. تفعيل أي نمط سيقوم بتطبيقه تلقائياً على كل من: <strong>شكل بطاقات البرامج، نمط الجداول، وبطاقة معلومات الخريطة التفاعلية</strong> لتوفير طابع متجانس وفريد للموقع.</p>
    </div>

    <!-- شبكة استعراض البطاقات مع زر التفعيل للإدارة -->
    <div class="admin-showcase-grid">

        <!-- النموذج 0: التصميم الحالي للموقع -->
        <div class="admin-card-wrapper <?php echo ($active_style == '0') ? 'active-style' : ''; ?>">
            <div class="style-label-area">
                <span class="style-title-text">التصميم الحالي للموقع</span>
                <span class="style-num-badge">النموذج 0</span>
            </div>
            
            <div class="program-card">
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
                            <div class="detail-text">الانتهاء: <?php echo htmlspecialchars($program['end_date'] ?? $program['start_date']); ?></div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user-friends detail-icon"></i>
                            <div class="detail-text"><?php echo htmlspecialchars($program['age_group']); ?></div>
                        </div>
                    </div>
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
                    <a href="#" class="register-btn">سجل الآن</a>
                </div>
            </div>

            <div class="activation-area">
                <?php if ($active_style == '0'): ?>
                    <span class="active-badge"><i class="fas fa-check-circle"></i> نشط حالياً ✅</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                        <input type="hidden" name="active_style" value="0">
                        <button type="submit" class="activate-btn">تفعيل هذا التصميم</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>


        <!-- النموذج 1: التصميم الزجاجي العصري (Modern Glassmorphism) -->
        <div class="admin-card-wrapper <?php echo ($active_style == '1') ? 'active-style' : ''; ?>">
            <div class="style-label-area">
                <span class="style-title-text">التصميم الزجاجي العصري (Glassmorphism)</span>
                <span class="style-num-badge">النموذج 1</span>
            </div>

            <div class="card-glass">
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
                            <span class="glass-detail-text" title="الانتهاء: <?php echo htmlspecialchars($program['end_date'] ?? $program['start_date']); ?>">الانتهاء: <?php echo htmlspecialchars($program['end_date'] ?? $program['start_date']); ?></span>
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
                            <span class="glass-price-value <?php echo $is_free ? 'free' : ''; ?>"><?php echo $price_text; ?></span>
                            <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                <span class="glass-price-notes"><?php echo htmlspecialchars($program['price_notes']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="#" class="glass-btn">سجل الآن <i class="fas fa-arrow-left" style="margin-right: 5px; font-size: 0.8rem;"></i></a>
                    </div>
                </div>
            </div>

            <div class="activation-area">
                <?php if ($active_style == '1'): ?>
                    <span class="active-badge"><i class="fas fa-check-circle"></i> نشط حالياً ✅</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                        <input type="hidden" name="active_style" value="1">
                        <button type="submit" class="activate-btn">تفعيل هذا التصميم</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>


        <!-- النموذج 2: التصميم التفاعلي الجريء (Bold & Playful Neo-brutalism) -->
        <div class="admin-card-wrapper <?php echo ($active_style == '2') ? 'active-style' : ''; ?>">
            <div class="style-label-area">
                <span class="style-title-text">التصميم التفاعلي الجريء (Neo-brutalism)</span>
                <span class="style-num-badge">النموذج 2</span>
            </div>

            <div class="card-brutal">
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
                            <span class="brutal-detail-text">الانتهاء: <?php echo htmlspecialchars($program['end_date'] ?? $program['start_date']); ?></span>
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
                            <span class="brutal-price-value <?php echo $is_free ? 'free' : ''; ?>"><?php echo $price_text; ?></span>
                            <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                <span class="brutal-price-notes"><?php echo htmlspecialchars($program['price_notes']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="#" class="brutal-btn">سجل الآن</a>
                    </div>
                </div>
            </div>

            <div class="activation-area">
                <?php if ($active_style == '2'): ?>
                    <span class="active-badge"><i class="fas fa-check-circle"></i> نشط حالياً ✅</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                        <input type="hidden" name="active_style" value="2">
                        <button type="submit" class="activate-btn">تفعيل هذا التصميم</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>


        <!-- النموذج 3: التصميم الأنيق الفاخر (Elegant Premium Theme) -->
        <div class="admin-card-wrapper <?php echo ($active_style == '3') ? 'active-style' : ''; ?>">
            <div class="style-label-area">
                <span class="style-title-text">التصميم الأنيق الفاخر (Elegant Premium)</span>
                <span class="style-num-badge">النموذج 3</span>
            </div>

            <div class="card-premium">
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
                            <span>الانتهاء: <?php echo htmlspecialchars($program['end_date'] ?? $program['start_date']); ?></span>
                        </div>
                        <div class="premium-detail-item">
                            <i class="fas fa-user-friends"></i>
                            <span><?php echo htmlspecialchars($program['age_group']); ?></span>
                        </div>
                    </div>
                    
                    <div class="card-premium-footer">
                        <div class="premium-price-container">
                            <span class="premium-price-label">الاستثمار</span>
                            <span class="premium-price-value <?php echo $is_free ? 'free' : ''; ?>"><?php echo $price_text; ?></span>
                            <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                <span class="premium-price-notes"><?php echo htmlspecialchars($program['price_notes']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="#" class="premium-btn">سجل الآن</a>
                    </div>
                </div>
            </div>

            <div class="activation-area">
                <?php if ($active_style == '3'): ?>
                    <span class="active-badge"><i class="fas fa-check-circle"></i> نشط حالياً ✅</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                        <input type="hidden" name="active_style" value="3">
                        <button type="submit" class="activate-btn">تفعيل هذا التصميم</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
$adminController->renderFooter();
?>
