<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "اسم المستخدم غير موجود 🚫";
        } else {
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "كلمة المرور غير صحيحة 🚫";
            }
        }
    } catch (PDOException $e) {
        $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول - دليل تكامل</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8a2be2;
            --secondary: #ff6b6b;
            --dark: #212529;
            --light-gray: #f1f5f9;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem 3rem;
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }
        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.5rem;
        }
        .login-logo .logo-image {
            width: 100px;
            height: 100px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }
        .login-logo .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        .login-logo .logo-subtext {
            font-size: 0.85rem;
            color: #666;
        }
        .login-card h2 {
            color: var(--dark);
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
            color: var(--secondary);
            background: #fff0f0;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .login-form {
            display: grid;
            gap: 1.2rem;
        }
        .form-group {
            position: relative;
        }
        .form-group i {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #aaa;
        }
        .login-form input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'Tajawal', sans-serif;
        }
        .login-form input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
        }
        .login-form input:focus + i {
            color: var(--primary);
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--dark);
            cursor: pointer;
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
            padding: 14px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
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
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="https://i.postimg.cc/sxNCrL6d/logo-white-03.png" alt="شعار" class="logo-image">
            <div class="logo-text">دليل البرامج الصيفية</div>
            <div class="logo-subtext">للفتيات في الرياض 1447هـ</div>
        </div>

        <h2>تسجيل دخول الأدمن</h2>
        <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
        <form method="POST" class="login-form" id="login-form">
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="اسم المستخدم" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="كلمة المرور" required>
                <i class="fas fa-lock"></i>
            </div>
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> تذكرني
                </label>
                <a href="#" class="forgot-password">نسيت كلمة المرور؟</a>
            </div>
            <button type="submit" class="login-btn">
                تسجيل الدخول
            </button>
        </form>
    </div>
</body>
</html>