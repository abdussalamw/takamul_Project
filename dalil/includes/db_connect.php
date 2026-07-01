<?php
// Load database configuration if available, otherwise fall back to local defaults
$config_path = __DIR__ . '/db_config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'takamul_2026');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_ENVIRONMENT', 'development');
}

// --- Production vs. Development Environment ---
define('ENVIRONMENT', DB_ENVIRONMENT);

if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

header('Content-Type: text/html; charset=utf-8');

$host = DB_HOST;
$db = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("Database Connection Failed: " . $e->getMessage());
    }
    die("عذراً، حدث خطأ أثناء الاتصال بالنظام. يرجى المحاولة لاحقاً.");
}

/**
 * Loads all site settings from the database into a global array.
 */
function load_site_settings($pdo) {
    $settings = [];
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    } catch (PDOException $e) {
        // Silently fail
    }
    return $settings;
}

/**
 * Extracts coordinates from a Google Maps link (short/full/iframe).
 * Supports: maps.app.goo.gl, goo.gl/maps, full URLs, embed iframes.
 * Uses GET requests + HTML parsing for maximum compatibility.
 *
 * @param string $url
 * @return array|null ['lat' => float, 'lng' => float] or null
 */
function get_coords_from_google_maps($url) {
    $url = trim($url);
    if (empty($url)) return null;

    // ❶ استخراج src من iframe إن وُجد
    if (strpos($url, '<iframe') !== false) {
        if (preg_match('/<iframe[^>]+src="([^"]+)"/', $url, $m)) {
            $url = html_entity_decode($m[1]);
        }
    }

    // ❷ تجربة استخراج مباشر من الرابط (للروابط الكاملة التي تحتوي إحداثيات)
    $coords = _gmaps_extract_from_url($url);
    if ($coords && _is_in_saudi_arabia($coords['lat'], $coords['lng'])) return $coords;

    // ❸ فكّ الرابط عبر curl بطلب GET حقيقي (يعمل مع الروابط المختصرة وجميع الأنواع)
    if (!function_exists('curl_init')) return null;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING       => '',
    ]);
    $body      = curl_exec($ch);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    // ❹ استخراج من الـ URL النهائي بعد تتبع الـ redirects
    if ($final_url && $final_url !== $url) {
        // أولاً: الرابط كما هو
        $coords = _gmaps_extract_from_url($final_url);
        if ($coords && _is_in_saudi_arabia($coords['lat'], $coords['lng'])) return $coords;
        // ثانياً: بعد فكّ URL-encoding (مهم لـ maps.app.goo.gl التي تُضمّن الإحداثيات في pb= مُرمَّزة)
        $decoded_url = urldecode($final_url);
        if ($decoded_url !== $final_url) {
            $coords = _gmaps_extract_from_url($decoded_url);
            if ($coords && _is_in_saudi_arabia($coords['lat'], $coords['lng'])) return $coords;
        }
    }

    // ❺ استخراج من محتوى HTML الصفحة كخط دفاع أخير
    if ($body) {
        $coords = _gmaps_extract_from_html($body);
        if ($coords && _is_in_saudi_arabia($coords['lat'], $coords['lng'])) {
            return $coords;
        }
    }

    return null;
}

/**
 * يتحقق مما إذا كانت الإحداثيات تقع داخل النطاق الجغرافي للمملكة العربية السعودية.
 * يمنع ذلك جلب إحداثيات خاطئة (مثل باريس أو خارج المملكة) ناتجة عن تحديد موقع الخادم (IP Geolocation)
 * عند تشغيل السكريبت من استضافة خارجية.
 */
function _is_in_saudi_arabia($lat, $lng) {
    return ($lat >= 15.0 && $lat <= 33.0 && $lng >= 34.0 && $lng <= 56.0);
}

/**
 * [محذوف] كان يحجب إحداثيات حقيقية قريبة من مراكز الرياض — تم الاستغناء عنه.
 * الفلتر الوحيد المستخدم الآن هو _is_in_saudi_arabia().
 */
