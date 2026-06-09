<?php
// --- Production vs. Development Environment ---
// Set this to 'development' to see detailed errors.
// Set this to 'production' to hide detailed errors for security.
define('ENVIRONMENT', 'development'); 

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

$host = 'localhost';
$db = 'takamul_2026';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // On production, log the error and show a generic message.
    // error_log("Database Connection Failed: " . $e->getMessage());
    die("عذراً، حدث خطأ أثناء الاتصال بالنظام. يرجى المحاولة لاحقاً.");
}

/**
 * Loads all site settings from the database into a global array.
 * This function is designed to be called once per request.
 *
 * @param PDO $pdo The database connection object.
 * @return array An associative array of settings.
 */
function load_site_settings($pdo) {
    $settings = [];
    try {
        // Check if the site_settings table exists before querying to prevent errors.
        $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    } catch (PDOException $e) {
        // Silently fail, but in a real app, this should be logged.
    }
    return $settings;
}

/**
 * Extracts coordinates (latitude and longitude) from a Google Maps link or iframe embed code.
 *
 * @param string $url The Google Maps URL or iframe code.
 * @return array|null An array with 'lat' and 'lng' keys, or null on failure.
 */
function get_coords_from_google_maps($url) {
    $url = trim($url);
    if (empty($url)) {
        return null;
    }

    $lat = null;
    $lng = null;

    // Step 1: Check for iframe and extract src
    if (str_starts_with($url, '<iframe') || strpos($url, '<iframe') !== false) {
        if (preg_match('/<iframe[^>]+src="([^"]+)"/', $url, $iframe_matches)) {
            $url = html_entity_decode($iframe_matches[1]);
        }
    }
    
    // Step 2: If the URL is a shortened goo.gl link, expand it to the full URL.
    if (strpos($url, 'maps.app.goo.gl') !== false || strpos($url, 'goo.gl/maps') !== false) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_exec($ch);
            $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
            if ($final_url) {
                $url = $final_url;
            }
        }
    }

    // Step 3: Try multiple patterns to extract coordinates from the final URL.
    if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
        $lat = $matches[1];
        $lng = $matches[2];
    } elseif (preg_match('/data=.*!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
        $lat = $matches[1];
        $lng = $matches[2];
    } elseif (preg_match('/ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
        $lat = $matches[1];
        $lng = $matches[2];
    } elseif (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
        $lat = $matches[1];
        $lng = $matches[2];
    } elseif (preg_match('!1d(-?\d+\.\d+)!2d(-?\d+\.\d+)!', $url, $matches)) {
        $val1 = (float)$matches[1];
        $val2 = (float)$matches[2];
        if (abs($val1) < abs($val2)) {
            $lat = $val1;
            $lng = $val2;
        } else {
            $lat = $val2;
            $lng = $val1;
        }
    }

    if ($lat !== null && $lng !== null) {
        return ['lat' => (float)$lat, 'lng' => (float)$lng];
    }
    return null;
}

// Load settings into a globally accessible variable
$site_settings = load_site_settings($pdo);
?>