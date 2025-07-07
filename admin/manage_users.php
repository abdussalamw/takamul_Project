<?php
session_start();
// Enable error reporting for debugging during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db_connect.php';

// Security check: must be logged in and have permission to manage users
if (empty($_SESSION['admin_id']) || empty($_SESSION['permissions']['can_manage_users'])) {
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

$error = null;
$success = null;

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Handle permission update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_permissions'])) {
    // 1. CSRF Token Validation
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    $response = [];

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $response = ['status' => 'error', 'message' => 'فشل التحقق من الطلب (CSRF).'];
    // 2. Validate User ID
    } elseif (!($user_id_to_update = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT))) {
        $response = ['status' => 'error', 'message' => 'معرف المستخدم غير صالح.'];
    // 3. Prevent user from changing their own permissions to avoid self-lockout
    } elseif ($user_id_to_update == $_SESSION['admin_id']) {
        $response = ['status' => 'error', 'message' => 'لا يمكنك تعديل صلاحياتك الخاصة.'];
    } else {
        try {
            // Get all permission columns dynamically to build the query
            $perm_stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'can_%'");
            $permission_columns = $perm_stmt->fetchAll(PDO::FETCH_COLUMN);

            $update_parts = [];
            $params = [];

            foreach ($permission_columns as $perm_col) {
                $update_parts[] = "`$perm_col` = ?";
                $params[] = isset($_POST[$perm_col]) ? 1 : 0;
            }

            if (!empty($update_parts)) {
                $params[] = $user_id_to_update; // Add user ID for the WHERE clause
                $sql = "UPDATE users SET " . implode(', ', $update_parts) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $response = ['status' => 'success', 'message' => 'تم الحفظ بنجاح!'];
            }
        } catch (PDOException $e) {
            // In production, log the detailed error and show a generic message.
            $response = ['status' => 'error', 'message' => 'حدث خطأ في قاعدة البيانات.'];
        }
    }

    // If it's an AJAX request, output JSON and exit.
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // For non-AJAX, set the session messages and redirect or show on page
        if ($response['status'] === 'success') $success = $response['message'];
        else $error = $response['message'];
    }
}

// Handle status messages from GET parameters
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'user_deleted':
            $success = "تم حذف المستخدم بنجاح.";
            break;
        case 'self_delete_error':
            $error = "لا يمكنك حذف حسابك الخاص.";
            break;
    }
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all permission columns dynamically for table display
$perm_stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'can_%'");
$permission_columns = $perm_stmt->fetchAll(PDO::FETCH_COLUMN);

