<?php
include 'includes/db_connect.php';

// بناء استعلام SQL بناءً على الفلاتر
$where_clauses = [];
$params = [];

if (isset($_GET['region']) && $_GET['region'] !== 'الكل') {
    $where_clauses[] = "Direction = ?";
    $params[] = $_GET['region'];
}

if (isset($_GET['age_group']) && $_GET['age_group'] !== 'الكل') {
    $where_clauses[] = "age_group = ?";
    $params[] = $_GET['age_group'];
}

if (isset($_GET['duration']) && $_GET['duration'] !== 'الكل') {
    $where_clauses[] = "duration = ?";
    $params[] = $_GET['duration'];
}

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $where_clauses[] = "(title LIKE ? OR description LIKE ?)";
    $search_term = "%{$_GET['search']}%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql = "SELECT * FROM programs";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// الفرز
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'الأقرب تاريخاً';
switch ($sort) {
    case 'الأقل سعراً':
        $sql .= " ORDER BY CAST(REGEXP_REPLACE(price, '[^0-9.]', '') AS DECIMAL)";
        break;
    case 'الأعلى سعراً':
        $sql .= " ORDER BY CAST(REGEXP_REPLACE(price, '[^0-9.]', '') AS DECIMAL) DESC";
        break;
    case 'الأقرب تاريخاً':
    default:
        $sql .= " ORDER BY STR_TO_DATE(start_date, '%d/%m/%Y')";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$programs = $stmt->fetchAll();

// تجميع البرامج حسب القسم (للاستخدام في عرض الجدول)
$grouped_programs = [];
foreach ($programs as $program) {
    // استخدام 'غير محدد' للبرامج التي ليس لها قسم
    // FIXED: Changed 'direction' to 'Direction' to match the database column name case.
    $direction = !empty($program['Direction']) ? $program['Direction'] : 'غير محدد';
    $grouped_programs[$direction][] = $program;
}

// جلب القيم الفريدة للفلاتر
$locations = $pdo->query("SELECT DISTINCT Direction FROM programs WHERE Direction IS NOT NULL ORDER BY Direction")->fetchAll(PDO::FETCH_COLUMN);
$age_groups = $pdo->query("SELECT DISTINCT age_group FROM programs WHERE age_group IS NOT NULL ORDER BY age_group")->fetchAll(PDO::FETCH_COLUMN);
$durations = $pdo->query("SELECT DISTINCT duration FROM programs WHERE duration IS NOT NULL ORDER BY duration")->fetchAll(PDO::FETCH_COLUMN);

// استعلام لعدد البرامج الكلي
$total_stmt = $pdo->query("SELECT COUNT(*) FROM programs");
$total_programs = $total_stmt->fetchColumn();
?>
<?php
// إذا كان هذا طلب AJAX، قم فقط بإخراج قسم البرامج وتوقف.
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    include 'programs.php';
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<section class="hero">
    <h1>اكتشف أفضل البرامج الصيفية للفتيات في الرياض</h1>
    <p>استثمر صيفك في تطوير مهاراتك واكتساب خبرات جديدة مع مجموعة متنوعة من البرامج المتميزة في مختلف المجالات</p>
    <form class="search-box" method="GET">
        <input type="text" name="search" placeholder="ابحث عن برنامج..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit">بحث</button>
    </form>
</section>


<section class="filters-section">
    <h2 class="section-title">تصفية البرامج</h2>
    <form method="GET" class="filters-grid" id="filter-form">
                <div class="filter-group">
            <h3><i class="fas fa-sort-amount-down"></i> الترتيب حسب</h3>
            <select name="sort">
                <option value="الأقل سعراً" <?php echo $sort === 'الأقل سعراً' ? 'selected' : ''; ?>>الأقل سعراً</option>
                <option value="الأعلى سعراً" <?php echo $sort === 'الأعلى سعراً' ? 'selected' : ''; ?>>الأعلى سعراً</option>
                <option value="الأقرب تاريخاً" <?php echo $sort === 'الأقرب تاريخاً' ? 'selected' : ''; ?>>الأقرب تاريخاً</option>
            </select>
        </div>
        <div class="filter-group">
            <h3><i class="fas fa-map-marker-alt"></i> المنطقة</h3>
            <select name="region">
                <option value="الكل">الكل</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?php echo htmlspecialchars($location); ?>" <?php echo isset($_GET['region']) && $_GET['region'] == $location ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($location); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <h3><i class="fas fa-user"></i> الفئة العمرية</h3>
            <select name="age_group">
                <option value="الكل">الكل</option>
                <?php foreach ($age_groups as $age_group): ?>
                    <option value="<?php echo htmlspecialchars($age_group); ?>" <?php echo isset($_GET['age_group']) && $_GET['age_group'] == $age_group ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($age_group); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <h3><i class="fas fa-clock"></i> المدة</h3>
            <select name="duration">
                <option value="الكل">الكل</option>
                <?php foreach ($durations as $duration): ?>
                    <option value="<?php echo htmlspecialchars($duration); ?>" <?php echo isset($_GET['duration']) && $_GET['duration'] == $duration ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($duration); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (isset($_GET['search'])): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
        <?php endif; ?>
    </form>
</section>

<?php 
// تضمين ملف العرض الجزئي للبرامج
// هذا الملف سيعرض البيانات التي تم جلبها وإعدادها في هذا الملف (index.php)
include 'programs.php'; 
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        // --- استخدام تفويض الأحداث للمحتوى الديناميكي ---
        // هذا يضمن أن الأزرار ستعمل حتى بعد تحديث المحتوى بواسطة AJAX.

        // التبديل بين عرض البطاقات والجدول
        $(document).on('click', '#show-cards-view-btn', function() {
            $('#programs-cards-view').show();
            $('#programs-table-view').hide();
            // تأكد من أن كلا الزرين (إذا كانا مكررين) يتم تحديثهما
            $('#show-cards-view-btn').addClass('active');
            $('#show-table-view-btn').removeClass('active');
        });

        $(document).on('click', '#show-table-view-btn', function() {
            $('#programs-table-view').show();
            $('#programs-cards-view').hide();
            $('#show-table-view-btn').addClass('active');
            $('#show-cards-view-btn').removeClass('active');
        });

        // التحكم في "قراءة المزيد" / "عرض أقل"
        $(document).on('click', '.read-more', function(e) {
            e.preventDefault();
            $(this).hide().siblings('.read-less').show().siblings('.short-desc').hide().siblings('.full-desc').show();
        });

        $(document).on('click', '.read-less', function(e) {
            e.preventDefault();
            $(this).hide().siblings('.read-more').show().siblings('.full-desc').hide().siblings('.short-desc').show();
        });

        // --- فلترة AJAX الفورية ---

        function applyFilters() {
            var form = $('#filter-form');
            var formData = form.serialize();
            var newUrl = window.location.pathname + '?' + formData;

            // إضافة مؤشر لطلب AJAX
            var ajaxFormData = formData + '&ajax=1';

            // إظهار مؤشر تحميل لتحسين تجربة المستخدم
            $('.programs-section').css('opacity', 0.5);

            $.ajax({
                url: 'index.php', // إرسال الطلب لنفس الصفحة
                type: 'GET',
                data: ajaxFormData,
                success: function(response) {
                    // استبدال قسم البرامج بالكامل بالمحتوى الجديد القادم من الخادم
                    $('.programs-section').replaceWith(response);
                    // تحديث رابط المتصفح بدون إعادة تحميل الصفحة
                    history.pushState({path: newUrl}, '', newUrl);
                },
                error: function() {
                    alert('حدث خطأ أثناء تحديث البرامج. يرجى المحاولة مرة أخرى.');
                    $('.programs-section').css('opacity', 1); // إعادة الشفافية عند حدوث خطأ
                }
            });
        }

        // تطبيق الفلاتر تلقائياً عند تغيير أي قائمة اختيار
        $(document).on('change', '#filter-form select', applyFilters);
        // منع الإرسال التقليدي للنموذج عند الضغط على زر "تطبيق الفلترة"
        $(document).on('submit', '#filter-form', function(e) {
            e.preventDefault();
            applyFilters();
        });
    });
