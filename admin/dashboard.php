<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debug = [];

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    $debug['auth_error'] = 'User not authenticated';
    header('Location: admin_login.php');
    exit;
}

// --- Fetch Statistics ---
try {
    $total_sections = $pdo->query("SELECT COUNT(DISTINCT Direction) FROM programs WHERE Direction IS NOT NULL AND Direction != ''")->fetchColumn();
    $total_organizers = $pdo->query("SELECT COUNT(DISTINCT organizer) FROM programs WHERE organizer IS NOT NULL AND organizer != ''")->fetchColumn();
    
    // New stats for the review/publish workflow
    $pending_review = $pdo->query("SELECT COUNT(*) FROM programs WHERE status = 'pending'")->fetchColumn();
    $pending_publish = $pdo->query("SELECT COUNT(*) FROM programs WHERE status = 'reviewed'")->fetchColumn();
    $published_programs = $pdo->query("SELECT COUNT(*) FROM programs WHERE status = 'published'")->fetchColumn();
    
    // Fetch distinct directions for the filter dropdown
    $directions = $pdo->query("SELECT DISTINCT Direction FROM programs WHERE Direction IS NOT NULL AND Direction != '' ORDER BY Direction")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Default values on error
    $total_sections = $total_organizers = $pending_review = $pending_publish = $published_programs = 0;
}

// --- Helper function to render the status cell ---
function render_status_cell($program, $csrf_token) {
    // Determine if the current user can interact with the badge
    $is_interactive = false;
    if ($program['status'] === 'pending' && !empty($_SESSION['permissions']['can_review_programs'])) {
        $is_interactive = true;
    } elseif (($program['status'] === 'reviewed' || $program['status'] === 'published') && !empty($_SESSION['permissions']['can_publish_programs'])) {
        $is_interactive = true;
    }

    // Determine badge text and class
    $status_text = '';
    $status_class = '';
    switch ($program['status']) {
        case 'published': $status_text = 'منشور'; $status_class = 'status-published'; break;
        case 'pending': $status_text = 'قيد المراجعة'; $status_class = 'status-pending'; break;
        case 'rejected': $status_text = 'مرفوض'; $status_class = 'status-rejected'; break;
        case 'reviewed': $status_text = 'تمت المراجعة'; $status_class = 'status-reviewed'; break;
        default: $status_text = htmlspecialchars($program['status']); break;
    }

    ob_start(); // Start output buffering to capture the HTML
    ?>
    <div class="status-cell-wrapper" data-program-id="<?php echo $program['id']; ?>">
        <?php
        // Determine next status for the main badge click
        $next_status_badge = null;
        if ($program['status'] === 'pending') $next_status_badge = 'reviewed';
        if ($program['status'] === 'reviewed') $next_status_badge = 'published';
        if ($program['status'] === 'published') $next_status_badge = 'reviewed'; // Unpublish action

        // Determine next status for the reject/revert button
        $next_status_revert = null;
        $can_revert = false;
        // A user with either review or publish permission can interact with this button
        if (!empty($_SESSION['permissions']['can_review_programs']) || !empty($_SESSION['permissions']['can_publish_programs'])) {
            if (in_array($program['status'], ['reviewed', 'published'])) {
                $can_revert = true;
                $next_status_revert = 'pending'; // Action is to revert to pending
            } elseif ($program['status'] === 'pending') {
                $can_revert = true;
                $next_status_revert = 'rejected'; // Action is to reject
            }
        }

        if ($is_interactive && $next_status_badge):
        ?>
            <form method="POST" class="status-toggle-form" style="display: inline-block;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                <input type="hidden" name="new_status" value="<?php echo $next_status_badge; ?>">
                <button type="submit" class="status-badge <?php echo $status_class; ?> interactive" title="اضغط لتغيير الحالة">
                    <?php echo $status_text; ?>
                </button>
            </form>
        <?php else: ?>
            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
        <?php endif; ?>

        <?php if ($can_revert && $next_status_revert): ?>
            <form method="POST" class="status-toggle-form" style="display: inline-block;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                <input type="hidden" name="new_status" value="<?php echo $next_status_revert; ?>">
                <button type="submit" class="revert-btn" title="<?php echo ($next_status_revert === 'rejected') ? 'رفض البرنامج' : 'إعادة للمراجعة'; ?>">
                    <i class="fas fa-times-circle"></i>
                </button>
            </form>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean(); // Return the captured HTML
}

