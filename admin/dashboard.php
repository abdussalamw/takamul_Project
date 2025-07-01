<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    $debug['auth_error'] = 'User not authenticated';
    header('Location: admin_login.php');
    exit;
}

try {
    // Fetch programs including the direction, and order by it
    $stmt = $pdo->query("SELECT id, title, organizer, price, start_date, location, Direction, age_group, registration_link FROM programs ORDER BY Direction, id DESC");
    $all_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['query_executed'] = true;
    $debug['row_count'] = $stmt->rowCount();

    // Group programs by direction
    $grouped_programs = [];
    foreach ($all_programs as $program) {
        // Use 'غير محدد' for programs with no direction
        $direction = !empty($program['Direction']) ? $program['Direction'] : 'غير محدد';
        $grouped_programs[$direction][] = $program;
    }
    $debug['programs_grouped'] = !empty($grouped_programs) ? array_keys($grouped_programs) : 'No groups';

} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
    $debug['error'] = 'Database error: ' . $e->getMessage();
    $grouped_programs = []; // Ensure it's an array on error
}
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن 🛠️</title>
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

        .dashboard-section {
            width: 100%;
            max-width: 1400px; /* Increased width for better table view */
            margin: 40px auto;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover {
            transform: scale(1.01);
        }

        .dashboard-card h2 {
            color: var(--primary);
            font-size: 1.6rem;
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .dashboard-card h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 50px;
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

        .add-program-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .add-program-btn:hover {
            background: #7a1fc2;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .direction-group {
            margin-bottom: 2.5rem;
        }

        .direction-heading {
            text-align: right;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-responsive-wrapper {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .programs-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px; /* Ensures table layout is consistent before scrolling */
        }

        .programs-table thead th {
            background: var(--primary);
            color: white;
            font-weight: 700;
            padding: 12px 15px;
            text-align: right;
        }

        .programs-table tbody td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .programs-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .programs-table tbody tr:hover {
            background-color: #e9ecef;
        }

        .action-links a {
            color: var(--primary);
            text-decoration: none;
            margin: 0 10px;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            white-space: nowrap;
        }

        .action-links a.delete {
            color: var(--secondary);
        }

        .action-links a:hover {
            text-decoration: underline;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--secondary);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
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

            .dashboard-section {
                margin: 20px;
                padding: 15px;
            }

            .dashboard-card {
                padding: 20px;
            }
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
                    <li><a href="/takamul/documents/دليل البرامج الصيفية للفتيات بمدينة الرياض1447.pdf" download><i class="fas fa-file-pdf"></i> تحميل الدليل (PDF)</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="dashboard-section">
        <div class="dashboard-card">
            <h2>لوحة تحكم الأدمن 🛠️</h2>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <a href="add_program.php" class="add-program-btn"><i class="fas fa-plus"></i> إضافة برنامج جديد</a>

            <?php if (empty($grouped_programs)): ?>
                <p style="text-align: center; padding: 20px; font-style: italic; color: #888;">لا توجد برامج متاحة للعرض.</p>
            <?php else: ?>
                <?php foreach ($grouped_programs as $direction => $programs_in_direction): ?>
                    <div class="direction-group">
                        <h3 class="direction-heading">
                            <i class="fas fa-map-signs"></i>
                            قسم: <?php echo htmlspecialchars($direction); ?> (<?php echo count($programs_in_direction); ?> برامج)
                        </h3>
                        <div class="table-responsive-wrapper">
                            <table class="programs-table">
                                <thead>
                                    <tr>
                                        <th>عنوان البرنامج</th>
                                        <th>الجهة المنظمة</th>
                                        <th>تاريخ البدء</th>
                                        <th>المكان</th>
                                        <th>الفئة العمرية</th>
                                        <th>السعر</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($programs_in_direction as $program): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($program['title']); ?></td>
                                            <td><?php echo htmlspecialchars($program['organizer']); ?></td>
                                            <td><?php echo htmlspecialchars($program['start_date']); ?></td>
                                            <td><?php echo htmlspecialchars($program['location']); ?></td>
                                            <td><?php echo htmlspecialchars($program['age_group']); ?></td>
                                            <td><?php echo htmlspecialchars($program['price']); ?></td>
                                            <td class="action-links">
                                                <a href="edit_program.php?id=<?php echo $program['id']; ?>"><i class="fas fa-edit"></i> تعديل</a>
                                                <a href="delete_program.php?id=<?php echo $program['id']; ?>" class="delete" onclick="return confirm('هل أنت متأكد من الحذف؟ هذا الإجراء لا يمكن التراجع عنه.')"><i class="fas fa-trash"></i> حذف</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

              <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
        </div>
    </section>

    <script>
        const debugInfo = <?php echo json_encode($debug); ?>;
        console.group('Dashboard Debug Info');
        console.log('Debug Data:', debugInfo);
        console.groupEnd();
    </script>
</body>
</html>
