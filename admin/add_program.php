<?php
// add_program.php

// 1. Initialization
include_once '../includes/db_connect.php';
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
            // Using a more specific upload directory for programs
            $upload_result = $adminController->handleFileUpload($_FILES['ad_link'], '../uploads/programs/', ['jpg', 'jpeg', 'png', 'pdf']);
            if ($upload_result['success']) {
                $ad_link_path = 'uploads/programs/' . $upload_result['filename'];
            } else {
                $error_message = $upload_result['message'];
                $adminController->setErrorMessage($error_message);
            }
        }

        // --- Validate Data ---
        if (empty($_POST['title']) || empty($_POST['start_date'])) {
            $adminController->setErrorMessage("حقل العنوان وتاريخ البدء مطلوبان على الأقل.");
            $error_message = "Validation failed"; // Prevent db insertion
        }

        if (is_null($error_message)) {
            $db_columns = [];
            $placeholders = [];
            $params = [];

            $stmt = $pdo->query("DESCRIBE programs");
            $table_columns_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($table_columns_info as $column_info) {
                $column_name = $column_info['Field'];
                if (in_array($column_name, ['id', 'status'])) continue;

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

            // All programs added by admins are 'reviewed' by default.
            $db_columns[] = '`status`';
            $placeholders[] = '?';
            $params[] = 'reviewed';

            // --- Execute Insert ---
            try {
                $sql = "INSERT INTO programs (" . implode(', ', $db_columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $adminController->redirect('manage_programs.php', 'تمت إضافة البرنامج بنجاح.', 'success');

            } catch (PDOException $e) {
                $adminController->setErrorMessage("خطأ في قاعدة البيانات: " . $e->getMessage());
            }
        }
    }
}