// Translation array for permission names for a user-friendly display
$permission_translations = [
    'can_manage_users' => 'إدارة المستخدمين',
    'can_add_programs' => 'إضافة برامج',
    'can_edit_programs' => 'تعديل برامج',
    'can_delete_programs' => 'حذف برامج',
    'can_manage_settings' => 'إدارة الإعدادات',
    'can_publish_programs' => 'نشر البرامج',
    'can_review_programs' => 'مراجعة البرامج',
];
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين 👥</title>
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Tajawal', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%); 
            color: var(--dark); 
            line-height: 1.6;
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
        .logo { display: flex; align-items: center; gap: 15px; }
        .logo-image { width: 60px; height: 60px; object-fit: contain; }
        .logo-text { font-size: 1.5rem; font-weight: 800; }
        .page-title-header { display: flex; align-items: center; font-size: 1.1rem; font-weight: 700; }
        .page-title-header i { margin-left: 10px; color: var(--accent); font-size: 1.2rem; }
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav a { color: white; text-decoration: none; font-weight: 500; padding: 10px 20px; border-radius: 30px; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        nav a:hover { background: rgba(255, 255, 255, 0.15); }
        .dashboard-section { width: 100%; max-width: 1200px; margin: 40px auto; padding: 20px; }
        .dashboard-card { background: white; border-radius: 20px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); padding: 2.5rem; text-align: right; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .dashboard-card h2 { color: var(--primary); font-size: 1.6rem; }
        .add-user-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--primary); color: white; padding: 12px 20px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; }
        .add-user-btn:hover { background: #7a1fc2; transform: translateY(-2px); }
        .table-responsive-wrapper { overflow-x: auto; border: 1px solid #ddd; border-radius: 10px; }
        .users-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .users-table thead th { background: var(--primary); color: white; padding: 12px 15px; text-align: center; }
        .users-table thead th:first-child { text-align: right; }
        .users-table tbody td { padding: 12px 15px; border-bottom: 1px solid #ddd; vertical-align: middle; text-align: center; }
        .users-table tbody td:first-child { text-align: right; }
        .users-table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .users-table tbody tr:hover { background-color: #e9ecef; }
        .save-btn { background: var(--success); color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s ease; }
        .save-btn:hover { background: #218838; transform: scale(1.05); }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
        .back-btn { display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }
        .back-btn i { margin-right: 5px; }
        .current-user-indicator {
            background-color: var(--accent);
            color: white;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 5px;
            margin-right: 8px;
            font-weight: bold;
        }
        /* Custom checkbox style */
        .permission-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }
        .permission-checkbox:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .permission-checkbox:checked::after {
            content: '✔';
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 14px;
        }
        .permission-checkbox:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        .action-cell { display: flex; gap: 10px; align-items: center; justify-content: center; }
        .delete-btn { background: var(--secondary); color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .delete-btn:hover { background: #e55a5a; transform: scale(1.05); }
        .action-placeholder {
            color: #aaa;
        }
        .toast-message {
            position: absolute;
            transform: translate(10px, -100%);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
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
                <i class="fas fa-users-cog"></i>
                <span>إدارة المستخدمين</span>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="dashboard-section">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h2><i class="fas fa-users-cog"></i> إدارة المستخدمين والصلاحيات</h2>
                <a href="add_user.php" class="add-user-btn"><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</a>
            </div>

            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

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
                            <!-- Using display: contents on the form makes the <tr> a valid child of <tbody> -->
                            <form method="POST" class="permissions-form" style="display: contents;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <?php if($is_current_user): ?>
                                            <span class="current-user-indicator">أنت</span>
                                        <?php endif; ?>
                                    </td>
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <?php foreach ($permission_columns as $perm_col): ?>
                                        <td><input type="checkbox" class="permission-checkbox" name="<?php echo $perm_col; ?>" id="<?php echo $perm_col . '_' . $user['id']; ?>" <?php if(!empty($user[$perm_col])) echo 'checked'; ?> <?php if($is_current_user) echo 'disabled'; ?>></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <div class="action-cell">
                                        <?php if (!$is_current_user): ?>
                                            <input type="hidden" name="update_permissions" value="1">
                                            <button type="submit" name="update_permissions" class="save-btn"><i class="fas fa-save"></i> حفظ</button>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ هذا الإجراء لا يمكن التراجع عنه وسيحذف المستخدم نهائياً.');"><i class="fas fa-trash"></i> حذف</a>
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
                const saveButton = form.querySelector('.save-btn');
                const originalButtonText = saveButton.innerHTML;
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                saveButton.disabled = true;

                const formData = new FormData(form);

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showToast(saveButton, data.message, data.status);
                })
                .catch(error => {
                    showToast(saveButton, 'حدث خطأ في الشبكة.', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveButton.innerHTML = originalButtonText;
                    saveButton.disabled = false;
                });
            });
        });

        function showToast(element, message, type) {
            const toast = document.createElement('div');
            toast.className = 'toast-message';
            toast.textContent = message;
            toast.style.backgroundColor = type === 'success' ? 'var(--success)' : 'var(--secondary)';
            toast.style.color = 'white';

            const actionCell = element.closest('.action-cell');
            actionCell.style.position = 'relative';
            actionCell.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translate(10px, -120%)';
            }, 10);

            setTimeout(() => { toast.remove(); }, 3000);
        }
    });
    </script>
</body>
</html>
