<?php
session_start();
include 'includes/db_connect.php'; // Corrected path for root directory

// This is a public page, so the admin security check has been removed.

$page_title_text = 'خريطة البرامج';
$locations = [];
$error = null;

try {
    // Fetch only published programs with a valid Google Map link
    $stmt = $pdo->query("SELECT * FROM programs WHERE status = 'published' AND google_map IS NOT NULL AND google_map != ''");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programs as $program) {
        $lat = null;
        $lng = null;
        $url = trim($program['google_map']);

        // Step 1: Check for and extract URL from an iframe embed code, if present.
        if (str_starts_with($url, '<iframe')) {
            if (preg_match('/<iframe[^>]+src="([^"]+)"/', $url, $iframe_matches)) {
                $url = html_entity_decode($iframe_matches[1]);
            }
        }
        
        // Step 2: If the URL is a shortened goo.gl link, expand it to the full URL.
        if (strpos($url, 'maps.app.goo.gl') !== false) {
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
                curl_setopt($ch, CURLOPT_NOBODY, true);         // We only need the final URL, not the content
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // Set a timeout
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);        // Limit redirects
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

                // This is crucial for local servers (like XAMPP) that may lack up-to-date SSL certificates.
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                curl_exec($ch);
                $curl_err = curl_error($ch);
                $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);
                
                if ($curl_err) {
                    // Log error if cURL fails, but don't stop the script.
                    if (is_null($error)) $error = '';
                    $error .= "خطأ في جلب الرابط المختصر: " . htmlspecialchars($curl_err) . "<br>";
                } elseif ($final_url) {
                    $url = $final_url;
                }
            } else {
                // Inform the user that cURL is necessary for this feature.
                if (is_null($error)) $error = '';
                $error = "مكتبة cURL غير مفعلة على الخادم، لا يمكن جلب إحداثيات الروابط المختصرة.";
            }
        }

        // Step 3: Try multiple patterns to extract coordinates from the final URL.

        // Pattern 1: Standard URL with @lat,lng (e.g., from browser address bar)
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        } 
        // Pattern 2: URL from "Embed" map with data=!3d(lat)!4d(lng)
        elseif (preg_match('/data=.*!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        // Pattern 3: URL with ll=lat,lng
        elseif (preg_match('/ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        // Pattern 4: URL with q=lat,lng (e.g., from "Share" link)
        elseif (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        // Pattern 5: URL from embed with `pb` parameter
        elseif (preg_match('!1d(-?\d+\.\d+)!2d(-?\d+\.\d+)!', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }

        if ($lat !== null && $lng !== null) {
            $locations[] = [
                'title' => $program['title'],
                'organizer' => $program['organizer'],
                'location' => $program['location'],
                'duration' => $program['duration'],
                'start_date' => $program['start_date'],
                'age_group' => $program['age_group'],
                'description' => $program['description'],
                'price' => $program['price'],
                'registration_link' => $program['registration_link'],
                'lat' => (float)$lat,
                'lng' => (float)$lng,
            ];
        }
    }
} catch (PDOException $e) {
    $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
}

// Convert the locations array to a JSON object for JavaScript
$locations_json = json_encode($locations, JSON_UNESCAPED_UNICODE);
?>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title_text); ?> 🗺️</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet Map Library CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

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
        }
        header {
            background: linear-gradient(120deg, var(--primary), #5c1d9c); /* Public header style */
            color: white;
            padding: 1.2rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1001; /* Higher than map */
            width: 100%;
        }
        .header-container {
            display: flex; /* Centered public header */
            justify-content: center;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            gap: 20px;
        }
        .logo { display: flex; align-items: center; gap: 20px; }
        .logo-image { 
            width: 80px; 
            height: 80px; 
            object-fit: contain; 
            border-radius: 8px;
        }
        .logo-text-group .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .logo-text-group .logo-subtext {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }
        nav ul { display: flex; list-style: none; gap: 20px; }
        nav a { color: white; text-decoration: none; font-weight: 500; padding: 10px 20px; border-radius: 30px; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        nav a:hover { background: rgba(255, 255, 255, 0.15); }
        
        .map-section { max-width: 1400px; width: 100%; margin: 40px auto; padding: 20px; }
        .map-card { background: white; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); padding: 2.5rem; text-align: right; }
        .map-card h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .map-card h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 3px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        /* Map Styles */
        #map {
            height: 600px;
            width: 100%;
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            z-index: 1;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .leaflet-popup-content {
            margin: 0;
            width: 320px !important; /* Force a wider popup */
            font-family: 'Tajawal', sans-serif;
        }
        /* Popup Card Styles - Inspired by index.php */
        .map-popup-card {
            padding: 1rem;
            background-color: #fff;
        }
        .map-popup-card .card-header {
            text-align: right;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .map-popup-card .program-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 5px 0;
        }
        .map-popup-card .organization {
            font-size: 0.9rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .map-popup-card .card-body { padding: 1rem 0; }
        .map-popup-card .program-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .map-popup-card .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }
        .map-popup-card .detail-icon { color: var(--primary); }
        .map-popup-card .program-description {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.5;
            margin: 0;
        }
        .map-popup-card .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            border-top: 1px solid #eee;
        }
        .map-popup-card .program-fee { font-weight: bold; font-size: 1rem; color: var(--success); }
        .map-popup-card .program-fee.free-badge { color: var(--accent); }
        .map-popup-card .register-btn {
            background: var(--primary);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }
        .map-popup-card .register-btn:hover { background: #7a1fc2; }
        .back-btn { display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }
        .back-btn i { margin-right: 5px; }

    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="<?php echo htmlspecialchars($site_settings['logo_path'] ?? 'assets/img/default-logo.png'); ?>" alt="شعار" class="logo-image">
                <div class="logo-text-group">
                    <div class="logo-text"><?php echo htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية'); ?></div>
                    <?php if (!empty($site_settings['guide_subtitle'])): ?>
                        <div class="logo-subtext"><?php echo htmlspecialchars($site_settings['guide_subtitle']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                    <li><a href="submit_program.php"><i class="fas fa-plus-circle"></i> أضف برنامجك</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="map-section">
        <div class="map-card">
            <h2><i class="fas fa-map-marked-alt"></i> خريطة توزيع البرامج في الرياض</h2>
            <?php if ($error): ?><p class="message error"><?php echo $error; ?></p><?php endif; ?>
            <div id="map"></div>
            <a href="index.php" class="back-btn"><i class="fas fa-arrow-right"></i> العودة إلى الرئيسية</a>
        </div>
    </section>

    <!-- Leaflet Map Library JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
    // Helper function to create the HTML for the program card popup
    function createPopupHtml(loc) {
        const isFree = (loc.price == 0 || ['مجاناً', 'مجاني'].includes(String(loc.price).trim().toLowerCase()));
        const priceHtml = `<div class="program-fee ${isFree ? 'free-badge' : ''}">${isFree ? 'مجاناً' : loc.price}</div>`;

        // Shorten description for the popup
        const words = loc.description.split(' ');
        const shortDesc = words.slice(0, 15).join(' ') + (words.length > 15 ? '...' : '');

        return `
            <div class="map-popup-card">
                <div class="card-header">
                    <h3 class="program-title">${loc.title}</h3>
                    <div class="organization">
                        <i class="fas fa-building"></i>
                        ${loc.organizer}
                    </div>
                </div>
                <div class="card-body">
                    <div class="program-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt detail-icon"></i>
                            <div class="detail-text">${loc.location}</div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock detail-icon"></i>
                            <div class="detail-text">${loc.duration}</div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar detail-icon"></i>
                            <div class="detail-text">${loc.start_date}</div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user-friends detail-icon"></i>
                            <div class="detail-text">${loc.age_group}</div>
                        </div>
                    </div>
                    <p class="program-description">${shortDesc}</p>
                </div>
                <div class="card-footer">
                    ${priceHtml}
                    <a href="${loc.registration_link || '#'}" class="register-btn" target="_blank" rel="noopener noreferrer">سجل الآن</a>
                </div>
            </div>
        `;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Initialize the map and set its view to Riyadh's coordinates
        const map = L.map('map').setView([24.7136, 46.6753], 10);

        // 2. Add a tile layer to the map (the map's background image)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // 3. Get the locations data from PHP
        const locations = <?php echo $locations_json; ?>;

        // 4. Loop through the locations and add markers to the map
        if (locations.length > 0) {
            locations.forEach(loc => {
                // Create a colored circle marker
                const circleMarker = L.circleMarker([loc.lat, loc.lng], {
                    radius: 15, // Increased radius for bigger circles
                    fillColor: "#8a2be2", // Primary color
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);

                // Add a popup with program information
                const popupContent = createPopupHtml(loc);
                circleMarker.bindPopup(popupContent, {
                    minWidth: 320, // Set a minimum width for the popup
                });
            });
        } else {
            console.log("No locations with valid coordinates found to display on the map.");
        }
    });
    </script>
</body>
</html>