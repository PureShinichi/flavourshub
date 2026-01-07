<?php
include "config/db.php";

try {
    // Check if column exists
    $check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar_url'");
    if(mysqli_num_rows($check) == 0) {
        $sql = "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL";
        if(mysqli_query($conn, $sql)) {
            echo "Successfully added 'avatar_url' column to users table.<br>";
        } else {
            throw new Exception("Error adding column: " . mysqli_error($conn));
        }
    } else {
        echo "'avatar_url' column already exists.<br>";
    }
    
    // Create uploads directory if not exists
    $upload_dir = "assets/uploads/avatars";
    if (!file_exists($upload_dir)) {
        if(mkdir($upload_dir, 0777, true)) {
            echo "Created upload directory: $upload_dir<br>";
        } else {
            echo "Failed to create upload directory. Check permissions.<br>";
        }
    } else {
        echo "Upload directory already exists.<br>";
    }

    echo "Database update complete. You take flight!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
