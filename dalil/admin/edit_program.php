<?php
// edit_program.php - Admin Edit Program (refactored to use unified form)

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

// Fetch program data with organizer details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               o.name as organizer_name, 
               o.department as organizer_department, 
               o.entry_officer_name, 
               o.entry_officer_phone 
        FROM programs p 
        LEFT JOIN organizers o ON p.organizer_id = o.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $adminController->redirect('manage_programs.php', "خطأ في قاعدة البيانات: " . $e->getMessage(), 'error');
}

// If program not found, redirect
if (!$program_id || !$program) {
    $adminController->redirect('manage_programs.php', 'لم يتم العثور على البرنامج المطلوب.', 'error');
}

// Extract age_group_other from age_group for the form
$standard_ages = ['متوسط', 'ثانوي', 'جامعي', 'مافوق الجامعي'];
$age_group_arr = array_map('trim', explode(',', $program['age_group'] ?? ''));
$other_age_values = array_diff($age_group_arr, $standard_ages);
$age_group_other_val = !empty($other_age_values) ? implode(', ', $other_age_values) : '';

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
            } else {
                $error_message = $upload_result['message'];
                $adminController->setErrorMessage($error_message);
            }
        }

        // --- Validate Data ---
        $field_translations = [
            'title' => 'عنوان البرنامج',
            'organizer_id' => 'الجهة المنظمة',
            'Direction' => 'المنطقة/الاتجاه',
            'venue_name' => 'مقر البرنامج',
            'start_date' => 'تاريخ البدء',
        ];
        $required_fields = ['title', 'start_date', 'organizer_id', 'Direction', 'venue_name'];
        $missing_fields = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field_translations[$field] ?? $field;
            }
        }
        
        $organizer_id = $_POST['organizer_id'] ?? '';
        if (!empty($organizer_id)) {
            if (empty(trim($_POST['organizer_name'] ?? ''))) {
                $missing_fields[] = 'اسم الجهة';
            }
            if (empty(trim($_POST['entry_officer_name'] ?? ''))) {
                $missing_fields[] = 'اسم مسؤولة التواصل';
            }
            if (empty(trim($_POST['entry_officer_phone'] ?? ''))) {
                $missing_fields[] = 'رقم جوال مسؤول التواصل';
            }
        }

        if (empty($_POST['age_group']) && empty($_POST['age_group_other'])) {
            $missing_fields[] = 'الفئة المستهدفة';
        }
        if (!empty($missing_fields)) {
            $adminController->setErrorMessage("الحقول التالية مطلوبة: " . implode('، ', $missing_fields));
            $error_message = "Validation failed";
        }

        if (is_null($error_message)) {
            // Phone number formatting to 9665
            $raw_phone = trim($_POST['entry_officer_phone'] ?? '');
            $clean_phone = preg_replace('/\D/', '', $raw_phone);
            if (preg_match('/^05\d{8}$/', $clean_phone)) {
                $clean_phone = '966' . substr($clean_phone, 1);
            } elseif (preg_match('/^5\d{8}$/', $clean_phone)) {
                $clean_phone = '966' . $clean_phone;
            } elseif (!empty($clean_phone)) {
                if (preg_match('/^9665\d{8}$/', $clean_phone)) {
                    // Valid
                } else {
                    $error_message = "رقم الجوال غير صحيح، يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.";
                    $adminController->setErrorMessage($error_message);
                }
            } else {
                $error_message = "رقم الجوال مطلوب.";
                $adminController->setErrorMessage($error_message);
            }
        }

        if (is_null($error_message)) {
            // Age group processing
            $age_groups = $_POST['age_group'] ?? [];
            if (!is_array($age_groups)) {
                $age_groups = (array) $age_groups;
            }
            if (($key = array_search('أخرى', $age_groups)) !== false) {
                unset($age_groups[$key]);
            }
            $final_age_group = implode(', ', $age_groups);
            $age_group_other = trim($_POST['age_group_other'] ?? '');
            if (!empty($age_group_other)) {
                $final_age_group = !empty($final_age_group) ? $final_age_group . ', ' . $age_group_other : $age_group_other;
            }
            $_POST['age_group'] = $final_age_group;

            // Extract coordinates if google_map changed
            $latitude = null;
            $longitude = null;
            $coords_extracted = false;

            if (isset($_POST['google_map'])) {
                $submitted_map = trim($_POST['google_map']);
                if ($submitted_map !== ($program['google_map'] ?? null)) {
                    $coords_extracted = true;
                    if (!empty($submitted_map)) {
                        $coords = get_coords_from_google_maps($submitted_map);
                        if ($coords) {
                            $latitude = $coords['lat'];
                            $longitude = $coords['lng'];
                        }
                    }
                }
            }

            try {
                $pdo->beginTransaction();

                if ($organizer_id === 'new') {
                    $stmtOrg = $pdo->prepare("INSERT INTO organizers (name, department, entry_officer_name, entry_officer_phone) VALUES (?, ?, ?, ?)");
                    $stmtOrg->execute([
                        trim($_POST['organizer_name']),
                        trim($_POST['organizer_department'] ?? ''),
                        trim($_POST['entry_officer_name'] ?? ''),
                        $clean_phone
                    ]);
                    $resolved_organizer_id = $pdo->lastInsertId();
                } else {
                    $resolved_organizer_id = (int)$organizer_id;
                    $stmtOrg = $pdo->prepare("UPDATE organizers SET name = ?, department = ?, entry_officer_name = ?, entry_officer_phone = ? WHERE id = ?");
                    $stmtOrg->execute([
                        trim($_POST['organizer_name']),
                        trim($_POST['organizer_department'] ?? ''),
                        trim($_POST['entry_officer_name'] ?? ''),
                        $clean_phone,
                        $resolved_organizer_id
                    ]);
                }

                // Update POST values
                $_POST['organizer_id'] = $resolved_organizer_id;
                $_POST['organizer'] = trim($_POST['organizer_name']);

                // 3. Build Dynamic UPDATE Query
                $update_parts = [];
                $params = [];

                $stmt = $pdo->query("DESCRIBE programs");
                $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($table_columns_info as $column_info) {
                    $column_name = $column_info['Field'];
                    if (in_array($column_name, ['id', 'status', 'latitude', 'longitude'])) continue;

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

                // Append updated coordinates if changed
                if ($coords_extracted) {
                    $update_parts[] = "`latitude` = ?";
                    $params[] = $latitude;

                    $update_parts[] = "`longitude` = ?";
                    $params[] = $longitude;
                }

                // 4. Execute Query if there are changes
                if (!empty($update_parts)) {
                    $params[] = $program_id; // Add program ID for WHERE clause
                    $sql = "UPDATE programs SET " . implode(', ', $update_parts) . " WHERE id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    // 5. Delete old file if a new one was uploaded successfully
                    if ($new_ad_link_path && !empty($original_ad_link) && file_exists('../' . $original_ad_link)) {
                        unlink('../' . $original_ad_link);
                    }
                }

                $pdo->commit();

                // Unset or regenerate CSRF token after successful submission
                unset($_SESSION['csrf_token']);
                $adminController->redirect('manage_programs.php', 'تم تحديث البرنامج بنجاح.', 'success');

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $adminController->setErrorMessage("خطأ في قاعدة البيانات: " . $e->getMessage());
            }
        }
    }
}

