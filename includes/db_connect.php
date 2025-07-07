<?php
// --- Production vs. Development Environment ---
// Set this to 'development' to see detailed errors.
// Set this to 'production' to hide detailed errors for security.
define('ENVIRONMENT', 'production'); 

if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

$host = 'localhost';
$db = 'takamul';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
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

// Load settings into a globally accessible variable
$site_settings = load_site_settings($pdo);
?>