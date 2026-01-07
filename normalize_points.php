<?php
include_once __DIR__ . "/config/db.php";

if (!isset($conn)) {
    die("Connection failed: \$conn is not set.");
}

$res = mysqli_query($conn, "SELECT user_id, points FROM users");
while($row = mysqli_fetch_assoc($res)) {
    $uid = $row['user_id'];
    $old_pts = (int)$row['points'];
    
    // Normalize: Scale by 20x. If they had > 20,000, they get 1000.
    $new_pts = round($old_pts / 20);
    if($new_pts > 1000) $new_pts = 1000;
    
    // New Badge Logic
    $total = $new_pts;
    $badge = "Newbie";
    if ($total >= 1000) $badge = "Food God";
    elseif ($total >= 900) $badge = "Connoisseur";
    elseif ($total >= 800) $badge = "Dish Discoverer";
    elseif ($total >= 700) $badge = "Cuisine Cadet";
    elseif ($total >= 600) $badge = "Taste Tester";
    elseif ($total >= 500) $badge = "Flavor Explorer";
    elseif ($total >= 400) $badge = "Foodie";
    elseif ($total >= 300) $badge = "Meal Enthusiast";
    elseif ($total >= 200) $badge = "Appetizer Apprentice";
    elseif ($total >= 100) $badge = "Snack Starter";
    elseif ($total >= 50) $badge = "Rookie Eater";
    
    mysqli_query($conn, "UPDATE users SET points = $new_pts, badge = '$badge' WHERE user_id = $uid");
    echo "Updated User $uid: $old_pts -> $new_pts ($badge)<br>";
}

echo "<br><b>Normalization Complete!</b>";
// unlink(__FILE__); // Disabling self-destruct for debugging
?>
