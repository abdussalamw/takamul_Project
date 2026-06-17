<?php
require __DIR__ . '/../includes/db_connect.php';
echo "=== age_groups table ===\n";
print_r($pdo->query("SELECT * FROM age_groups")->fetchAll(PDO::FETCH_ASSOC));

echo "=== Unique programs.age_group column values ===\n";
print_r($pdo->query("SELECT DISTINCT age_group FROM programs")->fetchAll(PDO::FETCH_COLUMN));
