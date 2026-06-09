<section class="programs-section">
    <div class="programs-header">
        <span class="results-count">عرض <span id="displayed-count-live"><?php echo count($programs); ?></span> برنامج من أصل <span id="total-count-live"><?php echo $total_programs; ?></span></span>
        <div class="view-controls">
            <button id="show-cards-view-btn" class="view-toggle-btn active">عرض كبطاقات <i class="fas fa-th-large"></i></button>
            <button id="show-table-view-btn" class="view-toggle-btn">عرض كجدول <i class="fas fa-list"></i></button>
            <button id="show-map-view-btn" class="view-toggle-btn">عرض الخريطة التفاعلية <i class="fas fa-map-marked-alt"></i></button>
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
                                <div class="detail-text" title="<?php echo htmlspecialchars($program['target_notes'] ?? ''); ?>"><?php echo htmlspecialchars($program['age_group']); ?></div>
                            </div>
                            <?php if(!empty($program['attendance_type'])): ?>
                            <div class="detail-item">
                                <i class="fas fa-chalkboard-teacher detail-icon"></i>
                                <div class="detail-text"><?php echo htmlspecialchars($program['attendance_type']); ?></div>
                            </div>
                            <?php endif; ?>
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
                            $is_free = (isset($program['is_free']) && $program['is_free'] == 1) || ($program['price'] == '0' || in_array(strtolower(trim($program['price'])), ['مجاناً', 'مجاني'], true));
                            $price_text = $is_free ? 'مجاني' : htmlspecialchars($program['price']);
                        ?>
                        <div class="program-fee <?php echo $is_free ? 'free-badge' : ''; ?>" title="<?php echo htmlspecialchars($program['price_notes'] ?? ''); ?>">
                            <?php echo $price_text; ?>
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
                                    <th>العنوان</th>
                                    <th>الجهة</th>
                                    <th>تاريخ البدء</th>
                                    <th>المقر</th>
                                    <th>الحضور</th>
                                    <th>الرسوم</th>
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
                                        <td><?php echo htmlspecialchars($program['attendance_type'] ?? 'حضوري'); ?></td>
                                        <td title="<?php echo htmlspecialchars($program['price_notes'] ?? ''); ?>">
                                            <?php
                                                $is_free_table = (isset($program['is_free']) && $program['is_free'] == 1) || ($program['price'] == '0' || in_array(strtolower(trim($program['price'])), ['مجاناً', 'مجاني'], true));
                                                echo $is_free_table ? 'مجاني' : htmlspecialchars($program['price']);
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

    <div id="programs-map-view" class="view-container" style="display: none;">
        <div id="map" style="height: 600px; width: 100%; border-radius: 15px; border: 2px solid #e0e0e0; z-index: 1; margin-top: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);"></div>
    </div>

    <?php
    // إعداد بيانات الخريطة مع إحداثيات قطعية افتراضية إذا لم يتوفر رابط جوجل ماب
    $map_programs = [];
    foreach ($programs as $program) {
        $lat = !empty($program['latitude']) ? (float)$program['latitude'] : null;
        $lng = !empty($program['longitude']) ? (float)$program['longitude'] : null;
        
        // إذا لم تتوفر إحداثيات، نقوم بتوليد إحداثيات قطعية موزعة في الرياض بناءً على المنطقة والمعرف
        if (empty($lat) || empty($lng)) {
            $seed = intval($program['id']);
            // استخدام جيب وجيب التمام (sin/cos) للمعرّف لتوليد إزاحة فريدة تمنع تطابق العلامات فوق بعضها
            $offset_lat = sin($seed * 45) * 0.025;
            $offset_lng = cos($seed * 45) * 0.025;
            
            $direction = !empty($program['Direction']) ? trim($program['Direction']) : '';
            
            if ($direction === 'شمال الرياض') {
                $lat = 24.794 + $offset_lat;
                $lng = 46.678 + $offset_lng;
            } elseif ($direction === 'جنوب الرياض') {
                $lat = 24.582 + $offset_lat;
                $lng = 46.721 + $offset_lng;
            } elseif ($direction === 'شرق الرياض') {
                $lat = 24.725 + $offset_lat;
                $lng = 46.802 + $offset_lng;
            } elseif ($direction === 'غرب الرياض') {
                $lat = 24.653 + $offset_lat;
                $lng = 46.584 + $offset_lng;
            } else {
                // وسط الرياض (العليا/السليمانية)
                $lat = 24.713 + $offset_lat;
                $lng = 46.675 + $offset_lng;
            }
        }
        
        $map_programs[] = [
            'id' => $program['id'],
            'title' => $program['title'],
            'organizer' => $program['organizer'],
            'location' => $program['location'],
            'Direction' => $program['Direction'] ?? '',
            'duration' => $program['duration'],
            'start_date' => $program['start_date'],
            'age_group' => $program['age_group'],
            'price' => $program['price'],
            'registration_link' => $program['registration_link'],
            'lat' => $lat,
            'lng' => $lng,
            'description' => $program['description'] ?? ''
        ];
    }
    ?>
    <script id="map-programs-data" type="application/json">
        <?php echo json_encode($map_programs, JSON_UNESCAPED_UNICODE); ?>
    </script>
</section>
