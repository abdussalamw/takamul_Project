<?php
include 'includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];
$page_title_text = 'طلب إضافة برنامج للدليل';
$error = null;
$success = null;

// Start session for CSRF token
session_start();

// The $site_settings variable is already loaded globally from db_connect.php
// No need to redefine the function or call it again here.

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
            $max_file_size = 5 * 1024 * 1024; // 5 MB

            if (!in_array($file_ext, $allowed_ext)) {
                $errors[] = "نوع الملف غير مسموح به. (المسموح: jpg, png, pdf) 🚫";
            } elseif ($file_size > $max_file_size) {
                $errors[] = "حجم الملف كبير جداً. الحد الأقصى هو 5 ميجابايت. 🚫";
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
            $errors[] = "حقل 'صورة الإعلان' مطلوب.";
        }

        // --- 2. Validate Data ---
        
        $required_fields = [
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
            'google_map'        => 'رابط خرائط جوجل',
        ];

        foreach ($required_fields as $field => $translation) {
            if (empty(trim($_POST[$field]))) {
                $errors[] = "حقل '{$translation}' مطلوب.";
            }
        }

        if (isset($_POST['start_date']) && !empty($_POST['start_date']) && !preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $_POST['start_date'])) {
            $errors[] = "تاريخ البدء غير صالح (يجب أن يكون DD/MM/YYYY) 🚫";
        }

        if (empty($errors)) {
            $db_columns = [];
            $placeholders = [];
            $params = [];

            $stmt = $pdo->query("DESCRIBE programs");
            $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($table_columns_info as $column_info) {
                $column_name = $column_info['Field'];
                if (in_array($column_name, ['id', 'status'])) continue; // Skip id and status

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

            // Set status to 'pending' for all public submissions
            $db_columns[] = '`status`';
            $placeholders[] = '?';
            $params[] = 'pending';

            // --- 3. Execute Insert ---
            try {
                $sql = "INSERT INTO programs (" . implode(', ', $db_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $success = "تم إرسال برنامجك بنجاح! سيتم مراجعته من قبل الإدارة قبل النشر. شكراً لك. ✅";
                $_POST = []; // Clear form on success
            } catch (PDOException $e) {
                $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
    // Regenerate CSRF token after submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];
}
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title_text); ?> 📝</title>
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: var(--dark);
            line-height: 1.6;
        }
        header {
            background: linear-gradient(120deg, var(--primary), #5c1d9c);
            color: white;
            padding: 1.2rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
        .logo-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 8px;
        }
        .logo-text-group .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .logo-text-group .logo-subtext {
            font-size: 0.9rem;
            font-weight: 400;
            opacity: 0.9;
            line-height: 1.2;
            margin-top: 12px;
        }
        .submission-section {
            max-width: 900px;
            width: 100%;
            margin: 40px auto;
            padding: 20px;
        }
        .submission-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
        }
        .submission-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
            text-align: center;
        }
        .submission-card h2::after {
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
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
        .submission-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            justify-content: space-between;
        }
        .form-group {
            flex: 1 1 100%;
            text-align: right;
            min-width: 250px;
            position: relative;
        }
        .form-group.full-width { flex: 1 1 100%; }
        .form-group.half-width { flex: 1 1 calc(50% - 0.75rem); }
        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group i { color: var(--primary); }
        .submission-form input,
        .submission-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'Tajawal', sans-serif;
        }
        .submission-form textarea {
            resize: vertical;
            min-height: 120px;
        }
        .submission-form input:focus,
        .submission-form textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
        }
        .form-actions {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .submit-btn:hover {
            background: #7a1fc2;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .form-group.half-width { flex: 1 1 100%; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <img src="<?php echo htmlspecialchars($site_settings['logo_path'] ?? 'assets/img/default-logo.png'); ?>" alt="شعار" class="logo-image">
            <div class="logo-text-group">
                <div class="logo-text"><?php echo htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية'); ?></div>
                <?php if (!empty($site_settings['guide_subtitle'])): ?>
                    <div class="logo-subtext"><?php echo htmlspecialchars($site_settings['guide_subtitle']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="submission-section">
        <div class="submission-card">
            <h2><i class="fas fa-paper-plane"></i> <?php echo htmlspecialchars($page_title_text); ?></h2>

            <?php if ($success): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!$success): // Hide form on success ?>
            <form method="POST" class="submission-form" enctype="multipart/form-data">
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
                        if (in_array($field_name, ['id', 'status'])) continue;

                        $is_date_field = in_array($field_name, ['start_date', 'end_date']);
                        $group_classes = 'form-group ' . ($field_name === 'description' ? 'full-width' : 'half-width');
                        $required = 'required'; // All fields are now required
                        $label = $field_translations[$field_name] ?? ucfirst(str_replace('_', ' ', $field_name));
                        $icon_class = $field_icons[$field_name] ?? 'fas fa-edit';
                        $submitted_value = isset($_POST[$field_name]) ? htmlspecialchars($_POST[$field_name]) : '';
                ?>
                        <div class="<?php echo $group_classes; ?>">
                            <label for="<?php echo $field_name; ?>"><i class="<?php echo $icon_class; ?>"></i> <?php echo $label; ?></label>
                            <?php if ($field_name === 'ad_link'): ?>
                                <input type="file" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" accept=".jpg, .jpeg, .png, .pdf" <?php echo $required; ?>>
                            <?php elseif ($column['Type'] == 'longtext' || $column['Type'] == 'text'): ?>
                                <textarea id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?>><?php echo $submitted_value; ?></textarea>
                            <?php else: ?>
                                <input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $submitted_value; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?> <?php if ($is_date_field) echo 'readonly style="cursor: pointer;"'; ?>>
                            <?php endif; ?>
                        </div>
                <?php
                    }
                } catch (PDOException $e) {
                    echo "<p class='message error'><i class='fas fa-exclamation-circle'></i> خطأ في جلب معلومات الحقول: " . $e->getMessage() . "</p>";
                }
                ?>
                <div class="form-group full-width form-actions">
                    <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> إرسال البرنامج للمراجعة</button>
                </div>
            </form>
            <?php endif; ?>
            <a href="index.php" class="back-link"><i class="fas fa-home"></i> العودة إلى الصفحة الرئيسية</a>
        </div>
    </section>

    <script>
    // Hijri Calendar Script (copied from add_program.php for consistency)
    document.addEventListener('DOMContentLoaded', function() {
        let activeCalendarInput = null;
        const calendarElement = createCalendarElement();
        document.body.appendChild(calendarElement);

        const hijriMonths = ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الثانية', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'];
        const hijriDays = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        
        const hijriYearStartDay = { 1446: 0, 1447: 4, 1448: 2 };
        const hijriMonthLengths = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29];

        function createCalendarElement() {
            const calendar = document.createElement('div');
            calendar.className = 'hijri-calendar';
            calendar.style.cssText = `
                position: absolute; background: white; border: 1px solid #ddd; border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 1002; padding: 15px;
                width: 320px; display: none; font-family: 'Tajawal', sans-serif; opacity: 0; 
                transform: translateY(10px); transition: opacity 0.3s ease, transform 0.3s ease; 
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