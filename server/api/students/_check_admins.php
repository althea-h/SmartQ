<?php
require_once __DIR__ . '/../../config/database.php';
$db = (new Database())->getConnection();

// Add validated_by_id column to store admin's ID for JOIN
$check = $db->query("SHOW COLUMNS FROM students LIKE 'validated_by_id'");
if ($check->rowCount() === 0) {
    $db->exec("ALTER TABLE students ADD COLUMN validated_by_id INT NULL DEFAULT NULL AFTER validated_by");
    echo "Column 'validated_by_id' added successfully.\n";
} else {
    echo "Column 'validated_by_id' already exists — skipping.\n";
}

echo "Migration complete.\n";