// --- Handle Quick Status Update via AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['new_status']))) {
    header('Content-Type: application/json');
    $response = [];

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $response = ['status' => 'error', 'message' => 'فشل التحقق من الطلب (CSRF).'];
    } elseif (!($program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT))) {
        $response = ['status' => 'error', 'message' => 'معرف البرنامج غير صالح.'];
    } else {
        try {
            $new_status = $_POST['new_status'];
            $allowed_statuses = ['published', 'reviewed', 'rejected', 'pending'];
            $error_permission = null;

            // More granular permission check
            if ($new_status == 'reviewed' && empty($_SESSION['permissions']['can_review_programs'])) $error_permission = "ليس لديك صلاحية مراجعة البرامج.";
            if ($new_status == 'published' && empty($_SESSION['permissions']['can_publish_programs'])) $error_permission = "ليس لديك صلاحية نشر البرامج.";
            if ($new_status == 'pending' && (empty($_SESSION['permissions']['can_review_programs']) && empty($_SESSION['permissions']['can_publish_programs']))) $error_permission = "ليس لديك صلاحية إعادة البرنامج للمراجعة.";
            if ($new_status == 'rejected' && empty($_SESSION['permissions']['can_review_programs'])) $error_permission = "ليس لديك صلاحية رفض البرامج.";

            if ($error_permission) {
                $response = ['status' => 'error', 'message' => $error_permission];
            } elseif (in_array($new_status, $allowed_statuses)) {
                $update_stmt = $pdo->prepare("UPDATE programs SET status = ? WHERE id = ?");
                $update_stmt->execute([$new_status, $program_id]);
                
                $program_stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
                $program_stmt->execute([$program_id]);
                $program = $program_stmt->fetch(PDO::FETCH_ASSOC);

                // Regenerate token for the next request
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $new_csrf_token = $_SESSION['csrf_token'];

                $response = [
                    'status' => 'success', 
                    'message' => 'تم تحديث الحالة بنجاح.', 
                    'new_html' => render_status_cell($program, $new_csrf_token),
                    'new_token' => $new_csrf_token
                ];
            } else {
                $response = ['status' => 'error', 'message' => 'حالة غير صالحة.'];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'error', 'message' => 'خطأ في قاعدة البيانات.'];
        }
    }
    echo json_encode($response);
    exit;
}

// --- Status Messages ---
$status_message = '';
$status_type = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'added') { $status_message = 'تمت إضافة البرنامج بنجاح!'; $status_type = 'success'; }
    if ($_GET['status'] === 'updated') { $status_message = 'تم تحديث البرنامج بنجاح!'; $status_type = 'success'; }
    if ($_GET['status'] === 'unauthorized') { $status_message = 'ليس لديك الصلاحية للوصول لهذه الصفحة!'; $status_type = 'error'; }
}

try {
    // Fetch all program data, and order by status first, then by ID
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY FIELD(status, 'pending', 'reviewed', 'published', 'rejected'), id DESC");
    $all_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['query_executed'] = true;
    $debug['row_count'] = $stmt->rowCount();

} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
    $debug['error'] = 'Database error: ' . $e->getMessage();
    $all_programs = []; // Ensure it's an array on error
}
?>
<?php
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf_token = $_SESSION['csrf_token'];
?>

