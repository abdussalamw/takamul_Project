<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['permissions']['can_manage_users']) || !$_SESSION['permissions']['can_manage_users']) {
    // Redirect if not logged in or doesn't have permission
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب (CSRF).";
    } else {
        $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    $debug['input'] = ['username' => $username, 'password' => '[hidden]', 'confirm_password' => '[hidden]'];

    try {
        // Validate inputs
        if (empty($username) || empty($password) || empty($confirm_password)) {
            $error = "جميع الحقول مطلوبة 🚫";
            $debug['error'] = 'Missing required fields';
        } elseif ($password !== $confirm_password) {
            $error = "كلمتا المرور غير متطابقتين 🚫";
            $debug['error'] = 'Passwords do not match';
        } elseif (strlen($username) > 50) {
            $error = "اسم المستخدم طويل جدًا (50 حرفًا كحد أقصى) 🚫";
            $debug['error'] = 'Username too long';
        } elseif (strlen($password) < 8) {
            $error = "كلمة المرور يجب أن تكون 8 أحرف على الأقل 🚫";
            $debug['error'] = 'Password too short';
        } else {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?)");
            $stmt->execute([$username]);
            $debug['query_executed'] = true;
            $debug['username_exists'] = $stmt->rowCount() > 0;

            if ($stmt->rowCount() > 0) {
                $error = "اسم المستخدم موجود بالفعل 🚫";
                $debug['error'] = 'Username already exists';
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashed_password]);
                $debug['user_added'] = true;
                $debug['user_id'] = $pdo->lastInsertId();

                $success = "تم إضافة المستخدم بنجاح ✅";
            }
        }
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
    <title>إضافة مستخدم جديد ➕</title>
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
        }
        .page-title-header { display: flex; align-items: center; font-size: 1.1rem; font-weight: 700; }
        .page-title-header i { margin-left: 10px; color: var(--accent); font-size: 1.2rem; }
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
        nav a:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .add-user-section {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }
        .add-user-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .add-user-card:hover {
            transform: scale(1.01);
        }

        .add-user-card h2 {
            color: var(--primary); 
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .add-user-card h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
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
        .success-message {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--success);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 10px;
            background: #e6ffe6;
            border-radius: 8px;
            animation: slideIn 0.5s ease-out;
        }
        .add-user-form {
            display: grid;
            gap: 1.5rem;
        }
        .form-group {
            text-align: right;
        }
        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .form-group i {
            color: var(--primary);
        }

        .add-user-form input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'Tajawal', sans-serif;
            outline: none;
            transition: all 0.3s ease;
        }
        .add-user-form input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
        }

        .add-user-btn {
            background: var(--primary);
            color: white;
            border: none; 
            padding: 12px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .add-user-btn:hover {
            background: #7a1fc2;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        .form-actions .add-user-btn {
            flex-grow: 1;
        }
        .back-btn {
            background: #6c757d;
            flex-grow: 1;
            text-decoration: none;
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 576px) {
            .add-user-card h2 {
                font-size: 1.5rem;
            }
            .add-user-form input {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .add-user-btn {
                padding: 10px;
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
                <div class="logo-text">دليل البرامج الصيفية</div>
            </div>
            <div class="page-title-header">
                <i class="fas fa-user-plus"></i>
                <span>إضافة مستخدم جديد</span>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users-cog"></i> إدارة المستخدمين</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="add-user-section">
        <div class="add-user-card">
            <h2><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</h2>
            <?php 
            if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>";
            if (isset($success)) echo "<p class='success-message'><i class='fas fa-check-circle'></i> $success</p>";
            ?>
            <form method="POST" class="add-user-form" id="add-user-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> اسم المستخدم</label>
                    <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور (8 أحرف على الأقل)" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="أكد كلمة المرور" required>
                </div>
                <div class="form-actions">
                    <a href="manage_users.php" class="add-user-btn back-btn"><i class="fas fa-arrow-right"></i> العودة</a>
                    <button type="submit" class="add-user-btn"><i class="fas fa-plus-circle"></i> إضافة المستخدم</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        const debugInfo = <?php echo json_encode($debug); ?>;
        console.group('Add User Debug Info');
        console.log('Debug Data:', debugInfo);
        console.groupEnd();

        document.getElementById('add-user-form').addEventListener('submit', function(e) {
            console.group('Form Submission');
            console.log('Username:', document.getElementById('username').value);
            console.log('Password: [hidden]');
            console.log('Confirm Password: [hidden]');
            console.groupEnd();
        });
    </script>
</body>
</html>