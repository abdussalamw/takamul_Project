<?php
// manage_users.php

// تفعيل عرض الأخطاء للتطوير
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Initialization
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'إدارة المستخدمين';
$adminController->requirePermission('can_manage_users');

// معالجة حفظ الصلاحيات عبر AJAX أو POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_permissions'])) {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $response = [];

    // تحقق CSRF
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $response = ['status' => 'error', 'message' => 'فشل التحقق من الطلب (CSRF).'];
    }
    // تحقق من معرف المستخدم
    elseif (!($user_id_to_update = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT))) {
        $response = ['status' => 'error', 'message' => 'معرف المستخدم غير صالح.'];
    }
    // منع تعديل صلاحيات الحساب الحالي
    elseif ($user_id_to_update == $_SESSION['admin_id']) {
        $response = ['status' => 'error', 'message' => 'لا يمكنك تعديل صلاحياتك الخاصة.'];
    }
    else {
        try {
            // جلب أعمدة الصلاحيات ديناميكياً
            $perm_stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'can_%'");
            $permission_columns = $perm_stmt->fetchAll(PDO::FETCH_COLUMN);

            $update_parts = [];
            $params = [];

            foreach ($permission_columns as $perm_col) {
                $update_parts[] = "`$perm_col` = ?";
                $params[] = isset($_POST[$perm_col]) ? 1 : 0;
            }

            // تنفيذ التحديث
            $params[] = $user_id_to_update;
            $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $update_parts) . " WHERE id = ?");
            $stmt->execute($params);
            $response = ['status' => 'success', 'message' => 'تم الحفظ بنجاح!'];
        } catch (PDOException $e) {
            $response = ['status' => 'error', 'message' => 'حدث خطأ في قاعدة البيانات.'];
        }
    }
    // Handle response
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        $adminController->setSuccessMessage($response['message']);
        header('Location: manage_users.php');
        exit;
    }
}

// جلب جميع المستخدمين
$users = $pdo->query("SELECT * FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// جلب أعمدة الصلاحيات لعرضها في الجدول
$perm_stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'can_%'");
$permission_columns = $perm_stmt->fetchAll(PDO::FETCH_COLUMN);

// ترجمات أسماء صلاحيات المستخدم
$permission_translations = [
    'can_manage_users'    => 'إدارة المستخدمين',
    'can_add_programs'    => 'إضافة برامج',
    'can_edit_programs'   => 'تعديل برامج',
    'can_delete_programs' => 'حذف برامج',
    'can_manage_settings' => 'إدارة الإعدادات',
    'can_publish_programs'=> 'نشر البرامج',
    'can_review_programs' => 'مراجعة البرامج',
];

$adminController->renderHeader($page_title);

?>
<section class="dashboard-section">
    <div class="dashboard-card">
        <div class="dashboard-header">
            <h2><i class="fas fa-users-cog"></i> إدارة المستخدمين والصلاحيات</h2>
            <a href="add_user.php" class="add-user-btn"><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</a>
        </div>

        <?php $adminController->renderMessages(); ?>
        <div class="table-responsive-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>اسم المستخدم</th>
                        <?php foreach ($permission_columns as $perm_col): ?>
                            <th><?php echo htmlspecialchars($permission_translations[$perm_col] ?? ucfirst(str_replace(['can_', '_'], ['', ' '], $perm_col))); ?></th>
                        <?php endforeach; ?>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php $is_current_user = ($user['id'] == $_SESSION['admin_id']); ?>
                        <form method="POST" class="permissions-form" style="display: contents;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($is_current_user): ?>
                                        <span class="current-user-indicator">أنت</span>
                                    <?php endif; ?>
                                </td>
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <?php foreach ($permission_columns as $perm_col): ?>
                                    <td>
                                        <input type="checkbox"
                                               name="<?php echo $perm_col; ?>"
                                               <?php if (!empty($user[$perm_col])) echo 'checked'; ?>
                                               <?php if ($is_current_user) echo 'disabled'; ?>>
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <div class="action-cell">
                                        <?php if (!$is_current_user): ?>
                                            <button type="submit" name="update_permissions" class="save-btn"><i class="fas fa-save"></i> حفظ</button>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="delete-btn"
                                               onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ هذا الإجراء لا يمكن التراجع عنه وسيحذف المستخدم نهائيًا.');">
                                                <i class="fas fa-trash"></i> حذف
                                            </a>
                                        <?php else: ?>
                                            <span class="action-placeholder">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </form>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-right"></i> العودة إلى لوحة التحكم</a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.permissions-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('.save-btn');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
            .then(res => res.json())
            .then(data => {
                const cell = btn.closest('.action-cell');
                const toast = document.createElement('div');
                toast.className = 'toast-message';
                toast.textContent = data.message;
                cell.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            })
            .finally(() => {
                btn.innerHTML = orig;
                btn.disabled = false;
            });
        });
    });
});
</script>

<?php
$adminController->renderFooter();
?>
