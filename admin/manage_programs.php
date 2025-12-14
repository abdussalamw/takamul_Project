<?php
// manage_programs.php - صفحة إدارة البرامج (قسم الجدول فقط)

// Include dependencies
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

// Initialize controller
$adminController = new AdminController($pdo);
$page_title = 'إدارة البرامج';
$csrf_token = $adminController->csrf_token;

// Render header
$adminController->renderHeader($page_title);
$adminController->renderMessages();

// دالة لعرض الأزرار بشكل موحد
function renderActionButton($program, $csrf_token, $permission, $condition, $next_status, $btn_class, $btn_text, $btn_icon, $btn_title, $action_name = 'update_status', $disabled = false) {
    if ($condition && !empty($_SESSION['permissions'][$permission])) {
        ?>
        <form method="POST" style="display:inline-block;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
            <?php if ($action_name === 'update_status'): ?>
                <input type="hidden" name="new_status" value="<?php echo htmlspecialchars($next_status); ?>">
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
$sql = "SELECT * FROM programs ORDER BY id DESC";
$programs = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'خطأ في جلب بيانات البرامج: ' . $e->getMessage();
}
?>

<!-- تنسيقات CSS -->
<style>
    .action-toggle-btn:disabled {
        background-color: #e9ecef !important;
        color: #6c757d !important;
        border-color: #ced4da !important;
        cursor: not-allowed;
        opacity: 0.7;
    }
    .action-toggle-btn:disabled:hover {
        background-color: #e9ecef !important;
    }
    .filters-container {
        margin-bottom: 20px;
    }
    .filter-form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    .filter-group select, .filter-group input {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    .action-cell {
        text-align: center;
        vertical-align: middle;
    }
    .action-toggle-btn, .action-btn, .delete-btn {
        margin: 0 5px;
        display: inline-block;
    }
    .static-status {
        display: inline-block;
        padding: 5px 10px;
    }
</style>

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
                        <th style="width: 140px;">المراجعة</th>
                        <th style="width: 140px;">النشر</th>
                        <th style="width: 140px;">التسجيل</th>
                        <th style="width: 100px;">إجراءات</th>
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
                                <td><?php echo htmlspecialchars($program['organizer']); ?></td>

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
                                        renderActionButton($program, $csrf_token, 'can_review_programs', true, $next_status, $btn_class, $btn_text, $btn_icon, $btn_title);
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
                                        $btn_class = $is_published ? 'unpublish' : 'publish';
                                        $btn_text = $is_published ? 'عدم نشر' : 'نشر';
                                        $btn_icon = $is_published ? 'fa-eye-slash' : 'fa-globe-americas';
                                        $btn_title = $is_published ? 'الحالة: منشور (اضغط لإلغاء النشر)' : 'الحالة: غير منشور (اضغط للنشر)';
                                        renderActionButton($program, $csrf_token, 'can_publish_programs', true, $next_status, $btn_class, $btn_text, $btn_icon, $btn_title);
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
        "paging": false,
        "searching": false,
        "ordering": true,
        "info": false,
        "language": {
            "emptyTable": "لا توجد برامج تطابق معايير البحث."
        }
    });

    $('#search').on('keyup', function() {
        table.column(0).search(this.value).column(1).search(this.value).draw();
    });

    $('#status-filter').on('change', function() {
        var value = this.value;
        if (value === 'registration_available') {
            table.search('').columns().search('').column(4).search('متاح').draw();
        } else if (value === 'registration_unavailable') {
            table.search('').columns().search('').column(4).search('غير متاح').draw();
        } else {
            table.search('').columns().search('').column(2).search(
                value === 'pending' ? 'غير مقبول' : 
                value === 'reviewed' ? 'مقبول' : 
                value === 'published' ? 'منشور' : 
                value === 'rejected' ? 'مرفوض' : ''
            ).draw();
        }
    });

    $('#direction-filter').on('change', function() {
        var value = this.value;
        table.search('').columns().search('').rows().every(function() {
            var row = this.node();
            var direction = $(row).data('direction') || '';
            if (value === '' || direction === value) {
                $(row).show();
            } else {
                $(row).hide();
            }
        });
        table.draw();
    });
});
</script>

<?php
$adminController->renderFooter();
?>
