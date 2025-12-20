<?php
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'إعدادات الموقع العامة';
$adminController->requirePermission('can_manage_settings', 'dashboard.php');

/**
 * Fetches all settings from the database into an associative array.
 * @param PDO $pdo The database connection object.
 * @return array An array of settings.
 */
function get_all_settings($pdo) {
    $settings = [];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        // In a real app, you'd log this error.
    }
    return $settings;
}

/**
 * Inserts or updates a setting in the database.
 * @param PDO $pdo The database connection object.
 * @param string $key The setting key.
 * @param string $value The setting value.
 */
function update_setting($pdo, $key, $value) {
    $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$key, $value, $value]);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF).");
    } else {
        try {
            $current_settings = get_all_settings($pdo);
            $error_messages = [];

            // Refactored File Upload Handling
            $file_uploads = [
                'site_logo' => ['db_key' => 'logo_path', 'allowed' => ['png', 'jpg', 'jpeg', 'svg'], 'label' => 'الشعار'],
                'guide_pdf' => ['db_key' => 'guide_pdf_path', 'allowed' => ['pdf'], 'label' => 'ملف الدليل']
            ];

            foreach ($file_uploads as $input_name => $details) {
                if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                    $upload_result = $adminController->handleFileUpload($_FILES[$input_name], '../uploads/settings/', $details['allowed']);
                    if ($upload_result['success']) {
                        $new_path = 'uploads/settings/' . $upload_result['filename'];
                        update_setting($pdo, $details['db_key'], $new_path);
                        if (!empty($current_settings[$details['db_key']]) && file_exists('../' . $current_settings[$details['db_key']])) {
                            unlink('../' . $current_settings[$details['db_key']]);
                        }
                    } else {
                        $error_messages[] = "خطأ في رفع " . $details['label'] . ": " . $upload_result['message'];
                    }
                }
            }

            $text_settings = ['guide_name', 'guide_subtitle', 'whatsapp_channel_url', 'telegram_channel_url', 'contact_number', 'contact_email'];
            foreach ($text_settings as $key) {
                if (isset($_POST[$key])) {
                    update_setting($pdo, $key, trim($_POST[$key]));
                }
            }

            $checkbox_settings = [
                'whatsapp_channel_header_enabled', 'whatsapp_channel_footer_enabled',
                'telegram_channel_header_enabled', 'telegram_channel_footer_enabled',
                'guide_pdf_header_enabled', 'guide_pdf_footer_enabled'
            ];
            foreach ($checkbox_settings as $key) {
                $value = isset($_POST[$key]) ? '1' : '0';
                update_setting($pdo, $key, $value);
            }

            // Set messages based on the outcome
            if (!empty($error_messages)) {
                $adminController->setErrorMessage(implode('<br>', $error_messages));
            } else {
                $adminController->setSuccessMessage("تم قبول التعديلات بنجاح! ✅");
            }
            
            // Redirect to the same page to show messages and prevent resubmission
            header('Location: site_settings.php');
            exit;

        } catch (PDOException $e) {
            $adminController->setErrorMessage("خطأ في قاعدة البيانات: " . $e->getMessage());
            // Redirect even on DB error to show the message
            header('Location: site_settings.php');
            exit;
        }
    }
}

// Fetch all settings to display in the form
$settings = get_all_settings($pdo);

$adminController->renderHeader($page_title);
$adminController->renderMessages();
?>

