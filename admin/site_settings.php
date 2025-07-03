<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title_text = 'إعدادات الموقع العامة';
$error = null;
$success = null;

// Security check: must be logged in and have permission to manage settings
if (empty($_SESSION['admin_id']) || empty($_SESSION['permissions']['can_manage_settings'])) {
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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

/**
 * Handles file uploads for settings.
 * @param PDO $pdo The database connection object.
 * @param string $file_input_name The name of the file input in the form (e.g., 'site_logo').
 * @param string $setting_key The key to store the file path in the database (e.g., 'logo_path').
 * @param array $allowed_ext Array of allowed file extensions.
 * @return string|null An error message if something went wrong, or null on success.
 */
function handle_file_upload($pdo, $file_input_name, $setting_key, $allowed_ext) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/settings/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp_path = $_FILES[$file_input_name]['tmp_name'];
        $file_name = basename($_FILES[$file_input_name]['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            return "نوع الملف غير مسموح به لـ " . $file_input_name;
        }

        // Fetch old file path to delete it later
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$setting_key]);
        $old_file_path = $stmt->fetchColumn();

        // Create a unique name and move the file
        $new_file_name = $setting_key . '_' . time() . '.' . $file_ext;
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $db_path = 'uploads/settings/' . $new_file_name;
            update_setting($pdo, $setting_key, $db_path);

            // Delete the old file if it exists
            if ($old_file_path && file_exists('../' . $old_file_path)) {
                unlink('../' . $old_file_path);
            }
        } else {
            return "حدث خطأ أثناء رفع الملف.";
        }
    }
    return null; // No error
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب (CSRF).";
    } else {
        try {
            // Process text fields
            $text_settings = ['guide_name', 'guide_subtitle', 'whatsapp_channel_url', 'telegram_channel_url', 'contact_number', 'contact_email'];
            foreach ($text_settings as $key) {
                if (isset($_POST[$key])) {
                    update_setting($pdo, $key, trim($_POST[$key]));
                }
            }

            // Process checkboxes (enabled/disabled status)
            $checkbox_settings = [
                'whatsapp_channel_header_enabled', 'whatsapp_channel_footer_enabled',
                'telegram_channel_header_enabled', 'telegram_channel_footer_enabled',
                'guide_pdf_header_enabled', 'guide_pdf_footer_enabled'
            ];
            foreach ($checkbox_settings as $key) {
                $value = isset($_POST[$key]) ? '1' : '0';
                update_setting($pdo, $key, $value);
            }

            // Process file uploads
            $logo_error = handle_file_upload($pdo, 'site_logo', 'logo_path', ['png', 'jpg', 'jpeg', 'svg']);
            $pdf_error = handle_file_upload($pdo, 'guide_pdf', 'guide_pdf_path', ['pdf']);

            if ($logo_error) $error = $logo_error;
            if ($pdf_error) $error = (isset($error) ? $error . '<br>' : '') . $pdf_error;

            if (!$error) {
                $success = "تم حفظ الإعدادات بنجاح! ✅";
            }

        } catch (PDOException $e) {
            $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    }
    // Regenerate token after submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];
}

// Fetch all settings to display in the form
$settings = get_all_settings($pdo);

