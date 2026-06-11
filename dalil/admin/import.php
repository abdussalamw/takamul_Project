<?php
// import.php - CSV Import Script for Programs
// Allows importing programs from Google Forms CSV export with an interactive preview

include_once '../includes/db_connect.php';
include_once '../includes/HijriDate.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'استيراد برامج من CSV';
$adminController->requirePermission('can_add_programs', 'manage_programs.php');

// Define CSV Column Mapping
$column_mapping = [
    'اسم الجهة'                 => 'organizer_name',
    'اسم الجهة الفرعي'          => 'organizer_department',
    'مسؤولة التواصل في حال وجود استفسار' => 'entry_officer_name',
    'رقم جوال مسؤول التواصل'     => 'entry_officer_phone',
    'اسم البرنامج'              => 'title',
    'وصف مختصر للبرنامج'        => 'description',
    'نوع البرنامج'              => 'program_type',
    'مقر البرنامج'              => 'venue_name',
    'حضوري/ عن بعد'             => 'attendance_type',
    'الموقع على خرائط جوجل'     => 'google_map',
    'مكان البرنامج حسب الجهات:' => 'Direction',
    'الفئة  المستهدفة'          => 'age_group',
    'أي ملاحظات على الفئات'     => 'target_notes',
    'تاريخ البداية'             => 'start_date',
    'تاريخ النهاية'             => 'end_date',
    'مدة البنامج'              => 'duration',
    'البرنامج هل هو مجاني او مدفوع' => 'is_free',
    'قيمة الرسوم'               => 'price',
    'ملاحظات على الرسوم'        => 'price_notes',
    'الصق رابط التسجيل هنا'     => 'registration_link',
    'اي ملاحظات تودون كتابتها عن البرنامج' => 'program_notes',
    'ارفاق التصميم الإعلاني أو الملف التعريفي للمشروع' => 'ad_link',
];

$action = $_POST['action'] ?? 'upload';
$import_results = [];
$preview_data = [];
$temp_file = '';
$default_organizer = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF).");
        $action = 'upload'; // Reset to upload
    } else {
        if ($action === 'preview') {
            // STEP 1: Process Upload and Generate Preview
            $file = $_FILES['csv_file'] ?? null;
            $default_organizer = $_POST['default_organizer'] ?? '';
            
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                $adminController->setErrorMessage("خطأ في رفع الملف.");
                $action = 'upload';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext !== 'csv') {
                    $adminController->setErrorMessage("يرجى رفع ملف CSV فقط.");
                    $action = 'upload';
                } else if (empty($default_organizer)) {
                    $adminController->setErrorMessage("يرجى تحديد اسم الجهة الافتراضية.");
                    $action = 'upload';
                } else {
                    // Save to temp directory
                    $temp_dir = '../uploads/temp/';
                    if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);
                    $temp_file = $temp_dir . 'import_' . uniqid() . '.csv';
                    
                    if (move_uploaded_file($file['tmp_name'], $temp_file)) {
                        // Process the CSV for preview
                        $preview_data = generatePreviewData($pdo, $temp_file, $default_organizer, $column_mapping);
                        if ($preview_data === false) {
                            $adminController->setErrorMessage("الملف لا يحتوي على بيانات أو غير صالح.");
                            @unlink($temp_file);
                            $action = 'upload';
                        }
                    } else {
                        $adminController->setErrorMessage("فشل في حفظ الملف المؤقت.");
                        $action = 'upload';
                    }
                }
            }
        } elseif ($action === 'import') {
            // STEP 2: Process Actual Import
            $temp_file = $_POST['temp_file'] ?? '';
            $default_organizer = $_POST['default_organizer'] ?? '';
            $selected_rows = $_POST['selected_rows'] ?? [];
            
            if (empty($temp_file) || !file_exists($temp_file)) {
                $adminController->setErrorMessage("الملف المؤقت مفقود أو منتهي الصلاحية. يرجى إعادة الرفع.");
                $action = 'upload';
            } elseif (empty($selected_rows)) {
                $adminController->setWarningMessage("لم يتم تحديد أي برامج للاستيراد.");
                $action = 'upload';
                @unlink($temp_file);
            } else {
                $import_results = performImport($pdo, $temp_file, $default_organizer, $selected_rows, $column_mapping);
                @unlink($temp_file);
                $action = 'upload'; // Return to initial state but show results
                
                if ($import_results['imported'] > 0) {
                    $adminController->setSuccessMessage("تم استيراد {$import_results['imported']} برنامج بنجاح.");
                }
                if ($import_results['skipped'] > 0) {
                    $adminController->setWarningMessage("تم تخطي {$import_results['skipped']} برامج.");
                }
                if (!empty($import_results['errors'])) {
                    $adminController->setErrorMessage("حدثت بعض الأخطاء أثناء الاستيراد. راجع التفاصيل.");
                }
            }
        } elseif ($action === 'upload') {
            // Cancel action from preview screen goes back to upload
            $temp_file = $_POST['temp_file'] ?? '';
            if (!empty($temp_file) && file_exists($temp_file)) {
                @unlink($temp_file);
            }
        }
    }
}

