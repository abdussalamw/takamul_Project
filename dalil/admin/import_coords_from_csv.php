<?php
/**
 * سكريبت استيراد الإحداثيات من ملف CSV
 * يقرأ ملف google_maps_expanded.csv ويحاول استخراج الإحداثيات من الروابط الطويلة
 * ثم يطابق البرامج بالاسم ويحدث قاعدة البيانات
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die(json_encode(['error' => 'غير مصرح'], JSON_UNESCAPED_UNICODE));
}

include_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$csv_file = __DIR__ . '/../../الخرائط/google_maps_expanded.csv';

if (!file_exists($csv_file)) {
    die(json_encode(['error' => 'ملف CSV غير موجود: ' . $csv_file], JSON_UNESCAPED_UNICODE));
}

/**
 * يستخرج الإحداثيات من رابط جوجل ماب طويل
 * يدعم الأنماط التالية:
 *   - q=24.730051,46.657001  → إحداثيات مباشرة في q
 *   - @24.7136,46.6753       → إحداثيات بعد @
 *   - !3d24.7!4d46.6         → إحداثيات مضمنة
 *   - /place/24.7,46.6       → إحداثيات في المسار
 */
function extract_coords_from_long_url($url) {
    if (empty($url)) return null;

    // نمط 1: q=lat,lng (إحداثيات رقمية مباشرة)
    if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    }

    // نمط 2: @lat,lng
    if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    }

    // نمط 3: !3d[lat]!4d[lng]
    if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m)) {
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    }

    // نمط 4: !2d[lng]!3d[lat]
    if (preg_match('/!2d(-?\d+\.\d+)!3d(-?\d+\.\d+)/', $url, $m)) {
        return ['lat' => (float)$m[2], 'lng' => (float)$m[1]];
    }

    // نمط 5: /place/lat,lng
    if (preg_match('#/place/(-?\d+\.\d+),(-?\d+\.\d+)#', $url, $m)) {
        return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
    }

    return null;
}

// قراءة ملف CSV
$rows = [];
if (($handle = fopen($csv_file, 'r')) !== false) {
    $header = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) >= 3) {
            $rows[] = [
                'name'     => trim($data[0]),
                'short'    => trim($data[1]),
                'long'     => trim($data[2]),
                'coords'   => isset($data[3]) ? trim($data[3]) : ''
            ];
        }
    }
    fclose($handle);
}

// جلب كل البرامج من قاعدة البيانات
$stmt = $pdo->query("SELECT id, title, google_map, latitude, longitude FROM programs");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// بناء فهرس سريع: الرابط المختصر → برنامج
$by_short_url = [];
$by_name      = [];
foreach ($programs as $prog) {
    $short = trim($prog['google_map'] ?? '');
    if (!empty($short)) {
        $by_short_url[$short] = $prog;
    }
    $by_name[trim($prog['title'])] = $prog;
}

$update_stmt = $pdo->prepare("UPDATE programs SET latitude = ?, longitude = ? WHERE id = ?");

$results = [
    'total_csv'  => count($rows),
    'updated'    => 0,
    'skipped'    => 0,
    'not_found'  => 0,
    'no_coords'  => 0,
    'details'    => []
];

foreach ($rows as $row) {
    $name  = $row['name'];
    $short = $row['short'];
    $long  = $row['long'];

    // ابحث عن البرنامج في قاعدة البيانات
    $matched_prog = null;
    if (!empty($short) && isset($by_short_url[$short])) {
        $matched_prog = $by_short_url[$short];
    }
    // بحث بديل بالاسم
    if (!$matched_prog) {
        foreach ($by_name as $title => $prog) {
            // مطابقة جزئية بالاسم
            if (mb_strpos($title, $name) !== false || mb_strpos($name, $title) !== false) {
                $matched_prog = $prog;
                break;
            }
        }
    }

    if (!$matched_prog) {
        $results['not_found']++;
        $results['details'][] = [
            'csv_name' => $name,
            'status'   => 'not_found',
            'note'     => 'لم يُوجد في قاعدة البيانات'
        ];
        continue;
    }

    // استخراج الإحداثيات من الرابط الطويل
    $coords = extract_coords_from_long_url($long);

    if (!$coords) {
        $results['no_coords']++;
        $results['details'][] = [
            'id'       => $matched_prog['id'],
            'title'    => $matched_prog['title'],
            'csv_name' => $name,
            'status'   => 'no_coords',
            'note'     => 'لا يمكن استخراج الإحداثيات من الرابط الطويل'
        ];
        continue;
    }

    // تحقق من السعودية
    if (!_is_in_saudi_arabia($coords['lat'], $coords['lng'])) {
        $results['skipped']++;
        $results['details'][] = [
            'id'    => $matched_prog['id'],
            'title' => $matched_prog['title'],
            'status'=> 'outside_sa',
            'lat'   => $coords['lat'],
            'lng'   => $coords['lng'],
            'note'  => 'إحداثيات خارج المملكة — تجاهلت'
        ];
        continue;
    }

    // حفظ في قاعدة البيانات
    $update_stmt->execute([$coords['lat'], $coords['lng'], $matched_prog['id']]);
    $results['updated']++;
    $results['details'][] = [
        'id'    => $matched_prog['id'],
        'title' => $matched_prog['title'],
        'status'=> 'updated',
        'lat'   => $coords['lat'],
        'lng'   => $coords['lng']
    ];
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