?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title_text); ?> ⚙️</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #8a2be2; --secondary: #ff6b6b; --accent: #4ecdc4; --light: #f8f9fa; --dark: #212529; --success: #28a745; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%); color: var(--dark); }
        header { background: linear-gradient(120deg, var(--primary), #5c1d9c); color: white; padding: 0.5rem 0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 1000; width: 100%; }
        .header-container { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .logo { display: flex; align-items: center; gap: 15px; }
        .logo-image { width: 60px; height: 60px; object-fit: contain; }
        .logo-text { font-size: 1.5rem; font-weight: 800; }
        .page-title-header { display: flex; align-items: center; font-size: 1.1rem; font-weight: 700; }
        .page-title-header i { margin-left: 10px; color: var(--accent); font-size: 1.2rem; }
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav a { color: white; text-decoration: none; font-weight: 500; padding: 10px 20px; border-radius: 30px; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        nav a:hover { background: rgba(255, 255, 255, 0.15); }
        .settings-section { max-width: 900px; width: 100%; margin: 40px auto; padding: 20px; }
        .settings-card { background: white; border-radius: 20px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); padding: 2.5rem; }
        .settings-card h2 { color: var(--primary); font-size: 1.8rem; margin-bottom: 1.5rem; position: relative; padding-bottom: 10px; text-align: right; }
        .settings-card h2::after { content: ''; position: absolute; bottom: 0; right: 0; width: 60px; height: 3px; background: var(--secondary); border-radius: 2px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
        .settings-form { display: grid; grid-template-columns: 1fr; gap: 2rem; }
        .form-section { border: 1px solid #e9ecef; border-radius: 15px; padding: 1.5rem; }
        .form-section h3 { font-size: 1.2rem; color: var(--dark); border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group:last-child { margin-bottom: 0; }
        .form-group label { display: block; font-size: 1rem; color: var(--dark); margin-bottom: 8px; font-weight: 500; }
        .form-group input[type="text"], .form-group input[type="file"] { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 0.95rem; outline: none; transition: all 0.3s ease; font-family: 'Tajawal', sans-serif; }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 8px rgba(138, 43, 226, 0.2); }
        .form-group .note { font-size: 0.85rem; color: #6c757d; margin-top: 5px; }
        .current-preview { margin-top: 10px; }
        .current-preview img { max-width: 100px; border-radius: 8px; border: 2px solid #eee; }
        .current-preview a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .switch-container { display: flex; align-items: center; gap: 10px; }
        .switch { position: relative; display: inline-block; width: 50px; height: 28px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 28px; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--primary); }
        input:checked + .slider:before { transform: translateX(22px); }
        .form-actions { text-align: center; margin-top: 2rem; }
        .save-btn { background: var(--primary); color: white; border: none; padding: 14px 30px; border-radius: 10px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .save-btn:hover { background: #7a1fc2; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        .back-btn { display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }

        /* New styles for activation grid */
        .activation-grid { margin-top: 1.5rem; border: 1px solid #e9ecef; border-radius: 10px; overflow: hidden; }
        .activation-grid-header, .activation-grid-row { display: grid; grid-template-columns: 2fr 1fr 1fr; align-items: center; text-align: center; }
        .activation-grid-header { background-color: #f8f9fa; font-weight: 600; padding: 10px; }
        .activation-grid-row { padding: 10px; border-top: 1px solid #e9ecef; }
        .activation-grid-row:first-of-type { border-top: none; }
        .activation-grid-row label:first-child { text-align: right; font-weight: 500; }
        .activation-grid .switch { margin: 0 auto; }

    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="../<?php echo htmlspecialchars($settings['logo_path'] ?? 'assets/img/default-logo.png'); ?>" alt="شعار" class="logo-image">
                <div class="logo-text"><?php echo htmlspecialchars($settings['guide_name'] ?? 'إعدادات الموقع'); ?></div>
            </div>
            <div class="page-title-header">
                <i class="fas fa-cogs"></i>
                <span><?php echo htmlspecialchars($page_title_text); ?></span>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="settings-section">
        <div class="settings-card">
            <h2><i class="fas fa-tools"></i> <?php echo htmlspecialchars($page_title_text); ?></h2>

            <?php if ($success): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="settings-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

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
                        <p class="note">اتركه فارغًا للإبقاء على الشعار الحالي. أفضل أبعاد 1:1 (مربع).</p>
                        <?php if (!empty($settings['logo_path']) && file_exists('../' . $settings['logo_path'])): ?>
                            <div class="current-preview">
                                <p>الشعار الحالي:</p>
                                <img src="../<?php echo htmlspecialchars($settings['logo_path']); ?>?t=<?php echo time(); ?>" alt="الشعار الحالي">
                            </div>
                        <?php endif; ?>
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
                    <button type="submit" class="save-btn"><i class="fas fa-save"></i> حفظ الإعدادات</button>
                </div>
            </form>
            <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-right"></i> العودة إلى لوحة التحكم</a>
        </div>
    </section>

    <script>
        // Hide success/error messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messageBox = document.querySelector('.message');
            if (messageBox) {
                setTimeout(() => {
                    messageBox.style.transition = 'opacity 0.5s ease';
                    messageBox.style.opacity = '0';
                    setTimeout(() => messageBox.style.display = 'none', 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>