<?php
// add_program.php - Admin Add Program (refactored to use unified form)

// 1. Initialization
include_once '../includes/db_connect.php';
include_once '../includes/HijriDate.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'إضافة برنامج جديد';
$adminController->requirePermission('can_add_programs', 'manage_programs.php');

// 2. POST Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF)، يرجى تحديث الصفحة والمحاولة مرة أخرى.");
    } else {
        $ad_link_path = null;
        $error_message = null;

        // --- Handle File Upload ---
        if (isset($_FILES['ad_link']) && $_FILES['ad_link']['error'] === UPLOAD_ERR_OK) {
            $upload_result = $adminController->handleFileUpload($_FILES['ad_link']);
            if ($upload_result['success']) {
                $ad_link_path = 'uploads/' . $upload_result['filename'];
            } else {
                $error_message = $upload_result['message'];
                $adminController->setErrorMessage($error_message);
            }
        }

        // Normalize dates to Gregorian directly in $_POST so dynamic insertion catches it
        $_POST['start_date'] = HijriDate::normalizeToGregorian($_POST['start_date'] ?? null);
        $_POST['end_date']   = HijriDate::normalizeToGregorian($_POST['end_date'] ?? null);

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

            // Extract coordinates from Google Map URL
            $latitude = null;
            $longitude = null;
            if (!empty($_POST['google_map'])) {
                $coords = get_coords_from_google_maps($_POST['google_map']);
                if ($coords) {
                    $latitude = $coords['lat'];
                    $longitude = $coords['lng'];
                }
            }

            try {
                $pdo->beginTransaction();

                if ($organizer_id === 'new') {
                    $stmtOrg = $pdo->prepare("INSERT INTO organizers (name, sub_name, communication_officer_name, communication_officer_phone) VALUES (?, ?, ?, ?)");
                    $stmtOrg->execute([
                        trim($_POST['organizer_name']),
                        trim($_POST['organizer_department'] ?? ''),
                        trim($_POST['entry_officer_name'] ?? ''),
                        $clean_phone
                    ]);
                    $resolved_organizer_id = $pdo->lastInsertId();
                } else {
                    $resolved_organizer_id = (int)$organizer_id;
                    $stmtOrg = $pdo->prepare("UPDATE organizers SET name = ?, sub_name = ?, communication_officer_name = ?, communication_officer_phone = ? WHERE id = ?");
                    $stmtOrg->execute([
                        trim($_POST['organizer_name']),
                        trim($_POST['organizer_department'] ?? ''),
                        trim($_POST['entry_officer_name'] ?? ''),
                        $clean_phone,
                        $resolved_organizer_id
                    ]);
                }

                $_POST['organizer_id'] = $resolved_organizer_id;
                $_POST['organizer'] = trim($_POST['organizer_name']);

                $db_columns = [];
                $placeholders = [];
                $params = [];

                $stmt = $pdo->query("DESCRIBE programs");
                $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($table_columns_info as $column_info) {
                    $column_name = $column_info['Field'];
                    if (in_array($column_name, ['id', 'status', 'latitude', 'longitude'])) continue;

                    if ($column_name === 'ad_link') {
                        if ($ad_link_path) {
                            $db_columns[] = "`$column_name`";
                            $placeholders[] = '?';
                            $params[] = $ad_link_path;
                        }
                    } elseif (isset($_POST[$column_name])) {
                        $db_columns[] = "`$column_name`";
                        $placeholders[] = '?';
                        $value = trim($_POST[$column_name]);
                        $params[] = (empty($value) && $column_info['Null'] === 'YES') ? null : $value;
                    }
                }

                // Add coordinates
                $db_columns[] = '`latitude`';
                $placeholders[] = '?';
                $params[] = $latitude;

                $db_columns[] = '`longitude`';
                $placeholders[] = '?';
                $params[] = $longitude;

                // Admin adds program -> status = 'reviewed'
                $db_columns[] = '`status`';
                $placeholders[] = '?';
                $params[] = 'reviewed';

                $sql = "INSERT INTO programs (" . implode(', ', $db_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $pdo->commit();
                $adminController->redirect('manage_programs.php', 'تمت إضافة البرنامج بنجاح.', 'success');

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $adminController->setErrorMessage("خطأ في قاعدة البيانات: " . $e->getMessage());
            }
        }
    }
}

// 3. Render View
$adminController->renderHeader($page_title);
$adminController->renderMessages(); // Display any success/error messages
?>
<link rel="stylesheet" href="../css/program-form.css">
<div class="admin-page">
    <?php
    // Prepare form_data from POST
    $form_data = $_POST ?: [];
    $form_options = [
        'form_id'      => 'add-program-form',
        'csrf_token'   => $adminController->csrf_token,
        'submit_label' => 'حفظ البرنامج',
        'back_url'     => 'manage_programs.php',
        'show_back_btn'=> true,
        'is_admin'     => true,
        'show_header'  => true,
        'ad_required'  => false,
        'word_limit'   => 50,
        'form_title'   => 'إضافة برنامج جديد',
    ];
    include '../includes/program_form.php';
    ?>
</div>
<script src="../js/program-form.js"></script>
<script src="../js/hijri-calendar.js"></script>
<?php
$adminController->renderFooter();
?>