// 3. Render View
$adminController->renderHeader($page_title);
$adminController->renderMessages(); // Display any success/error messages
?>
    <section class="add-program-section">
        <div class="add-program-card">
            <h2><i class="fas fa-plus-circle"></i> إضافة برنامج جديد</h2>
            <form method="POST" class="add-program-form" id="add-program-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminController->csrf_token); ?>">
                <?php
                try {
                    // مصفوفة لترجمة أسماء الحقول إلى العربية
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
                        'google_map'  => 'رابط الموقع على خرائط جوجل',
                    ];

                    // مصفوفة لربط الحقول بالأيقونات المناسبة
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

                    // --- Reorder columns to place end_date after start_date ---
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
                    // --- End of reordering ---

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
                        $submitted_value = isset($_POST[$field_name]) ? htmlspecialchars($_POST[$field_name]) : '';
                ?>
                        <div class="<?php echo $group_classes; ?>">
                            <label for="<?php echo $field_name; ?>"><i class="<?php echo $icon_class; ?>"></i> <?php echo $label; ?></label>
                            <?php if ($field_name === 'ad_link'): ?>
                                <input type="file" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" accept=".jpg, .jpeg, .png, .pdf">
                            <?php elseif ($column['Type'] == 'longtext' || $column['Type'] == 'text'): ?>
                                <textarea id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?>><?php echo $submitted_value; ?></textarea>
                            <?php else: ?>
                                <input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" value="<?php echo $submitted_value; ?>" placeholder="أدخل <?php echo $label; ?>" <?php echo $required; ?> <?php if ($is_date_field) echo 'readonly style="cursor: pointer;"'; ?>>
                            <?php endif; ?>
                        </div>
                <?php
                    }
                } catch (PDOException $e) {
                    echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> خطأ في جلب معلومات الحقول: " . $e->getMessage() . "</p>";
                }
                ?>
                <div class="form-actions-container">
                    <a href="manage_programs.php" class="back-btn-inline"><i class="fas fa-arrow-right"></i> رجوع إلى الإدارة</a>
                    <button type="submit" name="save_review" class="add-program-btn"><i class="fas fa-save"></i> حفظ البرنامج</button>
                </div>
            </form>
        </div>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    let activeCalendarInput = null;
    const calendarElement = createCalendarElement();
    document.body.appendChild(calendarElement);

    const hijriMonths = ['محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الثانية', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'];
    const hijriDays = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    
    const hijriYearStartDay = {
        1446: 0, 1447: 4, 1448: 2
    };
    const hijriMonthLengths = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29];

    function createCalendarElement() {
        const calendar = document.createElement('div');
        calendar.className = 'hijri-calendar';
        calendar.style.cssText = `
            position: absolute; background: white; border: 1px solid #ddd; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 1002; padding: 15px;
            width: 320px; display: none; font-family: 'Tajawal', sans-serif; opacity: 0;
            transform: translateY(10px); transition: opacity 0.3s ease, transform 0.3s ease;
        `;
        return calendar;
    }

    function renderCalendar(year, month, selectedDay = null) {
        calendarElement.innerHTML = \`
            <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <button type="button" class="nav-btn" data-action="prev-month">‹</button>
                <div style="display: flex; gap: 5px; font-weight: bold;">
                    <span id="current-month">\${hijriMonths[month-1]}</span>
                    <span id="current-year">\${year}هـ</span>
                </div>
                <button type="button" class="nav-btn" data-action="next-month">›</button>
            </div>
            <div class="calendar-grid-header" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 10px;">
                \${hijriDays.map(day => \`<div style="text-align: center; font-weight: bold; color: var(--primary); padding: 6px; font-size: 0.8rem;">\${day.substring(0,3)}</div>\`).join('')}
            </div>
            <div class="calendar-grid-days" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;"></div>
        \`;

        const daysContainer = calendarElement.querySelector('.calendar-grid-days');
        const daysInMonth = hijriMonthLengths[month - 1] + ((month === 12 && (year === 1446 || year === 1447)) ? 1 : 0);
        let firstDayOfMonth = hijriYearStartDay[year] || 0;
        for (let i = 0; i < month - 1; i++) {
            firstDayOfMonth = (firstDayOfMonth + hijriMonthLengths[i]) % 7;
        }

        for (let i = 0; i < firstDayOfMonth; i++) {
            daysContainer.innerHTML += '<div></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.textContent = day;
            dayElement.style.cssText = \`text-align: center; padding: 8px 4px; cursor: pointer; border-radius: 50%; transition: all 0.2s ease; font-weight: 500;\`;
            if (day === selectedDay) {
                dayElement.style.backgroundColor = 'var(--primary)';
                dayElement.style.color = 'white';
            }
            dayElement.addEventListener('click', () => selectDate(year, month, day));
            dayElement.addEventListener('mouseover', () => { if(day !== selectedDay) dayElement.style.backgroundColor = '#f0e6ff'; });
            dayElement.addEventListener('mouseout', () => { if(day !== selectedDay) dayElement.style.backgroundColor = ''; });
            daysContainer.appendChild(dayElement);
        }

        calendarElement.querySelectorAll('.nav-btn').forEach(btn => {
            btn.style.cssText = \`background: none; border: none; font-size: 1.5rem; color: var(--primary); cursor: pointer;\`;
            btn.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                let newMonth = month, newYear = year;
                if (action === 'prev-month') {
                    newMonth--;
                    if (newMonth < 1) { newMonth = 12; newYear--; }
                } else {
                    newMonth++;
                    if (newMonth > 12) { newMonth = 1; newYear++; }
                }
                renderCalendar(newYear, newMonth, selectedDay);
            });
        });
    }

    function selectDate(year, month, day) {
        if (!activeCalendarInput) return;
        const dateStr = \`\${String(day).padStart(2, '0')}/\${String(month).padStart(2, '0')}/\${year}\`;
        activeCalendarInput.value = dateStr;
        hideCalendar();
    }

    function showCalendar(targetInput) {
        activeCalendarInput = targetInput;
        const rect = targetInput.getBoundingClientRect();
        calendarElement.style.top = \`\${window.scrollY + rect.bottom + 5}px\`;
        calendarElement.style.right = \`\${window.innerWidth - rect.right}px\`;

        let currentYear = 1447, currentMonth = 1, currentDay = null;
        const currentValue = targetInput.value;
        if (currentValue && /^\\d{1,2}\\/\\d{1,2}\\/\\d{4}$/.test(currentValue)) {
            const parts = currentValue.split('/');
            currentDay = parseInt(parts[0], 10);
            currentMonth = parseInt(parts[1], 10);
            currentYear = parseInt(parts[2], 10);
        }

        renderCalendar(currentYear, currentMonth, currentDay);
        calendarElement.style.display = 'block';
        setTimeout(() => {
            calendarElement.style.opacity = '1';
            calendarElement.style.transform = 'translateY(0)';
        }, 10);
    }

    function hideCalendar() {
        calendarElement.style.opacity = '0';
        calendarElement.style.transform = 'translateY(10px)';
        setTimeout(() => {
            calendarElement.style.display = 'none';
            activeCalendarInput = null;
        }, 300);
    }

    document.querySelectorAll('input[id="start_date"], input[id="end_date"]').forEach(input => {
        input.addEventListener('click', (e) => {
            e.stopPropagation();
            if (activeCalendarInput === e.target) {
                hideCalendar();
            } else {
                showCalendar(e.target);
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (activeCalendarInput && !calendarElement.contains(e.target) && e.target !== activeCalendarInput) {
            hideCalendar();
        }
    });
});
    </script>
<?php
$adminController->renderFooter();
?>
