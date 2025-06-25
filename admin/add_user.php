<?php
// تعريف متغيرات الصفحة قبل تضمين الهيدر
$page_title = 'إضافة مستخدم جديد ➕';
include 'includes/header.php'; // سيقوم هذا الملف بالتحقق من تسجيل الدخول
include '../includes/db_connect.php'; // الاتصال بقاعدة البيانات بعد الهيدر

$debug = []; // لجمع معلومات التصحيح

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
?>
<main class="admin-section user-form">
        <div class="admin-card">
            <h2>إضافة مستخدم جديد ➕</h2>
            <?php 
            if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>";
            if (isset($success)) echo "<p class='success-message'><i class='fas fa-check-circle'></i> $success</p>";
            ?>
            <form method="POST" class="admin-form" id="add-user-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> اسم المستخدم</label>
                    <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="أكد كلمة المرور" required>
                </div>
                <button type="submit" class="add-user-btn">
                    <i class="fas fa-plus"></i> إضافة المستخدم
                </button>
            </form>
            <a href="dashboard.php" class="back-btn" style="margin: 20px auto 0;"><i class="fas fa-arrow-right"></i> رجوع للوحة التحكم</a>
        </div>
    </main>

<?php include 'includes/footer.php'; ?>