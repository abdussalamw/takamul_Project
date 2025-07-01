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
    $total_programs = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    $total_sections = $pdo->query("SELECT COUNT(DISTINCT Direction) FROM programs WHERE Direction IS NOT NULL AND Direction != ''")->fetchColumn();
    $free_programs = $pdo->query("SELECT COUNT(*) FROM programs WHERE price = '0' OR LOWER(TRIM(price)) IN ('مجاني', 'مجاناً')")->fetchColumn();
    
    // Fetch distinct directions for the filter dropdown
    $directions = $pdo->query("SELECT DISTINCT Direction FROM programs WHERE Direction IS NOT NULL AND Direction != '' ORDER BY Direction")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $total_programs = $total_sections = $free_programs = 0; // Default values on error
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
    // Fetch programs including the direction, and order by it
    $stmt = $pdo->query("SELECT id, title, organizer, price, start_date, location, Direction, age_group, registration_link FROM programs ORDER BY id DESC");
    $all_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['query_executed'] = true;
    $debug['row_count'] = $stmt->rowCount();

} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
    $debug['error'] = 'Database error: ' . $e->getMessage();
    $all_programs = []; // Ensure it's an array on error
}
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

        .controls-container {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
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
            color: var(--secondary);
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
                <span>لوحة التحكم</span>
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
                <h2><i class="fas fa-cogs"></i> إدارة البرامج</h2>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <?php if (isset($_SESSION['permissions']['can_manage_users']) && $_SESSION['permissions']['can_manage_users']): ?>
                        <a href="manage_users.php" class="add-program-btn" style="background-color: var(--accent);"><i class="fas fa-users-cog"></i> إدارة المستخدمين</a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['permissions']['can_add_programs']) && $_SESSION['permissions']['can_add_programs']): ?>
                        <a href="add_program.php" class="add-program-btn"><i class="fas fa-plus"></i> إضافة برنامج جديد</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($status_message): ?>
                <div class="status-message <?php echo $status_type; ?>" id="status-box">
                    <?php echo htmlspecialchars($status_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                    <div class="stat-info">
                        <h4>إجمالي البرامج</h4>
                        <p><?php echo $total_programs; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-map-signs"></i></div>
                    <div class="stat-info">
                        <h4>عدد الأقسام</h4>
                        <p><?php echo $total_sections; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-gift"></i></div>
                    <div class="stat-info">
                        <h4>البرامج المجانية</h4>
                        <p><?php echo $free_programs; ?></p>
                    </div>
                </div>
            </div>

            <div class="controls-container">
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
                                <th data-sort="start_date">تاريخ البدء <i class="sort-icon fas fa-sort"></i></th>
                                <th data-sort="price">السعر <i class="sort-icon fas fa-sort"></i></th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_programs as $program): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($program['title']); ?></td>
                                    <td><?php echo htmlspecialchars($program['organizer']); ?></td>
                                    <td><?php echo htmlspecialchars($program['Direction']); ?></td>
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

            const searchInput = document.getElementById('program-search');
            const directionFilter = document.getElementById('direction-filter');
            const table = document.getElementById('programs-main-table');
            if (!table) return; // Exit if table not found

            const tbody = table.querySelector('tbody');
            const headers = table.querySelectorAll('thead th[data-sort]');

            // --- Filtering ---
            function applyFilters() {
                const searchText = searchInput.value.toLowerCase();
                const selectedDirection = directionFilter.value;
                const rows = tbody.querySelectorAll('tr');

                rows.forEach(row => {
                    const title = row.cells[0].textContent.toLowerCase();
                    const organizer = row.cells[1].textContent.toLowerCase();
                    const direction = row.cells[2].textContent;

                    const searchMatch = title.includes(searchText) || organizer.includes(searchText);
                    const directionMatch = selectedDirection === '' || direction === selectedDirection;

                    if (searchMatch && directionMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if(searchInput) searchInput.addEventListener('keyup', applyFilters);
            if(directionFilter) directionFilter.addEventListener('change', applyFilters);

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
        });
    </script>
</body>
</html>
                }
            });
        });
    </script>
</body>
</html>