// Render view
$adminController->renderHeader($page_title);
$adminController->renderMessages();
?>
<link rel="stylesheet" href="../css/program-form.css">
<div class="admin-page">
    <?php
    // Prepare form_data from the program row (merged with any submitted POST data)
    // Use POST data if form was submitted with errors, otherwise use DB data
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($error_message)) {
        $form_data = $_POST;
        // Add organizer_name back if not set
        if (empty($form_data['organizer_name']) && !empty($program['organizer_name'])) {
            $form_data['organizer_name'] = $program['organizer_name'];
        }
    } else {
        $form_data = $program;
    }
    
    // Add age_group_other for the form
    if (!isset($form_data['age_group_other']) || empty($form_data['age_group_other'])) {
        $form_data['age_group_other'] = $age_group_other_val;
    }

    $form_options = [
        'form_id'          => 'edit-program-form',
        'csrf_token'       => $adminController->csrf_token,
        'submit_label'     => 'تحديث بيانات البرنامج',
        'back_url'         => 'manage_programs.php',
        'show_back_btn'    => true,
        'is_admin'         => true,
        'show_header'      => true,
        'ad_required'      => false,
        'word_limit'       => 50,
        'form_title'       => 'تعديل البرنامج: ' . htmlspecialchars($program['title'] ?? ''),
        'current_ad_link'  => $program['ad_link'] ?? '',
    ];
    include '../includes/program_form.php';
    ?>
</div>
<script src="../js/program-form.js"></script>
<script src="../js/hijri-calendar.js"></script>
<?php
$adminController->renderFooter();
?>