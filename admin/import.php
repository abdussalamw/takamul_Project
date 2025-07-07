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
    <link rel="stylesheet" href="assets/css/admin_style.css"> <!-- Assuming a shared admin style -->
    <style>
        .import-card { background: white; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); padding: 2.5rem; }
        .import-card h2 { color: var(--primary); margin-bottom: 1.5rem; }
        .instructions { background-color: #f8f9fa; border-right: 4px solid var(--accent); padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .instructions h4 { margin-top: 0; }
        .instructions ul { padding-right: 20px; }
        .upload-form { display: flex; align-items: center; gap: 1rem; }
        .upload-form input[type="file"] { border: 2px dashed #ccc; padding: 20px; border-radius: 10px; flex-grow: 1; }
        .upload-form button { background: var(--primary); color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .summary { margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #eee; }
        .summary .success { color: var(--success); }
        .summary .error { color: var(--secondary); }
        .summary ul { max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; }
        .message.error { background-color: #f8d7da; color: #721c24; }
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
    <section class="dashboard-section">
        <div class="import-card">
            <h2><i class="fas fa-file-import"></i> استيراد برامج من ملف إكسل</h2>

            <?php if ($success): ?><div class="message success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>

            <?php if (!empty($import_summary)): ?>
            <div class="summary">
                <h4>ملخص عملية الاستيراد:</h4>
                <p><strong class="success">تم استيراد <?php echo $import_summary['imported']; ?> برنامج بنجاح.</strong></p>
                <p><strong class="error">فشل استيراد <?php echo $import_summary['failed']; ?> برنامج.</strong></p>
                <?php if (!empty($import_summary['errors'])): ?>
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
                <input type="file" name="excel_file" required accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                <button type="submit"><i class="fas fa-upload"></i> رفع واستيراد</button>
            </form>
        </div>
    </section>
</body>
</html>