<section class="settings-section">
    <div class="settings-card">
        <h2><i class="fas fa-tools"></i> <?php echo htmlspecialchars($page_title); ?></h2>

        <form method="POST" class="settings-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">

            <!-- General Settings -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> الإعدادات العامة</h3>
                <div class="form-group">
                    <label for="guide_name">اسم الدليل (السطر الأول الكبير)</label>
                    <input type="text" id="guide_name" name="guide_name" value="<?php echo htmlspecialchars($settings['guide_name'] ?? ''); ?>" placeholder="مثال: دليل البرامج الصيفية">
                </div>
                <div class="form-group">
                    <label for="guide_subtitle">العنوان الفرعي (السطر الثاني الصغير)</label>
                    <input type="text" id="guide_subtitle" name="guide_subtitle" value="<?php echo htmlspecialchars($settings['guide_subtitle'] ?? ''); ?>" placeholder="مثال: للفتيات في مدينة الرياض 1447هـ">
                </div>
                <div class="form-group">
                    <label for="site_logo">شعار الموقع</label>
                    <input type="file" id="site_logo" name="site_logo" accept=".png, .jpg, .jpeg, .svg">
                    <p class="note">الشعار الحالي يظهر في الشريط الجانبي. ارفع ملفًا جديدًا لتغييره. أفضل أبعاد 1:1 (مربع).</p>
                </div>
                 <div class="form-group">
                    <label for="contact_number">رقم التواصل (للتذييل)</label>
                    <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($settings['contact_number'] ?? ''); ?>" placeholder="مثال: 05xxxxxxx">
                </div>
                <div class="form-group">
                    <label for="contact_email">البريد الإلكتروني (للتذييل)</label>
                    <input type="text" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" placeholder="مثال: info@example.com">
                </div>
            </div>

            <!-- Social Channels -->
            <div class="form-section">
                <h3><i class="fas fa-share-alt"></i> قنوات التواصل</h3>
                <div class="form-group">
                    <label for="whatsapp_channel_url">رابط قناة الواتساب</label>
                    <input type="text" id="whatsapp_channel_url" name="whatsapp_channel_url" value="<?php echo htmlspecialchars($settings['whatsapp_channel_url'] ?? ''); ?>" placeholder="https://whatsapp.com/channel/...">
                </div>
                <div class="form-group">
                    <label for="telegram_channel_url">رابط قناة التليجرام</label>
                    <input type="text" id="telegram_channel_url" name="telegram_channel_url" value="<?php echo htmlspecialchars($settings['telegram_channel_url'] ?? ''); ?>" placeholder="https://t.me/...">
                </div>
            </div>

            <!-- Guide PDF -->
            <div class="form-section">
                <h3><i class="fas fa-file-pdf"></i> ملف الدليل (PDF)</h3>
                <div class="form-group">
                    <label for="guide_pdf">ملف الدليل الحالي</label>
                    <input type="file" id="guide_pdf" name="guide_pdf" accept=".pdf">
                    <p class="note">اتركه فارغًا للإبقاء على الملف الحالي.</p>
                    <?php if (!empty($settings['guide_pdf_path']) && file_exists('../' . $settings['guide_pdf_path'])): ?>
                        <div class="current-preview">
                            <p>الملف الحالي: <a href="../<?php echo htmlspecialchars($settings['guide_pdf_path']); ?>" target="_blank">عرض الملف <i class="fas fa-external-link-alt"></i></a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activation Toggles -->
            <div class="form-section">
                <h3><i class="fas fa-toggle-on"></i> تفعيل ظهور الروابط</h3>
                <div class="activation-grid">
                    <div class="activation-grid-header">
                        <div>العنصر</div>
                        <div>الهيدر</div>
                        <div>الفوتر</div>
                    </div>
                    <div class="activation-grid-row">
                        <label>قناة الواتساب</label>
                        <label class="switch"><input type="checkbox" name="whatsapp_channel_header_enabled" value="1" <?php echo !empty($settings['whatsapp_channel_header_enabled']) ? 'checked' : ''; ?>><span class="slider"></span></label>
                        <label class="switch"><input type="checkbox" name="whatsapp_channel_footer_enabled" value="1" <?php echo !empty($settings['whatsapp_channel_footer_enabled']) ? 'checked' : ''; ?>><span class="slider"></span></label>
                    </div>
                    <div class="activation-grid-row">
                        <label>قناة التليجرام</label>
                        <label class="switch"><input type="checkbox" name="telegram_channel_header_enabled" value="1" <?php echo !empty($settings['telegram_channel_header_enabled']) ? 'checked' : ''; ?>><span class="slider"></span></label>
                        <label class="switch"><input type="checkbox" name="telegram_channel_footer_enabled" value="1" <?php echo !empty($settings['telegram_channel_footer_enabled']) ? 'checked' : ''; ?>><span class="slider"></span></label>
                    </div>
                    <div class="activation-grid-row">
                        <label>تحميل الدليل (PDF)</label>
                        <label class="switch"><input type="checkbox" name="guide_pdf_header_enabled" value="1" <?php echo !empty($settings['guide_pdf_header_enabled']) ? 'checked' : ''; ?>><span class="slider"></span></label>
                        <label class="switch"><input type="checkbox" name="guide_pdf_footer_enabled" value="1" <?php echo !empty($settings['guide_pdf_footer_enabled']) ? 'checked' : ''; ?>><span class="slider"></span></label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="save-btn"><i class="fas fa-check-circle"></i> قبول التعديلات</button>
            </div>
        </form>
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-right"></i> العودة إلى لوحة التحكم</a>
    </div>
</section>

<?php
$adminController->renderFooter();
?>
