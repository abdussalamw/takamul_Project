<?php
/**
 * submit_program.php - Public Program Submission Page
 * 
 * Users can submit new programs for review by admins.
 * Uses the unified program form template.
 */

include 'includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title_text = 'طلب إضافة برنامج للدليل';
$error = null;
$success = null;

// Start session for CSRF token
session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى. 🚫";
    } else {
        $errors = [];
        $ad_link_path = null;

        // --- 1. Handle File Upload ---
        if (isset($_FILES['ad_link']) && $_FILES['ad_link']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_tmp_path = $_FILES['ad_link']['tmp_name'];
            $file_name = basename($_FILES['ad_link']['name']);
            $file_size = $_FILES['ad_link']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
            $max_file_size = 100 * 1024 * 1024; // 100 MB

            // MIME validation
            $mime_map = [
                'jpg'  => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png'  => ['image/png'],
                'pdf'  => ['application/pdf'],
            ];

            $mime_verified = true;
            if (function_exists('finfo_open')) {
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false) {
                    $mime_type = finfo_file($finfo, $file_tmp_path);
                    finfo_close($finfo);
                    $expected_mimes = isset($mime_map[$file_ext]) ? $mime_map[$file_ext] : [];
                    if (!empty($expected_mimes) && !in_array($mime_type, $expected_mimes)) {
                        $mime_verified = false;
                    }
                }
            }

            if (!in_array($file_ext, $allowed_ext)) {
                $errors[] = "نوع الملف غير مسموح به. (المسموح: jpg, png, pdf) 🚫";
            } elseif (!$mime_verified) {
                $errors[] = "محتوى الملف غير صالح أو لا يتطابق مع امتداده. 🚫";
            } elseif ($file_size > $max_file_size) {
                $errors[] = "حجم الملف كبير جداً. الحد الأقصى هو 100 ميجابايت. 🚫";
            } else {
                $new_file_name = uniqid('ad_', true) . '.' . $file_ext;
                $dest_path = $upload_dir . $new_file_name;
                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    $ad_link_path = 'uploads/' . $new_file_name;
                } else {
                    $errors[] = "حدث خطأ أثناء نقل الملف المرفوع. 🚫";
                }
            }
        } elseif (!isset($_FILES['ad_link']) || $_FILES['ad_link']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = "حقل 'التصميم الإعلاني' مطلوب.";
        }

        // --- 2. Validate Data ---
        $required_fields = [
            'organizer_id'      => 'الجهة المنظمة',
            'title'             => 'عنوان البرنامج',
            'description'       => 'وصف البرنامج',
            'Direction'         => 'المنطقة/الاتجاه',
            'venue_name'        => 'مقر البرنامج',
            'start_date'        => 'تاريخ البدء',
            'end_date'          => 'تاريخ الانتهاء',
            'duration'          => 'المدة',
            'age_group'         => 'الفئة العمرية',
            'is_free'           => 'حالة الرسوم',
            'registration_link' => 'رابط التسجيل',
            'google_map'        => 'رابط خرائط جوجل',
        ];

        foreach ($required_fields as $field => $translation) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $errors[] = "حقل '{$translation}' مطلوب.";
            }
        }

        $organizer_id = $_POST['organizer_id'] ?? '';
        if (!empty($organizer_id)) {
            if (empty(trim($_POST['organizer_name'] ?? ''))) {
                $errors[] = "حقل 'اسم الجهة' مطلوب.";
            }
            if (empty(trim($_POST['entry_officer_name'] ?? ''))) {
                $errors[] = "حقل 'اسم مسؤولة التواصل' مطلوب.";
            }
            if (empty(trim($_POST['entry_officer_phone'] ?? ''))) {
                $errors[] = "حقل 'رقم جوال مسؤول التواصل' مطلوب.";
            }
        }

        if (isset($_POST['start_date']) && !empty($_POST['start_date']) && !preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $_POST['start_date'])) {
            $errors[] = "تاريخ البدء غير صالح (يجب أن يكون DD/MM/YYYY) 🚫";
        }

        if (empty($errors)) {
            // Phone number formatting
            $raw_phone = trim($_POST['entry_officer_phone'] ?? '');
            $clean_phone = preg_replace('/\D/', '', $raw_phone);
            if (preg_match('/^05\d{8}$/', $clean_phone)) {
                $clean_phone = '966' . substr($clean_phone, 1);
            } elseif (preg_match('/^5\d{8}$/', $clean_phone)) {
                $clean_phone = '966' . $clean_phone;
            } elseif (!empty($clean_phone)) {
                if (preg_match('/^9665\d{8}$/', $clean_phone)) {
                    // Valid
                } else {
                    $errors[] = "رقم الجوال غير صحيح، يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.";
                }
            } else {
                $errors[] = "رقم الجوال مطلوب.";
            }
        }

        if (empty($errors)) {
            // Age group processing
            $age_groups = $_POST['age_group'] ?? [];
            if (!is_array($age_groups)) {
                $age_groups = (array) $age_groups;
            }
            if (($key = array_search('أخرى', $age_groups)) !== false) {
                unset($age_groups[$key]);
            }
            $final_age_group = implode(', ', $age_groups);
            $age_group_other = trim($_POST['age_group_other'] ?? '');
            if (!empty($age_group_other)) {
                $final_age_group = !empty($final_age_group) ? $final_age_group . ', ' . $age_group_other : $age_group_other;
            }
            $_POST['age_group'] = $final_age_group;
            
            // Extract coordinates from Google Map URL
            $latitude = null;
            $longitude = null;
            if (!empty($_POST['google_map'])) {
                $coords = get_coords_from_google_maps($_POST['google_map']);
                if ($coords) {
                    $latitude = $coords['lat'];
                    $longitude = $coords['lng'];
                }
            }

            try {
                $pdo->beginTransaction();

                if ($organizer_id === 'new') {
                    $stmtOrg = $pdo->prepare("INSERT INTO organizers (name, department, entry_officer_name, entry_officer_phone) VALUES (?, ?, ?, ?)");
                    $stmtOrg->execute([
                        trim($_POST['organizer_name']),
                        trim($_POST['organizer_department'] ?? ''),
                        trim($_POST['entry_officer_name'] ?? ''),
                        $clean_phone
                    ]);
                    $resolved_organizer_id = $pdo->lastInsertId();
                } else {
                    $resolved_organizer_id = (int)$organizer_id;
                    $stmtOrg = $pdo->prepare("UPDATE organizers SET name = ?, department = ?, entry_officer_name = ?, entry_officer_phone = ? WHERE id = ?");
                    $stmtOrg->execute([
                        trim($_POST['organizer_name']),
                        trim($_POST['organizer_department'] ?? ''),
                        trim($_POST['entry_officer_name'] ?? ''),
                        $clean_phone,
                        $resolved_organizer_id
                    ]);
                }

                $_POST['organizer_id'] = $resolved_organizer_id;
                $_POST['organizer'] = trim($_POST['organizer_name']);

                $db_columns = [];
                $placeholders = [];
                $params = [];

                $stmt = $pdo->query("DESCRIBE programs");
                $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($table_columns_info as $column_info) {
                    $column_name = $column_info['Field'];
                    if (in_array($column_name, ['id', 'status', 'latitude', 'longitude'])) continue;

                    if ($column_name === 'ad_link') {
                        if ($ad_link_path) {
                            $db_columns[] = "`$column_name`";
                            $placeholders[] = '?';
                            $params[] = $ad_link_path;
                        }
                    } elseif (isset($_POST[$column_name])) {
                        $db_columns[] = "`$column_name`";
                        $placeholders[] = '?';
                        $value = trim($_POST[$column_name]);
                        $params[] = (empty($value) && $column_info['Null'] === 'YES') ? NULL : $value;
                    }
                }

                // Add coordinates
                $db_columns[] = '`latitude`';
                $placeholders[] = '?';
                $params[] = $latitude;

                $db_columns[] = '`longitude`';
                $placeholders[] = '?';
                $params[] = $longitude;

                // Status = pending for public submissions
                $db_columns[] = '`status`';
                $placeholders[] = '?';
                $params[] = 'pending';

                // Insert
                $sql = "INSERT INTO programs (" . implode(', ', $db_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $pdo->commit();

                $success = "تم إرسال برنامجك بنجاح! سيتم مراجعته من قبل الإدارة قبل النشر. شكراً لك. ✅";
                $_POST = []; // Clear form
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title_text); ?> 📝</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/program-form.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(99, 102, 241, 0.03) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(20, 184, 166, 0.03) 0%, transparent 40%);
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 1.2rem 0;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            gap: 20px;
        }

        .header-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 12px;
            background: rgba(255,255,255,0.15);
            padding: 5px;
        }

        .header-text .header-title {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .header-text .header-subtitle {
            font-size: 0.85rem;
            font-weight: 400;
            opacity: 0.9;
            line-height: 1.2;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <img src="<?php echo htmlspecialchars($site_settings['logo_path'] ?? 'https://i.postimg.cc/sxNCrL6d/logo-white-03.png'); ?>" alt="شعار" class="header-logo">
            <div class="header-text">
                <div class="header-title"><?php echo htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية'); ?></div>
                <?php if (!empty($site_settings['guide_subtitle'])): ?>
                    <div class="header-subtitle"><?php echo htmlspecialchars($site_settings['guide_subtitle']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="public-page" style="padding: 50px 30px;">
        <?php if ($success): ?>
            <div class="program-form-section" style="max-width: 800px;">
                <div class="program-form-card" style="text-align: center;">
                    <div class="form-message success"><?php echo $success; ?></div>
                    <a href="index.php" class="back-link" style="display: inline-block; margin-top: 20px;"><i class="fas fa-home"></i> العودة إلى الصفحة الرئيسية</a>
                </div>
            </div>
        <?php elseif ($error): ?>
            <div class="program-form-section">
                <div class="program-form-card">
                    <div class="form-message error"><?php echo $error; ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <?php
            // Prepare form_data from POST for the template
            $form_data = $_POST ?: [];
            
            // Render the unified form
            $form_options = [
                'form_id'      => 'submit-program-form',
                'csrf_token'   => $csrf_token,
                'submit_label' => 'إرسال البرنامج للمراجعة',
                'back_url'     => 'index.php',
                'show_back_btn'=> false,
                'is_admin'     => false,
                'show_header'  => true,
                'ad_required'  => true,
                'word_limit'   => 50,
                'form_title'   => 'طلب إضافة برنامج للدليل',
            ];
            include 'includes/program_form.php';
            ?>
        <?php endif; ?>
    </div>

    <script src="js/program-form.js"></script>
    <script src="js/hijri-calendar.js"></script>
</body>
</html>