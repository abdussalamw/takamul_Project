<?php
// import.php - CSV Import Script for Programs
// Allows importing programs from Google Forms CSV export

include_once '../includes/db_connect.php';
include_once 'AdminController.php';

$adminController = new AdminController($pdo);
$page_title = 'استيراد برامج من CSV';
$adminController->requirePermission('can_add_programs', 'manage_programs.php');

$import_results = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    if (!$adminController->verifyCSRFToken($_POST['csrf_token'])) {
        $adminController->setErrorMessage("فشل التحقق من الطلب (CSRF).");
    } else {
        $file = $_FILES['csv_file'];
        $errors = [];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "خطأ في رفع الملف.";
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $errors[] = "يرجى رفع ملف CSV فقط.";
        }
        
        if (empty($errors)) {
            // Use the CSV mapping to Google Forms columns
            // Google Forms exports columns in Arabic as header names
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
            
            // Default organizer
            $default_organizer_name = $_POST['default_organizer'] ?? '';
            if (empty($default_organizer_name)) {
                $errors[] = "يرجى تحديد اسم الجهة الافتراضية أو اختيار 'تحديد من CSV'.";
            }
            
            if (empty($errors)) {
                try {
                    // Open CSV file with UTF-8 BOM handling
                    $handle = fopen($file['tmp_name'], 'r');
                    if (!$handle) {
                        $errors[] = "لا يمكن فتح الملف.";
                    } else {
                        // Check for BOM
                        $bom = fread($handle, 3);
                        if ($bom !== "\xEF\xBB\xBF") {
                            rewind($handle); // No BOM, start from beginning
                        }
                        
                        // Read header row
                        $headers = fgetcsv($handle);
                        if (!$headers) {
                            $errors[] = "الملف لا يحتوي على بيانات (صف الرؤوس مفقود).";
                        } else {
                            // Normalize headers: trim and handle extra spaces
                            $headers = array_map(function($h) {
                                return trim(trim($h), "\"'\t ");
                            }, $headers);
                            
                            // Map CSV columns to DB columns
                            $db_columns = [];
                            $unmatched_headers = [];
                            foreach ($headers as $index => $header) {
                                if (isset($column_mapping[$header])) {
                                    $db_columns[$index] = $column_mapping[$header];
                                } else {
                                    // Try to find partial match
                                    $found = false;
                                    foreach ($column_mapping as $csv_col => $db_col) {
                                        if (strpos($header, $csv_col) !== false || strpos($csv_col, $header) !== false) {
                                            $db_columns[$index] = $db_col;
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        $unmatched_headers[] = $header;
                                    }
                                }
                            }
                            
                            // Initialize counters
                            $imported = 0;
                            $skipped = 0;
                            $row_errors = [];
                            
                            // Find organizer or create it
                            $organizer_id = null;
                            if ($default_organizer_name !== 'from_csv') {
                                // Find existing organizer or create new one
                                $stmt = $pdo->prepare("SELECT id FROM organizers WHERE name = ?");
                                $stmt->execute([$default_organizer_name]);
                                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($existing) {
                                    $organizer_id = $existing['id'];
                                } else {
                                    $stmt = $pdo->prepare("INSERT INTO organizers (name) VALUES (?)");
                                    $stmt->execute([$default_organizer_name]);
                                    $organizer_id = $pdo->lastInsertId();
                                }
                            }
                            
                            // Read data rows
                            $row_num = 1; // Header is row 1
                            while (($row = fgetcsv($handle)) !== false) {
                                $row_num++;
                                $data = [];
                                
                                // Map CSV row to associative array
                                foreach ($db_columns as $col_index => $db_col) {
                                    if (isset($row[$col_index])) {
                                        $data[$db_col] = trim($row[$col_index]);
                                    }
                                }
                                
                                // Skip empty rows
                                if (empty($data['title']) && empty($data['organizer_name'])) {
                                    continue;
                                }
                                
                                try {
                                    $pdo->beginTransaction();
                                    
                                    // Handle organizer
                                    if ($default_organizer_name === 'from_csv' && !empty($data['organizer_name'])) {
                                        $stmt = $pdo->prepare("SELECT id FROM organizers WHERE name = ?");
                                        $stmt->execute([$data['organizer_name']]);
                                        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                                        if ($existing) {
                                            $resolved_org_id = $existing['id'];
                                            // Update organizer info
                                            $stmt = $pdo->prepare("UPDATE organizers SET 
                                                sub_name = COALESCE(NULLIF(?, ''), sub_name),
                                                entry_officer_name = COALESCE(NULLIF(?, ''), entry_officer_name),
                                                entry_officer_phone = COALESCE(NULLIF(?, ''), entry_officer_phone)
                                                WHERE id = ?");
                                            $stmt->execute([
                                                $data['organizer_department'] ?? null,
                                                $data['entry_officer_name'] ?? null,
                                                $data['entry_officer_phone'] ?? null,
                                                $resolved_org_id
                                            ]);
                                        } else {
                                            $stmt = $pdo->prepare("INSERT INTO organizers (name, department, entry_officer_name, entry_officer_phone) VALUES (?, ?, ?, ?)");
                                            $stmt->execute([
                                                $data['organizer_name'],
                                                $data['organizer_department'] ?? null,
                                                $data['entry_officer_name'] ?? null,
                                                $data['entry_officer_phone'] ?? null
                                            ]);
                                            $resolved_org_id = $pdo->lastInsertId();
                                        }
                                    } else {
                                        $resolved_org_id = $organizer_id;
                                    }
                                    
                                    // Prepare program data
                                    $program_data = [
                                        'organizer_id' => $resolved_org_id,
                                        'title' => $data['title'] ?? '',
                                        'description' => $data['description'] ?? '',
                                        'program_type' => $data['program_type'] ?? null,
                                        'Direction' => $data['Direction'] ?? null,
                                        'venue_name' => $data['venue_name'] ?? $data['location'] ?? null,
                                        'location' => $data['location'] ?? null,
                                        'attendance_type' => $data['attendance_type'] ?? 'حضوري',
                                        'start_date' => $data['start_date'] ?? null,
                                        'end_date' => $data['end_date'] ?? null,
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
                                    
                                    // Check if program with same title + organizer already exists
                                    $stmt = $pdo->prepare("SELECT id FROM programs WHERE title = ? AND organizer_id = ?");
                                    $stmt->execute([$program_data['title'], $resolved_org_id]);
                                    if ($stmt->fetch()) {
                                        // Skip - already exists
                                        $pdo->rollBack();
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    // Dynamic INSERT based on DB columns
                                    $db_cols = [];
                                    $placeholders = [];
                                    $params = [];
                                    
                                    $stmt = $pdo->query("DESCRIBE programs");
                                    $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($table_columns_info as $column_info) {
                                        $col_name = $column_info['Field'];
                                        if (in_array($col_name, ['id', 'created_at', 'submitted_at', 'latitude', 'longitude'])) continue;
                                        
                                        if (isset($program_data[$col_name])) {
                                            $db_cols[] = "`$col_name`";
                                            $placeholders[] = '?';
                                            $val = $program_data[$col_name];
                                            $params[] = (empty($val) && $column_info['Null'] === 'YES') ? null : $val;
                                        }
                                    }
                                    
                                    if (!empty($params)) {
                                        $sql = "INSERT INTO programs (" . implode(', ', $db_cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute($params);
                                        $pdo->commit();
                                        $imported++;
                                    } else {
                                        $pdo->rollBack();
                                        $skipped++;
                                    }
                                    
                                } catch (Exception $e) {
                                    if ($pdo->inTransaction()) {
                                        $pdo->rollBack();
                                    }
                                    $row_errors[] = "الصف {$row_num}: " . $e->getMessage();
                                    $skipped++;
                                }
                            }
                            
                            $import_results = [
                                'imported' => $imported,
                                'skipped' => $skipped,
                                'errors' => $row_errors,
                                'unmatched' => $unmatched_headers,
                            ];
                            
                            if ($imported > 0) {
                                $adminController->setSuccessMessage("تم استيراد {$imported} برنامج بنجاح.");
                            }
                            if ($skipped > 0) {
                                $adminController->setWarningMessage("تم تخطي {$skipped} صف.");
                            }
                        }
                        fclose($handle);
                    }
                } catch (Exception $e) {
                    $errors[] = "خطأ عام: " . $e->getMessage();
                }
            }
        }
        
        if (!empty($errors)) {
            $adminController->setErrorMessage(implode('<br>', $errors));
        }
    }
}

$adminController->renderHeader($page_title);
$adminController->renderMessages();
?>

<div class="import-section">
    <div class="import-card">
        <h2><i class="fas fa-file-import"></i> استيراد برامج من CSV</h2>
        
        <div class="import-info">
            <p><i class="fas fa-info-circle"></i> هذا النموذج يتيح استيراد البرامج من ملف CSV المصدر من <strong>نماذج جوجل</strong>.</p>
            <p>يجب أن يحتوي ملف CSV على الرؤوس بالعربية كما هي في نموذج حصر البرامج.</p>
        </div>

        <form method="POST" enctype="multipart/form-data" class="import-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
            
            <div class="form-group">
                <label for="csv_file"><i class="fas fa-file-csv"></i> اختر ملف CSV</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            
            <div class="form-group">
                <label for="default_organizer"><i class="fas fa-building"></i> الجهة الافتراضية</label>
                <select id="default_organizer" name="default_organizer">
                    <option value="from_csv">تحديد من ملف CSV (حسب اسم الجهة)</option>
                    <?php
                    $orgs = $pdo->query("SELECT id, name FROM organizers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($orgs as $org) {
                        echo "<option value='" . htmlspecialchars($org['name']) . "'>" . htmlspecialchars($org['name']) . "</option>";
                    }
                    ?>
                </select>
                <span class="form-note">اختر 'تحديد من ملف CSV' إذا كان الملف يحتوي على أسماء الجهات، أو اختر جهة معينة لربط جميع البرامج بها.</span>
            </div>
            
            <div class="form-actions-container">
                <a href="manage_programs.php" class="back-btn-inline"><i class="fas fa-arrow-right"></i> رجوع</a>
                <button type="submit" class="submit-btn"><i class="fas fa-upload"></i> استيراد</button>
            </div>
        </form>
        
        <?php if (!empty($import_results)): ?>
        <div class="import-results">
            <h3><i class="fas fa-chart-bar"></i> نتائج الاستيراد</h3>
            <div class="result-stats">
                <div class="stat success">
                    <span class="stat-number"><?php echo $import_results['imported']; ?></span>
                    <span class="stat-label">تم الاستيراد</span>
                </div>
                <div class="stat warning">
                    <span class="stat-number"><?php echo $import_results['skipped']; ?></span>
                    <span class="stat-label">تم التخطي</span>
                </div>
            </div>
            
            <?php if (!empty($import_results['unmatched'])): ?>
                <div class="result-details">
                    <h4>أعمدة غير متطابقة:</h4>
                    <ul>
                        <?php foreach ($import_results['unmatched'] as $h): ?>
                            <li><?php echo htmlspecialchars($h); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
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
</div>

<style>
.import-section {
    max-width: 800px;
    margin: 20px auto;
}

.import-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 2rem;
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

.import-info p {
    margin: 5px 0;
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

.import-form input[type="file"]:hover,
.import-form select:focus {
    border-color: #8a2be2;
}

.import-results {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.import-results h3 {
    color: #8a2be2;
    margin-bottom: 15px;
}

.result-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.stat {
    padding: 15px 25px;
    border-radius: 8px;
    text-align: center;
    flex: 1;
}

.stat.success {
    background: #d4edda;
    color: #155724;
}

.stat.warning {
    background: #fff3cd;
    color: #856404;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
}

.result-details {
    margin-top: 15px;
}

.result-details h4 {
    color: #495057;
    margin-bottom: 10px;
}

.result-details ul {
    list-style: none;
    padding: 0;
}

.result-details li {
    padding: 5px 10px;
    margin-bottom: 5px;
    background: white;
    border-radius: 4px;
    font-size: 0.9rem;
    border-right: 3px solid #ff6b6b;
}

.form-note {
    display: block;
    margin-top: 5px;
    font-size: 0.85rem;
    color: #666;
}
</style>

<?php
$adminController->renderFooter();
?>