<?php
// Ensure the PhpSpreadsheet library is available
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    die("Error: The PhpSpreadsheet library is not found. Please install it via Composer: `composer require phpoffice/phpspreadsheet`");
}
use PhpOffice\PhpSpreadsheet\IOFactory;

// Initialize dependencies and controller
include_once '../includes/db_connect.php';
include_once 'AdminController.php';
$adminController = new AdminController($pdo);

// Page settings
$page_title = 'استيراد وتصدير البرامج';
$error = null;
$success = null;
$import_summary = [];

// Security check: require permission before any output
$adminController->requirePermission('can_add_programs');

// Handle POST logic before rendering the view
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "فشل التحقق من الطلب (CSRF).";
    } elseif (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['excel_file']['tmp_name'];
        $file_name = $_FILES['excel_file']['name'];

        // --- Server-side File Validation ---
        $allowed_extensions = ['xlsx'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // A more robust check using MIME type is better if fileinfo extension is enabled
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_mime_type = finfo_file($finfo, $file_tmp_path);
        finfo_close($finfo);
        $allowed_mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        if (!in_array($file_extension, $allowed_extensions) || $file_mime_type !== $allowed_mime_type) {
            $error = "نوع الملف غير صالح. يرجى رفع ملف بصيغة .xlsx فقط. 🚫";
        } else {
        
        try {
            $spreadsheet = IOFactory::load($file_tmp_path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);

            if (count($data) < 2) {
                $error = "ملف الإكسل فارغ أو يحتوي على صف الرأس فقط.";
            } else {
                // Map Arabic headers to DB columns
                $header_row = array_shift($data); // Get and remove header row
                $header_map = [
                    'عنوان البرنامج' => 'title', 'الجهة المنظمة' => 'organizer', 'الوصف' => 'description',
                    'المنطقة/الاتجاه' => 'Direction', 'الموقع (الحي)' => 'location', 'تاريخ البدء' => 'start_date',
                    'تاريخ الانتهاء' => 'end_date', 'المدة' => 'duration', 'الفئة العمرية' => 'age_group',
                    'السعر' => 'price', 'رابط التسجيل' => 'registration_link', 'رابط خرائط جوجل' => 'google_map',
                ];

                $db_columns_map = [];
                foreach ($header_row as $col_letter => $header_text) {
                    if (isset($header_map[trim($header_text)])) {
                        $db_columns_map[$col_letter] = $header_map[trim($header_text)];
                    }
                }

                if (empty($db_columns_map) || !in_array('title', $db_columns_map)) {
                     $error = "ملف الإكسل لا يحتوي على الأعمدة المطلوبة أو أن أسماء الأعمدة غير متطابقة. يجب أن يحتوي على 'عنوان البرنامج' على الأقل.";
                } else {
                    $imported_count = 0;
                    $failed_count = 0;
                    $failed_rows_details = [];

                    $pdo->beginTransaction();

                    foreach ($data as $row_index => $row) {
                        $program_data = [];
                        foreach ($db_columns_map as $col_letter => $db_col) {
                            $program_data[$db_col] = $row[$col_letter] ?? null;
                        }

                        // Basic validation: title is required
                        if (empty(trim($program_data['title']))) {
                            $failed_count++;
                            $failed_rows_details[] = "الصف " . $row_index . ": حقل 'عنوان البرنامج' فارغ.";
                            continue;
                        }

                        // Imported programs are considered reviewed by default
                        $program_data['status'] = 'reviewed'; 
                        
                        $columns_to_insert = array_keys($program_data);
                        $placeholders = array_fill(0, count($columns_to_insert), '?');
                        
                        $sql = "INSERT INTO programs (" . implode(', ', array_map(fn($c) => "`$c`", $columns_to_insert)) . ") VALUES (" . implode(', ', $placeholders) . ")";
                        $stmt = $pdo->prepare($sql);
                        
                        if ($stmt->execute(array_values($program_data))) {
                            $imported_count++;
                        } else {
                            $failed_count++;
                            $failed_rows_details[] = "الصف " . $row_index . ": فشل الإدخال في قاعدة البيانات.";
                        }
                    }

                    $pdo->commit();

                    $success = "اكتملت عملية الاستيراد!";
                    $import_summary = [
                        'imported' => $imported_count,
                        'failed' => $failed_count,
                        'errors' => $failed_rows_details
                    ];
                }
            }
        } catch (Exception $e) {
            $error = "حدث خطأ أثناء قراءة ملف الإكسل: " . $e->getMessage();
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
        } // End of file validation else block
    } else {
        $error = "يرجى اختيار ملف إكسل لرفعه.";
    }
}

// Render the view
$adminController->renderHeader($page_title);
if ($error) {
    $adminController->setErrorMessage($error);
}
if ($success) {
    $adminController->setSuccessMessage($success);
}
$adminController->renderMessages();
?>
<style>
    .import-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .import-card h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 3px;
            background: var(--secondary);
            border-radius: 2px;
        }
        .instructions {
            background-color: #f8f9fa;
            border-right: 4px solid var(--accent);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
        }
        .instructions h4 { margin-top: 0; color: var(--dark); font-size: 1.2rem; margin-bottom: 1rem; }
        .instructions ul { padding-right: 20px; list-style-type: '✓ '; }
        .instructions ul li { margin-bottom: 0.5rem; }

        .upload-form { margin-top: 2rem; }
        .file-upload-wrapper {
            border: 3px dashed #ccc;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        .file-upload-wrapper:hover { border-color: var(--primary); background-color: #fdfaff; }
        .file-upload-wrapper input[type="file"] { display: none; }
        .file-upload-label { font-size: 1.1rem; color: #555; font-weight: 500; }
        .file-upload-label i { font-size: 2.5rem; color: var(--primary); display: block; margin-bottom: 1rem; }
        #file-name-display { margin-top: 1rem; font-weight: bold; color: var(--accent); }

        .upload-btn { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 1.1rem; margin-top: 1.5rem; width: 100%; }
        .upload-btn:hover { background: #7a1fc2; transform: translateY(-2px); }
        .upload-btn:disabled { background: #ccc; cursor: not-allowed; transform: none; }

        .summary { margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid #eee; }
        .summary h4 { font-size: 1.2rem; margin-bottom: 1rem; }
        .summary .success { color: var(--success); }
        .summary .error { color: var(--secondary); }
        .summary ul { max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef; list-style-position: inside; }
        .summary ul li { padding: 5px; border-bottom: 1px solid #e9ecef; }
        .summary ul li:last-child { border-bottom: none; }

        /* New styles for export section */
        .export-section-card {
            background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
            border: 1px solid var(--accent);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: right;
        }
        .export-section-card h3 {
            color: #00796b;
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .export-section-card p {
            color: #004d40;
            margin-bottom: 1rem;
        }
        .export-btn {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .export-btn:hover {
            background: #26a69a;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
</style>

<section class="dashboard-section">
    <div class="dashboard-card import-card">
        <h2><i class="fas fa-exchange-alt"></i> <?php echo htmlspecialchars($page_title); ?></h2>

        <div class="content-wrapper">
            <!-- Export Section -->
            <div class="export-section-card">
                <h3><i class="fas fa-file-export"></i> تصدير البرامج</h3>
                <p>يمكنك تصدير جميع البرامج الموجودة في النظام إلى ملف Excel. هذا الملف يمكن استخدامه كقالب لعملية الاستيراد لاحقًا.</p>
                <a href="export.php" class="export-btn"><i class="fas fa-download"></i> تحميل ملف التصدير</a>
            </div>


            <?php if (!empty($import_summary)): ?>
            <div class="summary">
                <h4>ملخص عملية الاستيراد:</h4>
                <p><strong class="success">تم استيراد <?php echo $import_summary['imported']; ?> برنامج بنجاح.</strong></p>
                <?php if (!empty($import_summary['errors'])): ?>
                    <p><strong class="error">فشل استيراد <?php echo $import_summary['failed']; ?> برنامج.</strong></p>
                    <p>تفاصيل الأخطاء:</p>
                    <ul>
                        <?php foreach ($import_summary['errors'] as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="instructions">
                <h3><i class="fas fa-file-import"></i> استيراد برامج من ملف إكسل</h3>
                <h4>تعليمات الاستيراد:</h4>
                <ul>
                    <li>يجب أن يكون الملف بصيغة `.xlsx`.</li>
                    <li>يجب أن يحتوي الصف الأول على أسماء الأعمدة باللغة العربية كما في ملف التصدير.</li>
                    <li>عمود "عنوان البرنامج" إجباري لكل برنامج.</li>
                    <li>سيتم تعيين حالة جميع البرامج المستوردة إلى "تمت المراجعة" تلقائياً.</li>
                    <li>يمكنك استخدام قسم التصدير أعلاه لإنشاء ملف Excel لاستخدامه كقالب.</li>
                </ul>
            </div>

            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                <div class="file-upload-wrapper">
                    <input type="file" name="excel_file" id="excel_file" required accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    <label for="excel_file" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>اسحب الملف إلى هنا أو انقر للاختيار</span>
                    </label>
                    <div id="file-name-display"></div>
                </div>
                <button type="submit" class="upload-btn" id="upload-btn" disabled><i class="fas fa-upload"></i> رفع واستيراد</button>
            </form>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('excel_file');
        const fileNameDisplay = document.getElementById('file-name-display');
        const uploadBtn = document.getElementById('upload-btn');

        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = `الملف المختار: ${fileInput.files[0].name}`;
                uploadBtn.disabled = false;
            } else {
                fileNameDisplay.textContent = '';
                uploadBtn.disabled = true;
            }
        });
    });
</script>

<?php $adminController->renderFooter(); ?>
