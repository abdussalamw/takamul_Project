<?php
$host = 'localhost';
$db = 'takamul';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
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