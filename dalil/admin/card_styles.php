<?php
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'اختيار ثيم الموقع';
$adminController->requirePermission('can_manage_settings', 'dashboard.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'] ?? null)) {
        if (empty($_SESSION['error_message'])) {
            $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF).");
        }
    } else {
        if (isset($_POST['active_style'])) {
            $style = intval($_POST['active_style']);
            if ($style == 0 || $style == 3) {
                $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('active_card_style', ?) ON DUPLICATE KEY UPDATE setting_value = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$style, $style]);
                $adminController->setSuccessMessage("تم تفعيل الثيم المختار بنجاح! ✅");
            } else {
                $adminController->setErrorMessage("ثيم غير صالح.");
            }
        }
        header('Location: card_styles.php');
        exit;
    }
}

// جلب الثيم النشط الحالي
$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'active_card_style'");
$active_style = $stmt->fetchColumn();
if ($active_style === false) {
    $active_style = '0';
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

    /* =====================================================================
       تنسيقات البطاقة الحالية
       ===================================================================== */
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

    /* =====================================================================
       تنسيقات البطاقة الفاخرة
       ===================================================================== */
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
    .premium-btn:hover {
        background: #c5a880;
        border-color: #c5a880;
        color: #1d0f3a !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(197, 168, 128, 0.3);
    }
</style>

<div class="admin-showcase-container">
    <div class="admin-showcase-intro">
        <h3><i class="fas fa-palette"></i> اختيار ثيم الموقع</h3>
        <p>اختر أحد الثيمات أدناه لتفعيله على كامل الموقع. الثيم يطبق على <strong>جميع عناصر الصفحة</strong>: الهيدر، الهيرو، الفلاتر، البطاقات، الجداول، الخريطة، والفوتر.</p>
    </div>

    <div class="admin-showcase-grid">
        <!-- الثيم الافتراضي (Style 0) -->
        <div class="admin-card-wrapper <?php echo ($active_style == '0') ? 'active-style' : ''; ?>">
            <div class="style-label-area">
                <span class="style-title-text">الثيم الحالي (الافتراضي)</span>
                <span class="style-num-badge">0</span>
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
                    <span class="active-badge"><i class="fas fa-check-circle"></i> مفعل حالياً ✅</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                        <input type="hidden" name="active_style" value="0">
                        <button type="submit" class="activate-btn">تفعيل هذا الثيم</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- الثيم الفاخر (Style 3) -->
        <div class="admin-card-wrapper <?php echo ($active_style == '3') ? 'active-style' : ''; ?>">
            <div class="style-label-area">
                <span class="style-title-text">الثيم الفاخر (Elegant Premium)</span>
                <span class="style-num-badge">3</span>
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
                    <span class="active-badge"><i class="fas fa-check-circle"></i> مفعل حالياً ✅</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                        <input type="hidden" name="active_style" value="3">
                        <button type="submit" class="activate-btn">تفعيل هذا الثيم</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $adminController->renderFooter(); ?>