function _is_generic_gmaps_center($lat, $lng) {
    return false; // غير فعّال — الفلتر الحقيقي هو _is_in_saudi_arabia()
}

/**
 * يستخرج الإحداثيات من نص الـ URL بأنماط متعددة.
 */
function _gmaps_extract_from_url($url) {
    $coords = null;
    // !2d[lng]!3d[lat]  — روابط embed وصفحات maps.google.com بعد فكّ الـ encoding
    if (preg_match('/!2d(-?\d+\.\d+)!3d(-?\d+\.\d+)/', $url, $m))
        $coords = ['lat' => (float)$m[2], 'lng' => (float)$m[1]];
    // !3d[lat]!4d[lng]
    elseif (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m))
        $coords = ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    // @lat,lng  — روابط share/place
    elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m))
        $coords = ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    // q=lat,lng أو ll=lat,lng
    elseif (preg_match('/[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m))
        $coords = ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    // /place/lat,lng في المسار
    elseif (preg_match('#/place/(-?\d+\.\d+),(-?\d+\.\d+)#', $url, $m))
        $coords = ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
        
    if ($coords && _is_generic_gmaps_center($coords['lat'], $coords['lng'])) {
        return null;
    }
    return $coords;
}

/**
 * يستخرج الإحداثيات من HTML صفحة جوجل ماب.
 * مرتّبة من الأكثر دقةً إلى الأقل (Fallback cascade).
 */
function _gmaps_extract_from_html($html) {
    // نمط A: canonical أو og:url يحتويان @lat,lng
    if (preg_match('/rel="canonical" href="[^"]*@(-?\d+\.\d+),(-?\d+\.\d+)/', $html, $m))
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    if (preg_match('/og:url[^>]+content="[^"]*@(-?\d+\.\d+),(-?\d+\.\d+)/', $html, $m))
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];

    // نمط B: !2d!3d أو !3d!4d ظاهر مباشرة في HTML (embed URLs)
    if (preg_match('/!2d(-?\d+\.\d+)!3d(-?\d+\.\d+)/', $html, $m))
        return ['lat' => (float)$m[2], 'lng' => (float)$m[1]];
    if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $html, $m))
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];

    // نمط B2: %212d/%213d (URL-encoded !2d!3d) داخل hrefs — الأهم لصفحات maps.app.goo.gl
    // مثال: %211d3711026.2500759326%212d46.8225288%213d24.72519185
    if (preg_match('/%212d(-?\d+\.\d+)%213d(-?\d+\.\d+)/', $html, $m))
        return ['lat' => (float)$m[2], 'lng' => (float)$m[1]];
    if (preg_match('/%213d(-?\d+\.\d+)%214d(-?\d+\.\d+)/', $html, $m))
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];

    // نمط C: JSON مضمّن "center":{"lat":X,"lng":Y}
    if (preg_match('/"center"\s*:\s*\{\s*"lat"\s*:\s*(-?\d+\.\d+)\s*,\s*"lng"\s*:\s*(-?\d+\.\d+)/', $html, $m))
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];

    // نمط F: Fallback — البحث عن إحداثيات مستقلة في نطاق السعودية
    // ❗ (?<!\d) يمنع التقاط أرقام مضمّنة في أعداد أكبر (مثل 3711026.2500759326)
    preg_match_all('/(?<!\d)(-?(?:1[5-9]|2[0-9]|3[012])\.\d{5,})(?!\d)/', $html, $lat_m);
    preg_match_all('/(?<!\d)(-?(?:3[4-9]|4[0-9]|5[0-6])\.\d{5,})(?!\d)/', $html, $lng_m);
    $lats = array_values(array_unique($lat_m[1]));
    $lngs = array_values(array_unique($lng_m[1]));
    if (!empty($lats) && !empty($lngs)) {
        return ['lat' => (float)$lats[0], 'lng' => (float)$lngs[0]];
    }

    return null;
}

// Load settings into a globally accessible variable
$site_settings = load_site_settings($pdo);
?>