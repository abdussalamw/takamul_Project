<?php
// change_password.php
// تفعيل عرض الأخطاء للتطوير
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'تغيير كلمة المرور';

// معالجة تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = null;
    $success = null;

    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب (CSRF).";
    } else {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "جميع الحقول مطلوبة 🚫";
        } elseif ($new_password !== $confirm_password) {
            $error = "كلمتا المرور الجديدتان غير متطابقتين 🚫";
        } elseif (strlen($new_password) < 8) {
            $error = "كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل 🚫";
        } else {
            try {
                // جلب كلمة المرور الحالية من قاعدة البيانات
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($current_password, $user['password'])) {
                    // تحديث كلمة المرور الجديدة
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->execute([$hashed_password, $_SESSION['admin_id']]);

                    $adminController->logAction('Change Own Password', "Changed password for user: " . $_SESSION['username']);
                    $adminController->setSuccessMessage("تم تغيير كلمة المرور الخاصة بك بنجاح! ✅");
                    $adminController->redirect('dashboard.php');
                } else {
                    $error = "كلمة المرور الحالية غير صحيحة 🚫";
                }
            } catch (PDOException $e) {
                $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
            }
        }
    }

    if ($error) {
        $adminController->setErrorMessage($error);
    }
}

$adminController->renderHeader($page_title);
?>

<section class="add-user-section">
    <div class="add-user-card">
        <h2><i class="fas fa-key"></i> تغيير كلمة المرور الشخصية</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 0.9rem;">يمكنك تحديث كلمة المرور لحسابك الشخصي (<?php echo htmlspecialchars($_SESSION['username']); ?>) من هنا.</p>
        
        <?php $adminController->renderMessages(); ?>
        
        <form method="POST" class="add-user-form" id="change-password-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
            
            <div class="form-group">
                <label for="current_password"><i class="fas fa-lock-open"></i> كلمة المرور الحالية</label>
                <input type="password" id="current_password" name="current_password" placeholder="أدخل كلمة المرور الحالية" required>
            </div>
            
            <div class="form-group">
                <label for="new_password"><i class="fas fa-lock"></i> كلمة المرور الجديدة</label>
                <input type="password" id="new_password" name="new_password" placeholder="أدخل كلمة المرور الجديدة (8 أحرف على الأقل)" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-check-double"></i> تأكيد كلمة المرور الجديدة</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="أكد كلمة المرور الجديدة" required>
            </div>
            
            <div class="form-actions">
                <a href="dashboard.php" class="add-user-btn back-btn"><i class="fas fa-arrow-right"></i> العودة</a>
                <button type="submit" class="add-user-btn"><i class="fas fa-save"></i> حفظ التغييرات</button>
            </div>
        </form>
    </div>
</section>

<?php
$adminController->renderFooter();
?>
