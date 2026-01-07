<?php
include "config/db.php";

// Check if column exists
$res = mysqli_query($conn, "SHOW COLUMNS FROM `redemptions` LIKE 'voucher_code'");
if (mysqli_num_rows($res) == 0) {
    mysqli_query($conn, "ALTER TABLE `redemptions` ADD COLUMN `voucher_code` varchar(50) DEFAULT NULL AFTER `points_spent`") or die(mysqli_error($conn));
    echo "Column 'voucher_code' added successfully.<br>";
} else {
    echo "Column 'voucher_code' already exists.<br>";
}

// Also update status enum if needed
mysqli_query($conn, "ALTER TABLE `redemptions` MODIFY COLUMN `status` ENUM('pending','completed','cancelled','used') DEFAULT 'pending'") or die(mysqli_error($conn));
echo "Status enum updated successfully.<br>";

echo "DB Update Complete. You can delete this file.";
?>
