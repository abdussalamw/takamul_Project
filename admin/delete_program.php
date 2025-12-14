<?php
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'حذف البرنامج';
$adminController->requirePermission('can_delete_programs');

$program_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$response = $adminController->handleDeleteProgram($program_id);

if (isset($response['success'])) {
    $adminController->setSuccessMessage($response['success']);
    $adminController->redirect('manage_programs.php'); // Redirect to manage programs after successful deletion
} elseif (isset($response['error'])) {
    $adminController->setErrorMessage($response['error']);
    $adminController->redirect('manage_programs.php'); // Redirect with error
}

// If it's a GET request and no error, display the confirmation form
$program = $response['program'] ?? null;
if (!$program) {
    $adminController->setErrorMessage("البرنامج غير موجود أو معرف غير صالح.");
    $adminController->redirect('manage_programs.php');
}

$adminController->renderHeader($page_title);
?>

<section class="delete-program-section">
    <div class="delete-program-card">
        <h2>حذف البرنامج 🗑️</h2>
        <?php $adminController->renderMessages(); ?>
        <p class="confirmation-message">هل أنت متأكد من حذف البرنامج <strong><?php echo htmlspecialchars($program['title']); ?></strong>؟ هذا الإجراء لا يمكن التراجع عنه.</p>
        <form method="POST" class="delete-program-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
            <button type="submit" class="delete-btn"><i class="fas fa-trash"></i> تأكيد الحذف</button>
            <a href="manage_programs.php" class="cancel-btn"><i class="fas fa-times"></i> إلغاء</a>
        </form>
    </div>
</section>

<?php
$adminController->renderFooter();
?>
