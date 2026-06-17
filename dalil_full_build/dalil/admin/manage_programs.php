<?php
// manage_programs.php - صفحة إدارة البرامج (قسم الجدول فقط)

// Include dependencies
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'إدارة البرامج';
$csrf_token = $adminController->csrf_token;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من CSRF token
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'] ?? null)) {
        if (empty($_SESSION['error_message'])) {
            $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF).");
        }
    } elseif (isset($_POST['update_status'])) {
        // معالجة تغيير الحالة (قبول/نشر/رفض)
        $program_id = filter_var($_POST['program_id'] ?? 0, FILTER_VALIDATE_INT);
        $new_status = $_POST['new_status'] ?? '';
        $allowed_statuses = ['pending', 'reviewed', 'published', 'rejected'];
        
        if (!$program_id) {
            $adminController->setErrorMessage("معرف البرنامج غير صالح.");
        } elseif (!in_array($new_status, $allowed_statuses)) {
            $adminController->setErrorMessage("الحالة المطلوبة غير صالحة.");
        } else {
            // تحديد الصلاحية المطلوبة بناءً على مصدر الإجراء
            $action_source = $_POST['action_source'] ?? '';
            
            if ($action_source === 'publish') {
                // أي إجراء ياتي من عمود النشر (نشر أو عدم نشر) يتطلب can_publish_programs
                $required_perm = 'can_publish_programs';
            } elseif ($action_source === 'review') {
                // أي إجراء ياتي من عمود المراجعة (قبول أو رفض) يتطلب can_review_programs
                $required_perm = 'can_review_programs';
            } else {
                // الافتراضي: تحديد الصلاحية بناءً على الحالة المطلوبة
                $status_to_permission = [
                    'reviewed' => 'can_review_programs',
                    'rejected' => 'can_review_programs',
                    'pending'  => 'can_review_programs',
                    'published' => 'can_publish_programs',
                ];
                $required_perm = $status_to_permission[$new_status] ?? 'can_edit_programs';
            }
            
            if (empty($_SESSION['permissions'][$required_perm])) {
                $adminController->setErrorMessage("ليس لديك صلاحية لهذا الإجراء.");
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE programs SET status = ? WHERE id = ?");
                    $stmt->execute([$new_status, $program_id]);
                    $status_labels = [
                        'pending' => 'قيد المراجعة',
                        'reviewed' => 'مقبول',
                        'published' => 'منشور',
                        'rejected' => 'مرفوض'
                    ];
                    $label = $status_labels[$new_status] ?? $new_status;
                    $adminController->setSuccessMessage("تم تحديث حالة البرنامج إلى \"$label\" بنجاح ✅");
                    $adminController->logAction('Update Status', "Changed program #$program_id to '$new_status'");
                } catch (PDOException $e) {
                    $adminController->setErrorMessage("خطأ في قاعدة البيانات: " . $e->getMessage());
                }
            }
        }
    } elseif (isset($_POST['toggle_full_status'])) {
        // معالجة تبديل حالة التسجيل (متاح/غير متاح)
        $program_id = filter_var($_POST['program_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if (!$program_id) {
            $adminController->setErrorMessage("معرف البرنامج غير صالح.");
        } elseif (empty($_SESSION['permissions']['can_edit_programs'])) {
            $adminController->setErrorMessage("ليس لديك صلاحية لهذا الإجراء.");
        } else {
            try {
                // جلب الحالة الحالية
                $stmt = $pdo->prepare("SELECT is_full FROM programs WHERE id = ?");
                $stmt->execute([$program_id]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$current) {
                    $adminController->setErrorMessage("البرنامج غير موجود.");
                } else {
                    $new_full_status = $current['is_full'] ? 0 : 1;
                    $stmt = $pdo->prepare("UPDATE programs SET is_full = ? WHERE id = ?");
                    $stmt->execute([$new_full_status, $program_id]);
                    $status_text = $new_full_status ? 'غير متاح' : 'متاح';
                    $adminController->setSuccessMessage("تم تغيير حالة التسجيل إلى \"$status_text\" ✅");
                    $adminController->logAction('Toggle Registration', "Toggled program #$program_id registration to " . ($new_full_status ? 'full' : 'available'));
                }
            } catch (PDOException $e) {
                $adminController->setErrorMessage("خطأ في قاعدة البيانات: " . $e->getMessage());
            }
        }
    }
    // إعادة التوجيه لتجنب إعادة إرسال النموذج (PRG pattern)
    header('Location: manage_programs.php');
    exit;
}

