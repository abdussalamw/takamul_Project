<?php
/**
 * program_form.php - Unified Program Form Template
 * 
 * This file contains the shared form HTML for adding/editing programs.
 * It is included by:
 *   - submit_program.php (public submission)
 *   - admin/add_program.php (admin add)
 *   - admin/edit_program.php (admin edit)
 * 
 * Variables expected by this template:
 *   @param PDO   $pdo          Database connection
 *   @param array $form_data    Associative array of current field values (from $_POST or DB row)
 *   @param array $options      Configuration options: form_id, form_action, submit_label, 
 *                              back_url, show_back_btn, csrf_token, is_admin, show_header, 
 *                              ad_required, current_ad_link, word_limit
 */

// Fallback for options array name
if (isset($form_options) && !isset($options)) {
    $options = $form_options;
}

// Prevent direct access
if (!isset($pdo) || !isset($form_data)) {
    die('Error: Missing required variables for program form template.');
}

// Default options
$defaults = [
    'form_id'         => 'program-form',
    'form_action'     => '',
    'submit_label'    => 'حفظ البرنامج',
    'back_url'        => 'index.php',
    'show_back_btn'   => true,
    'csrf_token'      => '',
    'is_admin'        => false,
    'show_header'     => true,
    'ad_required'     => false,
    'current_ad_link' => '',
    'word_limit'      => 0,
    'form_title'      => ''
];
foreach ($defaults as $key => $val) {
    if (!isset($options[$key])) {
        $options[$key] = $val;
    }
}

// Field translations
$field_labels = [
    'organizer_id'          => 'الجهة المنظمة',
    'organizer_name'        => 'اسم الجهة',
    'organizer_department'  => 'اسم الجهة الفرعي',
    'entry_officer_name'    => 'مسؤولة التواصل',
    'entry_officer_phone'   => 'رقم جوال مسؤول التواصل',
    'title'                 => 'اسم البرنامج',
    'description'           => 'وصف مختصر للبرنامج',
    'program_type'          => 'نوع البرنامج',
    'program_type_other'    => 'نوع البرنامج (أخرى)',
    'venue_name'            => 'مقر البرنامج',
    'location'              => 'الحي',
    'attendance_type'       => 'حضوري/ عن بعد',
    'Direction'             => 'مكان البرنامج حسب الجهات',
    'age_group'             => 'الفئة المستهدفة',
    'target_notes'          => 'أي ملاحظات على الفئات',
    'start_date'            => 'تاريخ البداية',
    'end_date'              => 'تاريخ النهاية',
    'duration'              => 'مدة البرنامج',
    'is_free'               => 'البرنامج هل هو مجاني او مدفوع',
    'price'                 => 'قيمة الرسوم',
    'price_notes'           => 'ملاحظات على الرسوم',
    'registration_link'     => 'رابط التسجيل',
    'ad_link'               => 'التصميم الإعلاني',
    'program_notes'         => 'اي ملاحظات تودون كتابتها عن البرنامج',
];

$field_icons = [
    'organizer_id'          => 'fas fa-building',
    'organizer_name'        => 'fas fa-building',
    'organizer_department'  => 'fas fa-code-branch',
    'entry_officer_name'    => 'fas fa-user-edit',
    'entry_officer_phone'   => 'fas fa-phone',
    'title'                 => 'fas fa-heading',
    'description'           => 'fas fa-file-alt',
    'program_type'          => 'fas fa-shapes',
    'program_type_other'    => 'fas fa-shapes',
    'venue_name'            => 'fas fa-map-marker-alt',
    'location'              => 'fas fa-map-pin',
    'attendance_type'       => 'fas fa-chalkboard-teacher',
    'Direction'             => 'fas fa-compass',
    'age_group'             => 'fas fa-users',
    'target_notes'          => 'fas fa-info-circle',
    'start_date'            => 'fas fa-calendar-day',
    'end_date'              => 'fas fa-calendar-week',
    'duration'              => 'fas fa-clock',
    'is_free'               => 'fas fa-hand-holding-usd',
    'price'                 => 'fas fa-tag',
    'price_notes'           => 'fas fa-comment-dollar',
    'registration_link'     => 'fas fa-link',
    'ad_link'               => 'fas fa-image',
    'program_notes'         => 'fas fa-sticky-note',
];

