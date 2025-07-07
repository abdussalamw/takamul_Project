<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $debug['input'] = ['username' => $username, 'password' => '[hidden]'];

    try {
        // Test database connection
        $debug['db_connection'] = $pdo ? 'Connected' : 'Failed';

        // Prepare and execute SQL query
        // Fetch all columns to dynamically load permissions
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)");
        $debug['query_prepared'] = $stmt ? true : false;

        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $debug['user_found'] = $user ? true : false;

        // To prevent timing attacks, always run password_verify.
        // If user is not found, we use a dummy hash.
        $password_hash = $user ? $user['password'] : '$2y$10$abcdefghijklmnopqrstuv'; // Dummy hash
        $password_match = password_verify($password, $password_hash);
        $debug['password_match'] = $password_match;

        if ($user && $password_match) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Dynamically load all permissions into the session
            $_SESSION['permissions'] = [];
            foreach ($user as $key => $value) {
                // If the column name starts with 'can_', treat it as a permission
                if (str_starts_with($key, 'can_')) {
                    $_SESSION['permissions'][$key] = (bool)$value;
                }
            }

            $debug['session_set'] = ['admin_id' => $user['id'], 'username' => $user['username'], 'permissions' => $_SESSION['permissions']];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "اسم المستخدم أو كلمة المرور غير صحيحة 🚫";
            $debug['error'] = 'Invalid credentials';
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
    <title>تسجيل دخول الأدمن 🔒</title>
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

        .login-section {
            max-width: 450px;
            margin: 80px auto; /* Increased margin from top */
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: scale(1.01);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .login-card h2 {
            color: var(--primary);
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .login-card h2::after {
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

        .login-form {
            display: grid;
            gap: 1rem;
        }

        .form-group {
            text-align: right;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .form-group i {
            color: var(--primary);
        }

        .login-form input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .login-form input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            margin: 10px 0;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--dark);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .login-btn {
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

        .login-btn:hover {
            background: #7a1fc2;
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
            .login-section {
                margin: 20px;
                padding: 15px;
            }

            .login-card {
                padding: 20px;
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
                <i class="fas fa-sign-in-alt"></i>
                <span>تسجيل الدخول</span>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> العودة للموقع</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="login-section">
        <div class="login-card">
            <h2>تسجيل دخول الأدمن 🔒</h2>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <form method="POST" class="login-form" id="login-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> اسم المستخدم</label>
                    <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                </div>
                <!-- تم إخفاء خيارات "تذكرني" و "نسيت كلمة المرور" لأنها غير مبرمجة بعد -->
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                </button>
            </form>
        </div>
    </section>

    <script>
        const debugInfo = <?php echo json_encode($debug); ?>;
        console.group('Login Debug Info');
        console.log('Debug Data:', debugInfo);
        console.groupEnd();

        document.getElementById('login-form').addEventListener('submit', function(e) {
            console.group('Form Submission');
            console.log('Username:', document.getElementById('username').value);
            console.log('Password: [hidden]');
            console.groupEnd();
        });
    </script>
</body>
</html>