<?php
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'إضافة مستخدم جديد';
$adminController->requirePermission('can_manage_users');

// Handle user addition logic
$response = $adminController->handleAddUser();

if (isset($response['success'])) {
    $adminController->setSuccessMessage($response['success']);
    $adminController->redirect('manage_users.php'); // Redirect to manage users after successful addition
} elseif (isset($response['error'])) {
    $adminController->setErrorMessage($response['error']);
}

$adminController->renderHeader($page_title);
?>

<section class="add-user-section">
    <div class="add-user-card">
        <h2><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</h2>
        <?php $adminController->renderMessages(); ?>
        <form method="POST" class="add-user-form" id="add-user-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
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

<?php
$adminController->renderFooter();
?>
