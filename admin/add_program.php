<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];
$page_title_text = 'إضافة برنامج جديد';
$error = null; // Initialize error variable

if (!isset($_SESSION['admin_id'])) {
    $debug['auth_error'] = 'User not authenticated';
    header('Location: admin_login.php');
    exit;
}

// Security check: must have permission to add programs
if (!isset($_SESSION['permissions']['can_add_programs']) || !$_SESSION['permissions']['can_add_programs']) {
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى. 🚫";
    } else {
        $ad_link_path = null;

        // --- 1. التعامل مع رفع الملفات ---
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
                    $ad_link_path = 'uploads/' . $new_file_name;
                } else {
                    $error = "حدث خطأ أثناء نقل الملف المرفوع. 🚫";
                }
            }
        }

        // --- 2. التحقق من صحة البيانات وإعدادها للإدخال ---
        // التحقق من صحة البيانات الأساسية
        if (empty($_POST['title']) || empty($_POST['start_date'])) {
            $error = "حقل العنوان وتاريخ البدء مطلوبان على الأقل 🚫";
            $debug['error'] = 'Missing required fields: title or start_date';
        } elseif (isset($_POST['start_date']) && !preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $_POST['start_date'])) {
            $error = "تاريخ البدء غير صالح (يجب أن يكون DD/MM/YYYY) 🚫";
            $debug['error'] = 'Invalid start date format: ' . $_POST['start_date'];
        }

        // استمرار العملية فقط إذا لم يكن هناك أي خطأ
        if (!isset($error)) {
            $db_columns = [];
            $placeholders = [];
            $params = [];

            $stmt = $pdo->query("DESCRIBE programs");
            $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($table_columns_info as $column_info) {
                $column_name = $column_info['Field'];
                if ($column_name === 'id') continue;

                if ($column_name === 'ad_link') {
                    if ($ad_link_path) { // فقط إذا تم رفع ملف جديد
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

            // Determine status based on which button was clicked
            $status = 'pending'; // Default to pending (draft)
            if (isset($_POST['save_publish']) && !empty($_SESSION['permissions']['can_publish_programs'])) {
                $status = 'published';
            }
            $db_columns[] = '`status`';
            $placeholders[] = '?';
            $params[] = $status;

            // --- 3. تنفيذ الإدخال في قاعدة البيانات ---
            try {
                $sql = "INSERT INTO programs (" . implode(', ', $db_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $debug['program_added'] = true;
                $debug['program_id'] = $pdo->lastInsertId();
                // Unset token on success to prevent reuse
                unset($_SESSION['csrf_token']);
                header('Location: dashboard.php?status=added');
                exit;
            } catch (PDOException $e) {
                $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
                $debug['error'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title_text; ?> ➕</title>
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

        .beta-banner {
            position: fixed;
            top: 15px;
            left: 15px;
            background-color: var(--secondary);
            color: white;
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 4px;
            z-index: 1001;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
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

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo-subtext {
            font-size: 0.8rem;
            opacity: 0.9;
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
            gap: 12px;
        }

        nav a i {
            font-size: 1.8rem;
        }

        nav a:hover, nav a.active {
            background: rgba(255, 255, 255, 0.15);
        }

        .welcome-message {
            color: white;
            display: flex;
            align-items: center;
            font-weight: 500;
            padding: 10px 20px;
        }

        .page-title-header {
            color: white;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .page-title-header i {
            margin-left: 10px; /* مسافة بين الأيقونة والنص */
            color: var(--accent);
            font-size: 1.2rem;
        }

        .add-program-section {
            max-width: 900px;
            width: 100%;
            margin: 40px auto;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .add-program-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .add-program-card:hover {
            transform: scale(1.02);
        }

        .add-program-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .add-program-card h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
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
            background: #fff0f0; /* Light red background */
            border-radius: 8px;
            animation: slideIn 0.5s ease-out;
        }
        
        .add-program-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: space-between;
        }

        .form-group {
            flex: 1 1 100%; /* Default to full width */
            text-align: right;
            min-width: 250px;
            position: relative;
        }

        .form-group.full-width {
            flex: 1 1 100%;
        }

        /* New class for half-width fields */
        .form-group.half-width {
            flex: 1 1 calc(50% - 0.75rem); /* 50% width minus half the gap */
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

        .add-program-form input,
        .add-program-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'Tajawal', sans-serif;
        }

        .add-program-form input#start_date {
            cursor: pointer;
        }

        .add-program-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .add-program-form input:focus,
        .add-program-form textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
        }

        .add-program-btn {
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

        .add-program-btn:hover {
            background: #7a1fc2;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* New styles for form actions container */
        .form-actions-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
            width: 100%;
        }
        .status-toggle { display: flex; align-items: center; gap: 10px; }
        .status-toggle label { margin-bottom: 0; font-weight: 600; }
        .switch { position: relative; display: inline-block; width: 50px; height: 28px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 28px; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--success); }
        input:checked + .slider:before { transform: translateX(22px); }
        .back-btn-inline { text-decoration: none; font-weight: 600; color: var(--secondary); display: inline-flex; align-items: center; gap: 8px; }
        .back-btn-inline:hover { text-decoration: underline; }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--secondary);
            color: white;
            padding: 14px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #e55a5a;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
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

            .add-program-section {
                margin: 20px;
                padding: 15px;
                max-width: 100%;
            }

            .add-program-card {
                padding: 1.5rem;
            }

            .form-group {
                flex: 1 1 100%;
            }

            .add-program-form input,
            .add-program-form textarea {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .add-program-btn, .back-btn {
                padding: 12px;
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            nav ul {
                flex-wrap: wrap;
                justify-content: flex-start;
            }

            .add-program-card h2 {
                font-size: 1.5rem;
            }

            .form-group label {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="https://i.postimg.cc/sxNCrL6d/logo-white-03.png" alt="شعار" class="logo-image">
                <div>
                    <div class="logo-text">دليل البرامج الصيفية</div>
                    <div class="logo-subtext">للفتيات في مدينة الرياض 1447هـ</div>
                </div>
            </div>
            <div class="page-title-header">
                <i class="fas fa-plus-circle"></i>
                <span><?php echo htmlspecialchars($page_title_text); ?></span>
            </div>
            <nav>
                <ul>
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="welcome-message">
                            <span>أهلاً بك, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li>
                            <a href="logout.php" title="تسجيل الخروج"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <section class="add-program-section">
        <div class="add-program-card">
            <h2>إضافة برنامج جديد ➕</h2>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <form method="POST" class="add-program-form" id="add-program-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <?php
                try {
                    // مصفوفة لترجمة أسماء الحقول إلى العربية
                    // هام جداً: يجب أن تكون هذه الأسماء (المفاتيح) مطابقة تماماً لأسماء الأعمدة في جدول `programs` في قاعدة البيانات
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
                        'google_map'  => 'رابط الموقع على خرائط جوجل',
                    ];

                    // مصفوفة لربط الحقول بالأيقونات المناسبة
                    // يجب أن تكون الأسماء هنا مطابقة أيضاً
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

                    // --- Reorder columns to place end_date after start_date ---
                    $column_data = array_column($columns, null, 'Field');
                    $order = array_keys($column_data);

                    $end_date_key = array_search('end_date', $order);
                    if ($end_date_key !== false) {
                        // Remove end_date from its current position
                        $end_date_item = array_splice($order, $end_date_key, 1);
                        
                        // Find start_date's new position and insert end_date after it
                        $start_date_key = array_search('start_date', $order);
                        if ($start_date_key !== false) {
                            array_splice($order, $start_date_key + 1, 0, $end_date_item);
                        } else {
                            // if start_date is not found for some reason, put end_date back at the end
                            $order[] = $end_date_item[0];
                        }
                    }

                    $ordered_columns = [];
                    foreach ($order as $field_name) {
                        $ordered_columns[] = $column_data[$field_name];
                    }
                    // --- End of reordering ---

                    foreach ($ordered_columns as $column) {
                        $field_name = $column['Field'];
                        if ($field_name == 'id') continue; // تخطي حقل المعرف

                        $is_date_field = in_array($field_name, ['start_date', 'end_date']);

                        // تحديد أصناف CSS للحقل
                        $group_classes = 'form-group';
                        if ($field_name === 'description') {
                            $group_classes .= ' full-width';
                        } else {
                            $group_classes .= ' half-width';
                        }

                        $required = $column['Null'] == 'NO' ? 'required' : '';
                        $label = $field_translations[$field_name] ?? ucfirst(str_replace('_', ' ', $field_name));
                        $icon_class = $field_icons[$field_name] ?? 'fas fa-edit';

                        // Repopulate form with submitted data on error to improve UX
                        $submitted_value = isset($_POST[$field_name]) ? htmlspecialchars($_POST[$field_name]) : '';
                ?>
                        <div class="<?php echo $group_classes; ?>">
                            <label for="<?php echo $field_name; ?>"><i class="<?php echo $icon_class; ?>"></i> <?php echo $label; ?></label>
                            <?php if ($field_name === 'ad_link'): ?>
                                <input type="file" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" accept=".jpg, .jpeg, .png, .pdf">
                            <?php elseif ($column['Type'] == 'longtext' || $column['Type'] == 'text'): ?>
                                <textarea id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?>><?php echo $submitted_value; ?></textarea>
                            <?php else: ?>
                                <input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $submitted_value; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?> <?php if ($is_date_field) echo 'readonly style="cursor: pointer;"'; ?>>
                            <?php endif; ?>
                        </div>
                <?php
                    }
                } catch (PDOException $e) {
                    echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> خطأ في جلب معلومات الحقول: " . $e->getMessage() . "</p>";
                }
                ?>
                <!-- Form Actions Container -->
                <div class="form-actions-container">
                    <a href="dashboard.php" class="back-btn-inline"><i class="fas fa-arrow-right"></i> رجوع</a>
                    <div class="status-toggle">
                        <label for="status-checkbox">نشر فوري</label>
                        <label class="switch">
                            <input type="checkbox" id="status-checkbox" name="status" value="published" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <button type="submit" class="add-program-btn"><i class="fas fa-save"></i> حفظ البرنامج</button>
                </div>
            </form>
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
            transition: opacity 0.3s ease, transform 0.3s ease;
        `;
        return calendar;
    }

    function renderCalendar(year, month, selectedDay = null) {
        calendarElement.innerHTML = `
            <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <button type="button" class="nav-btn" data-action="prev-month">‹</button>
                <div style="display: flex; gap: 5px; font-weight: bold;">
                    <span id="current-month">${hijriMonths[month-1]}</span>
                    <span id="current-year">${year}هـ</span>
                </div>
                <button type="button" class="nav-btn" data-action="next-month">›</button>
            </div>
            <div class="calendar-grid-header" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 10px;">
                ${hijriDays.map(day => `<div style="text-align: center; font-weight: bold; color: var(--primary); padding: 6px; font-size: 0.8rem;">${day.substring(0,3)}</div>`).join('')}
            </div>
            <div class="calendar-grid-days" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;"></div>
        `;

        const daysContainer = calendarElement.querySelector('.calendar-grid-days');
        const daysInMonth = hijriMonthLengths[month - 1] + ((month === 12 && (year === 1446 || year === 1447)) ? 1 : 0); // Simple leap year adjustment

        let firstDayOfMonth = hijriYearStartDay[year] || 0;
        for (let i = 0; i < month - 1; i++) {
            firstDayOfMonth = (firstDayOfMonth + hijriMonthLengths[i]) % 7;
        }

        for (let i = 0; i < firstDayOfMonth; i++) {
            daysContainer.innerHTML += '<div></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.textContent = day;
            dayElement.style.cssText = `text-align: center; padding: 8px 4px; cursor: pointer; border-radius: 50%; transition: all 0.2s ease; font-weight: 500;`;
            if (day === selectedDay) {
                dayElement.style.backgroundColor = 'var(--primary)';
                dayElement.style.color = 'white';
            }
            dayElement.addEventListener('click', () => selectDate(year, month, day));
            dayElement.addEventListener('mouseover', () => { if(day !== selectedDay) dayElement.style.backgroundColor = '#f0e6ff'; });
            dayElement.addEventListener('mouseout', () => { if(day !== selectedDay) dayElement.style.backgroundColor = ''; });
            daysContainer.appendChild(dayElement);
        }

        calendarElement.querySelectorAll('.nav-btn').forEach(btn => {
            btn.style.cssText = `background: none; border: none; font-size: 1.5rem; color: var(--primary); cursor: pointer;`;
            btn.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                let newMonth = month, newYear = year;
                if (action === 'prev-month') {
                    newMonth--;
                    if (newMonth < 1) { newMonth = 12; newYear--; }
                } else {
                    newMonth++;
                    if (newMonth > 12) { newMonth = 1; newYear++; }
                }
                renderCalendar(newYear, newMonth, selectedDay);
            });
        });
    }

    function selectDate(year, month, day) {
        if (!activeCalendarInput) return;
        const dateStr = `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`;
        activeCalendarInput.value = dateStr;
        hideCalendar();
    }

    function showCalendar(targetInput) {
        activeCalendarInput = targetInput;
        const rect = targetInput.getBoundingClientRect();
        calendarElement.style.top = `${window.scrollY + rect.bottom + 5}px`;
        calendarElement.style.right = `${window.innerWidth - rect.right}px`;

        let currentYear = 1447, currentMonth = 1, currentDay = null;
        const currentValue = targetInput.value;
        if (currentValue && /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(currentValue)) {
            const parts = currentValue.split('/');
            currentDay = parseInt(parts[0], 10);
            currentMonth = parseInt(parts[1], 10);
            currentYear = parseInt(parts[2], 10);
        }

        renderCalendar(currentYear, currentMonth, currentDay);
        calendarElement.style.display = 'block';
        setTimeout(() => {
            calendarElement.style.opacity = '1';
            calendarElement.style.transform = 'translateY(0)';
        }, 10);
    }

    function hideCalendar() {
        calendarElement.style.opacity = '0';
        calendarElement.style.transform = 'translateY(10px)';
        setTimeout(() => {
            calendarElement.style.display = 'none';
            activeCalendarInput = null;
        }, 300);
    }

    document.querySelectorAll('input[id="start_date"], input[id="end_date"]').forEach(input => {
        input.addEventListener('click', (e) => {
            e.stopPropagation();
            if (activeCalendarInput === e.target) {
                hideCalendar();
            } else {
                showCalendar(e.target);
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (activeCalendarInput && !calendarElement.contains(e.target) && e.target !== activeCalendarInput) {
            hideCalendar();
        }
    });
});
    </script>
</body>
</html>
