<?php
session_start();
include '../includes/db_connect.php';

// --- تهيئة ---
$is_edit_mode = false;
$program_id = null;
$program = [];
$page_title = 'إضافة برنامج جديد';

// مصفوفة لترجمة أسماء الأعمدة وتحديد أنواع الحقول
$field_config = [
    'title'             => ['label' => 'عنوان البرنامج', 'type' => 'text', 'required' => true],
    'organizer'         => ['label' => 'الجهة المنظمة', 'type' => 'text', 'required' => true],
    'description'       => ['label' => 'وصف البرنامج', 'type' => 'textarea', 'required' => true],
    'Direction'         => ['label' => 'المنطقة/الاتجاه', 'type' => 'text', 'required' => true, 'placeholder' => 'مثال: شمال الرياض'],
    'location'          => ['label' => 'مكان البرنامج (الحي)', 'type' => 'text', 'required' => true, 'placeholder' => 'مثال: حي الملقا'],
    'start_date'        => ['label' => 'تاريخ البدء', 'type' => 'text', 'placeholder' => 'الصيغة: dd/mm/yyyy', 'required' => true],
    'end_date'          => ['label' => 'تاريخ الانتهاء (اختياري)', 'type' => 'text', 'placeholder' => 'الصيغة: dd/mm/yyyy', 'required' => false],
    'duration'          => ['label' => 'المدة', 'type' => 'text', 'placeholder' => 'مثال: 3 أسابيع', 'required' => true],
    'age_group'         => ['label' => 'الفئة العمرية', 'type' => 'text', 'placeholder' => 'مثال: 12-15 سنة', 'required' => true],
    'price'             => ['label' => 'رسوم البرنامج', 'type' => 'text', 'required' => true, 'placeholder' => 'اكتب "مجاني" أو السعر بالرقم'],
    'registration_link' => ['label' => 'رابط التسجيل', 'type' => 'url', 'required' => false],
];

// أعمدة يتم تجاهلها في النموذج
$ignore_columns = ['id'];

// التحقق إذا كنا في وضع التعديل
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit_mode = true;
    $program_id = $_GET['id'];
    $page_title = 'تعديل البرنامج';

    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$program) {
        die('البرنامج غير موجود.');
    }
}

// --- التعامل مع إرسال النموذج (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_to_process = [];
    
    // جلب الأعمدة من قاعدة البيانات لضمان الدقة
    $stmt = $pdo->query("DESCRIBE programs");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($columns as $column_name) {
        if (in_array($column_name, $ignore_columns)) {
            continue;
        }
        // قم بتجميع البيانات المرسلة من النموذج
        if (isset($_POST[$column_name])) {
            $data_to_process[$column_name] = trim($_POST[$column_name]);
        }
    }

    try {
        if ($is_edit_mode) {
            // --- وضع التحديث (UPDATE) ---
            $set_parts = [];
            $params = [];
            foreach ($data_to_process as $key => $value) {
                $set_parts[] = "`$key` = ?";
                $params[] = $value;
            }
            $params[] = $program_id; // لإضافته في جملة WHERE

            $sql = "UPDATE programs SET " . implode(', ', $set_parts) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success_message'] = 'تم تحديث البرنامج بنجاح!';

        } else {
            // --- وضع الإضافة (INSERT) ---
            $db_columns = array_keys($data_to_process);
            $placeholders = array_fill(0, count($db_columns), '?');
            $params = array_values($data_to_process);

            $sql = "INSERT INTO programs (" . implode(', ', $db_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $program_id = $pdo->lastInsertId(); // الحصول على ID البرنامج الجديد
            $_SESSION['success_message'] = 'تمت إضافة البرنامج بنجاح!';
        }

        // إعادة توجيه إلى نفس الصفحة لوضع التعديل لعرض البيانات المحدثة
        header("Location: manage_program.php?id=$program_id");
        exit;

    } catch (PDOException $e) {
        $error_message = "حدث خطأ في قاعدة البيانات: " . $e->getMessage();
    }
}

// جلب هيكل الجدول لإنشاء النموذج
try {
    $stmt = $pdo->query("DESCRIBE programs");
    $table_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في جلب هيكل الجدول: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap">
    <style>
        :root { --primary: #8a2be2; --secondary: #ff6b6b; --dark: #212529; --light: #f8f9fa; }
        body { font-family: 'Tajawal', sans-serif; background-color: #f4f7f6; color: var(--dark); margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 20px auto; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: var(--primary); }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 700; display: block; margin-bottom: 8px; color: #333; }
        input[type="text"], input[type="url"], input[type="number"], textarea {
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; transition: border-color 0.3s;
        }
        input:focus, textarea:focus { border-color: var(--primary); outline: none; }
        textarea { resize: vertical; min-height: 120px; }
        .btn {
            display: block; width: 100%; padding: 15px; background-color: var(--primary); color: #fff;
            border: none; border-radius: 5px; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: background-color 0.3s;
        }
        .btn:hover { background-color: #7a1fdb; }
        .required-star { color: var(--secondary); }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php
    // عرض رسائل النجاح أو الخطأ
    if (isset($_SESSION['success_message'])) {
        echo '<div class="message success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($error_message)) {
        echo '<div class="message error">' . $error_message . '</div>';
    }
    ?>

    <form action="manage_program.php<?php echo $is_edit_mode ? '?id=' . $program_id : ''; ?>" method="POST">
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            <?php foreach ($table_columns as $column):
                $column_name = $column['Field'];
                if (in_array($column_name, $ignore_columns)) continue;

                // للحقول المجاورة (تاريخ البدء والانتهاء)
                $group_class = '';
                if ($column_name === 'start_date' || $column_name === 'end_date') {
                    $group_class = 'form-group-half';
                }

                // الحصول على إعدادات الحقل من مصفوفة التهيئة أو استخدام قيم افتراضية
                $config = $field_config[$column_name] ?? [
                    'label' => ucfirst(str_replace('_', ' ', $column_name)),
                    'type' => 'text',
                    'required' => ($column['Null'] === 'NO' && is_null($column['Default'])),
                    'placeholder' => ''
                ];

                $label = htmlspecialchars($config['label']);
                $type = $config['type'];
                $is_required = $config['required'] ?? false;
                $placeholder = htmlspecialchars($config['placeholder'] ?? '');

                // جلب القيمة الحالية في وضع التعديل
                $current_value = $program[$column_name] ?? '';

                // في حال إرسال النموذج وفشل العملية، احتفظ بالبيانات التي أدخلها المستخدم
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$column_name])) {
                    $current_value = $_POST[$column_name];
                }
            ?>
                <div class="form-group <?php echo $group_class; ?>" style="width: <?php echo $group_class ? 'calc(50% - 10px)' : '100%'; ?>;">
                    <label for="<?php echo $column_name; ?>"><?php echo $label; ?><?php if ($is_required): ?><span class="required-star">*</span><?php endif; ?></label>
                    <?php if ($type === 'textarea'): ?>
                        <textarea name="<?php echo $column_name; ?>" id="<?php echo $column_name; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $is_required ? 'required' : ''; ?>><?php echo htmlspecialchars($current_value); ?></textarea>
                    <?php else: ?>
                        <input type="<?php echo $type; ?>" name="<?php echo $column_name; ?>" id="<?php echo $column_name; ?>" value="<?php echo htmlspecialchars($current_value); ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $is_required ? 'required' : ''; ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn">
            <?php echo $is_edit_mode ? 'تحديث البرنامج' : 'إضافة البرنامج'; ?>
        </button>
    </form>
</div>

<style>
    .form-group-half {
        box-sizing: border-box;
    }
</style>
</body>
</html>