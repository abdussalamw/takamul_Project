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

// معالجة تغيير كلمة مرور مستخدم (عبر المشرف)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_user_password'])) {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $response = [];

    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $response = ['status' => 'error', 'message' => 'فشل التحقق من الطلب (CSRF).'];
    }
    elseif (!($user_id_to_change = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT))) {
        $response = ['status' => 'error', 'message' => 'معرف المستخدم غير صالح.'];
    }
    elseif (empty($_POST['new_password']) || empty($_POST['confirm_new_password'])) {
        $response = ['status' => 'error', 'message' => 'جميع الحقول مطلوبة.'];
    }
    elseif ($_POST['new_password'] !== $_POST['confirm_new_password']) {
        $response = ['status' => 'error', 'message' => 'كلمتا المرور غير متطابقتين.'];
    }
    elseif (strlen($_POST['new_password']) < 8) {
        $response = ['status' => 'error', 'message' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.'];
    }
    else {
        try {
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id_to_change]);

            // جلب اسم المستخدم للتسجيل
            $u_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $u_stmt->execute([$user_id_to_change]);
            $username = $u_stmt->fetchColumn();

            $adminController->logAction('Change User Password', "Changed password for user: " . $username);
            $response = ['status' => 'success', 'message' => 'تم تغيير كلمة المرور بنجاح!'];
        } catch (PDOException $e) {
            $response = ['status' => 'error', 'message' => 'حدث خطأ في قاعدة البيانات.'];
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        if ($response['status'] === 'success') {
            $adminController->setSuccessMessage($response['message']);
        } else {
            $adminController->setErrorMessage($response['message']);
        }
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
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($is_current_user): ?>
                                    <span class="current-user-indicator">أنت</span>
                                <?php endif; ?>
                            </td>
                            <?php foreach ($permission_columns as $perm_col): ?>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox"
                                               name="<?php echo $perm_col; ?>"
                                               <?php if (!$is_current_user) echo 'form="form-user-' . $user['id'] . '"'; ?>
                                               <?php if (!empty($user[$perm_col])) echo 'checked'; ?>
                                               <?php if ($is_current_user) echo 'disabled'; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                            <?php endforeach; ?>
                            <td>
                                <div class="action-cell">
                                    <?php if (!$is_current_user): ?>
                                        <form id="form-user-<?php echo $user['id']; ?>" method="POST" class="permissions-form" style="display: none;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="update_permissions" value="1">
                                        </form>
                                        <button type="submit" form="form-user-<?php echo $user['id']; ?>" class="save-btn" title="حفظ الصلاحيات"><i class="fas fa-save"></i> حفظ</button>
                                        <button type="button" class="password-btn action-btn open-pwd-modal" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" title="تغيير كلمة المرور"><i class="fas fa-key"></i> كلمة المرور</button>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="delete-btn" title="حذف المستخدم"
                                           onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ هذا الإجراء لا يمكن التراجع عنه وسيحذف المستخدم نهائيًا.');">
                                            <i class="fas fa-trash"></i> حذف
                                        </a>
                                    <?php else: ?>
                                        <span class="action-placeholder">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-right"></i> العودة إلى لوحة التحكم</a>
    </div>
</section>

<!-- Modal Window for Changing User Password -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-key"></i> تغيير كلمة المرور للمستخدم: <span id="modalUsername" style="color: var(--secondary);"></span></h3>
            <button type="button" class="close-modal-btn" id="closeModal">&times;</button>
        </div>
        <form id="changePasswordForm" method="POST" action="manage_users.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
            <input type="hidden" name="user_id" id="modalUserId" value="">
            <input type="hidden" name="change_user_password" value="1">
            
            <div class="form-group">
                <label for="modal_new_password"><i class="fas fa-lock"></i> كلمة المرور الجديدة</label>
                <input type="password" id="modal_new_password" name="new_password" placeholder="أدخل كلمة المرور الجديدة (8 أحرف على الأقل)" required>
            </div>
            
            <div class="form-group">
                <label for="modal_confirm_password"><i class="fas fa-check-double"></i> تأكيد كلمة المرور</label>
                <input type="password" id="modal_confirm_password" name="confirm_new_password" placeholder="أكد كلمة المرور الجديدة" required>
            </div>
            
            <div class="form-actions" style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="cancel-btn" id="cancelModal"><i class="fas fa-times"></i> إلغاء</button>
                <button type="submit" class="save-btn" id="savePasswordBtn"><i class="fas fa-save"></i> حفظ</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. AJAX for Permissions Form Submission
    const forms = document.querySelectorAll('.permissions-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.querySelector(`.save-btn[form="${form.id}"]`);
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
                toast.style.position = 'absolute';
                toast.style.background = data.status === 'success' ? 'var(--success)' : 'var(--danger)';
                toast.style.color = 'white';
                toast.style.padding = '6px 12px';
                toast.style.borderRadius = 'var(--radius-sm)';
                toast.style.fontSize = '0.8rem';
                toast.style.marginTop = '4px';
                toast.style.zIndex = '100';
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

    // 2. Modal Logic for Password Change
    const modal = document.getElementById('passwordModal');
    const modalUsername = document.getElementById('modalUsername');
    const modalUserId = document.getElementById('modalUserId');
    const changePasswordForm = document.getElementById('changePasswordForm');
    const newPwdInput = document.getElementById('modal_new_password');
    const confirmPwdInput = document.getElementById('modal_confirm_password');

    // Open Modal
    document.querySelectorAll('.open-pwd-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            
            modalUserId.value = userId;
            modalUsername.textContent = username;
            
            // Reset input values
            newPwdInput.value = '';
            confirmPwdInput.value = '';
            
            modal.classList.add('open');
        });
    });

    // Close Modal Function
    function closeModal() {
        modal.classList.remove('open');
    }

    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelModal').addEventListener('click', closeModal);

    // Close when clicking outside content card
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // 3. AJAX for Password Change Form Submission
    changePasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const saveBtn = document.getElementById('savePasswordBtn');
        const origText = saveBtn.innerHTML;
        
        // Front-end Validation
        if (newPwdInput.value !== confirmPwdInput.value) {
            alert('كلمتا المرور غير متطابقتين 🚫');
            return;
        }
        if (newPwdInput.value.length < 8) {
            alert('يجب أن تكون كلمة المرور 8 أحرف على الأقل 🚫');
            return;
        }

        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> حفظ...';
        saveBtn.disabled = true;

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(changePasswordForm)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                closeModal();
            }
        })
        .catch(err => {
            alert('حدث خطأ غير متوقع أثناء تغيير كلمة المرور.');
        })
        .finally(() => {
            saveBtn.innerHTML = origText;
            saveBtn.disabled = false;
        });
    });
});
</script>

<?php
$adminController->renderFooter();
?>