// Function to generate preview data
function generatePreviewData($pdo, $temp_file, $default_organizer_name, $column_mapping) {
    $handle = fopen($temp_file, 'r');
    if (!$handle) return false;
    
    // Handle BOM
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);
    
    $headers = fgetcsv($handle);
    if (!$headers) { fclose($handle); return false; }
    
    // Normalize headers
    $headers = array_map(function($h) { return trim(trim($h), "\"'\t "); }, $headers);
    
    // Function to normalize Arabic strings and remove spaces for robust matching
    $normalize_string = function($str) {
        $str = preg_replace('/\s+/', '', $str);
        $str = preg_replace('/[أإآا]/u', 'ا', $str);
        $str = preg_replace('/[يى]/u', 'ي', $str);
        $str = preg_replace('/[ةه]/u', 'ه', $str);
        return $str;
    };
    
    $normalized_mapping = [];
    foreach ($column_mapping as $csv_col => $db_col) {
        $normalized_mapping[$normalize_string($csv_col)] = $db_col;
    }

    // Map CSV columns
    $db_columns = [];
    foreach ($headers as $index => $header) {
        $norm_header = $normalize_string($header);
        if (isset($normalized_mapping[$norm_header])) {
            $db_columns[$index] = $normalized_mapping[$norm_header];
        }
    }
    
    // Setup organizer ID cache
    $org_cache = [];
    if ($default_organizer_name !== 'from_csv') {
        $stmt = $pdo->prepare("SELECT id FROM organizers WHERE name = ?");
        $stmt->execute([$default_organizer_name]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) $org_cache[$default_organizer_name] = $existing['id'];
        else $org_cache[$default_organizer_name] = 'NEW';
    }
    
    $rows = [];
    $row_num = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $row_num++;
        $data = [];
        foreach ($db_columns as $col_index => $db_col) {
            if (isset($row[$col_index])) {
                $data[$db_col] = trim($row[$col_index]);
            }
        }
        
        if (empty($data['title']) && empty($data['organizer_name'])) continue;
        
        // Determine Organizer logic
        $org_name = ($default_organizer_name === 'from_csv' && !empty($data['organizer_name'])) 
                    ? $data['organizer_name'] 
                    : $default_organizer_name;
                    
        $resolved_org_id = null;
        if (!isset($org_cache[$org_name])) {
            $stmt = $pdo->prepare("SELECT id FROM organizers WHERE name = ?");
            $stmt->execute([$org_name]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            $org_cache[$org_name] = $existing ? $existing['id'] : 'NEW';
        }
        $resolved_org_id = $org_cache[$org_name];
        
        $title = $data['title'] ?? '';
        $venue = $data['venue_name'] ?? $data['location'] ?? '';
        
        $status = 'new';
        $status_text = 'جديد';
        $is_selectable = true;
        
        if ($resolved_org_id !== 'NEW') {
            // Check for duplicates
            // 1. Exact duplicate (Title + Organizer + Venue)
            $stmt = $pdo->prepare("SELECT id, venue_name, location FROM programs WHERE title = ? AND organizer_id = ?");
            $stmt->execute([$title, $resolved_org_id]);
            $existing_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($existing_programs) > 0) {
                $is_exact = false;
                foreach ($existing_programs as $ep) {
                    $ep_venue = $ep['venue_name'] ?? $ep['location'] ?? '';
                    if ($ep_venue == $venue) {
                        $is_exact = true;
                        break;
                    }
                }
                
                if ($is_exact) {
                    $status = 'duplicate';
                    $status_text = 'مكرر تماماً';
                    $is_selectable = false;
                } else {
                    $status = 'partial';
                    $status_text = 'نفس الاسم لمقر آخر';
                }
            }
        }
        
        $start_date = HijriDate::normalizeToGregorian($data['start_date'] ?? '');
        $end_date = HijriDate::normalizeToGregorian($data['end_date'] ?? '');

        $rows[] = [
            'row_num' => $row_num,
            'title' => $title,
            'organizer' => $org_name,
            'venue' => $venue,
            'start_date' => $start_date ?? '-',
            'status' => $status,
            'status_text' => $status_text,
            'is_selectable' => $is_selectable
        ];
    }
    
    fclose($handle);
    return $rows;
}

