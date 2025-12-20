<?php
session_start();
include 'includes/db_connect.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $debug['input'] = ['username' => $username, 'password' => '[hidden]'];

    try {
        // Test database connection
        $debug['db_connection'] = $pdo ? 'Connected' : 'Failed';

        // Prepare and execute SQL query
        // Fetch all columns to dynamically load permissions
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)");
        $debug['query_prepared'] = $stmt ? true : false;

        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $debug['user_found'] = $user ? true : false;

        // To prevent timing attacks, always run password_verify.
        // If user is not found, we use a dummy hash.
        $password_hash = $user ? $user['password'] : '$2y$10$abcdefghijklmnopqrstuv'; // Dummy hash
        $password_match = password_verify($password, $password_hash);
        $debug['password_match'] = $password_match;

        if ($user && $password_match) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Dynamically load all permissions into the session
            $_SESSION['permissions'] = [];
            foreach ($user as $key => $value) {
                // If the column name starts with 'can_', treat it as a permission
                if (str_starts_with($key, 'can_')) {
                    $_SESSION['permissions'][$key] = (bool)$value;
                }
            }

            $debug['session_set'] = ['admin_id' => $user['id'], 'username' => $user['username'], 'permissions' => $_SESSION['permissions']];
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = "اسم المستخدم أو كلمة المرور غير صحيحة 🚫";
            $debug['error'] = 'Invalid credentials';
        }
    } catch (PDOException $e) {
        $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
        $debug['error'] = 'Database error: ' . $e->getMessage();
    }
}

// Load site settings for the header
$site_settings = load_site_settings($pdo);
$guide_name = htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية');
$logo_path = htmlspecialchars($site_settings['logo_path'] ?? 'https://i.postimg.cc/sxNCrL6d/logo-white-03.png');
if (!empty($site_settings['logo_path']) && file_exists($site_settings['logo_path'])) {
    $logo_path = $logo_path;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - <?php echo $guide_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="login-section">
        <div class="login-card">
            <h2>تسجيل دخول لوحة التحكم</h2>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <form method="POST" class="login-form" id="login-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> اسم المستخدم</label>
                    <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                    <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                </div>
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                </button>
            </form>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        const debugInfo = <?php echo json_encode($debug); ?>;
        console.group('Login Debug Info');
        console.log('Debug Data:', debugInfo);
        console.groupEnd();

        document.getElementById('login-form').addEventListener('submit', function(e) {
            console.group('Form Submission');
            console.log('Username:', document.getElementById('username').value);
            console.log('Password: [hidden]');
            console.groupEnd();
        });
    </script>
</body>
</html>