<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن 🛠️</title>
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
        .status-badge { padding: 4px 10px; border-radius: 20px; color: white; font-weight: 600; font-size: 0.8rem; display: inline-block; text-align: center; min-width: 90px; border: none; }
        .status-badge.status-published { background-color: var(--success); }
        .status-badge.status-pending { background-color: #ffc107; color: var(--dark); }
        .status-badge.status-rejected { background-color: var(--secondary); }
        .status-badge.status-reviewed { background-color: var(--accent); color: white; }
        .status-badge.interactive { cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .status-badge.interactive:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
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

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-image {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo-subtext {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 30px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        nav a i {
            font-size: 1.8rem;
        }

        nav a:hover, nav a.active {
            background: rgba(255, 255, 255, 0.15);
        }

        .welcome-message {
            color: white;
            display: flex;
            align-items: center;
            font-weight: 500;
            padding: 10px 20px;
        }

        .page-title-header {
            color: white;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .page-title-header i {
            margin-left: 10px; /* مسافة بين الأيقونة والنص */
            color: var(--accent);
            font-size: 1.2rem;
        }

        .dashboard-section {
            width: 100%;
            max-width: 1400px; /* Increased width for better table view */
            margin: 40px auto;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            text-align: right;
            transform: scale(1);
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: scale(1.01);
        }

        .dashboard-card h2 {
            color: var(--primary);
            font-size: 1.6rem;
            text-align: right;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .dashboard-card h2::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: 0;
            transform: none;
            width: 50px;
            height: 3px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }

        .status-message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 10px;
            background: #fff0f0;
            border-radius: 8px;
            animation: slideIn 0.5s ease-out;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #f9f9f9, #fff);
            padding: 1.5rem;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary);
            background: rgba(138, 43, 226, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-info h4 {
            font-size: 1rem;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
        }
        .main-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .filter-actions { display: flex; flex-wrap: wrap; gap: 1.5rem; }


        .controls-container {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            flex-direction: column;
        }

        .search-bar-container, .filter-container {
            display: flex;
            align-items: center;
            background-color: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding-left: 15px;
            flex-grow: 1;
            transition: all 0.3s ease;
        }

        .search-bar-container:focus-within, .filter-container:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 8px rgba(138, 43, 226, 0.2);
        }

        .search-bar-container i, .filter-container i {
            color: #aaa;
        }

        #program-search, #direction-filter {
            width: 100%;
            padding: 12px 10px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
            background: transparent;
        }

        #direction-filter {
            cursor: pointer;
        }

        .add-program-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-program-btn:hover {
            background: #7a1fc2;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .table-responsive-wrapper {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .programs-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px; /* Ensures table layout is consistent before scrolling */
        }

        .programs-table thead th[data-sort] {
            cursor: pointer;
            position: relative;
        }

        .programs-table thead th[data-sort]:hover {
            background-color: #7a1fc2;
        }

        .sort-icon {
            margin-right: 8px;
            color: rgba(255, 255, 255, 0.5);
            transition: color 0.3s ease;
        }

        .programs-table thead th {
            background: var(--primary);
            color: white;
            font-weight: 700;
            padding: 12px 15px;
            text-align: right;
            transition: background-color 0.3s ease;
        }

        .programs-table tbody td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .programs-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .programs-table tbody tr:hover {
            background-color: #e9ecef;
        }

        .programs-table thead th[data-sort]:hover .sort-icon {
            color: white;
        }

        .programs-table thead th.sorted .sort-icon {
            color: var(--accent);
        }

        .action-links {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn.edit {
            background-color: var(--accent);
            color: white;
        }

        .action-btn.delete {
            background-color: var(--secondary);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                padding: 0 10px;
                gap: 5px;
            }

            header {
                padding: 5px 0;
                max-height: 20vh;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .logo {
                gap: 8px;
                flex-shrink: 1;
                min-width: 0;
                align-items: center;
            }

            .logo-image {
                width: 35px;
                height: 35px;
            }

            .logo-text {
                font-size: 1rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .logo-subtext {
                font-size: 0.65rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            nav {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
            }

            nav ul {
                flex-direction: row;
                flex-wrap: nowrap;
                align-items: center;
                padding: 0;
                margin: 0;
                gap: 5px;
                justify-content: center;
            }

            nav a {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 5px 3px;
                gap: 2px;
                font-size: 0.7rem;
            }

            nav a i {
                font-size: 1.1rem;
            }

            .dashboard-section {
                margin: 20px;
                padding: 15px;
            }

            .dashboard-card {
                padding: 20px;
            }
        }

        .toast-message { 
            position: fixed; 
            bottom: 20px; 
            left: 50%; 
            transform: translateX(-50%) translateY(100px); 
            background-color: var(--dark); 
            color: white; 
            padding: 12px 25px; 
            border-radius: 30px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); 
            z-index: 1001; 
            transition: transform 0.4s ease-in-out; 
            font-weight: 600; 
        }
        .toast-message.show { transform: translateX(-50%) translateY(0); }
        .toast-message.success { background-color: var(--success); }
        .toast-message.error { background-color: var(--secondary); }
        .status-cell-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .revert-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 2px 5px;
            transition: color 0.2s ease;
        }
        .revert-btn:hover {
            color: var(--secondary);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="https://i.postimg.cc/sxNCrL6d/logo-white-03.png" alt="شعار" class="logo-image">
                <div>
                    <div class="logo-text">دليل البرامج الصيفية</div>
                    <div class="logo-subtext">لوحة التحكم</div>
                </div>
            </div>
            <div class="page-title-header">
                <i class="fas fa-tachometer-alt"></i>
                <span>لوحة التحكم collapses</span>
            </div>
            <nav>
                <ul>
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="welcome-message">
                            <span>أهلاً بك, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li>
                            <a href="logout.php" title="تسجيل الخروج"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <section class="dashboard-section">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h2><i class="fas fa-cogs"></i> لوحة إدارة البرامج</h2>
            </div>

            <?php if ($status_message): ?>
                <div class="status-message <?php echo $status_type; ?>" id="status-box">
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #ffc107; background: rgba(255, 193, 7, 0.1);"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-info">
                        <h4>بانتظار المراجعة</h4>
                        <p><?php echo $pending_review; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent); background: rgba(78, 205, 196, 0.1);"><i class="fas fa-check-double"></i></div>
                    <div class="stat-info">
                        <h4>بانتظار النشر</h4>
                        <p><?php echo $pending_publish; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success); background: rgba(40, 167, 69, 0.1);"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h4>البرامج المنشورة</h4>
                        <p><?php echo $published_programs; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: #6f42c1; background: rgba(111, 66, 193, 0.1);"><i class="fas fa-building"></i></div>
                    <div class="stat-info">
                        <h4>عدد الجهات</h4>
                        <p><?php echo $total_organizers; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-map-signs"></i></div>
                    <div class="stat-info">
                        <h4>عدد الأقسام</h4>
                        <p><?php echo $total_sections; ?></p>
                    </div>
                </div>
            </div>

            <div class="controls-container">
                <div class="main-actions">
                    <?php if (isset($_SESSION['permissions']['can_add_programs']) && $_SESSION['permissions']['can_add_programs']): ?>
                        <a href="add_program.php" class="add-program-btn"><i class="fas fa-plus"></i> إضافة برنامج جديد</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['permissions']['can_add_programs']) && $_SESSION['permissions']['can_add_programs']): ?>
                        <a href="import.php" class="add-program-btn" style="background-color: #17a2b8;"><i class="fas fa-file-import"></i> استيراد من إكسل</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['permissions']['can_edit_programs']) && $_SESSION['permissions']['can_edit_programs']): ?>
                        <a href="export.php" class="add-program-btn" style="background-color: #20c997;"><i class="fas fa-file-export"></i> تصدير إلى إكسل</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['permissions']['can_manage_users']) && $_SESSION['permissions']['can_manage_users']): ?>
                        <a href="manage_users.php" class="add-program-btn" style="background-color: var(--accent);"><i class="fas fa-users-cog"></i> إدارة المستخدمين</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['permissions']['can_manage_settings']) && $_SESSION['permissions']['can_manage_settings']): ?>
                        <a href="site_settings.php" class="add-program-btn" style="background-color: var(--secondary);"><i class="fas fa-cogs"></i> إعدادات الموقع</a>
                    <?php endif; ?>
                    
                    <!-- New Map Button -->
                    <a href="../programs_map.php" target="_blank" class="add-program-btn" style="background-color: #fd7e14;"><i class="fas fa-map-marked-alt"></i> عرض الخريطة العامة</a>
                </div>
                <div class="filter-actions">
                    <div class="search-bar-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="program-search" placeholder="ابحث عن برنامج بالاسم أو الجهة المنظمة...">
                    </div>
                    <div class="filter-container">
                        <i class="fas fa-filter"></i>
                        <select id="direction-filter">
                            <option value="">كل الأقسام</option>
                            <?php foreach ($directions as $direction_option): ?>
                                <option value="<?php echo htmlspecialchars($direction_option); ?>"><?php echo htmlspecialchars($direction_option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-container">
                        <i class="fas fa-tasks"></i>
                        <select id="status-filter">
                            <option value="">كل الحالات</option>
                            <option value="pending">بانتظار المراجعة</option>
                            <option value="reviewed">بانتظار النشر</option>
                            <option value="published">البرامج المنشورة</option>
                            <option value="rejected">البرامج المرفوضة</option>
                        </select>
                    </div>
                </div>
            </div>

            <?php if (empty($all_programs)): ?>
                <p style="text-align: center; padding: 20px; font-style: italic; color: #888;">لا توجد برامج متاحة للعرض.</p>
            <?php else: ?>
                <div class="table-responsive-wrapper">
                    <table class="programs-table" id="programs-main-table">
                        <thead>
                            <tr>
                                <th data-sort="title">عنوان البرنامج <i class="sort-icon fas fa-sort"></i></th>
                                <th data-sort="organizer">الجهة المنظمة <i class="sort-icon fas fa-sort"></i></th>
                                <th data-sort="direction">القسم <i class="sort-icon fas fa-sort"></i></th>
                                <th data-sort="status">الحالة <i class="sort-icon fas fa-sort"></i></th>
                                <th data-sort="start_date">تاريخ البدء <i class="sort-icon fas fa-sort"></i></th>
                                <th data-sort="price">السعر <i class="sort-icon fas fa-sort"></i></th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_programs as $program): ?>
                                <tr data-program-id="<?php echo $program['id']; ?>" data-status="<?php echo $program['status']; ?>">
                                    <td><?php echo htmlspecialchars($program['title']); ?></td>
                                    <td><?php echo htmlspecialchars($program['organizer']); ?></td>
                                    <td><?php echo htmlspecialchars($program['Direction']); ?></td>
                                    <td class="status-cell"><?php echo render_status_cell($program, $csrf_token); ?></td>
                                    <td><?php echo htmlspecialchars($program['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($program['price']); ?></td>
                                    <td class="action-links">
                                        <?php if (isset($_SESSION['permissions']['can_edit_programs']) && $_SESSION['permissions']['can_edit_programs']): ?>
                                            <a href="edit_program.php?id=<?php echo $program['id']; ?>" class="action-btn edit"><i class="fas fa-edit"></i> تعديل</a>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['permissions']['can_delete_programs']) && $_SESSION['permissions']['can_delete_programs']): ?>
                                            <a href="delete_program.php?id=<?php echo $program['id']; ?>" class="action-btn delete" onclick="return confirm('هل أنت متأكد من الحذف؟ هذا الإجراء لا يمكن التراجع عنه.')"><i class="fas fa-trash"></i> حذف</a>
                                        <?php endif; ?>
                                        <?php if (empty($_SESSION['permissions']['can_edit_programs']) && empty($_SESSION['permissions']['can_delete_programs'])): ?>
                                            <span style="color: #999;">لا توجد صلاحيات</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        // Toast Notification Function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-message ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.classList.add('show'); }, 100);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 500); }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Hide status message after 5 seconds
            const statusBox = document.getElementById('status-box');
            if (statusBox) {
                setTimeout(() => {
                    statusBox.style.transition = 'opacity 0.5s ease';
                    statusBox.style.opacity = '0';
                    setTimeout(() => statusBox.style.display = 'none', 500);
                }, 5000);
            }

            const table = document.getElementById('programs-main-table');
            if (!table) return; // Exit if table not found
            const searchInput = document.getElementById('program-search');
            const directionFilter = document.getElementById('direction-filter');
            const statusFilter = document.getElementById('status-filter');

            const tbody = table.querySelector('tbody');
            const headers = table.querySelectorAll('thead th[data-sort]');

            // --- Filtering ---
            function applyFilters() {
                const searchText = searchInput.value.toLowerCase();
                const selectedDirection = directionFilter.value;
                const selectedStatus = statusFilter.value;
                const rows = tbody.querySelectorAll('tr');

                rows.forEach(row => {
                    const title = row.cells[0].textContent.toLowerCase();
                    const organizer = row.cells[1].textContent.toLowerCase();
                    const direction = row.cells[2].textContent;
                    const status = row.dataset.status;

                    const searchMatch = title.includes(searchText) || organizer.includes(searchText);
                    const directionMatch = selectedDirection === '' || direction === selectedDirection;
                    const statusMatch = selectedStatus === '' || status === selectedStatus;

                    if (searchMatch && directionMatch && statusMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if(searchInput) searchInput.addEventListener('keyup', applyFilters);
            if(directionFilter) directionFilter.addEventListener('change', applyFilters);
            if(statusFilter) statusFilter.addEventListener('change', applyFilters);

            // --- Sorting ---
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortOrder = header.dataset.order === 'desc' ? 'asc' : 'desc';
                    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
                    
                    // Reset other headers
                    headers.forEach(h => {
                        h.classList.remove('sorted');
                        h.dataset.order = '';
                        h.querySelector('.sort-icon').className = 'sort-icon fas fa-sort';
                    });

                    header.dataset.order = sortOrder;
                    header.classList.add('sorted');
                    header.querySelector('.sort-icon').className = `sort-icon fas fa-sort-${sortOrder === 'asc' ? 'up' : 'down'}`;

                    const rowsArray = Array.from(tbody.querySelectorAll('tr'));

                    rowsArray.sort((a, b) => {
                        let valA = a.cells[columnIndex].textContent.trim();
                        let valB = b.cells[columnIndex].textContent.trim();

                        // Special handling for price
                        if (header.dataset.sort === 'price') {
                            const parsePrice = (priceStr) => {
                                if (priceStr.toLowerCase().includes('مجاني')) return 0;
                                return parseFloat(priceStr.replace(/[^0-9.]/g, '')) || 0;
                            };
                            valA = parsePrice(valA);
                            valB = parsePrice(valB);
                        }
                        
                        // Special handling for date
                        if (header.dataset.sort === 'start_date') {
                            const parseDate = (dateStr) => {
                                const parts = dateStr.split('/'); // dd/mm/yyyy
                                if (parts.length === 3) {
                                    return new Date(parts[2], parts[1] - 1, parts[0]);
                                }
                                return new Date(0); // Invalid date
                            };
                            valA = parseDate(valA);
                            valB = parseDate(valB);
                        }

                        if (valA < valB) {
                            return sortOrder === 'asc' ? -1 : 1;
                        }
                        if (valA > valB) {
                            return sortOrder === 'asc' ? 1 : -1;
                        }
                        return 0;
                    });

                    // Re-append sorted rows
                    rowsArray.forEach(row => tbody.appendChild(row));
                });
            });

            // --- Interactive Status Badge AJAX ---
            // Use event delegation for dynamically added content
            tbody.addEventListener('submit', function(e) {
                if (e.target && e.target.classList.contains('status-toggle-form')) {
                    e.preventDefault();
                    const form = e.target;
                    const button = form.querySelector('button');
                    const originalContent = button.innerHTML;
                    
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    button.disabled = true;

                    const formData = new FormData(form);

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast(data.message, 'success');
                            const statusCellWrapper = form.closest('.status-cell-wrapper');
                            if (statusCellWrapper) {
                                // Replace the entire wrapper to update both buttons and their forms
                                statusCellWrapper.outerHTML = data.new_html;
                            }
                            // IMPORTANT: Update CSRF token on all other forms on the page for the next request
                            if (data.new_token) {
                                document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
                                    input.value = data.new_token;
                                });
                            }
                        } else {
                            showToast(data.message || 'حدث خطأ غير متوقع.', 'error');
                            button.innerHTML = originalContent;
                            button.disabled = false;
                        }
                    })
                    .catch(error => {
                        showToast('خطأ في الشبكة، يرجى المحاولة مرة أخرى.', 'error');
                        console.error('Error:', error);
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    });
                }
            });
        });
    </script>
</body>
</html>