<?php
include "config/db.php";

$sql = "UPDATE reward_items 
        SET image_url = 'assets/img/rewards/voucher10.jpg' 
        WHERE name LIKE '%RM10%'";

if(mysqli_query($conn, $sql)) {
    echo "<h1>Database Updated Successfully!</h1>";
    echo "<p>The RM10 voucher now points to <b>assets/img/rewards/voucher10.jpg</b></p>";
    echo "<hr>";
    echo "<h3>Troubleshooting steps if image still doesn't show:</h3>";
    echo "<ul>
            <li>Make sure the image is in: <b>c:\\xampph\\htdocs\\icm572group\\assets\\img\\rewards\\voucher10.jpg</b></li>
            <li>Double check the file extension. Windows sometimes hides extensions. It should NOT be <b>voucher10.jpg.png</b></li>
            <li>Refresh your browser with <b>Ctrl + F5</b> to clear cache.</li>
          </ul>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
