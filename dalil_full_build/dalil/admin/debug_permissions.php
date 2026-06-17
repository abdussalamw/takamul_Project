<?php
/**
 * debug_permissions.php - أداة تشخيص الصلاحيات وقاعدة البيانات
 * استخدم هذه الأداة للتحقق من صحة الإعدادات
 * 
 * للاستخدام: افتح هذا الملف في المتصفح
 * http://localhost/Dalil/dalil/admin/debug_permissions.php
 */

include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'تشخيص الصلاحيات';

$adminController->renderHeader($page_title);
$adminController->renderMessages();
?>

<section class="dashboard-section">
    <div class="dashboard-card">
        <h2><i class="fas fa-bug"></i> أداة تشخيص الصلاحيات وقاعدة البيانات</h2>
        
        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
            <p style="font-weight: 700; color: #3b82f6; margin-bottom: 10px;">
                <i class="fas fa-info-circle"></i> هذه الصفحة لأغراض التشخيص فقط. احذفها بعد الانتهاء.
            </p>
        </div>

        <!-- معلومات المستخدم الحالي -->
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
            <h3 style="color: #10b981; margin-bottom: 15px;"><i class="fas fa-user-check"></i> معلومات المستخدم الحالي</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: 700;">معرف المستخدم (admin_id)</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                        <?php echo $_SESSION['admin_id'] ?? '<span style="color: #ef4444;">غير محدد ❌ - المستخدم غير مسجل الدخول!</span>'; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: 700;">اسم المستخدم</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? '<span style="color: #ef4444;">غير محدد ❌</span>'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: 700;">CSRF Token</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                        <?php echo !empty($_SESSION['csrf_token']) ? '<span style="color: #10b981;">موجود ✅ (طول: ' . strlen($_SESSION['csrf_token']) . ')</span>' : '<span style="color: #ef4444;">غير موجود ❌</span>'; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- الصلاحيات -->
        <div style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
            <h3 style="color: #6366f1; margin-bottom: 15px;"><i class="fas fa-key"></i> الصلاحيات المحفوظة في الجلسة</h3>
            <?php 
            $all_permissions = [
                'can_review_programs' => 'مراجعة البرامج (قبول/رفض)',
                'can_publish_programs' => 'نشر البرامج',
                'can_edit_programs' => 'تعديل البرامج + تبديل التسجيل',
                'can_delete_programs' => 'حذف البرامج',
                'can_add_programs' => 'إضافة برامج',
                'can_manage_users' => 'إدارة المستخدمين',
                'can_manage_settings' => 'إعدادات الموقع',
            ];
            
            if (empty($_SESSION['permissions'])):
            ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 15px;">
                    <p style="color: #ef4444; font-weight: 700;">
                        <i class="fas fa-exclamation-triangle"></i> لا توجد صلاحيات محفوظة في الجلسة!
                    </p>
                    <p style="color: #666; margin-top: 10px;">هذا يعني أن جدول <code>users</code> لا يحتوي على أعمدة <code>can_*</code> أو أن المستخدم الحالي ليس له أي صلاحية.</p>
                    <p style="color: #666; margin-top: 10px;">الحل: تأكد من أن جدول <code>users</code> يحتوي على الأعمدة المطلوبة وقم بتسجيل الدخول مرة أخرى.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <?php foreach ($all_permissions as $perm_key => $perm_label): ?>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: 700;"><?php echo $perm_label; ?></td>
                        <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                            <?php if (!empty($_SESSION['permissions'][$perm_key])): ?>
                                <span style="color: #10b981; font-weight: 700;">✅ مفعّل</span>
                            <?php else: ?>
                                <span style="color: #ef4444; font-weight: 700;">❌ غير مفعّل</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- حالة قاعدة البيانات -->
        <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
            <h3 style="color: #f59e0b; margin-bottom: 15px;"><i class="fas fa-database"></i> حالة قاعدة البيانات</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <?php
                $tables_to_check = ['users', 'programs', 'admin_logs', 'site_settings'];
                foreach ($tables_to_check as $table) {
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                        $count = $stmt->fetchColumn();
                        $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN);
                        ?>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: 700;">الجدول: <?php echo $table; ?></td>
                            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                                <span style="color: #10b981;">✅ موجود</span> - 
                                السجلات: <?php echo $count; ?> - 
                                الأعمدة: <?php echo implode(', ', $cols); ?>
                            </td>
                        </tr>
                        <?php
                    } catch (PDOException $e) {
                    ?>
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: 700;">الجدول: <?php echo $table; ?></td>
                            <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                                <span style="color: #ef4444;">❌ غير موجود أو خطأ: </span>
                                <?php echo htmlspecialchars($e->getMessage()); ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </table>
        </div>

        <!-- التحقق من أعمدة الصلاحيات -->
        <div style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
            <h3 style="color: #8b5cf6; margin-bottom: 15px;"><i class="fas fa-columns"></i> التحقق من أعمدة الصلاحيات في جدول users</h3>
            <?php
            try {
                $user_cols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
                $required_cols = ['can_manage_users', 'can_add_programs', 'can_edit_programs', 'can_delete_programs', 'can_manage_settings', 'can_publish_programs', 'can_review_programs'];
                
                echo '<table style="width: 100%; border-collapse: collapse;">';
                foreach ($required_cols as $col):
                    $exists = in_array($col, $user_cols);
                ?>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #e5e7eb; font-weight: 700;"><code><?php echo $col; ?></code></td>
                        <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                            <?php if ($exists): ?>
                                <span style="color: #10b981;">✅ موجود</span>
                            <?php else: ?>
                                <span style="color: #ef4444;">❌ غير موجود!</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </table>
                
                <?php
                // التحقق من صلاحيات المستخدم الحالي
                if (isset($_SESSION['admin_id'])) {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($current_user):
                    ?>
                    <div style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.5); border-radius: 8px;">
                        <h4 style="margin-bottom: 10px;">القيم الفعلية للمستخدم "<?php echo htmlspecialchars($current_user['username']); ?>":</h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            <?php foreach ($required_cols as $col): ?>
                            <tr>
                                <td style="padding: 5px; border-bottom: 1px solid #e5e7eb;"><code><?php echo $col; ?></code></td>
                                <td style="padding: 5px; border-bottom: 1px solid #e5e7eb;">
                                    <?php 
                                    $val = $current_user[$col] ?? 'عمود غير موجود';
                                    if ($val == 1): ?>
                                        <span style="color: #10b981; font-weight: 700;">1 (مفعّل ✅)</span>
                                    <?php elseif ($val == 0): ?>
                                        <span style="color: #ef4444; font-weight: 700;">0 (غير مفعّل ❌)</span>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($val); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                    <?php
                    endif;
                }
            } catch (PDOException $e) {
                echo '<p style="color: #ef4444;">خطأ: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <!-- روابط مفيدة -->
        <div style="background: rgba(20, 184, 166, 0.1); border: 1px solid rgba(20, 184, 166, 0.3); border-radius: 12px; padding: 20px;">
            <h3 style="color: #14b8a6; margin-bottom: 15px;"><i class="fas fa-link"></i> روابط مفيدة</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="manage_programs.php" class="action-toggle-btn accepted"><i class="fas fa-tasks"></i> إدارة البرامج</a>
                <a href="manage_users.php" class="action-toggle-btn accepted"><i class="fas fa-users"></i> إدارة المستخدمين</a>
                <a href="dashboard.php" class="action-toggle-btn accepted"><i class="fas fa-home"></i> لوحة التحكم</a>
                <a href="site_settings.php" class="action-toggle-btn accepted"><i class="fas fa-cog"></i> إعدادات الموقع</a>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px;">
            <p style="color: #ef4444; font-weight: 700;">
                <i class="fas fa-exclamation-triangle"></i> تذكير: احذف هذا الملف بعد الانتهاء من التشخيص!
            </p>
            <code style="display: block; margin-top: 5px; color: #666;">المسار: dalil/admin/debug_permissions.php</code>
        </div>
    </div>
</section>

<?php
$adminController->renderFooter();
?>