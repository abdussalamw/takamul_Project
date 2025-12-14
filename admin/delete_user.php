<?php
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'تأكيد حذف المستخدم';
$adminController->requirePermission('can_manage_users');

$user_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$response = $adminController->handleDeleteUser($user_id_to_delete);

if (isset($response['success'])) {
    $adminController->setSuccessMessage($response['success']);
    $adminController->redirect('manage_users.php'); // Redirect to manage users after successful deletion
} elseif (isset($response['error'])) {
    $adminController->setErrorMessage($response['error']);
    $adminController->redirect('manage_users.php'); // Redirect with error
}

// If it's a GET request and no error, display the confirmation form
$user = $response['user'] ?? null;
if (!$user) {
    $adminController->setErrorMessage("المستخدم غير موجود أو معرف غير صالح.");
    $adminController->redirect('manage_users.php');
}

$adminController->renderHeader($page_title);
?>

<section class="delete-user-section">
    <div class="confirmation-card">
        <h2><i class="fas fa-exclamation-triangle"></i> تأكيد الحذف</h2>
        <?php $adminController->renderMessages(); ?>
        <p class="confirmation-message">
            هل أنت متأكد من رغبتك في حذف المستخدم <strong><?php echo htmlspecialchars($user['username']); ?></strong>؟<br>
            هذا الإجراء لا يمكن التراجع عنه.
        </p>
        <form method="POST" class="form-actions">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> نعم، قم بالحذف</button>
            <a href="manage_users.php" class="btn btn-secondary"><i class="fas fa-times"></i> إلغاء</a>
        </form>
    </div>
</section>

<?php
$adminController->renderFooter();
?>
