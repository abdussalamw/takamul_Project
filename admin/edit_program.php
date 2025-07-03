<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title_text = 'تعديل البرنامج';
$error = null;

// Security check: must be logged in and have permission to edit programs
if (empty($_SESSION['admin_id']) || empty($_SESSION['permissions']['can_edit_programs'])) {
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

// Validate program ID from GET parameter
$program_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
}

if (!$program_id || !$program) {
    header('Location: dashboard.php?status=not_found');
    exit;
}
// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى. 🚫";
    } else {
        $original_ad_link = $program['ad_link']; // Get old file path before processing
        $new_ad_link_path = null;

        // 2. Handle File Upload if a new file is provided
        if (isset($_FILES['ad_link']) && $_FILES['ad_link']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_tmp_path = $_FILES['ad_link']['tmp_name'];
            $file_name = basename($_FILES['ad_link']['name']);
            $file_size = $_FILES['ad_link']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
            $max_file_size = 5 * 1024 * 1024; // 5 MB

            if (!in_array($file_ext, $allowed_ext)) {
                $error = "نوع الملف غير مسموح به. (المسموح: jpg, png, pdf) 🚫";
            } elseif ($file_size > $max_file_size) {
                $error = "حجم الملف كبير جداً. الحد الأقصى هو 5 ميجابايت. 🚫";
            } else {
                $new_file_name = uniqid('ad_', true) . '.' . $file_ext;
                $dest_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    $new_ad_link_path = 'uploads/' . $new_file_name;
                    $_POST['ad_link'] = $new_ad_link_path; // Set POST value to new path
                } else {
                    $error = "حدث خطأ أثناء نقل الملف المرفوع. 🚫";
                }
            }
        }

        if (!isset($error)) {
            // 3. Build Dynamic UPDATE Query
            $update_parts = [];
            $params = [];
            
            $stmt = $pdo->query("DESCRIBE programs");
            $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($table_columns_info as $column_info) {
                $column_name = $column_info['Field'];
                if ($column_name === 'id') continue;

                $is_file_field = ($column_name === 'ad_link');
                
                // For file fields, we only update if a new file was uploaded
                if ($is_file_field) {
                    if ($new_ad_link_path) {
                        $update_parts[] = "`$column_name` = ?";
                        $params[] = $new_ad_link_path;
                    }
                } 
                // For other fields, check if the submitted value is different from the current one
                elseif (isset($_POST[$column_name])) {
                    $submitted_value = trim($_POST[$column_name]);
                    $current_value = $program[$column_name] ?? null;

                    if ($submitted_value !== $current_value) {
                        $update_parts[] = "`$column_name` = ?";
                        if (empty($submitted_value) && $column_info['Null'] === 'YES') {
                            $params[] = NULL;
                        } else {
                            $params[] = $submitted_value;
                        }
                    }
                }
            }

            // 4. Execute Query if there are changes
            if (!empty($update_parts)) {
                $params[] = $program_id; // Add program ID for the WHERE clause
                $sql = "UPDATE programs SET " . implode(', ', $update_parts) . " WHERE id = ?";
                
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    // 5. Delete old file if a new one was uploaded successfully
                    if ($new_ad_link_path && !empty($original_ad_link) && file_exists('../' . $original_ad_link)) {
                        unlink('../' . $original_ad_link);
                    }

                    // Unset or regenerate CSRF token after successful submission
                    unset($_SESSION['csrf_token']);
                    header('Location: dashboard.php?status=updated');
                    exit;

                } catch (PDOException $e) {
                    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
                }
            } else {
                // No changes were submitted, just redirect
                 header('Location: dashboard.php');
                 exit;
            }
        } else {
            // If there was an error, repopulate the form with the submitted data for correction
            foreach ($_POST as $key => $value) {
                if (array_key_exists($key, $program)) {
                    $program[$key] = htmlspecialchars($value);
                }
            }
        }
    }
}
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل البرنامج ✏️</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            background: linear-gradient(120deg, var(--primary), #5c1d9c);
            color: white;
            padding: 0.5rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
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
            gap: 15px;
        }

        .logo-image {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 30px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }        

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .page-title-header {
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .page-title-header i {
            margin-left: 10px;
            color: var(--accent);
            font-size: 1.2rem;
        }        

        nav a:hover, nav a.active {
            background: rgba(255, 255, 255, 0.15);
        }

        .edit-program-section {
            max-width: 900px;
            width: 100%;
            margin: 40px auto;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .edit-program-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;            
        }

        .edit-program-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;            
            text-align: right;
        }

        .edit-program-card h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 3px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 10px;
            background: #fff0f0;
            border-radius: 8px;
            animation: slideIn 0.5s ease-out;
        }
        
        .edit-program-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: space-between;
        }

        .form-group {
            flex: 1 1 100%;
            min-width: 250px;
            text-align: right;
            position: relative;
        }

        .form-group.full-width {
            flex: 1 1 100%;
        }

        .form-group.half-width {
            flex: 1 1 calc(50% - 0.75rem);
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group i {
            color: var(--primary);
        }

        .edit-program-form input,
        .edit-program-form textarea,
        .edit-program-form input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'Tajawal', sans-serif;
        }

        .edit-program-form input[type="file"] {
            padding: 8px;
        }

        .edit-program-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .edit-program-form input:focus,
        .edit-program-form textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
        }

        .edit-program-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;            
            transition: all 0.3s ease;
            margin: 0 auto;
        }

        .edit-program-btn:hover {
            background: #7a1fc2;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;            
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .current-ad-preview {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .current-ad-preview span {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }        

        @media (max-width: 768px) {
            .form-group.half-width {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="https://i.postimg.cc/sxNCrL6d/logo-white-03.png" alt="شعار" class="logo-image">
                <div class="logo-text">دليل البرامج الصيفية</div>
            </div>
            <div class="page-title-header">
                <i class="fas fa-edit"></i>
                <span><?php echo $page_title_text; ?></span>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="edit-program-section">
        <div class="edit-program-card">
            <h2><i class="fas fa-edit"></i> <?php echo $page_title_text; ?></h2>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <form method="POST" class="edit-program-form" id="edit-program-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <?php
                try {
                    $field_translations = [
                        'title'             => 'عنوان البرنامج',
                        'organizer'         => 'الجهة المنظمة',
                        'description'       => 'وصف البرنامج',
                        'Direction'         => 'المنطقة/الاتجاه',
                        'location'          => 'مكان البرنامج (الحي)',
                        'start_date'        => 'تاريخ البدء',
                        'end_date'          => 'تاريخ الانتهاء',
                        'duration'          => 'المدة',
                        'age_group'         => 'الفئة العمرية',
                        'price'             => 'رسوم البرنامج',
                        'registration_link' => 'رابط التسجيل',
                        'ad_link'           => 'صورة الإعلان (صورة أو PDF)',
                        'google_map'        => 'رابط الموقع على خرائط جوجل',
                    ];

                    $field_icons = [
                        'title'             => 'fas fa-heading',
                        'organizer'         => 'fas fa-user-tie',
                        'description'       => 'fas fa-file-alt',
                        'Direction'         => 'fas fa-map-signs',
                        'location'          => 'fas fa-map-marker-alt',
                        'start_date'        => 'fas fa-calendar-day',
                        'end_date'          => 'fas fa-calendar-week',
                        'duration'          => 'fas fa-clock',
                        'age_group'         => 'fas fa-users',
                        'price'             => 'fas fa-money-bill',
                        'registration_link' => 'fas fa-link',
                        'ad_link'           => 'fas fa-image',
                        'google_map'        => 'fas fa-map-marked-alt',
                    ];

                    $stmt = $pdo->query("DESCRIBE programs");
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $column_data = array_column($columns, null, 'Field');
                    $order = array_keys($column_data);
                    $end_date_key = array_search('end_date', $order);
                    if ($end_date_key !== false) {
                        $end_date_item = array_splice($order, $end_date_key, 1);
                        $start_date_key = array_search('start_date', $order);
                        if ($start_date_key !== false) {
                            array_splice($order, $start_date_key + 1, 0, $end_date_item);
                        } else {
                            $order[] = $end_date_item[0];
                        }
                    }
                    $ordered_columns = [];
                    foreach ($order as $field_name) {
                        $ordered_columns[] = $column_data[$field_name];
                    }

                    foreach ($ordered_columns as $column) {
                        $field_name = $column['Field'];
                        if ($field_name == 'id') continue;

                        $is_date_field = in_array($field_name, ['start_date', 'end_date']);
                        $group_classes = 'form-group';
                        if ($field_name === 'description') {
                            $group_classes .= ' full-width';
                        } else {
                            $group_classes .= ' half-width';
                        }

                        $required = $column['Null'] == 'NO' ? 'required' : '';
                        $label = $field_translations[$field_name] ?? ucfirst(str_replace('_', ' ', $field_name));
                        $icon_class = $field_icons[$field_name] ?? 'fas fa-edit';
                        $current_value = htmlspecialchars($program[$field_name] ?? '');
                ?>
                        <div class="<?php echo $group_classes; ?>">                            
                            <label for="<?php echo $field_name; ?>"><i class="<?php echo $icon_class; ?>"></i> <?php echo $label; ?></label>
                            <?php if ($field_name === 'ad_link'): ?>
                                <?php if (!empty($current_value)): ?>
                                    <div class="current-ad-preview">
                                        <span>الإعلان الحالي:</span>
                                        <?php
                                        $file_ext = strtolower(pathinfo($current_value, PATHINFO_EXTENSION));
                                        if (in_array($file_ext, ['jpg', 'jpeg', 'png'])):
                                        ?>
                                            <a href="../<?php echo $current_value; ?>" target="_blank"><img src="../<?php echo $current_value; ?>" alt="صورة الإعلان الحالي" style="max-width: 100px; max-height: 100px; border-radius: 5px; margin-top: 5px;"></a>
                                        <?php else: ?>
                                            <a href="../<?php echo $current_value; ?>" target="_blank">عرض الملف الحالي (<?php echo $file_ext; ?>)</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" accept=".jpg, .jpeg, .png, .pdf">
                                <small style="display: block; margin-top: 5px; color: #666;">اترك الحقل فارغاً للإبقاء على الإعلان الحالي.</small>
                            <?php elseif ($column['Type'] == 'longtext' || $column['Type'] == 'text'): ?>
                                <textarea id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?>><?php echo $current_value; ?></textarea>
                            <?php else: ?>
                                <input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $current_value; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?> <?php if ($is_date_field) echo 'readonly style="cursor: pointer;"'; ?>>
                            <?php endif; ?>
                        </div>
                <?php
                    }
                } catch (PDOException $e) {
                    echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> خطأ في جلب معلومات الحقول: " . $e->getMessage() . "</p>";
                }
                ?>
                <div class="form-group full-width">
                    <button type="submit" class="edit-program-btn"><i class="fas fa-save"></i> حفظ التغييرات</button>
                </div>
            </form>
            <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-right"></i> العودة إلى لوحة التحكم</a>
        </div>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    let activeCalendarInput = null;
    const calendarElement = createCalendarElement();
    document.body.appendChild(calendarElement);

    const hijriMonths = ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الثانية', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'];
    const hijriDays = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    
    // More accurate calculation for the first day of the month
    const hijriYearStartDay = {
        1446: 0, // 1 Muharram 1446 is a Sunday
        1447: 4, // 1 Muharram 1447 is a Thursday
        1448: 2  // 1 Muharram 1448 is a Tuesday
    };
    const hijriMonthLengths = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29]; // Approximate lengths

    function createCalendarElement() {
        const calendar = document.createElement('div');
        calendar.className = 'hijri-calendar';
        calendar.style.cssText = `
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            z-index: 1002;
            padding: 15px;
            width: 320px;
            display: none;
            font-family: 'Tajawal', sans-serif;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s
