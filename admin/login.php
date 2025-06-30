<?php
// تعريف متغيرات الصفحة قبل تضمين الهيدر
$page_title = 'تسجيل دخول الأدمن 🔒';
$is_login_page = true; // لمنع إعادة التوجيه اللانهائية

// لا نضع show_header = false; لأننا نريد إظهار الهيدر
include 'includes/header.php'; 
include '../includes/db_connect.php';

$error = null; // Initialize error variable

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
<main class="admin-section user-form">
    <div class="admin-card">
        <!-- بناءً على طلبك، هذا هو الشعار داخل الصندوق، مع وجود الهيدر في الأعلى -->
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
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="كلمة المرور" required>
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
</main>

<?php include 'includes/footer.php'; ?>