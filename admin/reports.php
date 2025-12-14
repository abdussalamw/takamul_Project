<?php
// reports.php - Reports Page

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include dependencies
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

// Initialize controller
$adminController = new AdminController($pdo);
$page_title = 'التقارير';

// Render header and messages
$adminController->renderHeader($page_title);
$adminController->renderMessages();

// Fetch reports data
$reports_data = $adminController->getReportsData();
$error = $reports_data['error'];
?>

<section class="reports-section">
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="report-card">
        <h3><i class="fas fa-building"></i> الجهات المشاركة</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>الجهة</th>
                    <th>عدد البرامج</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports_data['organizers'] as $organizer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($organizer['organizer']); ?></td>
                        <td><?php echo htmlspecialchars($organizer['count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="report-card">
        <h3><i class="fas fa-map-signs"></i> الأقسام</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>القسم</th>
                    <th>عدد البرامج</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports_data['directions'] as $direction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($direction['Direction']); ?></td>
                        <td><?php echo htmlspecialchars($direction['count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="report-card">
        <h3><i class="fas fa-map-marker-alt"></i> الأماكن</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>المكان</th>
                    <th>عدد البرامج</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports_data['locations'] as $location): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($location['location']); ?></td>
                        <td><?php echo htmlspecialchars($location['count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
// Render footer
$adminController->renderFooter();
?>