// Helper function to get form value
function getFormValue($data, $key, $default = '') {
    if (is_array($data) && isset($data[$key])) {
        return htmlspecialchars($data[$key]);
    }
    return htmlspecialchars($default);
}

function getFormValueSelected($data, $key, $value) {
    if (is_array($data) && isset($data[$key])) {
        return ($data[$key] == $value) ? 'selected' : '';
    }
    return '';
}

function getFormValueChecked($data, $key, $value, $delimiter = ',') {
    if (empty($data) || !isset($data[$key])) return '';
    $values = $data[$key];
    if (!is_array($values)) {
        $values = array_map('trim', explode($delimiter, $values));
    }
    return in_array($value, $values) ? 'checked' : '';
}

// Fetch organizers data
$orgs_data = [];
try {
    $orgs_data = $pdo->query("SELECT id, name, sub_name, communication_officer_name, communication_officer_phone FROM organizers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently fail
}

// Directions list
$directions = ['شمال الرياض', 'جنوب ووسط الرياض', 'شرق الرياض', 'غرب الرياض'];
?>

<?php if ($options['show_header'] && $options['form_title']): ?>
<div class="program-form-section">
    <div class="program-form-card">
        <h2><i class="fas fa-paper-plane"></i> <?php echo htmlspecialchars($options['form_title']); ?></h2>
<?php endif; ?>

<form method="POST" class="program-form" id="<?php echo htmlspecialchars($options['form_id']); ?>" enctype="multipart/form-data" action="<?php echo htmlspecialchars($options['form_action']); ?>">
    <?php if ($options['csrf_token']): ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($options['csrf_token']); ?>">
    <?php endif; ?>

    <!-- ========== القسم الأول: بيانات الجهة ========== -->
    <div class="form-section-header">
        <h3><i class="fas fa-sitemap"></i> أولاً: بيانات الجهة</h3>
    </div>

    <!-- Organizer Name Input -->
    <div class="form-group half-width" id="organizer_name_group">
        <label for="organizer_name">
            <i class="fas fa-building"></i> <?php echo $field_labels['organizer_name']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="text" id="organizer_name" name="organizer_name" 
               value="<?php echo getFormValue($form_data, 'organizer_name'); ?>" 
               placeholder="اسم الجمعية أو المؤسسة: مثلا جمعية مكارم الأخلاق" required>
    </div>

    <div class="form-group half-width">
        <label for="organizer_department">
            <i class="fas fa-code-branch"></i> <?php echo $field_labels['organizer_department']; ?>
        </label>
        <input type="text" id="organizer_department" name="organizer_department" 
               value="<?php echo getFormValue($form_data, 'organizer_department'); ?>" 
               placeholder="مثلا: أكاديمية مكارم الأخلاق">
    </div>

    <div class="form-group half-width">
        <label for="entry_officer_name">
            <i class="fas fa-user-edit"></i> <?php echo $field_labels['entry_officer_name']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="text" id="entry_officer_name" name="entry_officer_name" 
               value="<?php echo getFormValue($form_data, 'entry_officer_name'); ?>" 
               placeholder="الاسم الثلاثي" required>
    </div>

    <div class="form-group half-width" id="entry_officer_phone_group">
        <label for="entry_officer_phone">
            <i class="fas fa-phone"></i> <?php echo $field_labels['entry_officer_phone']; ?> (واتساب) <span class="required-asterisk">*</span>
        </label>
        <input type="tel" id="entry_officer_phone" name="entry_officer_phone" 
               value="<?php echo getFormValue($form_data, 'entry_officer_phone'); ?>" 
               placeholder="9665XXXXXXXX" 
               pattern="^(05|5|9665)\d{8}$" 
               title="نرجو كتابة الرقم بصيغة 9665XXXXXXXX" 
               required style="direction: ltr; text-align: right;">
        <span class="form-note">رقم الجوال بصيغة 9665**********</span>
    </div>

    <!-- ========== القسم الثاني: بيانات البرنامج ========== -->
    <div class="form-section-header">
        <h3><i class="fas fa-clipboard-list"></i> ثانياً: بيانات البرنامج</h3>
    </div>

    <div class="form-group half-width">
        <label for="title">
            <i class="fas fa-heading"></i> <?php echo $field_labels['title']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="text" id="title" name="title" 
               value="<?php echo getFormValue($form_data, 'title'); ?>" required>
    </div>

    <div class="form-group half-width">
        <label for="program_type">
            <i class="fas fa-shapes"></i> <?php echo $field_labels['program_type']; ?>
        </label>
        <select id="program_type" name="program_type">
            <option value="نادي" <?php echo getFormValueSelected($form_data, 'program_type', 'نادي'); ?>>نادي</option>
            <option value="دورة" <?php echo getFormValueSelected($form_data, 'program_type', 'دورة'); ?>>دورة</option>
            <option value="برنامج(قيمي - تربوي)" <?php echo getFormValueSelected($form_data, 'program_type', 'برنامج(قيمي - تربوي)'); ?>>برنامج(قيمي - تربوي)</option>
            <option value="مسابقة" <?php echo getFormValueSelected($form_data, 'program_type', 'مسابقة'); ?>>مسابقة</option>
            <option value="رحلة (مخيم)" <?php echo getFormValueSelected($form_data, 'program_type', 'رحلة (مخيم)'); ?>>رحلة (مخيم)</option>
            <option value="أخرى" <?php echo getFormValueSelected($form_data, 'program_type', 'أخرى'); ?>>آخر:</option>
        </select>
    </div>

    <div class="form-group half-width" id="program_type_other_group" 
         style="display: <?php echo (isset($form_data['program_type']) && $form_data['program_type'] == 'أخرى') ? 'block' : 'none'; ?>;">
        <label for="program_type_other"><i class="fas fa-pen"></i> نوع البرنامج (أخرى)</label>
        <input type="text" id="program_type_other" name="program_type_other" 
               value="<?php echo getFormValue($form_data, 'program_type_other'); ?>">
    </div>

    <div class="form-group half-width">
        <label for="attendance_type">
            <i class="fas fa-chalkboard-teacher"></i> <?php echo $field_labels['attendance_type']; ?> <span class="required-asterisk">*</span>
        </label>
        <select id="attendance_type" name="attendance_type" required>
            <option value="حضوري" <?php echo getFormValueSelected($form_data, 'attendance_type', 'حضوري'); ?>>حضوري</option>
            <option value="عن بعد" <?php echo getFormValueSelected($form_data, 'attendance_type', 'عن بعد'); ?>>عن بعد</option>
        </select>
    </div>

    <div class="form-group half-width">
        <label for="venue_name">
            <i class="fas fa-map-marker-alt"></i> <?php echo $field_labels['venue_name']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="text" id="venue_name" name="venue_name" 
               value="<?php echo getFormValue($form_data, 'venue_name'); ?>" 
               required placeholder="اسم مبنى/مسجد/مدرسة + اسم الحي">
    </div>

    <div class="form-group half-width">
        <label for="location">
            <i class="fas fa-map-pin"></i> الحي
        </label>
        <input type="text" id="location" name="location" 
               value="<?php echo getFormValue($form_data, 'location'); ?>" 
               placeholder="اسم الحي (اختياري إذا ذكر في المقر)">
    </div>

    <div class="form-group half-width">
        <label for="Direction">
            <i class="fas fa-compass"></i> <?php echo $field_labels['Direction']; ?> <span class="required-asterisk">*</span>
        </label>
        <select id="Direction" name="Direction" required>
            <option value="">اختر المنطقة...</option>
            <?php foreach ($directions as $dir): ?>
                <option value="<?php echo $dir; ?>" <?php echo getFormValueSelected($form_data, 'Direction', $dir); ?>>
                    <?php echo $dir; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group half-width" style="display: flex; flex-direction: column;">
        <label><i class="fas fa-users"></i> <?php echo $field_labels['age_group']; ?> <span class="required-asterisk">*</span></label>
        <div class="checkbox-group">
            <?php 
            $age_options = ['متوسط', 'ثانوي', 'جامعي', 'مافوق الجامعي'];
            foreach ($age_options as $age): 
            ?>
                <label>
                    <input type="checkbox" name="age_group[]" value="<?php echo $age; ?>" 
                           <?php echo getFormValueChecked($form_data, 'age_group', $age); ?>> 
                    <?php echo $age; ?>
                </label>
            <?php endforeach; ?>
            <label>
                <input type="checkbox" id="age_group_other_checkbox" value="أخرى" 
                       <?php echo (!empty($form_data['age_group_other'])) ? 'checked' : ''; ?>> 
                أخرى:
            </label>
        </div>
        <input type="text" id="age_group_other_input" name="age_group_other" 
               value="<?php echo getFormValue($form_data, 'age_group_other', getFormValue($form_data, 'age_group_other_raw')); ?>" 
               style="margin-top: 10px; display: <?php echo !empty($form_data['age_group_other']) ? 'block' : 'none'; ?>;" 
               placeholder="حدد الفئة الأخرى">
    </div>

    <div class="form-group half-width">
        <label for="target_notes">
            <i class="fas fa-info-circle"></i> <?php echo $field_labels['target_notes']; ?>
        </label>
        <input type="text" id="target_notes" name="target_notes" 
               value="<?php echo getFormValue($form_data, 'target_notes'); ?>" 
               placeholder="في حال وجود اي تفاصيل عن الفئات المشاركة تكتب هنا">
    </div>

    <div class="form-group full-width">
        <label for="description">
            <i class="fas fa-file-alt"></i> <?php echo $field_labels['description']; ?> <span class="required-asterisk">*</span>
        </label>
        <textarea id="description" name="description" required 
                  placeholder="يكتب وصف مختصر وجذاب لا يزيد عن 50 كلمة، ويعبر عن فكرة البرنامج دون الإطالة."><?php echo getFormValue($form_data, 'description'); ?></textarea>
        <?php if ($options['word_limit'] > 0): ?>
            <span class="form-note">الحد الأقصى: <?php echo $options['word_limit']; ?> كلمة.</span>
        <?php endif; ?>
    </div>

    <!-- ========== القسم الثالث: الزمان والمكان ========== -->
    <div class="form-section-header">
        <h3><i class="fas fa-calendar-alt"></i> ثالثاً: الزمان والمكان</h3>
    </div>

    <div class="form-group half-width">
        <label for="start_date">
            <i class="fas fa-calendar-day"></i> <?php echo $field_labels['start_date']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="text" id="start_date" name="start_date" 
               value="<?php echo getFormValue($form_data, 'start_date'); ?>" 
               placeholder="DD/MM/YYYY" required readonly style="cursor: pointer;">
    </div>

    <div class="form-group half-width">
        <label for="end_date">
            <i class="fas fa-calendar-week"></i> <?php echo $field_labels['end_date']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="text" id="end_date" name="end_date" 
               value="<?php echo getFormValue($form_data, 'end_date'); ?>" 
               placeholder="DD/MM/YYYY" required readonly style="cursor: pointer;">
    </div>

    <div class="form-group half-width">
        <label for="duration">
            <i class="fas fa-clock"></i> <?php echo $field_labels['duration']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="text" id="duration" name="duration" 
               value="<?php echo getFormValue($form_data, 'duration'); ?>" 
               required placeholder="عدد الأيام / عدد الساعات ..الخ أي وصف">
    </div>

    <div class="form-group half-width">
        <label for="google_map">
            <i class="fas fa-map-marked-alt"></i> الموقع على خرائط جوجل <span class="required-asterisk">*</span>
        </label>
        <input type="url" id="google_map" name="google_map" 
               value="<?php echo getFormValue($form_data, 'google_map'); ?>" 
               required placeholder="https://maps.google.com/...">
        <span class="form-note">الرجاء لصق مكان اقامة البرنامج على خرائط جوجل</span>
    </div>

    <!-- ========== القسم الرابع: الرسوم والروابط الإضافية ========== -->
    <div class="form-section-header">
        <h3><i class="fas fa-money-bill-wave"></i> رابعاً: الرسوم والمرفقات</h3>
    </div>

    <div class="form-group half-width">
        <label for="is_free">
            <i class="fas fa-hand-holding-usd"></i> <?php echo $field_labels['is_free']; ?> <span class="required-asterisk">*</span>
        </label>
        <select id="is_free" name="is_free" required>
            <option value="1" <?php echo getFormValueSelected($form_data, 'is_free', '1'); ?>>مجاني</option>
            <option value="0" <?php echo getFormValueSelected($form_data, 'is_free', '0'); ?>>مدفوع</option>
        </select>
    </div>

    <div class="form-group half-width" id="price_group" 
         style="display: <?php echo (isset($form_data['is_free']) && $form_data['is_free'] == '0') ? 'block' : 'none'; ?>;">
        <label for="price">
            <i class="fas fa-tag"></i> <?php echo $field_labels['price']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="number" id="price" name="price" 
               value="<?php echo getFormValue($form_data, 'price'); ?>" 
               min="0" step="0.01" placeholder="نرجو كتابة المبلغ الصحيح فقط بدون كلمة ريال">
        <span class="form-note">نرجو كتابة المبلغ الصحيح فقط بدون كلمة ريال - مثلا: 50</span>
    </div>

    <div class="form-group half-width">
        <label for="price_notes">
            <i class="fas fa-comment-dollar"></i> <?php echo $field_labels['price_notes']; ?>
        </label>
        <input type="text" id="price_notes" name="price_notes" 
               value="<?php echo getFormValue($form_data, 'price_notes'); ?>" 
               placeholder="خصم للمجموعات - التسجيل المبكر - ...الخ">
    </div>

    <div class="form-group half-width">
        <label for="registration_link">
            <i class="fas fa-link"></i> <?php echo $field_labels['registration_link']; ?> <span class="required-asterisk">*</span>
        </label>
        <input type="url" id="registration_link" name="registration_link" 
               value="<?php echo getFormValue($form_data, 'registration_link'); ?>" 
               required placeholder="رابط نموذج التسجيل">
    </div>

    <div class="form-group half-width">
        <label for="ad_link">
            <i class="fas fa-image"></i> <?php echo $field_labels['ad_link']; ?> 
            <?php if ($options['ad_required']): ?>
                <span class="required-asterisk">*</span>
            <?php endif; ?>
        </label>
        <?php if (!empty($options['current_ad_link'])): 
            $file_ext = strtolower(pathinfo($options['current_ad_link'], PATHINFO_EXTENSION));
        ?>
            <div class="current-ad-preview">
                <span>الإعلان الحالي:</span>
                <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                    <a href="../<?php echo htmlspecialchars($options['current_ad_link']); ?>" target="_blank">
                        <img src="../<?php echo htmlspecialchars($options['current_ad_link']); ?>" alt="الإعلان الحالي">
                    </a>
                <?php else: ?>
                    <a href="../<?php echo htmlspecialchars($options['current_ad_link']); ?>" target="_blank">
                        عرض الملف الحالي (<?php echo $file_ext; ?>)
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <input type="file" id="ad_link" name="ad_link" accept=".jpg, .jpeg, .png, .pdf"
               <?php echo $options['ad_required'] ? 'required' : ''; ?>>
        <span class="form-note">يمكنك تحميل ملف واحد. الحد الأقصى لحجم الملف: 100 MB. الصيغ المسموحة: JPG, PNG, PDF</span>
    </div>

    <div class="form-group half-width">
        <label for="program_notes">
            <i class="fas fa-sticky-note"></i> <?php echo $field_labels['program_notes']; ?>
        </label>
        <input type="text" id="program_notes" name="program_notes" 
               value="<?php echo getFormValue($form_data, 'program_notes'); ?>">
    </div>

    <!-- ========== Buttons ========== -->
    <div class="form-actions-container">
        <?php if ($options['show_back_btn']): ?>
            <a href="<?php echo htmlspecialchars($options['back_url']); ?>" class="back-btn-inline">
                <i class="fas fa-arrow-right"></i> رجوع
            </a>
        <?php endif; ?>
        <button type="submit" class="submit-btn <?php echo $options['is_admin'] ? 'add-program-btn' : ''; ?>">
            <i class="fas fa-save"></i> <?php echo htmlspecialchars($options['submit_label']); ?>
        </button>
    </div>
</form>

<?php if ($options['show_header'] && $options['form_title']): ?>
        <?php if (!$options['is_admin']): ?>
            <a href="index.php" class="back-link"><i class="fas fa-home"></i> العودة إلى الصفحة الرئيسية</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    ProgramForm.init({
        organizersData: <?php echo json_encode($orgs_data, JSON_UNESCAPED_UNICODE); ?>,
        formId: '<?php echo $options['form_id']; ?>',
        wordLimit: <?php echo (int)$options['word_limit']; ?>
    });
});
</script>