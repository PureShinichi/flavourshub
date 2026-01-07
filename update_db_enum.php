<?php
include "config/db.php";
try {
    $q = "ALTER TABLE cart_orders MODIFY COLUMN status enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending'";
    if(mysqli_query($conn, $q)) {
        echo "Database updated successfully.";
    } else {
        echo "Error updating database: " . mysqli_error($conn);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
?>
