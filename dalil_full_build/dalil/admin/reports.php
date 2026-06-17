<?php
// reports.php - Enhanced Analytics & Reports Page

include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'التقارير والتحليلات';

$adminController->renderHeader($page_title);
$adminController->renderMessages();

// =============================================
// جلب البيانات من قاعدة البيانات
// =============================================
$reports_data  = $adminController->getReportsData();
$error         = $reports_data['error'];

$status_counts   = [];
$price_counts    = [];
$monthly_counts  = [];
$direction_chart = [];
$location_chart  = [];
$total_programs  = 0;
$total_published = 0;
$total_pending   = 0;
$total_free      = 0;

try {
    // --- إحصائيات عامة ---
    $total_programs  = (int) $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    $total_published = (int) $pdo->query("SELECT COUNT(*) FROM programs WHERE status='published'")->fetchColumn();
    $total_pending   = (int) $pdo->query("SELECT COUNT(*) FROM programs WHERE status='pending'")->fetchColumn();
    $total_free      = (int) $pdo->query("
        SELECT COUNT(*) FROM programs 
        WHERE price IS NULL OR TRIM(price)='' OR price=0 OR TRIM(LOWER(price))='مجاني'
    ")->fetchColumn();

    // --- توزيع الحالات ---
    $status_counts = $pdo->query("SELECT status, COUNT(*) AS count FROM programs GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

    // --- مجاني / برسوم ---
    $price_counts = $pdo->query("
        SELECT 
            CASE WHEN price IS NULL OR TRIM(price)='' OR price=0 OR TRIM(LOWER(price))='مجاني' THEN 'مجاني' ELSE 'برسوم' END AS price_category,
            COUNT(*) AS count
        FROM programs GROUP BY price_category
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // --- التسجيلات الشهرية (آخر 12 شهر) ---
    $monthly_counts = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
        FROM programs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month ORDER BY month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // --- توزيع الأقسام (أعلى 8) ---
    $direction_chart = $pdo->query("
        SELECT Direction AS label, COUNT(*) AS count 
        FROM programs WHERE Direction IS NOT NULL AND Direction!=''
        GROUP BY Direction ORDER BY count DESC LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    // --- توزيع الأماكن (أعلى 8) ---
    $location_chart = $pdo->query("
        SELECT location AS label, COUNT(*) AS count 
        FROM programs WHERE location IS NOT NULL AND location!=''
        GROUP BY location ORDER BY count DESC LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Silent fail
}

$total_paid = $total_programs - $total_free;
$publish_rate = $total_programs > 0 ? round(($total_published / $total_programs) * 100) : 0;
?>

<style>
/* ====== Reports Page Specific Styles ====== */

/* --- Summary KPI Cards --- */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 32px;
}

.kpi-card {
    background: white;
    border-radius: 18px;
    padding: 24px 22px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(229,231,235,0.6);
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 0 18px 0 100%;
    opacity: 0.08;
}

.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.1);
}

.kpi-card.indigo::before { background: #6366f1; }
.kpi-card.emerald::before { background: #10b981; }
.kpi-card.amber::before { background: #f59e0b; }
.kpi-card.teal::before { background: #14b8a6; }

.kpi-icon {
    width: 50px; height: 50px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.kpi-card.indigo .kpi-icon { background: rgba(99,102,241,0.12); color: #6366f1; }
.kpi-card.emerald .kpi-icon { background: rgba(16,185,129,0.12); color: #10b981; }
.kpi-card.amber .kpi-icon { background: rgba(245,158,11,0.12); color: #f59e0b; }
.kpi-card.teal .kpi-icon { background: rgba(20,184,166,0.12); color: #14b8a6; }

.kpi-value {
    font-size: 2.2rem;
    font-weight: 900;
    color: #1e293b;
    line-height: 1;
}

.kpi-label {
    font-size: 0.88rem;
    font-weight: 600;
    color: #64748b;
}

.kpi-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 99px;
    width: fit-content;
}
.kpi-badge.success { background: rgba(16,185,129,0.1); color: #059669; }
.kpi-badge.warning { background: rgba(245,158,11,0.1); color: #d97706; }
.kpi-badge.info    { background: rgba(99,102,241,0.1); color: #6366f1; }
.kpi-badge.teal    { background: rgba(20,184,166,0.1); color: #0d9488; }

/* --- Section Headers --- */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.section-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-title i {
    color: #6366f1;
    font-size: 1.1rem;
}

/* --- Charts Layout --- */
.charts-row-top {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

.charts-row-bottom {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 32px;
}

.chart-card {
    background: white;
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(229,231,235,0.6);
    transition: box-shadow 0.3s ease;
}
.chart-card:hover {
    box-shadow: 0 12px 35px rgba(99,102,241,0.08);
}

.chart-card h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chart-card h3 i { color: #6366f1; }

.chart-container {
    position: relative;
    height: 240px;
}

.chart-container.tall {
    height: 320px;
}

/* --- Export Bar --- */
.export-bar {
    background: linear-gradient(135deg, rgba(99,102,241,0.06), rgba(139,92,246,0.06));
    border: 1px solid rgba(99,102,241,0.15);
    border-radius: 16px;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.export-bar-text h4 {
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 2px;
}
.export-bar-text p {
    font-size: 0.88rem;
    color: #64748b;
}

.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 11px 22px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(16,185,129,0.3);
}
.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16,185,129,0.4);
    color: white;
}

/* --- Tables Section --- */
.tables-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 12px;
}

.report-card {
    background: white;
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(229,231,235,0.6);
}

.report-card h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.report-card h3 i { color: #6366f1; }

.table-responsive { overflow-x: auto; }

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.table thead th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 700;
    padding: 10px 14px;
    text-align: right;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
.table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}
.table tbody tr:hover { background: #f8fafc; }
.table tbody td {
    padding: 10px 14px;
    color: #1e293b;
    vertical-align: middle;
}

/* Progress bar in table */
.bar-cell { display: flex; align-items: center; gap: 8px; }
.mini-bar-bg {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 99px;
    overflow: hidden;
}
.mini-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
    transition: width 1s ease;
}

.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px; height: 24px;
    border-radius: 50%;
    font-size: 0.78rem;
    font-weight: 700;
}
.rank-1 { background: #fef3c7; color: #d97706; }
.rank-2 { background: #f1f5f9; color: #64748b; }
.rank-3 { background: #fef3c7; color: #92400e; }
.rank-n { background: #f8fafc; color: #94a3b8; }

/* No data placeholder */
.no-data {
    text-align: center;
    color: #94a3b8;
    font-size: 0.9rem;
    padding: 24px;
}

/* --- Responsive --- */
@media (max-width: 1100px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .charts-row-top { grid-template-columns: 1fr 1fr; }
    .charts-row-bottom { grid-template-columns: 1fr; }
    .tables-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 700px) {
    .kpi-grid, .charts-row-top, .charts-row-bottom, .tables-grid { grid-template-columns: 1fr; }
}
</style>

<section class="reports-section">
    <?php if ($error): ?>
        <div class="message error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- ====== شريط التصدير ====== -->
    <div class="export-bar">
        <div class="export-bar-text">
            <h4><i class="fas fa-chart-line"></i> لوحة التحليلات والتقارير</h4>
            <p>نظرة شاملة على إحصائيات البرامج والأداء العام</p>
        </div>
        <a href="export_report.php?type=excel" class="export-btn">
            <i class="fas fa-file-excel"></i> تصدير تقرير Excel الكامل
        </a>
    </div>

    <!-- ====== بطاقات KPI الملخصة ====== -->
    <div class="kpi-grid">
        <div class="kpi-card indigo">
            <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
            <div class="kpi-value"><?php echo number_format($total_programs); ?></div>
            <div class="kpi-label">إجمالي البرامج</div>
            <div class="kpi-badge info"><i class="fas fa-database"></i> قاعدة البيانات</div>
        </div>
        <div class="kpi-card emerald">
            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-value"><?php echo number_format($total_published); ?></div>
            <div class="kpi-label">البرامج المنشورة</div>
            <div class="kpi-badge success"><i class="fas fa-arrow-up"></i> <?php echo $publish_rate; ?>% من الإجمالي</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-icon"><i class="fas fa-clock"></i></div>
            <div class="kpi-value"><?php echo number_format($total_pending); ?></div>
            <div class="kpi-label">بانتظار المراجعة</div>
            <div class="kpi-badge warning"><i class="fas fa-hourglass-half"></i> تحتاج مراجعة</div>
        </div>
        <div class="kpi-card teal">
            <div class="kpi-icon"><i class="fas fa-gift"></i></div>
            <div class="kpi-value"><?php echo number_format($total_free); ?></div>
            <div class="kpi-label">البرامج المجانية</div>
            <div class="kpi-badge teal"><i class="fas fa-tag"></i> <?php echo $total_paid; ?> برسوم</div>
        </div>
    </div>

    <!-- ====== الصف الأول من المخططات ====== -->
    <div class="section-header">
        <div class="section-title"><i class="fas fa-chart-bar"></i> الرسوم البيانية التحليلية</div>
    </div>

    <div class="charts-row-top">
        <!-- مخطط حالات البرامج - Doughnut -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> توزيع حالات البرامج</h3>
            <div class="chart-container">
                <canvas id="statusDoughnutChart"></canvas>
            </div>
        </div>

        <!-- مخطط مجاني/مدفوع - Pie -->
        <div class="chart-card">
            <h3><i class="fas fa-dollar-sign"></i> البرامج المجانية مقابل المدفوعة</h3>
            <div class="chart-container">
                <canvas id="pricePieChart"></canvas>
            </div>
        </div>

        <!-- مخطط الأقسام - Bar أفقي -->
        <div class="chart-card">
            <h3><i class="fas fa-sitemap"></i> توزيع البرامج حسب الأقسام</h3>
            <div class="chart-container">
                <canvas id="directionBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ====== الصف الثاني من المخططات ====== -->
    <div class="charts-row-bottom">
        <!-- مخطط الإضافات الشهرية - Line -->
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> البرامج المضافة شهرياً (آخر 12 شهر)</h3>
            <div class="chart-container tall">
                <canvas id="monthlyLineChart"></canvas>
            </div>
        </div>

        <!-- مخطط الأماكن - Bar عمودي -->
        <div class="chart-card">
            <h3><i class="fas fa-map-marker-alt"></i> أبرز المواقع والأماكن</h3>
            <div class="chart-container tall">
                <canvas id="locationBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ====== جداول التفاصيل ====== -->
    <div class="section-header">
        <div class="section-title"><i class="fas fa-table"></i> جداول التفاصيل التحليلية</div>
    </div>

    <div class="tables-grid">
        <!-- جدول الجهات -->
        <div class="report-card">
            <h3><i class="fas fa-building"></i> الجهات المشاركة</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الجهة</th>
                            <th>عدد البرامج</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_org = !empty($reports_data['organizers']) ? max(array_column($reports_data['organizers'], 'count')) : 1;
                        foreach ($reports_data['organizers'] as $i => $organizer):
                            $rank = $i + 1;
                            $rank_class = $rank <= 3 ? "rank-{$rank}" : "rank-n";
                            $pct = $max_org > 0 ? round(($organizer['count'] / $max_org) * 100) : 0;
                        ?>
                        <tr>
                            <td><span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span></td>
                            <td><?php echo htmlspecialchars($organizer['organizer']); ?></td>
                            <td>
                                <div class="bar-cell">
                                    <span><?php echo $organizer['count']; ?></span>
                                    <div class="mini-bar-bg"><div class="mini-bar-fill" style="width:<?php echo $pct; ?>%"></div></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (empty($reports_data['organizers'])): ?>
                    <p class="no-data"><i class="fas fa-inbox"></i> لا توجد بيانات</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- جدول الأقسام -->
        <div class="report-card">
            <h3><i class="fas fa-map-signs"></i> الأقسام والاتجاهات</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>القسم</th>
                            <th>عدد البرامج</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_dir = !empty($reports_data['directions']) ? max(array_column($reports_data['directions'], 'count')) : 1;
                        foreach ($reports_data['directions'] as $i => $direction):
                            $rank = $i + 1;
                            $rank_class = $rank <= 3 ? "rank-{$rank}" : "rank-n";
                            $pct = $max_dir > 0 ? round(($direction['count'] / $max_dir) * 100) : 0;
                        ?>
                        <tr>
                            <td><span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span></td>
                            <td><?php echo htmlspecialchars($direction['Direction']); ?></td>
                            <td>
                                <div class="bar-cell">
                                    <span><?php echo $direction['count']; ?></span>
                                    <div class="mini-bar-bg"><div class="mini-bar-fill" style="width:<?php echo $pct; ?>%; background: linear-gradient(90deg,#14b8a6,#06b6d4)"></div></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (empty($reports_data['directions'])): ?>
                    <p class="no-data"><i class="fas fa-inbox"></i> لا توجد بيانات</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- جدول الأماكن -->
        <div class="report-card">
            <h3><i class="fas fa-map-marker-alt"></i> الأماكن والمواقع</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المكان</th>
                            <th>عدد البرامج</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_loc = !empty($reports_data['locations']) ? max(array_column($reports_data['locations'], 'count')) : 1;
                        foreach ($reports_data['locations'] as $i => $location):
                            $rank = $i + 1;
                            $rank_class = $rank <= 3 ? "rank-{$rank}" : "rank-n";
                            $pct = $max_loc > 0 ? round(($location['count'] / $max_loc) * 100) : 0;
                        ?>
                        <tr>
                            <td><span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span></td>
                            <td><?php echo htmlspecialchars($location['location']); ?></td>
                            <td>
                                <div class="bar-cell">
                                    <span><?php echo $location['count']; ?></span>
                                    <div class="mini-bar-bg"><div class="mini-bar-fill" style="width:<?php echo $pct; ?>%; background: linear-gradient(90deg,#f43f5e,#f97316)"></div></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (empty($reports_data['locations'])): ?>
                    <p class="no-data"><i class="fas fa-inbox"></i> لا توجد بيانات</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</section>

<!-- ====== Chart.js ====== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // --- إعدادات عامة لـ Chart.js ---
    Chart.defaults.font.family  = 'Tajawal, sans-serif';
    Chart.defaults.font.size    = 12;
    Chart.defaults.color        = '#64748b';
    Chart.defaults.plugins.tooltip.rtl = true;
    Chart.defaults.plugins.tooltip.titleAlign = 'right';
    Chart.defaults.plugins.tooltip.bodyAlign  = 'right';

    // ألوان الحالات
    const statusColors = {
        'pending':   '#f59e0b',
        'reviewed':  '#3b82f6',
        'published': '#10b981',
        'rejected':  '#ef4444'
    };
    const statusLabels = {
        'pending':   'بانتظار المراجعة',
        'reviewed':  'بانتظار النشر',
        'published': 'منشورة',
        'rejected':  'مرفوضة'
    };
    const palette = ['#6366f1','#14b8a6','#f43f5e','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#84cc16'];

    // ============================================================
    // 1. مخطط حالات البرامج – Doughnut
    // ============================================================
    const statusCtx = document.getElementById('statusDoughnutChart');
    if (statusCtx) {
        const statusData = <?php echo json_encode($status_counts ?: new stdClass()); ?>;
        const sLabels = Object.keys(statusData).map(k => statusLabels[k] || k);
        const sData   = Object.values(statusData);
        const sColors = Object.keys(statusData).map(k => statusColors[k] || '#ccc');

        if (sLabels.length > 0) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: sLabels,
                    datasets: [{
                        data: sData,
                        backgroundColor: sColors,
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 14, font: { weight: '700' }, usePointStyle: true, pointStyle: 'circle' } },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} برنامج`
                            }
                        }
                    }
                }
            });
        } else {
            statusCtx.parentElement.innerHTML = '<p class="no-data"><i class="fas fa-chart-pie"></i> لا توجد بيانات</p>';
        }
    }

    // ============================================================
    // 2. مخطط مجاني / مدفوع – Pie
    // ============================================================
    const priceCtx = document.getElementById('pricePieChart');
    if (priceCtx) {
        const priceData = <?php echo json_encode($price_counts ?: new stdClass()); ?>;
        const freeCount = priceData['مجاني'] || 0;
        const paidCount = priceData['برسوم'] || 0;

        if (freeCount > 0 || paidCount > 0) {
            new Chart(priceCtx, {
                type: 'pie',
                data: {
                    labels: ['مجانية', 'برسوم'],
                    datasets: [{
                        data: [freeCount, paidCount],
                        backgroundColor: ['#10b981', '#f43f5e'],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 14, font: { weight: '700' }, usePointStyle: true, pointStyle: 'circle' } },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} برنامج`
                            }
                        }
                    }
                }
            });
        } else {
            priceCtx.parentElement.innerHTML = '<p class="no-data"><i class="fas fa-chart-pie"></i> لا توجد بيانات</p>';
        }
    }

    // ============================================================
    // 3. مخطط توزيع الأقسام – Bar أفقي
    // ============================================================
    const dirCtx = document.getElementById('directionBarChart');
    if (dirCtx) {
        const dirData = <?php echo json_encode($direction_chart ?: []); ?>;
        const dirLabels = dirData.map(d => d.label);
        const dirCounts = dirData.map(d => parseInt(d.count));

        if (dirLabels.length > 0) {
            new Chart(dirCtx, {
                type: 'bar',
                data: {
                    labels: dirLabels,
                    datasets: [{
                        label: 'عدد البرامج',
                        data: dirCounts,
                        backgroundColor: palette.map(c => c + '22'),
                        borderColor: palette,
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(226,232,240,0.6)' } },
                        y: { grid: { display: false }, ticks: { font: { weight: '600', size: 11 } } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        } else {
            dirCtx.parentElement.innerHTML = '<p class="no-data"><i class="fas fa-sitemap"></i> لا توجد بيانات</p>';
        }
    }

    // ============================================================
    // 4. مخطط الإضافات الشهرية – Line
    // ============================================================
    const monthlyCtx = document.getElementById('monthlyLineChart');
    if (monthlyCtx) {
        const monthlyData = <?php echo json_encode($monthly_counts ?: []); ?>;
        const mLabels = monthlyData.map(m => m.month);
        const mCounts = monthlyData.map(m => parseInt(m.count));

        if (mLabels.length > 0) {
            const gradient = monthlyCtx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(99,102,241,0.25)');
            gradient.addColorStop(1, 'rgba(99,102,241,0.01)');

            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: mLabels,
                    datasets: [{
                        label: 'برامج مضافة',
                        data: mCounts,
                        borderColor: '#6366f1',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.45,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: '600' } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(226,232,240,0.6)' } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(30,41,59,0.9)',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} برنامج مضاف`
                            }
                        }
                    }
                }
            });
        } else {
            monthlyCtx.parentElement.innerHTML = '<p class="no-data"><i class="fas fa-chart-line"></i> لا توجد بيانات كافية</p>';
        }
    }

    // ============================================================
    // 5. مخطط الأماكن – Bar عمودي
    // ============================================================
    const locCtx = document.getElementById('locationBarChart');
    if (locCtx) {
        const locData = <?php echo json_encode($location_chart ?: []); ?>;
        const locLabels = locData.map(l => l.label);
        const locCounts = locData.map(l => parseInt(l.count));

        if (locLabels.length > 0) {
            new Chart(locCtx, {
                type: 'bar',
                data: {
                    labels: locLabels,
                    datasets: [{
                        label: 'عدد البرامج',
                        data: locCounts,
                        backgroundColor: [
                            'rgba(244,63,94,0.2)','rgba(249,115,22,0.2)','rgba(234,179,8,0.2)',
                            'rgba(34,197,94,0.2)','rgba(6,182,212,0.2)','rgba(99,102,241,0.2)',
                            'rgba(168,85,247,0.2)','rgba(236,72,153,0.2)'
                        ],
                        borderColor: ['#f43f5e','#f97316','#eab308','#22c55e','#06b6d4','#6366f1','#a855f7','#ec4899'],
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: '600', size: 10 }, maxRotation: 35 } },
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(226,232,240,0.6)' } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        } else {
            locCtx.parentElement.innerHTML = '<p class="no-data"><i class="fas fa-map-marker-alt"></i> لا توجد بيانات</p>';
        }
    }

});
</script>

<?php $adminController->renderFooter(); ?>