// Function to perform the actual import
function performImport($pdo, $temp_file, $default_organizer_name, $selected_rows, $column_mapping) {
    // Almost same logic as before but filter by selected_rows array
    $handle = fopen($temp_file, 'r');
    if (!$handle) return ['imported' => 0, 'skipped' => 0, 'errors' => ["لا يمكن فتح الملف المؤقت."]];
    
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);
    
    $headers = fgetcsv($handle);
    $headers = array_map(function($h) { return trim(trim($h), "\"'\t "); }, $headers);
    
    $normalize_string = function($str) {
        $str = preg_replace('/\s+/', '', $str);
        $str = preg_replace('/[أإآا]/u', 'ا', $str);
        $str = preg_replace('/[يى]/u', 'ي', $str);
        $str = preg_replace('/[ةه]/u', 'ه', $str);
        return $str;
    };
    
    $normalized_mapping = [];
    foreach ($column_mapping as $csv_col => $db_col) {
        $normalized_mapping[$normalize_string($csv_col)] = $db_col;
    }

    $db_columns = [];
    foreach ($headers as $index => $header) {
        $norm_header = $normalize_string($header);
        if (isset($normalized_mapping[$norm_header])) {
            $db_columns[$index] = $normalized_mapping[$norm_header];
        }
    }
    
    $organizer_id = null;
    if ($default_organizer_name !== 'from_csv') {
        $stmt = $pdo->prepare("SELECT id FROM organizers WHERE name = ?");
        $stmt->execute([$default_organizer_name]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) $organizer_id = $existing['id'];
        else {
            $stmt = $pdo->prepare("INSERT INTO organizers (name) VALUES (?)");
            $stmt->execute([$default_organizer_name]);
            $organizer_id = $pdo->lastInsertId();
        }
    }
    
    $imported = 0;
    $skipped = 0;
    $errors = [];
    
    // Get programs table schema dynamically
    $stmt = $pdo->query("DESCRIBE programs");
    $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $valid_db_cols = [];
    foreach ($table_columns_info as $col) {
        $valid_db_cols[$col['Field']] = $col;
    }
    
    $row_num = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $row_num++;
        
        // Skip if not selected
        if (!in_array((string)$row_num, $selected_rows)) {
            $skipped++;
            continue;
        }
        
        $data = [];
        foreach ($db_columns as $col_index => $db_col) {
            if (isset($row[$col_index])) $data[$db_col] = trim($row[$col_index]);
        }
        
        if (empty($data['title']) && empty($data['organizer_name'])) continue;
        
        try {
            $pdo->beginTransaction();
            
            // Resolve organizer
            if ($default_organizer_name === 'from_csv' && !empty($data['organizer_name'])) {
                $stmt = $pdo->prepare("SELECT id FROM organizers WHERE name = ?");
                $stmt->execute([$data['organizer_name']]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $resolved_org_id = $existing['id'];
                    $stmt = $pdo->prepare("UPDATE organizers SET sub_name = COALESCE(NULLIF(?, ''), sub_name), communication_officer_name = COALESCE(NULLIF(?, ''), communication_officer_name), communication_officer_phone = COALESCE(NULLIF(?, ''), communication_officer_phone) WHERE id = ?");
                    $stmt->execute([$data['organizer_department'] ?? null, $data['entry_officer_name'] ?? null, $data['entry_officer_phone'] ?? null, $resolved_org_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO organizers (name, sub_name, communication_officer_name, communication_officer_phone) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$data['organizer_name'], $data['organizer_department'] ?? null, $data['entry_officer_name'] ?? null, $data['entry_officer_phone'] ?? null]);
                    $resolved_org_id = $pdo->lastInsertId();
                }
            } else {
                $resolved_org_id = $organizer_id;
            }
            
            // Build Program Data
            $program_data = [
                'organizer_id' => $resolved_org_id,
                'title' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'program_type' => $data['program_type'] ?? null,
                'Direction' => $data['Direction'] ?? null,
                'venue_name' => $data['venue_name'] ?? $data['location'] ?? null,
                'location' => $data['location'] ?? null,
                'attendance_type' => $data['attendance_type'] ?? 'حضوري',
                'start_date' => HijriDate::normalizeToGregorian($data['start_date'] ?? ''),
                'end_date' => HijriDate::normalizeToGregorian($data['end_date'] ?? ''),
                'duration' => $data['duration'] ?? null,
                'is_free' => ($data['is_free'] ?? '') === 'مدفوع' ? '0' : '1',
                'age_group' => $data['age_group'] ?? null,
                'target_notes' => $data['target_notes'] ?? null,
                'price' => $data['price'] ?? null,
                'price_notes' => $data['price_notes'] ?? null,
                'registration_link' => $data['registration_link'] ?? null,
                'google_map' => $data['google_map'] ?? null,
                'ad_link' => $data['ad_link'] ?? null,
                'program_notes' => $data['program_notes'] ?? null,
                'status' => 'reviewed',
            ];
            
            $cols = [];
            $placeholders = [];
            $params = [];
            
            foreach ($program_data as $col_name => $val) {
                if (isset($valid_db_cols[$col_name])) {
                    $cols[] = "`$col_name`";
                    $placeholders[] = '?';
                    $params[] = (empty($val) && $valid_db_cols[$col_name]['Null'] === 'YES') ? null : $val;
                }
            }
            
            if (!empty($params)) {
                $sql = "INSERT INTO programs (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $pdo->commit();
                $imported++;
            } else {
                $pdo->rollBack();
                $skipped++;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = "الصف {$row_num}: " . $e->getMessage();
        }
    }
    
    fclose($handle);
    return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
}

