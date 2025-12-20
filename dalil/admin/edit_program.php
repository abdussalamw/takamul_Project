<?php
// edit_program.php

// Include dependencies
include_once '../includes/db_connect.php';
include_once 'AdminController.php';

// Initialize AdminController
$adminController = new AdminController($pdo);

// Set page title
$page_title = 'تعديل البرنامج';

// Require permission to access this page
$adminController->requirePermission('can_edit_programs', 'manage_programs.php');

// Validate program ID from GET parameter
$program_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Fetch program data
try {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Use controller to set error and redirect
    $adminController->redirect('manage_programs.php', "خطأ في قاعدة البيانات: " . $e->getMessage(), 'error');
}

// If program not found, redirect
if (!$program_id || !$program) {
    $adminController->redirect('manage_programs.php', 'لم يتم العثور على البرنامج المطلوب.', 'error');
}

// Handle POST request for updating the program
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. CSRF Token Validation
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى.");
    } else {
        $original_ad_link = $program['ad_link'];
        $new_ad_link_path = null;
        $error_message = null;

        // 2. Handle File Upload
        if (isset($_FILES['ad_link']) && $_FILES['ad_link']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $adminController->handleFileUpload($_FILES['ad_link']);
            if ($upload_result['success']) {
                $new_ad_link_path = 'uploads/' . $upload_result['filename'];
                $_POST['ad_link'] = $new_ad_link_path;
            } else {
                $error_message = $upload_result['message'];
                $adminController->setErrorMessage($error_message);
            }
        }

        if (is_null($error_message)) {
            // 3. Build Dynamic UPDATE Query
            $update_parts = [];
            $params = [];

            $stmt = $pdo->query("DESCRIBE programs");
            $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($table_columns_info as $column_info) {
                $column_name = $column_info['Field'];
                if (in_array($column_name, ['id', 'status'])) continue;

                if ($column_name === 'ad_link' && $new_ad_link_path) {
                    $update_parts[] = "`$column_name` = ?";
                    $params[] = $new_ad_link_path;
                } elseif (isset($_POST[$column_name]) && $column_name !== 'ad_link') {
                    $submitted_value = trim($_POST[$column_name]);
                    // Only update if the value has changed
                    if ($submitted_value !== ($program[$column_name] ?? null)) {
                        $update_parts[] = "`$column_name` = ?";
                        if (empty($submitted_value) && $column_info['Null'] === 'YES') {
                            $params[] = NULL;
                        } else {
                            $params[] = $submitted_value;
                        }
                    }
                }
            }

            // 4. Execute Query if there are changes
            if (!empty($update_parts)) {
                $params[] = $program_id; // Add program ID for the WHERE clause
                $sql = "UPDATE programs SET " . implode(', ', $update_parts) . " WHERE id = ?";
                
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    // 5. Delete old file if a new one was uploaded successfully
                    if ($new_ad_link_path && !empty($original_ad_link) && file_exists('../' . $original_ad_link)) {
                        unlink('../' . $original_ad_link);
                    }

                    // Unset or regenerate CSRF token after successful submission
                    unset($_SESSION['csrf_token']);
                    $adminController->redirect('manage_programs.php', 'تم تحديث البرنامج بنجاح.', 'success');

                } catch (PDOException $e) {
                    $adminController->setErrorMessage("خطأ في قاعدة البيانات: " . $e->getMessage());
                }
            } else {
                // No changes were submitted, just redirect
                 $adminController->redirect('manage_programs.php');
            }
        } else {
            // If there was an error, repopulate the form with the submitted data for correction
            foreach ($_POST as $key => $value) {
                if (array_key_exists($key, $program)) {
                    $program[$key] = htmlspecialchars($value);
                }
            }
        }
    }
}

$adminController->renderHeader($page_title);
$adminController->renderMessages();
?>

