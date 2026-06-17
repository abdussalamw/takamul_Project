<?php
include_once 'includes/db_connect.php';
include_once 'includes/HijriDate.php';

// بناء استعلام SQL بناءً على الفلاتر
$where_clauses = [];
$params = [];

if (isset($_GET['region']) && $_GET['region'] !== 'الكل') {
    $where_clauses[] = "Direction = ?";
    $params[] = $_GET['region'];
}

if (isset($_GET['age_group']) && $_GET['age_group'] !== 'الكل') {
    if ($_GET['age_group'] === 'أخرى') {
        $where_clauses[] = "(age_group LIKE '%أخرى%' OR (age_group NOT LIKE '%متوسط%' AND age_group NOT LIKE '%ثانوي%' AND age_group NOT LIKE '%جامعي%' AND age_group NOT LIKE '%مافوق الجامعي%'))";
    } else {
        $where_clauses[] = "age_group LIKE ?";
        $params[] = "%" . $_GET['age_group'] . "%";
    }
}

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $where_clauses[] = "(title LIKE ? OR description LIKE ?)";
    $search_term = "%{$_GET['search']}%";
    $params[] = $search_term;
    $params[] = $search_term;
}

// دائماً نبدأ بفلتر البرامج المنشورة فقط (تُخفى المعلّقة والمرفوضة والغير منشورة)
$sql = "SELECT programs.*, COALESCE(organizers.name, programs.organizer) as organizer FROM programs LEFT JOIN organizers ON programs.organizer_id = organizers.id WHERE programs.status = 'published'";
if (!empty($where_clauses)) {
    $sql .= " AND " . implode(" AND ", $where_clauses);
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
        // التواريخ مخزنة الآن بصيغة ميلادية قياسية YYYY-MM-DD
        $sql .= " ORDER BY start_date ASC";
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

// جلب القيم الفريدة للفلاتر — من البرامج المتاحة للعرض فقط
$locations  = $pdo->query("SELECT DISTINCT Direction FROM programs WHERE status = 'published' AND Direction IS NOT NULL AND Direction != '' ORDER BY Direction")->fetchAll(PDO::FETCH_COLUMN);
$age_groups = $pdo->query("SELECT name_ar FROM age_groups ORDER BY sort_order")->fetchAll(PDO::FETCH_COLUMN);

// عدد البرامج المتاحة للعرض فقط
$total_stmt     = $pdo->query("SELECT COUNT(*) FROM programs WHERE status = 'published'");
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
</section>


<section class="filters-section">
    <h2 class="section-title">تصفية البرامج</h2>
    <form method="GET" class="filters-grid" id="filter-form">
        <div class="filter-group search-filter-group">
            <h3><i class="fas fa-search"></i> بحث عن برنامج</h3>
            <div class="search-box">
                <input type="text" name="search" placeholder="اكتب اسم البرنامج..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </div>
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
    </form>
</section>

<?php 
// تضمين ملف العرض الجزئي للبرامج
// هذا الملف سيعرض البيانات التي تم جلبها وإعدادها في هذا الملف (index.php)
include 'programs.php'; 
?>

<!-- Leaflet Map Library CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        var activeCardStyle = '<?php echo $site_settings['active_card_style'] ?? "0"; ?>';
        var activeView = localStorage.getItem('preferredView') || 'cards'; // 'cards', 'table', or 'map'
        var mapInstance = null;

        // دالة لتهيئة وتحديث الخريطة التفاعلية
        function initMap() {
            // إزالة نسخة الخريطة القديمة لتجنب أخطاء التهيئة المتكررة بعد AJAX
            if (mapInstance !== null) {
                try {
                    mapInstance.remove();
                } catch(e) {
                    console.error("Error removing Leaflet map instance:", e);
                }
                mapInstance = null;
            }

            var mapContainer = $('#map');
            if (mapContainer.length === 0) return;

            // جلب البيانات المصدرة كـ JSON من الصفحة
            var programsData = [];
            try {
                programsData = JSON.parse($('#map-programs-data').html() || '[]');
            } catch(e) {
                console.error("Error parsing map data JSON:", e);
                return;
            }

            if (programsData.length === 0) {
                mapContainer.html('<div style="text-align:center; padding: 50px; color: #777;">لا توجد برامج تطابق معايير البحث الحالية لعرضها على الخريطة.</div>');
                return;
            }

            // إنشاء الخريطة وتثبيت المركز الافتراضي على الرياض
            mapInstance = L.map('map').setView([24.7136, 46.6753], 11);

            // إضافة طبقة الخريطة من OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mapInstance);

            var bounds = [];

            // إضافة علامات للبرامج المتوفرة
            programsData.forEach(function(loc) {
                if (loc.lat && loc.lng) {
                    var latLng = [loc.lat, loc.lng];
                    bounds.push(latLng);

                    // إنشاء أيقونة نبض مخصصة باستخدام HTML و CSS (أكبر وتنبض)
                    var pulsatingIcon = L.divIcon({
                        className: 'pulsating-icon-container',
                        html: '<div class="pulse-ring"></div><div class="pulse-dot"></div>',
                        iconSize: [40, 40],
                        iconAnchor: [20, 20]
                    });

                    // رسم علامة تفاعلية بالنبض على الخريطة
                    var marker = L.marker(latLng, {
                        icon: pulsatingIcon
                    }).addTo(mapInstance);

                    // إعداد بطاقة منبثقة بتصميم أنيق يتوافق مع البطاقات الأساسية للموقع
                    var isFree = (loc.price == 0 || ['مجاناً', 'مجاني'].includes(String(loc.price).trim().toLowerCase()));
                    
                    var priceNum = parseFloat(loc.price);
                    var priceClean = (isNaN(priceNum)) ? loc.price : (priceNum == parseInt(priceNum) ? parseInt(priceNum) : priceNum);
                    var priceText = isFree ? 'مجاناً' : priceClean + ' ريال';
                    
                    var priceNotesHtml = '';
                    if (!isFree && loc.price_notes) {
                        priceNotesHtml = `<div class="popup-price-notes">${loc.price_notes}</div>`;
                    }
                    
                    var words = (loc.description || '').split(' ');
                    var shortDesc = words.slice(0, 15).join(' ') + (words.length > 15 ? '...' : '');

                    // استخدام نفس كلاسات التخصيص والأنماط المعينة للبطاقات
                    var popupContent = `
                        <div class="map-popup-card style-${activeCardStyle}">
                            <div class="popup-header">
                                <h3 class="popup-title">${loc.title}</h3>
                                <div class="popup-organizer">
                                    <i class="fas fa-building"></i>
                                    ${loc.organizer}
                                </div>
                            </div>
                            <div class="popup-body">
                                <div class="popup-details">
                                    <div class="popup-detail-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>${loc.location}</span>
                                    </div>
                                    <div class="popup-detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span>${loc.duration}</span>
                                    </div>
                                    <div class="popup-detail-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>البدء: ${loc.start_date}</span>
                                    </div>
                                    ${loc.end_date ? `
                                    <div class="popup-detail-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>الانتهاء: ${loc.end_date}</span>
                                    </div>` : ''}
                                    <div class="popup-detail-item" style="grid-column: span 2;">
                                        <i class="fas fa-user-friends"></i>
                                        <span>${loc.age_group}</span>
                                    </div>
                                </div>
                                <p class="popup-description">${shortDesc}</p>
                            </div>
                            <div class="popup-footer">
                                <div class="popup-price-box">
                                    <span class="popup-price-label">الرسوم</span>
                                    <span class="popup-price-value ${isFree ? 'free' : ''}">${priceText}</span>
                                    ${priceNotesHtml}
                                </div>
                                <a href="${loc.registration_link || '#'}" class="popup-register-btn" target="_blank" rel="noopener noreferrer">سجل الآن</a>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent, {
                        minWidth: 280,
                        maxWidth: 320
                    });
                }
            });

            // ضبط زووم الخريطة تلقائياً ليغطي جميع العلامات المعروضة
            if (bounds.length > 0) {
                mapInstance.fitBounds(bounds, { padding: [30, 30] });
            }
        }

        // تطبيق وعرض الوضع النشط
        function applyView() {
            // إخفاء كافة عروض الحاويات
            $('#programs-cards-view, #programs-table-view, #programs-map-view').hide();
            // إزالة الكلاس النشط من كافة الأزرار
            $('.view-toggle-btn').removeClass('active');

            if (activeView === 'cards') {
                $('#programs-cards-view').show();
                $('#show-cards-view-btn').addClass('active');
            } else if (activeView === 'table') {
                $('#programs-table-view').show();
                $('#show-table-view-btn').addClass('active');
            } else if (activeView === 'map') {
                $('#programs-map-view').show();
                $('#show-map-view-btn').addClass('active');
                initMap();
            }
        }

        // --- استخدام تفويض الأحداث للتبديل بين العروض الثلاثة ---
        $(document).on('click', '#show-cards-view-btn', function() {
            activeView = 'cards';
            localStorage.setItem('preferredView', activeView);
            applyView();
        });

        $(document).on('click', '#show-table-view-btn', function() {
            activeView = 'table';
            localStorage.setItem('preferredView', activeView);
            applyView();
        });

        $(document).on('click', '#show-map-view-btn', function() {
            activeView = 'map';
            localStorage.setItem('preferredView', activeView);
            applyView();
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

        // تهيئة الحالة الأولية عند تحميل الصفحة لأول مرة
        applyView();

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
                    // إعادة تطبيق العرض الحالي لضمان عدم ضياعه
                    applyView();
                },
                error: function() {
                    alert('حدث خطأ أثناء تحديث البرامج. يرجى المحاولة مرة أخرى.');
                    $('.programs-section').css('opacity', 1); // إعادة الشفافية عند حدوث خطأ
                }
            });
        }

        // تطبيق الفلاتر تلقائياً عند تغيير أي قائمة اختيار
        $(document).on('change', '#filter-form select', applyFilters);
        // منع الإرسال التقليدي للنموذج عند الضغط على زر "تطبيق الفلترة" أو زر البحث
        $(document).on('submit', '#filter-form', function(e) {
            e.preventDefault();
            clearTimeout(searchTimeout);
            applyFilters();
        });

        // البحث الفوري أثناء الكتابة (مع مؤقت لتجنب تكرار الطلبات)
        var searchTimeout = null;
        $(document).on('input', '#filter-form input[name="search"]', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 300); // إرسال الطلب بعد 300 ملي ثانية من التوقف عن الكتابة
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
