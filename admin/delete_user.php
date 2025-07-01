<?php
session_start();
include '../includes/db_connect.php';

// Security: Must be logged in and have permission to manage users
if (empty($_SESSION['admin_id']) || empty($_SESSION['permissions']['can_manage_users'])) {
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

$error = null;
$user_id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// 1. Validate User ID from GET parameter
if (!$user_id_to_delete) {
    header('Location: manage_users.php');
    exit;
}

// 2. Prevent a user from deleting themselves
if ($user_id_to_delete == $_SESSION['admin_id']) {
    header('Location: manage_users.php?status=self_delete_error');
    exit;
}

// 3. Fetch user info to display on the confirmation page
try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id_to_delete]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        // User not found, redirect back
        header('Location: manage_users.php');
        exit;
    }
} catch (PDOException $e) {
    // In a production environment, you should log this error and show a generic message.
    die("Database error. Please contact support.");
}

// 4. Generate a CSRF token for the delete confirmation form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// 5. Handle the POST request to confirm deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب. يرجى المحاولة مرة أخرى.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id_to_delete]);
            
            // Regenerate token and redirect with a success message
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: manage_users.php?status=user_deleted');
            exit;
        } catch (PDOException $e) {
            $error = "حدث خطأ أثناء حذف المستخدم. قد يكون هذا المستخدم مرتبطًا ببيانات أخرى.";
        }
    }
}
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد حذف المستخدم 🗑️</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #8a2be2; --secondary: #ff6b6b; --dark: #212529; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            padding: 2.5rem 3rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            border-top: 5px solid var(--secondary);
        }
        .confirmation-card h2 {
            color: var(--dark);
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        .confirmation-card h2 i {
            color: var(--secondary);
        }
        .confirmation-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #555;
            line-height: 1.7;
        }
        .confirmation-message strong {
            color: var(--secondary);
            font-weight: 700;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .btn {
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-danger { background: var(--secondary); color: white; }
        .btn-danger:hover { background: #e55a5a; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <h2><i class="fas fa-exclamation-triangle"></i> تأكيد الحذف</h2>
        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <p class="confirmation-message">
            هل أنت متأكد من رغبتك في حذف المستخدم <strong><?php echo htmlspecialchars($user['username']); ?></strong>؟<br>
            هذا الإجراء لا يمكن التراجع عنه.
        </p>
        <form method="POST" class="form-actions">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> نعم، قم بالحذف</button>
            <a href="manage_users.php" class="btn btn-secondary"><i class="fas fa-times"></i> إلغاء</a>
        </form>
    </div>
</body>
</html>