<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];
$error = null; // Initialize error variable

if (!isset($_SESSION['admin_id'])) {
    $debug['auth_error'] = 'User not authenticated';
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    $debug['id_missing'] = 'Program ID not provided';
    header('Location: dashboard.php');
    exit;
}

$program_id = $_GET['id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['program_fetched'] = !empty($program);
    $debug['program_data'] = $program ? array_map(function($value) {
        return is_string($value) && strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value;
    }, $program) : null;
} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
    $debug['error'] = 'Database error: ' . $e->getMessage();
}

if (!$program) {
    $debug['program_not_found'] = 'Program ID: ' . $program_id;
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $organizer = trim($_POST['organizer']);
    $direction = trim($_POST['Direction']); // جلب قيمة Direction
    $location = trim($_POST['location']);
    $duration = trim($_POST['duration']);
    $start_date = trim($_POST['start_date']);
    $age_group = trim($_POST['age_group']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $registration_link = trim($_POST['registration_link']);
    $submitted_csrf_token = $_POST['csrf_token'] ?? '';

    $debug['input'] = [
        'title' => $title,
        'organizer' => $organizer,
        'direction' => $direction,
        'location' => $location,
        'duration' => $duration,
        'start_date' => $start_date,
        'age_group' => $age_group,
        'description' => substr($description, 0, 20) . '...',
        'price' => $price,
        'registration_link' => substr($registration_link, 0, 20) . (strlen($registration_link) > 20 ? '...' : ''),
        'csrf_token_received' => !empty($submitted_csrf_token)
    ];
    
    // Update program data in the array for immediate display after POST
    $program['title'] = $title;
    $program['organizer'] = $organizer;
    $program['Direction'] = $direction;
    $program['location'] = $location;
    $program['duration'] = $duration;
    $program['start_date'] = $start_date;
    $program['age_group'] = $age_group;
    $program['description'] = $description;
    $program['price'] = $price;
    $program['registration_link'] = $registration_link;


    try {
        if (!hash_equals($_SESSION['csrf_token'], $submitted_csrf_token)) {
            $error = "فشل التحقق من الطلب (CSRF) 🚫";
            $debug['error'] = 'Invalid CSRF token';
        } elseif (empty($title) || empty($organizer) || empty($location) || empty($direction) || empty($duration) || empty($start_date) || empty($age_group) || empty($description)) {
            $error = "جميع الحقول مطلوبة باستثناء رابط التسجيل 🚫";
            $debug['error'] = 'Missing required fields';
        } elseif (!preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $start_date)) {
            $error = "تاريخ البدء غير صالح (يجب أن يكون DD/MM/YYYY) 🚫";
            $debug['error'] = 'Invalid date range format: ' . $start_date;
        } else {
            $stmt = $pdo->prepare(
                "UPDATE programs SET title = ?, organizer = ?, Direction = ?, location = ?, duration = ?, start_date = ?, age_group = ?, description = ?, price = ?, registration_link = ? WHERE id = ?"
            );
            $stmt->execute([
                $title, $organizer, $direction, $location, $duration, $start_date, $age_group, $description, $price, $registration_link ?: NULL, $program_id
            ]);
            $debug['program_updated'] = true;
            // Unset or regenerate CSRF token after successful submission
            unset($_SESSION['csrf_token']);
            header('Location: dashboard.php?status=updated');
            exit;
        }
    } catch (PDOException $e) {
        $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
        $debug['error'] = 'Database error: ' . $e->getMessage();
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
            padding: 1rem 0;
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
            gap: 60px;
        }

        .logo-image {
            width: 140px;
            height: 140px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo-subtext {
            font-size: 0.9rem;
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
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .edit-program-card:hover {
            transform: scale(1.02);
        }

        .edit-program-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .edit-program-card h2::after {
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
            flex: 1 1 45%;
            text-align: right;
            min-width: 250px;
        }

        .form-group.full-width {
            flex: 1 1 100%;
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
        .edit-program-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'Tajawal', sans-serif;
        }

        .edit-program-form input#start_date {
            cursor: pointer;
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
            .header-container { flex-direction: column; padding: 0 10px; gap: 5px; }
            header { padding: 5px 0; max-height: 20vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }
            .logo { gap: 8px; flex-shrink: 1; min-width: 0; align-items: center; }
            .logo-image { width: 35px; height: 35px; }
            .logo-text { font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .logo-subtext { font-size: 0.65rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            nav { flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 100%; }
            nav ul { flex-direction: row; flex-wrap: nowrap; align-items: center; padding: 0; margin: 0; gap: 5px; justify-content: center; }
            nav a { flex-direction: column; align-items: center; text-align: center; padding: 5px 3px; gap: 2px; font-size: 0.7rem; }
            nav a i { font-size: 1.1rem; }
            .edit-program-section { margin: 20px; padding: 15px; max-width: 100%; }
            .edit-program-card { padding: 1.5rem; }
            .form-group { flex: 1 1 100%; }
            .edit-program-form input, .edit-program-form textarea { padding: 10px 15px; font-size: 0.9rem; }
            .edit-program-btn, .back-btn { padding: 12px; font-size: 1rem; }
        }

        @media (max-width: 576px) {
            nav ul { flex-wrap: wrap; justify-content: flex-start; }
            .edit-program-card h2 { font-size: 1.5rem; }
            .form-group label { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="beta-banner">إطلاق تجريبي</div>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="https://i.postimg.cc/sxNCrL6d/logo-white-03.png" alt="شعار" class="logo-image">
                <div>
                    <div class="logo-text">دليل البرامج الصيفية</div>
                    <div class="logo-subtext">للفتيات في مدينة الرياض 1447هـ</div>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="https://whatsapp.com/channel/0029VahQ1kvLI8YTd9OMQl35" target="_blank"><i class="fab fa-whatsapp"></i> قناة الواتساب</a></li>
                    <li><a href="#" id="telegram-link-placeholder"><i class="fab fa-telegram"></i> قناة التليجرام</a></li>
                    <li><a href="#" id="pdf-link-placeholder"><i class="fas fa-file-pdf"></i> تحميل الدليل (PDF)</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="edit-program-section">
        <div class="edit-program-card">
            <h2>تعديل البرنامج ✏️</h2>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <form method="POST" class="edit-program-form" id="edit-program-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> عنوان البرنامج</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($program['title'] ?? ''); ?>" placeholder="أدخل عنوان البرنامج" required>
                </div>
                <div class="form-group">
                    <label for="organizer"><i class="fas fa-user-tie"></i> اسم الجهة المنظمة</label>
                    <input type="text" id="organizer" name="organizer" value="<?php echo htmlspecialchars($program['organizer'] ?? ''); ?>" placeholder="أدخل اسم الجهة المنظمة" required>
                </div>
                <div class="form-group">
                    <label for="Direction"><i class="fas fa-map-signs"></i> المنطقة (Direction)</label>
                    <input type="text" id="Direction" name="Direction" value="<?php echo htmlspecialchars($program['Direction'] ?? ''); ?>" placeholder="مثال: شمال الرياض، شرق الرياض" required>
                </div>
                <div class="form-group">
                    <label for="start_date"><i class="fas fa-calendar"></i> تاريخ بدء البرنامج</label>
                    <input type="text" id="start_date" name="start_date" value="<?php echo htmlspecialchars($program['start_date'] ?? ''); ?>" placeholder="اختر تاريخ بدء البرنامج (مثال: 01/01/1447)" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> مكان إقامة البرنامج</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($program['location'] ?? ''); ?>" placeholder="أدخل مكان إقامة البرنامج" required>
                </div>
                <div class="form-group">
                    <label for="duration"><i class="fas fa-clock"></i> مدة البرنامج</label>
                    <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($program['duration'] ?? ''); ?>" placeholder="أدخل مدة البرنامج (مثال: أسبوع)" required>
                </div>
                <div class="form-group">
                    <label for="age_group"><i class="fas fa-users"></i> الفئة العمرية</label>
                    <input type="text" id="age_group" name="age_group" value="<?php echo htmlspecialchars($program['age_group'] ?? ''); ?>" placeholder="أدخل الفئة العمرية (مثال: 10-15)" required>
                </div>
                <div class="form-group">
                    <label for="price"><i class="fas fa-money-bill"></i> رسوم البرنامج</label>
                    <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($program['price'] ?? ''); ?>" placeholder="أدخل رسوم البرنامج (مثال: 500 أو مجاني)" required>
                </div>
                <div class="form-group">
                    <label for="registration_link"><i class="fas fa-link"></i> رابط التسجيل</label>
                    <input type="text" id="registration_link" name="registration_link" value="<?php echo htmlspecialchars($program['registration_link'] ?? ''); ?>" placeholder="أدخل رابط التسجيل أو نص مثل 'عبر الواتساب'">
                </div>
                <div class="form-group full-width">
                    <label for="description"><i class="fas fa-file-alt"></i> وصف مختصر للبرنامج</label>
                    <textarea id="description" name="description" placeholder="أدخل وصف مختصر للبرنامج" required><?php echo htmlspecialchars($program['description'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="edit-program-btn"><i class="fas fa-save"></i> حفظ التغييرات</button>
            </form>
            <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-right"></i> رجوع</a>
        </div>
    </section>

    <script>
        // ننتظر حتى يتم تحميل الصفحة بالكامل قبل تشغيل الكود
        document.addEventListener('DOMContentLoaded', function() {
            
            const debugInfo = <?php echo json_encode($debug); ?>;
            console.group('Edit Program Debug Info');
            console.log('Debug Data:', debugInfo);
            console.groupEnd();

            // أسماء الشهور الهجرية
            const hijriMonths = [
                'محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الثانية',
                'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'
            ];

            // أسماء الأيام
            const hijriDays = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

            // إنشاء التقويم الهجري
            function createHijriCalendar() {
                const calendar = document.createElement('div');
                calendar.className = 'hijri-calendar';
                calendar.style.cssText = `
                    position: absolute;
                    top: 100%;
                    right: 0;
                    background: white;
                    border: 2px solid var(--primary);
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                    z-index: 1002; /* تم رفع الطبقة لتظهر فوق كل العناصر الأخرى */
                    padding: 15px;
                    min-width: 300px;
                    display: none;
                    font-family: 'Tajawal', sans-serif;
                `;

                let displayYear = 1447; // Initialize to 1447 AH
                let displayMonth = 1;   // 1-based (Muharram)

                // Parse current value if exists
                const currentValue = <?php echo json_encode($program['start_date'] ?? null); ?>;
                if (currentValue && /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(currentValue)) {
                    const [day, month, year] = currentValue.split('/').map(Number);
                    displayYear = year;
                    displayMonth = month;
                }

                function updateCalendar() {
                    calendar.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <button type="button" class="nav-btn" data-action="prev-year">«</button>
                            <button type="button" class="nav-btn" data-action="prev-month">‹</button>
                            <div style="display: flex; gap: 5px;">
                                <select id="hijri-month-select" class="hijri-select"></select>
                                <select id="hijri-year-select" class="hijri-select"></select>
                            </div>
                            <button type="button" class="nav-btn" data-action="next-month">›</button>
                            <button type="button" class="nav-btn" data-action="next-year">»</button>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 15px;">
                            ${hijriDays.map(day => `<div style="text-align: center; font-weight: bold; color: var(--primary); padding: 6px; font-size: 0.8rem;">${day}</div>`).join('')}
                        </div>
                        <div id="calendar-days" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px;">
                        </div>
                        <div style="text-align: center; margin-top: 15px;">
                            <button type="button" id="close-calendar" style="background: var(--secondary); color: white; border: none; padding: 7px 18px; border-radius: 8px; cursor: pointer; font-size: 0.9rem;">إغلاق</button>
                        </div>
                    `;

                    const monthSelect = calendar.querySelector('#hijri-month-select');
                    const yearSelect = calendar.querySelector('#hijri-year-select');

                    hijriMonths.forEach((monthName, index) => {
                        const option = document.createElement('option');
                        option.value = index + 1;
                        option.textContent = monthName;
                        if ((index + 1) === displayMonth) option.selected = true;
                        monthSelect.appendChild(option);
                    });
                    monthSelect.addEventListener('change', (e) => {
                        displayMonth = parseInt(e.target.value);
                        updateCalendar();
                    });

                    for (let y = 1446; y <= 1448; y++) {
                        const option = document.createElement('option');
                        option.value = y;
                        option.textContent = y + 'هـ';
                        if (y === displayYear) option.selected = true;
                        yearSelect.appendChild(option);
                    }
                    yearSelect.addEventListener('change', (e) => {
                        displayYear = parseInt(e.target.value);
                        updateCalendar();
                    });

                    const daysContainer = calendar.querySelector('#calendar-days');
                    daysContainer.innerHTML = ''; // Clear previous days
                    const daysInMonth = (displayMonth === 12 || displayMonth === 11) ? 29 : 30;
                    
                    const referenceDate = new Date(2025, 5, 19); // 1 Muharram 1447 ≈ 19 June 2025
                    const daysSinceReference = (displayYear - 1447) * 354 + (displayMonth - 1) * 29.5;
                    const firstDayDate = new Date(referenceDate.getTime() + daysSinceReference * 24 * 60 * 60 * 1000);
                    const startDay = firstDayDate.getDay();

                    for (let i = 0; i < startDay; i++) {
                        daysContainer.innerHTML += '<div></div>';
                    }

                    for (let day = 1; day <= daysInMonth; day++) {
                        const dayElement = document.createElement('div');
                        dayElement.style.cssText = `
                            text-align: center; padding: 8px 4px; cursor: pointer; border-radius: 8px;
                            transition: all 0.2s ease; font-weight: 500; font-size: 0.9rem;
                        `;
                        dayElement.textContent = day;
                        dayElement.addEventListener('click', () => selectDate(displayYear, displayMonth, day));
                        dayElement.addEventListener('mouseover', () => {
                            dayElement.style.backgroundColor = 'var(--primary)';
                            dayElement.style.color = 'white';
                        });
                        dayElement.addEventListener('mouseout', () => {
                            dayElement.style.backgroundColor = '';
                            dayElement.style.color = '';
                        });
                        daysContainer.appendChild(dayElement);
                    }

                    calendar.querySelectorAll('.nav-btn').forEach(btn => {
                        btn.style.cssText = `
                            background: var(--primary); color: white; border: none; padding: 6px 10px;
                            border-radius: 8px; cursor: pointer; font-size: 1rem; transition: all 0.2s ease;
                        `;
                        btn.addEventListener('mouseover', () => btn.style.backgroundColor = '#7a1fc2');
                        btn.addEventListener('mouseout', () => btn.style.backgroundColor = 'var(--primary)');
                        btn.addEventListener('click', (e) => {
                            const action = e.target.dataset.action;
                            switch(action) {
                                case 'prev-year': displayYear--; break;
                                case 'next-year': displayYear++; break;
                                case 'prev-month':
                                    displayMonth--;
                                    if (displayMonth < 1) { displayMonth = 12; displayYear--; }
                                    break;
                                case 'next-month':
                                    displayMonth++;
                                    if (displayMonth > 12) { displayMonth = 1; displayYear++; }
                                    break;
                            }
                            updateCalendar();
                        });
                    });

                    calendar.querySelector('#close-calendar').addEventListener('click', () => {
                        calendar.style.display = 'none';
                    });

                    calendar.querySelectorAll('.hijri-select').forEach(sel => {
                        sel.style.cssText = `
                            padding: 5px 8px; border: 1px solid var(--primary); border-radius: 5px;
                            font-family: 'Tajawal', sans-serif; font-size: 0.9rem;`;
                    });
                }

                function selectDate(year, month, day) {
                    const hijriDateStr = `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`;
                    document.getElementById('start_date').value = hijriDateStr;
                    calendar.style.display = 'none';
                }

                updateCalendar();
                return calendar;
            }

            const dateInput = document.getElementById('start_date');
            
            if (dateInput) {
                const dateContainer = dateInput.parentElement;
                
                // التأكد من أن الحاوية لها موضع نسبي
                if (window.getComputedStyle(dateContainer).position === 'static') {
                    dateContainer.style.position = 'relative';
                }
                
                const hijriCalendar = createHijriCalendar();
                dateContainer.appendChild(hijriCalendar);

                dateInput.addEventListener('click', (e) => {
                    e.preventDefault();
                    hijriCalendar.style.display = hijriCalendar.style.display === 'block' ? 'none' : 'block';
                });

                document.addEventListener('click', (e) => {
                    if (!dateContainer.contains(e.target)) {
                        hijriCalendar.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
