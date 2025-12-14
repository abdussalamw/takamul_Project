<?php
// dashboard.php - لوحة التحكم الرئيسية

// تفعيل عرض الأخطاء للتطوير
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include dependencies
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

// Initialize controller and render header
$adminController = new AdminController($pdo);
$page_title = 'لوحة التحكم الرئيسية';
$adminController->renderHeader($page_title);
$adminController->renderMessages();

// تفعيل عرض الأخطاء للتطوير
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 4) جلب إحصائيات وبرامج
$error = '';
try {
    // إحصائيات عامة
    $total_programs    = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    $total_users       = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $status_counts     = $pdo->query("SELECT status, COUNT(*) AS count FROM programs GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
    $total_sections    = $pdo->query("SELECT COUNT(DISTINCT Direction) FROM programs WHERE Direction != ''")->fetchColumn();
    $total_organizers  = $pdo->query("SELECT COUNT(DISTINCT organizer) FROM programs WHERE organizer != ''")->fetchColumn();

    // إحصائيات الرسوم
    $price_counts = $pdo->query("
        SELECT 
            CASE 
                WHEN price IS NULL OR TRIM(price) = '' OR price = 0 OR TRIM(LOWER(price)) = 'مجاني' THEN 'مجاني'
                ELSE 'برسوم' 
            END as price_category, 
            COUNT(*) as count 
        FROM programs 
        GROUP BY price_category
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // إحصائيات الأقسام
    $direction_counts = $pdo->query("SELECT Direction, COUNT(*) as count FROM programs WHERE Direction IS NOT NULL AND Direction != '' GROUP BY Direction ORDER BY count DESC")->fetchAll(PDO::FETCH_KEY_PAIR);

    // بطاقة الإحصائيات
    $stats = [
        ['icon' => 'fas fa-tasks',          'label' => 'إجمالي البرامج',     'value' => $total_programs],
        ['icon' => 'fas fa-check-circle',   'label' => 'المنشورة',           'value' => $status_counts['published'] ?? 0],
        ['icon' => 'fas fa-check-double',   'label' => 'بانتظار النشر',      'value' => $status_counts['reviewed'] ?? 0],
        ['icon' => 'fas fa-hourglass-half', 'label' => 'قيد المراجعة',        'value' => $status_counts['pending']  ?? 0],
        ['icon' => 'fas fa-times-circle',   'label' => 'المرفوضة',           'value' => $status_counts['rejected'] ?? 0],
        ['icon' => 'fas fa-users',          'label' => 'المستخدمون',          'value' => $total_users],
        ['icon' => 'fas fa-building',       'label' => 'الجهات',             'value' => $total_organizers],
        ['icon' => 'fas fa-map-signs',      'label' => 'الأقسام',            'value' => $total_sections],
    ];

    // بيانات مخطط الحالات
    $translations = ['pending'=>'بانتظار المراجعة','reviewed'=>'بانتظار النشر','published'=>'منشورة','rejected'=>'مرفوض'];
    $colors = ['pending'=>'#ffc107','reviewed'=>'#4ecdc4','published'=>'#28a745','rejected'=>'#ff6b6b'];
    $chart_labels = [];
    $chart_data   = [];
    $chart_colors = [];
    foreach ($translations as $status => $translation) {
        $chart_labels[] = $translation;
        $chart_data[]   = $status_counts[$status] ?? 0;
        $chart_colors[] = $colors[$status];
    }

    // بيانات مخطط الرسوم
    $price_chart_labels = ['برامج مجانية', 'برامج برسوم'];
    $price_chart_data   = [$price_counts['مجاني'] ?? 0, $price_counts['برسوم'] ?? 0];
    $price_chart_colors = ['#4ecdc4', '#ff6b6b'];

    // بيانات مخطط الأقسام
    $direction_chart_labels = array_keys($direction_counts);
    $direction_chart_data   = array_values($direction_counts);

} catch (PDOException $e) {
    $error = 'خطأ في جلب البيانات.';
    $stats = [];
    $chart_labels = $chart_data = $chart_colors = [];
    $price_chart_labels = $price_chart_data = $price_chart_colors = [];
    $direction_chart_labels = $direction_chart_data = [];
}
?>

<section class="dashboard-section">
    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dashboard-cards">
        <?php foreach ($stats as $card): ?>
            <div class="dashboard-card">
                <div class="card-icon"><i class="<?php echo $card['icon']; ?>"></i></div>
                <div class="card-info">
                    <h3><?php echo htmlspecialchars($card['value']); ?></h3>
                    <p><?php echo htmlspecialchars($card['label']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="charts-section">
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> توزيع حالات البرامج</h3>
            <div class="chart-container">
                <canvas id="statusPieChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-dollar-sign"></i> البرامج المجانية والمدفوعة</h3>
            <div class="chart-container">
                <canvas id="priceDoughnutChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-map-marked-alt"></i> توزيع البرامج حسب الأقسام</h3>
            <div class="chart-container">
                <canvas id="directionBarChart"></canvas>
            </div>
        </div>
    </div>
</section>

<!-- تحميل مكتبة Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // مخطط حالات البرامج
    const ctx = document.getElementById('statusPieChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: <?php echo json_encode($chart_colors); ?>,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // مخطط البرامج المجانية والمدفوعة
    const priceCtx = document.getElementById('priceDoughnutChart');
    if (priceCtx) {
        new Chart(priceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($price_chart_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($price_chart_data); ?>,
                    backgroundColor: <?php echo json_encode($price_chart_colors); ?>,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // مخطط توزيع البرامج حسب الأقسام
    const directionCtx = document.getElementById('directionBarChart');
    if (directionCtx) {
        new Chart(directionCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($direction_chart_labels); ?>,
                datasets: [{
                    label: 'عدد البرامج',
                    data: <?php echo json_encode($direction_chart_data); ?>,
                    backgroundColor: 'rgba(138, 43, 226, 0.7)',
                    borderColor: 'var(--primary)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>

<?php
// Render footer
$adminController->renderFooter();
?>