// Render header
$adminController->renderHeader($page_title);
$adminController->renderMessages();

// دالة لعرض الأزرار بشكل موحد
function renderActionButton($program, $csrf_token, $permission, $condition, $next_status, $btn_class, $btn_text, $btn_icon, $btn_title, $action_name = 'update_status', $disabled = false, $source = '') {
    if ($condition && !empty($_SESSION['permissions'][$permission])) {
        ?>
        <form method="POST" style="display:inline-block;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
            <?php if ($action_name === 'update_status'): ?>
                <input type="hidden" name="new_status" value="<?php echo htmlspecialchars($next_status); ?>">
                <?php if (!empty($source)): ?>
                    <input type="hidden" name="action_source" value="<?php echo htmlspecialchars($source); ?>">
                <?php endif; ?>
            <?php endif; ?>
            <button type="submit" name="<?php echo htmlspecialchars($action_name); ?>" class="action-toggle-btn <?php echo htmlspecialchars($btn_class); ?>" title="<?php echo htmlspecialchars($btn_title); ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                <i class="fas <?php echo htmlspecialchars($btn_icon); ?>"></i> <?php echo htmlspecialchars($btn_text); ?>
            </button>
        </form>
        <?php
    } else {
        $status_text = '—';
        $status_class = '';
        if ($condition) {
            $status_text = $btn_text;
            $status_class = "status-$btn_class";
        }
        ?>
        <span class="static-status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($status_text); ?></span>
        <?php
    }
}

// معالجة البيانات والتحقق من الصلاحيات (يفترض أنها موجودة وصحيحة)
// جلب بيانات البرامج والأقسام (يفترض أن $programs و$directions تم تهيئتها)
$directions = $pdo->query("SELECT DISTINCT Direction FROM programs WHERE Direction IS NOT NULL AND Direction != '' ORDER BY Direction ASC")->fetchAll(PDO::FETCH_COLUMN);
$sql = "SELECT programs.*, organizers.name AS organizer_name FROM programs LEFT JOIN organizers ON programs.organizer_id = organizers.id ORDER BY programs.id DESC";
$programs = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'خطأ في جلب بيانات البرامج: ' . $e->getMessage();
}
?>



