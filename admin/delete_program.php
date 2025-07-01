<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];

if (!isset($_SESSION['admin_id'])) {
    $debug['auth_error'] = 'User not authenticated';
    header('Location: admin_login.php');
    exit;
}

if (!isset($_SESSION['permissions']['can_delete_programs']) || !$_SESSION['permissions']['can_delete_programs']) {
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

if (!isset($_GET['id'])) {
    $debug['id_missing'] = 'Program ID not provided';
    header('Location: dashboard.php');
    exit;
}

$program_id = $_GET['id'];
try {
    $stmt = $pdo->prepare("SELECT title FROM programs WHERE id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['program_fetched'] = !empty($program);
} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
    $debug['error'] = 'Database error: ' . $e->getMessage();
}

if (!$program) {
    $debug['program_not_found'] = 'Program ID: ' . $program_id;
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "فشل التحقق من الطلب 🚫";
        $debug['error'] = 'Invalid CSRF token';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
            $stmt->execute([$program_id]);
            $debug['program_deleted'] = true;
            unset($_SESSION['csrf_token']);
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
            $debug['error'] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حذف البرنامج 🗑️</title>
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

        .delete-program-section {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .delete-program-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .delete-program-card:hover {
            transform: scale(1.02);
        }

        .delete-program-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .delete-program-card h2::after {
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

        .confirmation-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: var(--dark);
        }

        .confirmation-message strong {
            color: var(--secondary);
        }

        .delete-program-form {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .delete-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: #e55a5a;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .cancel-btn {
            background: var(--dark);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .cancel-btn:hover {
            background: #343a40;
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

            .delete-program-section {
                margin: 20px;
                padding: 15px;
                max-width: 100%;
            }

            .delete-program-card {
                padding: 1.5rem;
            }

            .delete-program-form {
                flex-direction: column;
                gap: 0.5rem;
            }

            .delete-btn, .cancel-btn {
                padding: 12px;
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            nav ul {
                flex-wrap: wrap;
                justify-content: flex-start;
            }

            .delete-program-card h2 {
                font-size: 1.5rem;
            }

            .confirmation-message {
                font-size: 1rem;
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
                    <li><a href="#" id="pdf-link-placeholder"><i class="fas fa-file-pdf"></i> تحميل الدليل (PDF)</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="delete-program-section">
        <div class="delete-program-card">
            <h2>حذف البرنامج 🗑️</h2>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <p class="confirmation-message">هل أنت متأكد من حذف البرنامج <strong><?php echo htmlspecialchars($program['title']); ?></strong>؟ هذا الإجراء لا يمكن التراجع عنه.</p>
            <form method="POST" class="delete-program-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button type="submit" class="delete-btn"><i class="fas fa-trash"></i> تأكيد الحذف</button>
                <a href="dashboard.php" class="cancel-btn"><i class="fas fa-times"></i> إلغاء</a>
            </form>
        </div>
    </section>

    <script>
        const debugInfo = <?php echo json_encode($debug); ?>;
        console.group('Delete Program Debug Info');
        console.log('Debug Data:', debugInfo);
        console.groupEnd();

        document.querySelector('.delete-program-form').addEventListener('submit', function(e) {
            console.group('Delete Form Submission');
            console.log('Program ID:', <?php echo json_encode($program_id); ?>);
            console.log('CSRF Token:', document.querySelector('input[name="csrf_token"]').value);
            console.groupEnd();
        });
    </script>
</body>
</html>