<?php
require 'c:/xampp/htdocs/Dalil/dalil/includes/db_connect.php';

$url = 'https://www.google.com/maps/place/24.7135514,46.6582983';

$coords = _gmaps_extract_from_url($url);
if ($coords) {
    echo "✅ نجح: Lat={$coords['lat']}, Lng={$coords['lng']}\n";
    echo "داخل السعودية: " . (_is_in_saudi_arabia($coords['lat'], $coords['lng']) ? 'نعم ✅' : 'لا ❌') . "\n";
} else {
    echo "❌ فشل\n";
}