</script>

<!-- أنماط جديدة للجداول المقسمة -->
<style>
.direction-group {
    margin-bottom: 2.5rem;
}

.direction-heading {
    text-align: right;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 3px solid var(--primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-responsive-wrapper {
    overflow-x: auto;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.programs-table-public {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto; /* تم تعديل الهامش */
    border-collapse: collapse;
    font-size: 14px;
    min-width: 900px; /* يضمن تناسق شكل الجدول قبل التمرير */
}
.programs-table-public th, .programs-table-public td {
    padding: 10px;
    text-align: right;
    border: 1px solid #ddd;
    vertical-align: middle;
}
.programs-table-public th {
    background-color: #f8f9fa;
    font-weight: bold;
    white-space: nowrap;
}
.programs-table-public td {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
.programs-table-public tr:nth-child(even) {
    background-color: #f2f2f2;
}
.programs-table-public tr:hover {
    background-color: #e9ecef;
}
.register-btn-table {
    color: #fff;
    background-color: #007bff;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    white-space: nowrap;
}
.register-btn-table:hover {
    background-color: #0056b3;
}
.view-toggle-btn {
    padding: 10px 20px;
    margin: 0 5px;
    border: none;
    background-color: #f8f9fa;
    cursor: pointer;
    border-radius: 5px;
}
.view-toggle-btn.active {
    background-color: #007bff;
    color: #fff;
}
.read-more, .read-less {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
    margin-right: 5px;
}
.read-more:hover, .read-less:hover {
    text-decoration: underline;
}
.program-description {
    margin: 10px 0;
    line-height: 1.5;
}
</style>

<!-- أنماط جديدة لأزرار التبديل بين العرض -->
<style>
.programs-header {
    /* Ensure flex properties are set for alignment */
    display: flex;
    justify-content: space-between;
    align-items: center; /* Align items vertically */
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
    gap: 20px; /* Space between items */
    margin-bottom: 30px;
}

.view-controls {
    display: flex; /* Arrange buttons horizontally */
    gap: 10px; /* Space between buttons */
}

.view-toggle-btn {
    padding: 8px 15px; /* Padding similar to action-btn */
    border: 2px solid #e0e0e0; /* Default border */
    border-radius: 30px; /* Rounded corners */
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: white; /* Default background */
    color: var(--dark); /* Default text color */
    display: flex; /* Align icon and text */
    align-items: center;
    gap: 8px;
}

.view-toggle-btn:hover, .view-toggle-btn.active {
    background: var(--primary); /* Active/hover background */
    color: white; /* Active/hover text color */
    border-color: var(--primary); /* Active/hover border */
}

/* إخفاء أزرار التبديل على شاشات الجوال */
@media (max-width: 768px) {
    .view-controls {
        display: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