<section class="dashboard-section">
    <!-- رسائل الخطأ والنجاح (يفترض أنها موجودة) -->
    <div class="dashboard-card">
        <div class="dashboard-header">
            <h2><i class="fas fa-tasks"></i> إدارة البرامج</h2>
        </div>

        <!-- نموذج الفلاتر والبحث -->
        <div class="filters-container">
            <form id="filter-form" class="filter-form">
                <div class="filter-group">
                    <input type="text" id="search" placeholder="بحث بالاسم أو الجهة...">
                </div>
                <div class="filter-group">
                    <select id="status-filter">
                        <option value="">كل الحالات</option>
                        <option value="pending">غير مقبول (قيد المراجعة)</option>
                        <option value="reviewed">مقبول</option>
                        <option value="published">منشور</option>
                        <option value="rejected">مرفوض</option>
                        <option value="registration_available">التسجيل متاح</option>
                        <option value="registration_unavailable">التسجيل غير متاح</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="direction-filter">
                        <option value="">كل الأقسام</option>
                        <?php foreach ($directions as $direction): ?>
                            <option value="<?php echo htmlspecialchars($direction); ?>"><?php echo htmlspecialchars($direction); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="table-responsive-wrapper">
            <table id="programs-table" class="users-table">
                <thead>
                    <tr>
                        <th>العنوان</th>
                        <th>الجهة</th>
                        <th style="width: 110px;">المراجعة</th>
                        <th style="width: 110px;">النشر</th>
                        <th style="width: 110px;">التسجيل</th>
                        <th style="width: 80px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs)): ?>
                        <tr>
                            <td colspan="6" class="no-results">لا توجد برامج تطابق معايير البحث.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs as $program): ?>
                            <tr data-direction="<?php echo htmlspecialchars($program['Direction'] ?? ''); ?>" 
                                data-status="<?php echo htmlspecialchars($program['status']); ?>" 
                                data-registration="<?php echo $program['status'] === 'published' ? ($program['is_full'] ? 'unavailable' : 'available') : ''; ?>">
                                <td><?php echo htmlspecialchars($program['title']); ?></td>
                                <td><?php echo htmlspecialchars($program['organizer_name'] ?? $program['organizer']); ?></td>

                                <!-- عمود المراجعة: زر قبول/عدم قبول -->
                                <td class="action-cell">
                                    <?php
                                    if (!empty($_SESSION['permissions']['can_review_programs'])) {
                                        $is_accepted = in_array($program['status'], ['reviewed', 'published']);
                                        $next_status = $is_accepted ? 'rejected' : 'reviewed';
                                        $btn_class = $is_accepted ? 'rejected' : 'accepted';
                                        $btn_text = $is_accepted ? 'عدم قبول' : 'قبول';
                                        $btn_icon = $is_accepted ? 'fa-times' : 'fa-check';
                                        $btn_title = $is_accepted ? 'الحالة: مقبول (اضغط للرفض)' : 'الحالة: غير مقبول (اضغط للقبول)';
                                        renderActionButton($program, $csrf_token, 'can_review_programs', true, $next_status, $btn_class, $btn_text, $btn_icon, $btn_title, 'update_status', false, 'review');
                                    } else {
                                        $status_text = $program['status'] === 'reviewed' || $program['status'] === 'published' ? 'مقبول' : ($program['status'] === 'rejected' ? 'مرفوض' : 'غير مقبول');
                                        $status_class = $program['status'] === 'reviewed' || $program['status'] === 'published' ? 'status-accepted' : ($program['status'] === 'rejected' ? 'status-rejected' : 'status-pending');
                                        ?>
                                        <span class="static-status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($status_text); ?></span>
                                        <?php
                                    }
                                    ?>
                                </td>

                                <!-- عمود النشر: زر نشر/عدم نشر -->
                                <td class="action-cell">
                                    <?php
                                    $is_publishable = in_array($program['status'], ['reviewed', 'published']);
                                    if (!empty($_SESSION['permissions']['can_publish_programs']) && $is_publishable) {
                                        $is_published = $program['status'] === 'published';
                                        $next_status = $is_published ? 'reviewed' : 'published';
                                        $btn_class = $is_published ? 'unpublished' : 'published';
                                        $btn_text = $is_published ? 'عدم نشر' : 'نشر';
                                        $btn_icon = $is_published ? 'fa-eye-slash' : 'fa-globe-americas';
                                        $btn_title = $is_published ? 'الحالة: منشور (اضغط لإلغاء النشر)' : 'الحالة: غير منشور (اضغط للنشر)';
                                        renderActionButton($program, $csrf_token, 'can_publish_programs', true, $next_status, $btn_class, $btn_text, $btn_icon, $btn_title, 'update_status', false, 'publish');
                                    } else {
                                        $publish_text = $program['status'] === 'published' ? 'منشور' : ($program['status'] === 'reviewed' ? 'جاهز للنشر' : 'غير منشور');
                                        $publish_class = $program['status'] === 'published' ? 'status-published' : ($program['status'] === 'reviewed' ? 'status-reviewed' : 'status-unpublished');
                                        ?>
                                        <span class="static-status <?php echo htmlspecialchars($publish_class); ?>"><?php echo htmlspecialchars($publish_text); ?></span>
                                        <?php
                                    }
                                    ?>
                                </td>

                                <!-- عمود التسجيل: زر متاح/غير متاح -->
                                <td class="action-cell">
                                    <?php
                                    $is_published = $program['status'] === 'published';
                                    if (!empty($_SESSION['permissions']['can_edit_programs']) && $is_published) {
                                        $is_full = !empty($program['is_full']);
                                        $btn_class = $is_full ? 'unavailable' : 'available';
                                        $btn_text = $is_full ? 'غير متاح' : 'متاح';
                                        $btn_icon = $is_full ? 'fa-times-circle' : 'fa-check-circle';
                                        $btn_title = $is_full ? 'الحالة: غير متاح (اضغط لفتح التسجيل)' : 'الحالة: متاح (اضغط لإغلاق التسجيل)';
                                        renderActionButton($program, $csrf_token, 'can_edit_programs', true, $program['status'], $btn_class, $btn_text, $btn_icon, $btn_title, 'toggle_full_status', false);
                                    } else {
                                        $reg_text = $is_published ? (!empty($program['is_full']) ? 'غير متاح' : 'متاح') : '—';
                                        $reg_class = $is_published ? (!empty($program['is_full']) ? 'status-unavailable' : 'status-available') : '';
                                        ?>
                                        <span class="static-status <?php echo htmlspecialchars($reg_class); ?>"><?php echo htmlspecialchars($reg_text); ?></span>
                                        <?php
                                    }
                                    ?>
                                </td>

                                <!-- عمود الإجراءات: أيقونات التعديل والحذف -->
                                <td class="action-cell">
                                    <?php if (!empty($_SESSION['permissions']['can_edit_programs'])): ?>
                                        <a href="edit_program.php?id=<?php echo $program['id']; ?>" class="action-btn icon-only" title="تعديل تفاصيل البرنامج"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($_SESSION['permissions']['can_delete_programs'])): ?>
                                        <a href="delete_program.php?id=<?php echo $program['id']; ?>" class="delete-btn icon-only" onclick="return confirm('هل أنت متأكد من حذف هذا البرنامج نهائياً؟');" title="حذف البرنامج نهائياً"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- إضافة مكتبات JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<script>
