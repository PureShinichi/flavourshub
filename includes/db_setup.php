<?php
include __DIR__ . "/../config/db.php";

// Create reward_items table
$sql1 = "CREATE TABLE IF NOT EXISTS reward_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    points_cost INT NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(50) DEFAULT 'general',
    stock INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create redemptions table
$sql2 = "CREATE TABLE IF NOT EXISTS redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_item_id INT NOT NULL,
    points_spent INT NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)";

// Add some default items if empty
mysqli_query($conn, $sql1);
mysqli_query($conn, $sql2);

$res = mysqli_query($conn, "SELECT COUNT(*) as count FROM reward_items");
$row = mysqli_fetch_assoc($res);
if ($row['count'] == 0) {
    $items = [
        ['Bear Plushie', 'A cute fluffy bear plushie', 500, 'assets/img/rewards/plushie1.png', 'plushie'],
        ['Cat Plushie', 'Soft kitty plushie', 600, 'assets/img/rewards/plushie2.png', 'plushie'],
        ['Flavour Tumblr', 'Keep your drinks hot/cold', 1200, 'assets/img/rewards/tumblr1.png', 'tumblr'],
        ['Enamel Pin', 'Cool logo pin', 200, 'assets/img/rewards/pin1.png', 'accessory'],
        ['RM10 Voucher', 'Discount for your next order', 1000, 'assets/img/rewards/voucher10.png', 'voucher']
    ];
    foreach ($items as $item) {
        $n = mysqli_real_escape_string($conn, $item[0]);
        $d = mysqli_real_escape_string($conn, $item[1]);
        $p = $item[2];
        $i = $item[3];
        $c = $item[4];
        mysqli_query($conn, "INSERT INTO reward_items (name, description, points_cost, image_url, category) VALUES ('$n', '$d', $p, '$i', '$c')");
    }
}
?>
