<?php
/**
 * سكريبت تنظيف الإحداثيات الخاطئة
 * يحذف أي إحداثيات مُخزّنة خارج نطاق المملكة العربية السعودية
 * ويُعيد تعيينها لـ NULL حتى يتم استخراجها مجدداً بشكل صحيح
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    http_response_code(403);
    die(json_encode(['error' => 'غير مصرح — يجب تسجيل الدخول أولاً'], JSON_UNESCAPED_UNICODE));
}

include_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// النطاق الجغرافي للمملكة العربية السعودية
$SA_LAT_MIN = 15.0;
$SA_LAT_MAX = 33.0;
$SA_LNG_MIN = 34.0;
$SA_LNG_MAX = 56.0;

// جلب كل البرامج التي لديها إحداثيات مُخزّنة
$stmt = $pdo->query("
    SELECT id, title, latitude, longitude, google_map
    FROM programs
    WHERE latitude IS NOT NULL AND latitude != 0
      AND longitude IS NOT NULL AND longitude != 0
");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [
    'total_with_coords' => count($programs),
    'cleared'           => 0,
    'valid'             => 0,
    'details'           => []
];

$clear_stmt = $pdo->prepare("UPDATE programs SET latitude = NULL, longitude = NULL WHERE id = ?");

foreach ($programs as $prog) {
    $lat = (float) $prog['latitude'];
    $lng = (float) $prog['longitude'];

    $in_sa = ($lat >= $SA_LAT_MIN && $lat <= $SA_LAT_MAX && $lng >= $SA_LNG_MIN && $lng <= $SA_LNG_MAX);

    if (!$in_sa) {
        // إحداثيات خاطئة — امسحها
        $clear_stmt->execute([$prog['id']]);
        $results['cleared']++;
        $results['details'][] = [
            'id'     => $prog['id'],
            'title'  => $prog['title'],
            'status' => 'cleared',
            'old_lat'=> $lat,
            'old_lng'=> $lng,
            'note'   => 'إحداثيات خارج المملكة — تم حذفها'
        ];
    } else {
        $results['valid']++;
        $results['details'][] = [
            'id'     => $prog['id'],
            'title'  => $prog['title'],
            'status' => 'valid',
            'lat'    => $lat,
            'lng'    => $lng
        ];
    }
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