$(document).ready(function() {
    var table = $('#programs-table').DataTable({
        "scrollX": true,
        "paging": false,
        "searching": false,
        "ordering": true,
        "info": false,
        "language": {
            "emptyTable": "لا توجد برامج تطابق معايير البحث."
        }
    });

    // البحث بالاسم أو الجهة - يبحث في العمود 0 (العنوان) والعمود 1 (الجهة)
    $('#search').on('keyup', function() {
        var searchValue = this.value;
        table.columns().search(''); // مسح جميع البحث
        // نستخدم custom search function للبحث في عمودين معاً
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = table.row(dataIndex).node();
            var title = $(row).find('td:eq(0)').text().toLowerCase();
            var organizer = $(row).find('td:eq(1)').text().toLowerCase();
            var search = searchValue.toLowerCase();
            return title.includes(search) || organizer.includes(search);
        });
        table.draw();
        $.fn.dataTable.ext.search.pop(); // إزالة البحث المخصص بعد التنفيذ
    });

    // فلتر الحالات
    $('#status-filter').on('change', function() {
        var value = this.value;
        table.search('').columns().search(''); // مسح جميع الفلاتر أولاً

        if (value === '') {
            // عرض الكل
            table.draw();
        } else if (value === 'registration_available') {
            // البحث في عمود التسجيل (العمود 4)
            table.column(4).search('متاح').draw();
        } else if (value === 'registration_unavailable') {
            // البحث في عمود التسجيل (العمود 4)
            table.column(4).search('غير متاح').draw();
        } else {
            // البحث في عمود المراجعة (العمود 2)
            var searchText = '';
            switch(value) {
                case 'pending': searchText = 'غير مقبول'; break;
                case 'reviewed': searchText = 'مقبول'; break;
                case 'published': searchText = 'منشور'; break;
                case 'rejected': searchText = 'مرفوض'; break;
            }
            if (searchText) {
                table.column(2).search(searchText).draw();
            } else {
                table.draw();
            }
        }
    });

    // فلتر الأقسام - باستخدام custom filter
    $('#direction-filter').on('change', function() {
        var value = this.value;
        if (value === '') {
            // إزالة جميع الفلاتر المخصصة وإظهار الكل
            table.search('').columns().search('');
            // إزالة فلتر الاتجاه المخصص إن وُجد
            $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
                return fn.name !== 'directionFilter';
            });
        } else {
            // إضافة فلتر مخصص للاتجاه
            // إزالة الفلتر القديم أولاً
            $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
                return fn.name !== 'directionFilter';
            });
            $.fn.dataTable.ext.search.push({
                name: 'directionFilter',
                fn: function(settings, data, dataIndex) {
                    var row = table.row(dataIndex).node();
                    var direction = $(row).data('direction') || '';
                    return direction === value;
                }
            });
        }
        table.draw();
    });

    // تأكيد الحذف عند الضغط على زر الحذف في الجدول
    $('#programs-table').on('click', '.delete-btn', function(e) {
        if (!confirm('هل أنت متأكد من حذف هذا البرنامج نهائياً؟ هذا الإجراء لا يمكن التراجع عنه.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php
$adminController->renderFooter();
?>
