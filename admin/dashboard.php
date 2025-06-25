<?php
// Start session and include DB connection
$page_title = 'لوحة تحكم الأدمن 🛠️';
include 'includes/header.php'; // This file handles session_start() and auth check
include '../includes/db_connect.php'; // DB connection after header to ensure session is started

$debug = []; // لجمع معلومات التصحيح

try {
    // Fetch programs including the direction, and order by it
    $stmt = $pdo->query("SELECT id, title, organizer, price, start_date, location, Direction, age_group, registration_link FROM programs ORDER BY Direction, id DESC");
    $all_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['query_executed'] = true;
    $debug['row_count'] = $stmt->rowCount();

    // Group programs by direction
    $grouped_programs = [];
    foreach ($all_programs as $program) {
        // Use 'غير محدد' for programs with no direction
        $direction = !empty($program['Direction']) ? $program['Direction'] : 'غير محدد';
        $grouped_programs[$direction][] = $program;
    }
    $debug['programs_grouped'] = !empty($grouped_programs) ? array_keys($grouped_programs) : 'No groups';

} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage() . " 🚫";
    $debug['error'] = 'Database error: ' . $e->getMessage();
    $grouped_programs = []; // Ensure it's an array on error
}
?>
<main class="dashboard-section">
    <div class="dashboard-card">
        <h2><?php echo htmlspecialchars($page_title); ?></h2>
        <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
        <a href="add_program.php" class="add-program-btn"><i class="fas fa-plus"></i> إضافة برنامج جديد</a>

        <?php if (empty($grouped_programs)): ?>
            <p style="text-align: center; padding: 20px; font-style: italic; color: #888;">لا توجد برامج متاحة للعرض.</p>
        <?php else: ?>
            <?php foreach ($grouped_programs as $direction => $programs_in_direction): ?>
                <div class="direction-group">
                    <h3 class="direction-heading">
                        <i class="fas fa-map-signs"></i>
                        قسم: <?php echo htmlspecialchars($direction); ?> (<?php echo count($programs_in_direction); ?> برامج)
                    </h3>
                    <div class="table-responsive-wrapper">
                        <table class="programs-table">
                            <thead>
                                <tr>
                                    <th>عنوان البرنامج</th>
                                    <th>الجهة المنظمة</th>
                                    <th>تاريخ البدء</th>
                                    <th>المكان</th>
                                    <th>الفئة العمرية</th>
                                    <th>السعر</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($programs_in_direction as $program): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($program['title']); ?></td>
                                        <td><?php echo htmlspecialchars($program['organizer']); ?></td>
                                        <td><?php echo htmlspecialchars($program['start_date']); ?></td>
                                        <td><?php echo htmlspecialchars($program['location']); ?></td>
                                        <td><?php echo htmlspecialchars($program['age_group']); ?></td>
                                        <td><?php echo htmlspecialchars($program['price']); ?></td>
                                        <td class="action-links">
                                            <a href="edit_program.php?id=<?php echo $program['id']; ?>"><i class="fas fa-edit"></i> تعديل</a>
                                            <a href="delete_program.php?id=<?php echo $program['id']; ?>" class="delete" onclick="return confirm('هل أنت متأكد من الحذف؟ هذا الإجراء لا يمكن التراجع عنه.')"><i class="fas fa-trash"></i> حذف</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- The logout button is now in the header, so this can be removed -->
        <!-- <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a> -->
    </div>
</main>

<?php include 'includes/footer.php'; ?>
            <?php if (isset($error)) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
            <a href="add_program.php" class="add-program-btn"><i class="fas fa-plus"></i> إضافة برنامج جديد</a>

            <?php if (empty($grouped_programs)): ?>
                <p style="text-align: center; padding: 20px; font-style: italic; color: #888;">لا توجد برامج متاحة للعرض.</p>
            <?php else: ?>
                <?php foreach ($grouped_programs as $direction => $programs_in_direction): ?>
                    <div class="direction-group">
                        <h3 class="direction-heading">
                            <i class="fas fa-map-signs"></i>
                            قسم: <?php echo htmlspecialchars($direction); ?> (<?php echo count($programs_in_direction); ?> برامج)
                        </h3>
                        <div class="table-responsive-wrapper">
                            <table class="programs-table">
                                <thead>
                                    <tr>
                                        <th>عنوان البرنامج</th>
                                        <th>الجهة المنظمة</th>
                                        <th>تاريخ البدء</th>
                                        <th>المكان</th>
                                        <th>الفئة العمرية</th>
                                        <th>السعر</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($programs_in_direction as $program): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($program['title']); ?></td>
                                            <td><?php echo htmlspecialchars($program['organizer']); ?></td>
                                            <td><?php echo htmlspecialchars($program['start_date']); ?></td>
                                            <td><?php echo htmlspecialchars($program['location']); ?></td>
                                            <td><?php echo htmlspecialchars($program['age_group']); ?></td>
                                            <td><?php echo htmlspecialchars($program['price']); ?></td>
                                            <td class="action-links">
                                                <a href="edit_program.php?id=<?php echo $program['id']; ?>"><i class="fas fa-edit"></i> تعديل</a>
                                                <a href="delete_program.php?id=<?php echo $program['id']; ?>" class="delete" onclick="return confirm('هل أنت متأكد من الحذف؟ هذا الإجراء لا يمكن التراجع عنه.')"><i class="fas fa-trash"></i> حذف</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

              <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
        </div>
    </section>

    <script>
        const debugInfo = <?php echo json_encode($debug); ?>;
        console.group('Dashboard Debug Info');
        console.log('Debug Data:', debugInfo);
        console.groupEnd();
    </script>
</body>
</html>
