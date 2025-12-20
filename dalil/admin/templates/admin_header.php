<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo $guide_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Submenu Styles */
        .sidebar-nav .has-submenu > a {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar-nav .submenu-arrow {
            transition: transform 0.3s ease;
            font-size: 0.8em;
            margin-left: auto; /* Pushes arrow to the left */
        }
        .sidebar-nav .has-submenu.open > a .submenu-arrow {
            transform: rotate(180deg);
        }
        .sidebar-nav .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: none;
            background-color: rgba(0, 0, 0, 0.15);
        }
        .sidebar-nav .submenu li a {
            padding-right: 45px; /* Indent submenu items */
            font-size: 0.95em;
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <img src="<?php echo $logo_path; ?>" alt="شعار" class="sidebar-logo">
            <h1 class="sidebar-title"><?php echo $guide_name; ?></h1>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>>
                        <i class="fas fa-tachometer-alt fa-fw"></i><span>لوحة التحكم</span>
                    </a>
                </li>
                <?php 
                // Check for any program-related permissions to show the main menu
                $can_manage_programs = !empty($_SESSION['permissions']['can_add_programs']) || 
                                       !empty($_SESSION['permissions']['can_edit_programs']) || 
                                       !empty($_SESSION['permissions']['can_review_programs']) || 
                                       !empty($_SESSION['permissions']['can_publish_programs']);
                if ($can_manage_programs): 
                ?>
                <?php
                    // Determine if the main menu item or any of its children are active
                    $program_pages = ['manage_programs.php', 'add_program.php', 'edit_program.php', 'import.php', 'export.php'];
                    $is_programs_active = in_array(basename($_SERVER['PHP_SELF']), $program_pages);
                ?>
                <li class="has-submenu <?php echo $is_programs_active ? 'open' : ''; ?>">
                    <a href="#" class="submenu-toggle <?php echo $is_programs_active ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt fa-fw"></i>
                        <span>إدارة البرامج</span>
                        <i class="fas fa-chevron-down submenu-arrow"></i>
                    </a>
                    <ul class="submenu" <?php echo $is_programs_active ? 'style="display: block;"' : ''; ?>>
                        <li>
                            <a href="manage_programs.php" <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['manage_programs.php', 'edit_program.php'])) ? 'class="active"' : ''; ?>>
                                <i class="fas fa-tasks fa-fw"></i><span>عرض وتعديل البرامج</span>
                            </a>
                        </li>
                        <?php if (!empty($_SESSION['permissions']['can_add_programs'])): ?>
                        <li>
                            <a href="add_program.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'add_program.php') ? 'class="active"' : ''; ?>>
                                <i class="fas fa-plus-circle fa-fw"></i><span>إضافة برنامج</span>
                            </a>
                        </li>
                        <li>
                            <a href="import.php" <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['import.php', 'export.php'])) ? 'class="active"' : ''; ?>>
                                <i class="fas fa-exchange-alt fa-fw"></i><span>استيراد / تصدير</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if (!empty($_SESSION['permissions']['can_manage_users'])): ?>
                <li>
                    <a href="manage_users.php" <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['manage_users.php', 'add_user.php'])) ? 'class="active"' : ''; ?>>
                        <i class="fas fa-users-cog fa-fw"></i><span>إدارة المستخدمين</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (!empty($_SESSION['permissions']['can_manage_settings'])): ?>
                <li>
                    <a href="site_settings.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'site_settings.php') ? 'class="active"' : ''; ?>>
                        <i class="fas fa-cogs fa-fw"></i><span>إعدادات الموقع</span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="reports.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'class="active"' : ''; ?>>
                        <i class="fas fa-chart-bar fa-fw"></i><span>التقارير</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
             <a href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i><span>تسجيل الخروج</span></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <div class="topbar-title">
                <h2><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'الصفحة الرئيسية'; ?></h2>
            </div>
            <div class="topbar-user">
                <i class="fas fa-user-circle"></i>
                <span>أهلاً بك, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </header>

        <!-- Page Content Start -->
        <div class="admin-content">
