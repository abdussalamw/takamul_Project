<?php
session_start();
include '../includes/db_connect.php';

// Ensure the PhpSpreadsheet library is available
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    die("Error: The PhpSpreadsheet library is not found. Please install it via Composer: `composer require phpoffice/phpspreadsheet`");
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// Security check
if (empty($_SESSION['admin_id']) || empty($_SESSION['permissions']['can_add_programs'])) {
    header('Location: dashboard.php?status=unauthorized');
    exit;
}

$page_title_text = 'استيراد برامج من إكسل';
$error = null;
$success = null;
$import_summary = [];

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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
?>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title_text; ?> 📥</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- The new beautiful styles -->
    <style>
        :root { 
            --primary: #8a2be2; 
            --secondary: #ff6b6b; 
            --accent: #4ecdc4; 
            --light: #f8f9fa; 
            --dark: #212529; 
            --success: #28a745; 
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Tajawal', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%); 
            color: var(--dark); 
            line-height: 1.6;
            min-height: 100vh;
        }
        header {
            background: linear-gradient(120deg, var(--primary), #5c1d9c);
            color: white;
            padding: 0.5rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .logo { display: flex; align-items: center; gap: 15px; }
        .logo-image { width: 60px; height: 60px; object-fit: contain; }
        .logo-text { font-size: 1.5rem; font-weight: 800; }
        .page-title-header { display: flex; align-items: center; font-size: 1.1rem; font-weight: 700; }
        .page-title-header i { margin-left: 10px; color: var(--accent); font-size: 1.2rem; }
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav a { color: white; text-decoration: none; font-weight: 500; padding: 10px 20px; border-radius: 30px; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        nav a:hover { background: rgba(255, 255, 255, 0.15); }
        
        .import-section { max-width: 800px; width: 100%; margin: 40px auto; padding: 20px; }
        .import-card { background: white; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); padding: 2.5rem; text-align: right; }
        .import-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
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
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }

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
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="https://i.postimg.cc/sxNCrL6d/logo-white-03.png" alt="شعار" class="logo-image">
                <div class="logo-text">دليل البرامج الصيفية</div>
            </div>
            <div class="page-title-header">
                <i class="fas fa-file-import"></i>
                <span><?php echo htmlspecialchars($page_title_text); ?></span>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="import-section">
        <div class="import-card">
            <h2><i class="fas fa-file-excel"></i> استيراد برامج من ملف إكسل</h2>
            
            <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>

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
                <h4>تعليمات الاستيراد:</h4>
                <ul>
                    <li>يجب أن يكون الملف بصيغة `.xlsx`.</li>
                    <li>يجب أن يحتوي الصف الأول على أسماء الأعمدة باللغة العربية كما في ملف التصدير.</li>
                    <li>عمود "عنوان البرنامج" إجباري لكل برنامج.</li>
                    <li>سيتم تعيين حالة جميع البرامج المستوردة إلى "تمت المراجعة" تلقائياً.</li>
                    <li>يمكنك <a href="export.php" style="font-weight: bold; color: var(--primary);">تصدير ملف إكسل</a> لاستخدامه كقالب.</li>
                </ul>
            </div>

            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
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
</body>
</html>