$adminController->renderHeader($page_title);
$adminController->renderMessages();
?>

<div class="import-section">
    <?php if ($action === 'upload'): ?>
        <!-- STEP 1: UPLOAD FORM -->
        <div class="import-card">
            <h2><i class="fas fa-file-import"></i> استيراد برامج من CSV (تفاعلي)</h2>
            
            <div class="import-info">
                <p><i class="fas fa-info-circle"></i> يتيح لك هذا النموذج رفع الردود من جوجل ومعاينة البرامج واختيار ما تود استيراده.</p>
                <p>النظام سيكتشف التكرار تلقائياً بناءً على <strong>(اسم البرنامج + الجهة + المقر)</strong> لمنع تكرار البرامج المتطابقة.</p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="import-form">
                <input type="hidden" name="action" value="preview">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                
                <div class="form-group">
                    <label for="csv_file"><i class="fas fa-file-csv"></i> اختر ملف CSV</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                </div>
                
                <div class="form-group">
                    <label for="default_organizer"><i class="fas fa-building"></i> الجهة الافتراضية</label>
                    <select id="default_organizer" name="default_organizer">
                        <option value="from_csv">تحديد من ملف CSV (حسب عمود اسم الجهة)</option>
                        <?php
                        $orgs = $pdo->query("SELECT id, name FROM organizers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($orgs as $org) {
                            echo "<option value='" . htmlspecialchars($org['name'], ENT_QUOTES) . "'>" . htmlspecialchars($org['name']) . "</option>";
                        }
                        ?>
                    </select>
                    <span class="form-note">اختر 'تحديد من ملف CSV' إذا كان الملف يحتوي على أسماء الجهات.</span>
                </div>
                
                <div class="form-actions-container">
                    <a href="manage_programs.php" class="back-btn-inline"><i class="fas fa-arrow-right"></i> رجوع</a>
                    <button type="submit" class="submit-btn"><i class="fas fa-search"></i> قراءة ومعاينة البرامج</button>
                </div>
            </form>
            
            <?php if (!empty($import_results)): ?>
            <div class="import-results mt-4">
                <h3><i class="fas fa-chart-bar"></i> نتائج الاستيراد السابق</h3>
                <div class="result-stats">
                    <div class="stat success">
                        <span class="stat-number"><?php echo $import_results['imported'] ?? 0; ?></span>
                        <span class="stat-label">تم الاستيراد</span>
                    </div>
                    <div class="stat warning">
                        <span class="stat-number"><?php echo $import_results['skipped'] ?? 0; ?></span>
                        <span class="stat-label">تم التخطي</span>
                    </div>
                </div>
                <?php if (!empty($import_results['errors'])): ?>
                    <div class="result-details">
                        <h4>أخطاء:</h4>
                        <ul>
                            <?php foreach ($import_results['errors'] as $e): ?>
                                <li><?php echo htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'preview'): ?>
        <!-- STEP 2: PREVIEW FORM -->
        <div class="import-card preview-card">
            <h2><i class="fas fa-list-check"></i> معاينة وتحديد البرامج للاستيراد</h2>
            
            <form method="POST" id="import_preview_form">
                <!-- Action default to import, but we have a cancel button that changes it to upload -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                <input type="hidden" name="temp_file" value="<?php echo htmlspecialchars($temp_file); ?>">
                <input type="hidden" name="default_organizer" value="<?php echo htmlspecialchars($default_organizer); ?>">
                
                <div class="table-responsive">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="select_all" checked>
                                </th>
                                <th>اسم البرنامج</th>
                                <th>الجهة</th>
                                <th>المقر/الحي</th>
                                <th>تاريخ البداية</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preview_data as $row): ?>
                            <tr class="status-<?php echo $row['status']; ?>">
                                <td>
                                    <?php if ($row['is_selectable']): ?>
                                        <input type="checkbox" name="selected_rows[]" value="<?php echo $row['row_num']; ?>" class="row-checkbox" checked>
                                    <?php else: ?>
                                        <input type="checkbox" disabled>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['organizer']); ?></td>
                                <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo $row['status']; ?>">
                                        <?php echo $row['status_text']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($preview_data)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">لا توجد برامج يمكن قراءتها من الملف.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="form-actions-container mt-4" style="justify-content: space-between;">
                    <button type="submit" name="action" value="upload" class="cancel-btn" formnovalidate><i class="fas fa-times"></i> إلغاء وحذف المؤقت</button>
                    <button type="submit" name="action" value="import" class="submit-btn"><i class="fas fa-check-circle"></i> اعتماد واستيراد المحدد</button>
                </div>
            </form>
        </div>
        
        <script>
        document.getElementById('select_all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.row-checkbox');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });
        </script>

    <?php endif; ?>
