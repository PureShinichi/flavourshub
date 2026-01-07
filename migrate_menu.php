<?php
include "config/db.php";

// 1. Create Table
$sql = "CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    points INT DEFAULT 0,
    img VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

// 2. Wipe and re-seed to ensure all data is there
mysqli_query($conn, "TRUNCATE TABLE menu_items");

// 3. Complete Data from order.php
$foods = [
  "burgers" => [
    ["name"=>"El Classico","img"=>"assets/img/bimg1.png","points"=>10, "price"=>12.50],
    ["name"=>"Cheezzy","img"=>"assets/img/bimg2.png","points"=>12, "price"=>14.00],
    ["name"=>"Double D","img"=>"assets/img/bimg3.png","points"=>15, "price"=>18.50],
    ["name"=>"Veggie Karen","img"=>"assets/img/bimg4.png","points"=>8, "price"=>11.00],
    ["name"=>"Spicy Acy","img"=>"assets/img/bimg5.png","points"=>11, "price"=>13.50]
  ],
  "pizzas" => [
    ["name"=>"El Classico","img"=>"assets/img/pimg1.png","points"=>10, "price"=>15.00],
    ["name"=>"Suppepperoni","img"=>"assets/img/pimg2.png","points"=>14, "price"=>17.50],
    ["name"=>"ChisiCheezy","img"=>"assets/img/pimg3.png","points"=>15, "price"=>16.00],
    ["name"=>"Flaming Pitzas","img"=>"assets/img/pimg4.png","points"=>16, "price"=>19.50],
    ["name"=>"Supra Cheesy","img"=>"assets/img/pimg5.png","points"=>12, "price"=>16.50]
  ],
  "pastas" => [
    ["name"=>"El Carbonara","img"=>"assets/img/paimg3.png","points"=>12, "price"=>16.00],
    ["name"=>"El Spicy","img"=>"assets/img/paimg2.png","points"=>14, "price"=>15.50],
    ["name"=>"El Bakso","img"=>"assets/img/paimg1.png","points"=>11, "price"=>14.50],
    ["name"=>"Elio Olio","img"=>"assets/img/paimg4.png","points"=>10, "price"=>13.00],
    ["name"=>"El Tom n Yam","img"=>"assets/img/paimg5.png","points"=>13, "price"=>18.00]
  ],
  "coffee" => [
    ["name"=>"Espresso","img"=>"assets/img/coffe1.png","points"=>5, "price"=>6.00],
    ["name"=>"Latte","img"=>"assets/img/coffe2.png","points"=>7, "price"=>10.50],
    ["name"=>"Cappuccino","img"=>"assets/img/coffe3.png","points"=>7, "price"=>10.50],
    ["name"=>"Mocha","img"=>"assets/img/coffe4.png","points"=>8, "price"=>11.00],
    ["name"=>"Americano","img"=>"assets/img/coffe5.png","points"=>6, "price"=>8.00]
  ],
  "frappe" => [
    ["name"=>"Caramel Frappe","img"=>"assets/img/frappe1.png","points"=>9, "price"=>13.50],
    ["name"=>"Mocha Frappe","img"=>"assets/img/frappe2.png","points"=>9, "price"=>13.50],
    ["name"=>"Vanilla Frappe","img"=>"assets/img/frappe3.png","points"=>9, "price"=>12.50],
    ["name"=>"Choco Chip","img"=>"assets/img/frappe4.png","points"=>10, "price"=>14.00],
    ["name"=>"Strawberry","img"=>"assets/img/frappe5.png","points"=>9, "price"=>13.00]
  ],
  "fruity" => [
    ["name"=>"Mango Smoothie","img"=>"assets/img/fruity1.png","points"=>8, "price"=>11.50],
    ["name"=>"Berry Blast","img"=>"assets/img/fruity2.png","points"=>8, "price"=>12.00],
    ["name"=>"Citrus Splash","img"=>"assets/img/fruity3.png","points"=>7, "price"=>10.00],
    ["name"=>"Kiwi Kick","img"=>"assets/img/fruity4.png","points"=>8, "price"=>11.50],
    ["name"=>"Peach Perfect","img"=>"assets/img/fruity5.png","points"=>7, "price"=>11.00]
  ],
  "ice_cream" => [
    ["name"=>"Vanilla Scoop","img"=>"assets/img/ice1.png","points"=>5, "price"=>5.00],
    ["name"=>"Chocolate Fudge","img"=>"assets/img/ice2.png","points"=>6, "price"=>6.50],
    ["name"=>"Strawberry Sundae","img"=>"assets/img/ice3.png","points"=>7, "price"=>8.00],
    ["name"=>"Mint Choco","img"=>"assets/img/ice4.png","points"=>6, "price"=>6.50],
    ["name"=>"Cookie Dough","img"=>"assets/img/ice5.png","points"=>7, "price"=>7.50]
  ],
  "cakes" => [
    ["name"=>"Cheese Cake","img"=>"assets/img/cake1.png","points"=>10, "price"=>12.00],
    ["name"=>"Choco Lava","img"=>"assets/img/cake2.png","points"=>12, "price"=>14.50],
    ["name"=>"Red Velvet","img"=>"assets/img/cake3.png","points"=>11, "price"=>13.00],
    ["name"=>"Tiramisu","img"=>"assets/img/cake4.png","points"=>12, "price"=>14.00],
    ["name"=>"Matcha Slice","img"=>"assets/img/cake5.png","points"=>11, "price"=>13.50]
  ],
  "bingsu" => [
    ["name"=>"Mango Bingsu","img"=>"assets/img/bing1.png","points"=>15, "price"=>18.00],
    ["name"=>"Choco Bingsu","img"=>"assets/img/bing2.png","points"=>15, "price"=>18.50],
    ["name"=>"Berry Bingsu","img"=>"assets/img/bing3.png","points"=>14, "price"=>17.00],
    ["name"=>"Matcha Bingsu","img"=>"assets/img/bing4.png","points"=>14, "price"=>17.50],
    ["name"=>"Caramel Bingsu","img"=>"assets/img/bing5.png","points"=>15, "price"=>18.00]
  ],
  "pastries" => [
    ["name"=>"Croissant","img"=>"assets/img/pas1.png","points"=>6, "price"=>7.00],
    ["name"=>"Danish","img"=>"assets/img/pas2.png","points"=>7, "price"=>8.50],
    ["name"=>"Muffin","img"=>"assets/img/pas3.png","points"=>5, "price"=>6.00],
    ["name"=>"Donut","img"=>"assets/img/pas4.png","points"=>4, "price"=>5.00],
    ["name"=>"Eclair","img"=>"assets/img/pas5.png","points"=>7, "price"=>8.00]
  ]
];

foreach($foods as $cat => $items) {
    foreach($items as $i) {
        $n = $i['name'];
        $im = $i['img'];
        $p = $i['points'];
        $pr = $i['price'];
        mysqli_query($conn, "INSERT INTO menu_items (name, category, price, points, img) VALUES ('$n', '$cat', $pr, $p, '$im')");
    }
}

echo "All " . mysqli_affected_rows($conn) . " menu items imported successfully!";
?>
