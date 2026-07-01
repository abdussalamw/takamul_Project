<?php
/**
 * سكريبت تحويل روابط Google Maps إلى إحداثيات
 * يُشغّل مرة واحدة من لوحة الإدارة لتحديث جميع البرامج
 * التي لديها رابط مختصر ولا تزال بدون إحداثيات
 */

// حماية: هذا السكريبت للإدارة فقط
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    http_response_code(403);
    die(json_encode(['error' => 'غير مصرح — يجب تسجيل الدخول أولاً'], JSON_UNESCAPED_UNICODE));
}

include_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// جلب جميع البرامج التي لديها google_map لكن بدون إحداثيات مخزنة
$stmt = $pdo->query("
    SELECT id, google_map 
    FROM programs 
    WHERE (latitude IS NULL OR latitude = '' OR latitude = 0)
      AND (longitude IS NULL OR longitude = '' OR longitude = 0)
      AND google_map IS NOT NULL 
      AND google_map != ''
      AND status = 'published'
    ORDER BY id
");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [
    'total'    => count($programs),
    'updated'  => 0,
    'failed'   => 0,
    'details'  => []
];

// تحديث كل برنامج
$update_stmt = $pdo->prepare("UPDATE programs SET latitude = ?, longitude = ? WHERE id = ?");

foreach ($programs as $prog) {
    $coords = get_coords_from_google_maps($prog['google_map']);

    if ($coords) {
        $update_stmt->execute([$coords['lat'], $coords['lng'], $prog['id']]);
        $results['updated']++;
        $results['details'][] = [
            'id'     => $prog['id'],
            'status' => 'ok',
            'lat'    => $coords['lat'],
            'lng'    => $coords['lng']
        ];
    } else {
        $results['failed']++;
        $results['details'][] = [
            'id'     => $prog['id'],
            'status' => 'failed',
            'map'    => substr($prog['google_map'], 0, 80)
        ];
    }
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