</div>

<style>
.import-section {
    max-width: 1000px;
    margin: 20px auto;
}
.import-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 2rem;
}
.preview-card {
    max-width: 100%;
}
.import-card h2 {
    color: #8a2be2;
    margin-bottom: 1.5rem;
    text-align: center;
}
.import-info {
    background: #f0f4ff;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    border-right: 4px solid #8a2be2;
}
.import-form .form-group {
    margin-bottom: 20px;
}
.import-form label {
    font-weight: 500;
    display: block;
    margin-bottom: 8px;
    color: #212529;
}
.import-form input[type="file"],
.import-form select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-family: 'Tajawal', sans-serif;
    font-size: 0.95rem;
}
.table-responsive {
    overflow-x: auto;
}
.preview-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.preview-table th, .preview-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    text-align: right;
}
.preview-table th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
}
.status-duplicate { background-color: #fff5f5; color: #7f8c8d; }
.status-partial { background-color: #fff9e6; }
.status-new { background-color: #f0fff4; }

.status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}
.badge-new { background: #d4edda; color: #155724; }
.badge-partial { background: #fff3cd; color: #856404; }
.badge-duplicate { background: #f8d7da; color: #721c24; }

.cancel-btn {
    background: #f1f3f5;
    color: #495057;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-family: 'Tajawal', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.cancel-btn:hover { background: #e9ecef; color: #dc3545;}
.mt-4 { margin-top: 1.5rem; }

.result-stats { display: flex; gap: 20px; margin-bottom: 20px; }
.stat { padding: 15px 25px; border-radius: 8px; text-align: center; flex: 1; }
.stat.success { background: #d4edda; color: #155724; }
.stat.warning { background: #fff3cd; color: #856404; }
.stat-number { display: block; font-size: 2rem; font-weight: 700; }
.result-details li { padding: 5px 10px; margin-bottom: 5px; background: white; border-radius: 4px; border-right: 3px solid #ff6b6b; font-size: 0.9rem; }
</style>

<?php $adminController->renderFooter(); ?>