<section class="edit-program-section">
    <div class="edit-program-card">
        <h2><i class="fas fa-edit"></i> تعديل البرنامج</h2>
        <form method="POST" class="edit-program-form" id="edit-program-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
            <?php
            try {
                $field_translations = [
                    'title'             => 'عنوان البرنامج',
                    'organizer'         => 'الجهة المنظمة',
                    'description'       => 'وصف البرنامج',
                    'Direction'         => 'المنطقة/الاتجاه',
                    'location'          => 'مكان البرنامج (الحي)',
                    'start_date'        => 'تاريخ البدء',
                    'end_date'          => 'تاريخ الانتهاء',
                    'duration'          => 'المدة',
                    'age_group'         => 'الفئة العمرية',
                    'price'             => 'رسوم البرنامج',
                    'registration_link' => 'رابط التسجيل',
                    'ad_link'           => 'صورة الإعلان (صورة أو PDF)',
                    'google_map'        => 'رابط الموقع على خرائط جوجل',
                ];

                $field_icons = [
                    'title'             => 'fas fa-heading',
                    'organizer'         => 'fas fa-user-tie',
                    'description'       => 'fas fa-file-alt',
                    'Direction'         => 'fas fa-map-signs',
                    'location'          => 'fas fa-map-marker-alt',
                    'start_date'        => 'fas fa-calendar-day',
                    'end_date'          => 'fas fa-calendar-week',
                    'duration'          => 'fas fa-clock',
                    'age_group'         => 'fas fa-users',
                    'price'             => 'fas fa-money-bill',
                    'registration_link' => 'fas fa-link',
                    'ad_link'           => 'fas fa-image',
                    'google_map'        => 'fas fa-map-marked-alt',
                ];

                $stmt = $pdo->query("DESCRIBE programs");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $column_data = array_column($columns, null, 'Field');
                $order = array_keys($column_data);
                $end_date_key = array_search('end_date', $order);
                if ($end_date_key !== false) {
                    $end_date_item = array_splice($order, $end_date_key, 1);
                    $start_date_key = array_search('start_date', $order);
                    if ($start_date_key !== false) {
                        array_splice($order, $start_date_key + 1, 0, $end_date_item);
                    } else {
                        $order[] = $end_date_item[0];
                    }
                }
                $ordered_columns = [];
                foreach ($order as $field_name) {
                    $ordered_columns[] = $column_data[$field_name];
                }

                foreach ($ordered_columns as $column) {
                    $field_name = $column['Field'];
                    if (in_array($field_name, ['id', 'status'])) continue;

                    $is_date_field = in_array($field_name, ['start_date', 'end_date']);
                    $group_classes = 'form-group';
                    if ($field_name === 'description') {
                        $group_classes .= ' full-width';
                    } else {
                        $group_classes .= ' half-width';
                    }

                    $required = $column['Null'] == 'NO' ? 'required' : '';
                    $label = $field_translations[$field_name] ?? ucfirst(str_replace('_', ' ', $field_name));
                    $icon_class = $field_icons[$field_name] ?? 'fas fa-edit';
                    $current_value = htmlspecialchars($program[$field_name] ?? '');
            ?>
                    <div class="<?php echo $group_classes; ?>">                            
                        <label for="<?php echo $field_name; ?>"><i class="<?php echo $icon_class; ?>"></i> <?php echo $label; ?></label>
                        <?php if ($field_name === 'ad_link'): ?>
                            <?php if (!empty($current_value)): ?>
                                <div class="current-ad-preview">
                                    <span>الإعلان الحالي:</span>
                                    <?php
                                    $file_ext = strtolower(pathinfo($current_value, PATHINFO_EXTENSION));
                                    if (in_array($file_ext, ['jpg', 'jpeg', 'png'])):
                                    ?>
                                        <a href="../<?php echo $current_value; ?>" target="_blank"><img src="../<?php echo $current_value; ?>" alt="صورة الإعلان الحالي" style="max-width: 100px; max-height: 100px; border-radius: 5px; margin-top: 5px;"></a>
                                    <?php else: ?>
                                        <a href="../<?php echo $current_value; ?>" target="_blank">عرض الملف الحالي (<?php echo $file_ext; ?>)</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" accept=".jpg, .jpeg, .png, .pdf">
                            <small style="display: block; margin-top: 5px; color: #666;">اترك الحقل فارغاً للإبقاء على الإعلان الحالي.</small>
                        <?php elseif ($column['Type'] == 'longtext' || $column['Type'] == 'text'): ?>
                            <textarea id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?>><?php echo $current_value; ?></textarea>
                        <?php else: ?>
                            <input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $current_value; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?> <?php if ($is_date_field) echo 'readonly style="cursor: pointer;"'; ?>>
                        <?php endif; ?>
                    </div>
            <?php
                }
            } catch (PDOException $e) {
                echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> خطأ في جلب معلومات الحقول: " . $e->getMessage() . "</p>";
            }
            ?>
            <div class="form-actions-container">
                <a href="manage_programs.php" class="back-btn-inline"><i class="fas fa-arrow-right"></i> رجوع</a>
                <button type="submit" class="edit-program-btn"><i class="fas fa-save"></i> تحديث بيانات البرنامج</button>
            </div>
        </form>
    </div>
</section>

<script>
// (The same calendar script from add_program.php can be included here or in admin_footer.php)
document.addEventListener('DOMContentLoaded', function() {
    // Calendar logic...
});
</script>

<?php
$adminController->renderFooter();
?>
