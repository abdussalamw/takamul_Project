<section class="programs-section">
    <div class="programs-header">
        <span class="results-count">عرض <span id="displayed-count-live"><?php echo count($programs); ?></span> برنامج من أصل <span id="total-count-live"><?php echo $total_programs; ?></span></span>
                <div class="view-controls">
            <button id="show-cards-view-btn" class="view-toggle-btn active">عرض كبطاقات <i class="fas fa-th-large"></i></button>
            <button id="show-table-view-btn" class="view-toggle-btn">عرض كجدول <i class="fas fa-list"></i></button>
        </div>

        </div>
    </div>

    <div id="programs-cards-view" class="view-container active-view">
        <div class="programs-grid">
            <?php if (empty($programs)): ?>
                <p style="text-align:center; grid-column: 1 / -1; color: #777; font-style: italic;">لا توجد برامج تطابق معايير البحث الحالية.</p>
            <?php else: ?>
                <?php foreach ($programs as $program): ?>
                <div class="program-card">
                    <div class="card-header">
                        <h3 class="program-title"><?php echo htmlspecialchars($program['title']); ?></h3>
                        <div class="organization">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($program['organizer']); ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="program-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['location']); ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['duration']); ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['start_date']); ?></div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-user-friends detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['age_group']); ?></div>
                            </div>
                        </div>
                        <?php 
                            $description = htmlspecialchars($program['description']);
                            $words = explode(' ', $description);
                            $word_count = count($words);
                            $short_desc = implode(' ', array_slice($words, 0, 30));
                            $show_more = $word_count > 30;
                        ?>
                        <p class="program-description">
                            <span class="short-desc"><?php echo $short_desc . ($show_more ? '...' : ''); ?></span>
                            <?php if ($show_more): ?>
                                <span class="full-desc" style="display: none;"><?php echo $description; ?></span>
                                <a href="#" class="read-more">قراءة المزيد</a>
                                <a href="#" class="read-less" style="display: none;">عرض أقل</a>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <?php
                            $free_texts = ['مجاناً', 'مجاني'];
                            $is_free = ($program['price'] == 0 || in_array(strtolower(trim($program['price'])), $free_texts, true));
                        ?>
                        <div class="program-fee <?php echo $is_free ? 'free-badge' : ''; ?>">
                            <?php echo $is_free ? 'مجاناً' : htmlspecialchars($program['price']); ?>
                        </div>
                        <a href="<?php echo !empty($program['registration_link']) ? htmlspecialchars($program['registration_link']) : '#'; ?>" 
                           class="register-btn" 
                           <?php if(!empty($program['registration_link'])): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                           سجل الآن
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="programs-table-view" class="view-container" style="display: none;">
        <?php if (empty($grouped_programs)): ?>
            <p style="text-align:center; color: #777; font-style: italic;">لا توجد برامج تطابق معايير البحث الحالية.</p>
        <?php else: ?>
            <?php foreach ($grouped_programs as $direction => $programs_in_direction): ?>
                <div class="direction-group">
                    <h3 class="direction-heading">
                        <i class="fas fa-map-signs"></i>
                        قسم: <?php echo htmlspecialchars($direction); ?> (<?php echo count($programs_in_direction); ?> برامج)
                    </h3>
                    <div class="table-responsive-wrapper">
                        <table class="programs-table-public">
                            <thead>
                                <tr>
                                    <th>عنوان البرنامج</th>
                                    <th>اسم الجهة المنظمة</th>
                                    <th>تاريخ البدء</th>
                                    <th>مكان البرنامج</th>
                                    <th>الفئة العمرية</th>
                                    <th>رسوم البرنامج</th>
                                    <th>رابط التسجيل</th>
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
                                        <td>
                                            <?php
                                                $free_texts_check = ['مجاناً', 'مجاني'];
                                                echo ($program['price'] == 0 || in_array(strtolower(trim($program['price'])), $free_texts_check, true)) ? 'مجاناً' : htmlspecialchars($program['price']);
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($program['registration_link'])): ?>
                                                <a href="<?php echo htmlspecialchars($program['registration_link']); ?>" target="_blank" rel="noopener noreferrer" class="register-btn-table">رابط مباشر</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
