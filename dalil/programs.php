<?php
$active_style = $site_settings['active_card_style'] ?? '0';
?>
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
                <?php foreach ($programs as $program): 
                    $is_free = (isset($program['is_free']) && $program['is_free'] == 1) || ($program['price'] == '0' || in_array(strtolower(trim($program['price'])), ['مجاناً', 'مجاني'], true));
                    $price_val = floatval($program['price']);
                    $price_clean = ($price_val == intval($price_val)) ? intval($price_val) : $price_val;
                    $price_text = $is_free ? 'مجاني' : $price_clean . ' ريال';

                    $is_ended = false;
                    if (!empty($program['end_date'])) {
                        $clean_end = str_replace('/', '-', trim($program['end_date']));
                        $end_ts = strtotime($clean_end);
                        if (!$end_ts && preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $clean_end, $matches)) {
                            $clean_end = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                            $end_ts = strtotime($clean_end);
                        }
                        if ($end_ts) {
                            $is_ended = $end_ts < strtotime(date('Y-m-d'));
                        }
                    }

                    $description = htmlspecialchars($program['description'] ?? '');
                    $words = explode(' ', $description);
                    $short_desc = implode(' ', array_slice($words, 0, 30));
                    $show_more = count($words) > 30;

                    $start_dates = HijriDate::getDatesForDisplay($program['start_date']);
                    $end_dates = HijriDate::getDatesForDisplay($program['end_date']);
                    $start_date_html = $start_dates['hijri'] ? "{$start_dates['hijri']} <small style='display:block; color:#888; font-size:0.85em; margin-top:2px;'>{$start_dates['gregorian']}</small>" : "-";
                    $end_date_html = $end_dates['hijri'] ? "{$end_dates['hijri']} <small style='display:block; color:#888; font-size:0.85em; margin-top:2px;'>{$end_dates['gregorian']}</small>" : "-";
                ?>
                
                <?php if ($active_style == '3'): // Elegant Premium ?>
                    <div class="card-premium <?php echo $is_ended ? 'ended' : ''; ?>">
                        <?php if ($is_ended): ?>
                            <div class="ended-badge"><i class="fas fa-lock"></i> انتهى التسجيل</div>
                        <?php endif; ?>
                        <div class="premium-header">
                            <h3 class="premium-header-title"><?php echo htmlspecialchars($program['title'] ?? ''); ?></h3>
                            <div class="premium-header-top">
                                <span class="premium-header-organizer">
                                    <i class="fas fa-building"></i>
                                    <?php echo htmlspecialchars($program['organizer'] ?? ''); ?>
                                </span>
                                <span class="premium-header-badge"><?php echo htmlspecialchars($program['attendance_type'] ?? 'حضوري'); ?></span>
                            </div>
                        </div>
                        
                        <div class="card-premium-body">
                            <p class="card-premium-description">
                                <span class="short-desc"><?php echo $short_desc . ($show_more ? '...' : ''); ?></span>
                                <?php if ($show_more): ?>
                                    <span class="full-desc" style="display: none;"><?php echo $description; ?></span>
                                    <a href="#" class="read-more">قراءة المزيد</a>
                                    <a href="#" class="read-less" style="display: none;">عرض أقل</a>
                                <?php endif; ?>
                            </p>
                            
                            <div class="card-premium-details">
                                <div class="premium-detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($program['venue_name'] ?? $program['location'] ?? ''); ?></span>
                                </div>
                                <div class="premium-detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo htmlspecialchars($program['duration'] ?? ''); ?></span>
                                </div>
                                <div class="premium-detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>البدء:<br><?php echo $start_date_html; ?></span>
                                </div>
                                <div class="premium-detail-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>الانتهاء:<br><?php echo $end_date_html; ?></span>
                                </div>
                                <div class="premium-detail-item">
                                    <i class="fas fa-user-friends"></i>
                                    <span><?php echo htmlspecialchars($program['age_group'] ?? ''); ?></span>
                                </div>
                            </div>
                            
                            <div class="card-premium-footer">
                                <div class="premium-price-container">
                                    <span class="premium-price-label">الاستثمار</span>
                                    <span class="premium-price-value <?php echo $is_free ? 'free' : ''; ?>"><?php echo $price_text; ?></span>
                                    <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                        <span class="premium-price-notes"><?php echo htmlspecialchars($program['price_notes'] ?? ''); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo !empty($program['registration_link']) ? htmlspecialchars($program['registration_link'] ?? '') : '#'; ?>" 
                                   class="premium-btn" 
                                   <?php if(!empty($program['registration_link'])): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                                   <?php echo $is_ended ? 'مغلق' : 'سجل الآن'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: // Default current design ?>
                    <div class="program-card <?php echo $is_ended ? 'ended' : ''; ?>">
                        <?php if ($is_ended): ?>
                            <div class="ended-badge"><i class="fas fa-lock"></i> انتهى التسجيل</div>
                        <?php endif; ?>
                        <div class="card-header">
                            <h3 class="program-title"><?php echo htmlspecialchars($program['title'] ?? ''); ?></h3>
                            <div class="organization">
                                <i class="fas fa-building"></i>
                                <?php echo htmlspecialchars($program['organizer'] ?? ''); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="program-details">
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt detail-icon"></i>
                                    <div class="detail-text"><?php echo htmlspecialchars($program['venue_name'] ?? $program['location'] ?? ''); ?></div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock detail-icon"></i>
                                    <div class="detail-text"><?php echo htmlspecialchars($program['duration'] ?? ''); ?></div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-calendar detail-icon"></i>
                                    <div class="detail-text">البدء:<br><?php echo $start_date_html; ?></div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-calendar-check detail-icon"></i>
                                    <div class="detail-text">الانتهاء:<br><?php echo $end_date_html; ?></div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-user-friends detail-icon"></i>
                                    <div class="detail-text" title="<?php echo htmlspecialchars($program['target_notes'] ?? ''); ?>"><?php echo htmlspecialchars($program['age_group'] ?? ''); ?></div>
                                </div>
                            </div>
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
                            <div class="program-fee-wrapper">
                                <div class="program-fee <?php echo $is_free ? 'free-badge' : ''; ?>" title="<?php echo htmlspecialchars($program['price_notes'] ?? ''); ?>">
                                    <?php echo $price_text; ?>
                                </div>
                                <?php if (!$is_free && !empty($program['price_notes'])): ?>
                                    <span class="program-fee-notes"><?php echo htmlspecialchars($program['price_notes'] ?? ''); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo !empty($program['registration_link']) ? htmlspecialchars($program['registration_link'] ?? '') : '#'; ?>" 
                               class="register-btn" 
                               <?php if(!empty($program['registration_link'])): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                               <?php echo $is_ended ? 'مغلق' : 'سجل الآن'; ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
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
                    <div class="table-responsive-wrapper style-<?php echo $active_style; ?>">
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
                                <?php foreach ($programs_in_direction as $program): 
                                    $tbl_start_dates = HijriDate::getDatesForDisplay($program['start_date']);
                                    $tbl_end_dates = HijriDate::getDatesForDisplay($program['end_date']);
                                    $tbl_start_date_html = $tbl_start_dates['hijri'] ? "{$tbl_start_dates['hijri']} <small style='display:block; color:#888; font-size:0.85em; margin-top:2px;'>{$tbl_start_dates['gregorian']}</small>" : "-";
                                    $tbl_end_date_html = $tbl_end_dates['hijri'] ? "{$tbl_end_dates['hijri']} <small style='display:block; color:#888; font-size:0.85em; margin-top:2px;'>{$tbl_end_dates['gregorian']}</small>" : "-";
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($program['title'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($program['organizer'] ?? ''); ?></td>
                                        <td><?php echo $tbl_start_date_html; ?></td>
                                        <td><?php echo htmlspecialchars($program['venue_name'] ?? $program['location'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($program['attendance_type'] ?? 'حضوري'); ?></td>
                                        <td title="<?php echo htmlspecialchars($program['price_notes'] ?? ''); ?>">
                                            <?php
                                                $is_free_table = (isset($program['is_free']) && $program['is_free'] == 1) || ($program['price'] == '0' || in_array(strtolower(trim($program['price'])), ['مجاناً', 'مجاني'], true));
                                                echo $is_free_table ? 'مجاني' : htmlspecialchars($program['price'] ?? '');
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($program['registration_link'])): ?>
                                                <a href="<?php echo htmlspecialchars($program['registration_link'] ?? ''); ?>" target="_blank" rel="noopener noreferrer" class="register-btn-table">رابط مباشر</a>
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
        
        // إذا لم تتوفر إحداثيات في قاعدة البيانات، نحاول استخراجها من الرابط مباشرة دون إبطاء الصفحة (تجنب طلبات curl)
        if (empty($lat) || empty($lng)) {
            if (!empty($program['google_map'])) {
                $url = trim($program['google_map']);
                // نقوم بالتحليل فقط إذا كان الرابط كاملاً ولا يحتاج لاختصار (تجنباً لـ curl البطيء)
                if (strpos($url, 'maps.app.goo.gl') === false && strpos($url, 'goo.gl/maps') === false) {
                    $coords = get_coords_from_google_maps($url);
                    if ($coords) {
                        $lat = $coords['lat'];
                        $lng = $coords['lng'];
                    }
                }
            }
        }
        
        // إذا لم تتوفر إحداثيات بعد كل المحاولات، نقوم بتوليد إحداثيات قطعية موزعة في الرياض بناءً على المنطقة والمعرف
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
            'end_date' => $program['end_date'] ?? '',
            'age_group' => $program['age_group'],
            'price' => $program['price'],
            'price_notes' => $program['price_notes'] ?? '',
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