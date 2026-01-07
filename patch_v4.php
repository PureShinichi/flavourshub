<?php
include "config/db.php";

// Add available column if not exists
$check = mysqli_query($conn, "SHOW COLUMNS FROM menu_items LIKE 'available'");
if(mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE menu_items ADD COLUMN available TINYINT(1) DEFAULT 1");
}

// Create settings table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS settings (
    key_name VARCHAR(50) PRIMARY KEY,
    val VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Seed maintenance mode
mysqli_query($conn, "INSERT IGNORE INTO settings (key_name, val) VALUES ('maintenance_mode', '0')");

echo "Schema updated for stock and maintenance.";
?>
