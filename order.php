<?php
// order.php - Robust Version with Global Error Handling
ini_set('display_errors', 0); // Hide standard errors to keep JSON clean
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

include "config/db.php";

// Custom Error Handler: Convert Warnings to Exceptions
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

/* --- HELPER --- */
function endJson($data) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function errorJson($msg) {
    endJson(['error' => $msg]);
}

try { /* GLOBAL TRY-CATCH BLOCK */

    // 1. Check Auth (Session started in header if not already, but we need it here for logic)
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    if(!isset($_SESSION['user_id'])) {
        if(isset($_POST['action'])) errorJson("Not Logged In");
        header("Location: login.php");
        exit();
    }
    $user_id = (int)$_SESSION['user_id'];

    // 2. Check DB
    if(!$conn) throw new Exception("Database Connection Failed: " . mysqli_connect_error());

    // 2b. Self-Healing DB (Ensure tables exist)
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'cart_orders'");
    if(mysqli_num_rows($check) == 0) {
        $sql = "CREATE TABLE `cart_orders` (
          `order_id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `food_name` varchar(100) NOT NULL,
          `category` varchar(50) NOT NULL,
          `price` decimal(10,2) NOT NULL,
          `quantity` int(11) NOT NULL DEFAULT 1,
          `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
          `img` varchar(255) DEFAULT NULL,
          `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        if(!mysqli_query($conn, $sql)) throw new Exception("Failed to create cart table: ".mysqli_error($conn));
    }
    
    // Check Columns
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'points'");
    if(mysqli_num_rows($cols) == 0) mysqli_query($conn, "ALTER TABLE users ADD COLUMN points INT DEFAULT 0");
    
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'badge'");
    if(mysqli_num_rows($cols) == 0) mysqli_query($conn, "ALTER TABLE users ADD COLUMN badge VARCHAR(50) DEFAULT 'New Welcomer'");

    /* --- DYNAMIC FOOD DATA FROM DB --- */
    $foods = [];
    $menu_res = mysqli_query($conn, "SELECT * FROM menu_items");
    while($row = mysqli_fetch_assoc($menu_res)) {
        $cat = $row['category'];
        if(!isset($foods[$cat])) $foods[$cat] = [];
        $foods[$cat][] = [
            "name" => $row['name'],
            "img" => $row['img'],
            "points" => (int)$row['points'],
            "price" => (float)$row['price'],
            "available" => (bool)$row['available']
        ];
    }
    
    // Fallback if DB is empty during migration
    if(empty($foods)) {
        $foods = ["burgers" => [["name"=>"El Classico","img"=>"assets/img/bimg1.png","points"=>10, "price"=>12.50]]];
    }
    $mainCategories = ['burgers', 'pizzas', 'pastas'];
    $drinkCategories = ['coffee', 'frappe', 'fruity'];
    $dessertCategories = ['ice_cream', 'cakes', 'bingsu', 'pastries'];


    /* --- ACTION HANDLING --- */
    if(isset($_POST['action'])) {
        $action = $_POST['action'];

        /* ADD */
        if($action == 'add') {
            $food = $_POST['food'] ?? '';
            $cat = $_POST['category'] ?? '';
            $img = $_POST['img'] ?? '';
            
            if(empty($food)) throw new Exception("Food name is missing");

            // Check Stock status
            $stock_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT available FROM menu_items WHERE name='".mysqli_real_escape_string($conn, $food)."' LIMIT 1"));
            if($stock_check && !$stock_check['available']) {
                errorJson("Sorry, this item is OUT OF STOCK!");
            }

            // Look up price
            $price = 15.00; 
            $found = false;
            foreach($foods as $c=>$list){
                foreach($list as $i){
                    if($i['name']===$food){$price=$i['price']; $found=true; break 2;}
                }
            }
            if(!$found) $price = 15.00;

            // Session
            if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if(!isset($_SESSION['cart'][$food])) {
                $_SESSION['cart'][$food] = ['quantity'=>0, 'price'=>$price, 'img'=>$img];
            }
            $_SESSION['cart'][$food]['quantity']++;

            // DB
            $f_esc = mysqli_real_escape_string($conn, $food);
            $cat_esc = mysqli_real_escape_string($conn, $cat);
            $check = mysqli_query($conn, "SELECT * FROM cart_orders WHERE user_id=$user_id AND food_name='$f_esc' AND status='pending'");
            if(mysqli_num_rows($check) > 0) {
                if(!mysqli_query($conn, "UPDATE cart_orders SET quantity=quantity+1 WHERE user_id=$user_id AND food_name='$f_esc' AND status='pending'")) {
                    throw new Exception("DB Update Error: " . mysqli_error($conn));
                }
            } else {
                $q = "INSERT INTO cart_orders (user_id, food_name, category, price, quantity, img) VALUES ($user_id, '$f_esc', '$cat_esc', $price, 1, '$img')";
                if(!mysqli_query($conn, $q)) throw new Exception("DB Insert Error: " . mysqli_error($conn));
            }

            // Return Cart
            $out = [];
            foreach($_SESSION['cart'] as $k=>$v) $out[] = ['name'=>$k, 'quantity'=>$v['quantity'], 'price'=>$v['price'], 'img'=>$v['img']];
            endJson($out);
        }
        
        /* REMOVE */
        if($action == 'remove') {
            $food = $_POST['food'] ?? '';
            unset($_SESSION['cart'][$food]);
            $f_esc = mysqli_real_escape_string($conn, $food);
            mysqli_query($conn, "DELETE FROM cart_orders WHERE user_id=$user_id AND food_name='$f_esc' AND status='pending'");
            
            $out = [];
            foreach($_SESSION['cart'] as $k=>$v) $out[] = ['name'=>$k, 'quantity'=>$v['quantity'], 'price'=>$v['price'], 'img'=>$v['img']];
            endJson($out);
        }

        /* CLEAR */
        if($action == 'clear') {
            $_SESSION['cart'] = [];
            mysqli_query($conn, "DELETE FROM cart_orders WHERE user_id=$user_id AND status='pending'");
            endJson([]);
        }

        /* CLAIM FIRST VOUCHER */
        if($action == 'claim_first_voucher') {
            $check_user = mysqli_query($conn, "SELECT first_login FROM users WHERE user_id=$user_id");
            $u_data = mysqli_fetch_assoc($check_user);
            
            if($u_data['first_login'] == 1) {
                // Find the reward item
                $ri_q = mysqli_query($conn, "SELECT id FROM reward_items WHERE name = 'First Order Voucher' LIMIT 1");
                $ri = mysqli_fetch_assoc($ri_q);
                
                if($ri) {
                    $ri_id = $ri['id'];
                    $code = "FLAME-WELCOME-" . strtoupper(substr(md5(time() . $user_id), 0, 5));
                    
                    // Award it
                    $ins = "INSERT INTO redemptions (user_id, reward_item_id, points_spent, voucher_code, status) VALUES 
                            ($user_id, $ri_id, 0, '$code', 'pending')";
                    
                    if(mysqli_query($conn, $ins)) {
                        // Mark user as not first login anymore
                        mysqli_query($conn, "UPDATE users SET first_login=0 WHERE user_id=$user_id");
                        endJson(['status'=>'success', 'message'=>'Voucher Claimed!', 'code'=>$code]);
                    }
                }
            }
            errorJson("Voucher already claimed or user not eligible.");
        }

        /* GET VOUCHERS */
        if($action == 'get_vouchers') {
            $v_res = mysqli_query($conn, "
                SELECT r.id, ri.name, r.voucher_code
                FROM redemptions r 
                JOIN reward_items ri ON r.reward_item_id = ri.id 
                WHERE r.user_id = $user_id AND ri.category = 'voucher' AND r.status IN ('completed', 'pending')
                ORDER BY r.redeemed_at DESC
            ");
            $v_out = [];
            while($vr = mysqli_fetch_assoc($v_res)) {
                $code = $vr['voucher_code'];
                if(empty($code)) {
                    $code = "FLAME-" . strtoupper(substr(md5($vr['id'] . "salt"), 0, 8));
                    mysqli_query($conn, "UPDATE redemptions SET voucher_code='$code' WHERE id=" . $vr['id']);
                }
                $v_out[] = ['id' => $vr['id'], 'name' => $vr['name'], 'code' => $code];
            }
            endJson($v_out);
        }

        /* CONFIRM */
        if($action == 'confirm') {
            mysqli_begin_transaction($conn);
            try {
                $q = mysqli_query($conn, "SELECT * FROM cart_orders WHERE user_id=$user_id AND status='pending'");
                $pts = 0;
                $total_qty = 0;
                $subtotal = 0;
                while($row = mysqli_fetch_assoc($q)) {
                    $fname = $row['food_name'];
                    $qty = $row['quantity'];
                    $price = $row['price'];
                    $subtotal += ($price * $qty);
                    $total_qty += $qty;
                    foreach($foods as $c=>$l){
                        foreach($l as $i){
                            if($i['name']==$fname) { $pts += ($i['points'] * $qty); break 2; }
                        }
                    }
                }

                $voucher_code = $_POST['voucher'] ?? '';
                $discount = 0;
                $voucher_id = null;
                
                if(!empty($voucher_code)) {
                    // Bypass check for the Surprise Code
                    if($voucher_code === 'VOUCHER15') {
                        // We will validat qty > 5 later or right here
                        // For now just allow it to pass validation so it doesn't throw error
                        // The actual discount logic is applied below automatically if qty > 5
                    } else {
                        // Validate normal voucher from DB
                        $v_val_res = mysqli_query($conn, "SELECT r.id, ri.name 
                                                          FROM redemptions r 
                                                          JOIN reward_items ri ON r.reward_item_id = ri.id
                                                          WHERE r.user_id=$user_id 
                                                          AND r.voucher_code='" . mysqli_real_escape_string($conn, $voucher_code) . "' 
                                                          AND r.status IN ('completed', 'pending') LIMIT 1");
                        
                        if(mysqli_num_rows($v_val_res) > 0) {
                            $v_data = mysqli_fetch_assoc($v_val_res);
                            $voucher_id = $v_data['id'];
                            
                            // Determine discount
                            if(strpos($v_data['name'], '10') !== false) $discount = 10.00;
                            elseif(strpos($v_data['name'], '5') !== false) $discount = 5.00;
                            elseif(strpos($v_data['name'], 'First') !== false) $discount = 5.00; 
                            else $discount = 5.00; 
                            
                        } else {
                            throw new Exception("Invalid or already used voucher code.");
                        }
                    }
                }

                $final_total = $subtotal - $discount;
                if($final_total < 0) $final_total = 0;

                // Apply automatic surprise discount if > 5 items (stackable)
                $surprise_discount = 0;
                if($total_qty > 5) {
                    $surprise_discount = $final_total * 0.15;
                    $final_total -= $surprise_discount;
                    if(!mysqli_query($conn, "UPDATE cart_orders SET price = price * 0.85 WHERE user_id=$user_id AND status='pending'")) throw new Exception("Surprise Discount Failed");
                }
                
                if(!mysqli_query($conn, "UPDATE cart_orders SET status='confirmed' WHERE user_id=$user_id AND status='pending'")) throw new Exception("Order Update Failed");
                
                // Mark voucher as used
                if($voucher_id) {
                    mysqli_query($conn, "UPDATE redemptions SET status='used' WHERE id=$voucher_id");
                }

                // Enforce 1000 points cap
                $current_user_q = mysqli_query($conn, "SELECT points FROM users WHERE user_id=$user_id");
                $current_user = mysqli_fetch_assoc($current_user_q);
                $new_points = $current_user['points'] + $pts;
                if ($new_points > 1000) $new_points = 1000;
                
                if(!mysqli_query($conn, "UPDATE users SET points = $new_points WHERE user_id=$user_id")) throw new Exception("Points Update Failed");
                
                // Get Final Points for Badge Logic
                $total = $new_points;
                
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
                
                if(!mysqli_query($conn, "UPDATE users SET badge='" . mysqli_real_escape_string($conn, $badge) . "' WHERE user_id=$user_id")) throw new Exception("Badge Error");
                
                // Fetch order count for response
                $order_count_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_orders WHERE user_id=$user_id AND status='confirmed'");
                $order_count = mysqli_fetch_assoc($order_count_res)['c'];

                mysqli_commit($conn);
                $_SESSION['cart'] = [];
                
                endJson([
                    'status'=>'confirmed', 
                    'points'=>$pts, 
                    'new_badge'=>$badge,
                    'is_first_order' => ($order_count == 0),
                    'original_total' => $subtotal,
                    'voucher_discount' => $discount,
                    'surprise_discount' => $surprise_discount,
                    'final_total' => $final_total
                ]);

            } catch(Exception $e) {
                mysqli_rollback($conn);
                throw $e;
            }
        }
    }

} catch(Throwable $e) {
    errorJson("Server Error: " . $e->getMessage());
}

/* --- RENDER HTML --- */
// Init session cart if needed
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Sync simple
if(empty($_SESSION['cart']) && isset($conn) && isset($user_id)) {
    $q = mysqli_query($conn, "SELECT * FROM cart_orders WHERE user_id=$user_id AND status='pending'");
    if($q) {
        while($row=mysqli_fetch_assoc($q)){
            $img = $row['img'] ? $row['img'] : 'assets/img/bimg1.png';
            $_SESSION['cart'][$row['food_name']] = ['quantity'=>$row['quantity'], 'price'=>$row['price'], 'img'=>$img];
        }
    }
}
ob_end_flush();
?>
<!-- Include Shared Header -->
<?php include "includes/header.php"; ?>

<!-- Specific Order Page Styles -->
<link rel="stylesheet" href="assets/css/order.css?v=<?= time() ?>">

<div class="fire-bg"></div>

<div id="success-popup"></div>

<!-- Basket Toggle with counter -->
<button id="basket-toggle" onclick="toggleBasket()">
  <i class="fas fa-basket-shopping"></i> Basket <span class="count"><?= count($_SESSION['cart']??[]) ?></span>
</button>

<!-- Basket Sidebar -->
<div id="basket" class="hidden">
  <div class="basket-header"><h3>Your Basket</h3><span class="close-basket" onclick="toggleBasket()">&times;</span></div>
  <div id="basket-items"></div>
  <div id="basket-footer">
    <div id="basket-total">Total: RM 0.00</div>
    <button id="checkout-btn" onclick="openCheckout()">Checkout</button>
  </div>
</div>

<div class="order-container">
  <section class="hero-section" id="hero">
  <h1>Flavour's Hub</h1>
  <p class="subtitle">Hit the Spices!</p>
  <button class="btn-primary" onclick="document.getElementById('menu').scrollIntoView({behavior:'smooth'})">Check Menu</button>
  </section>

  <section class="menu-section" id="menu">
    <div class="category-buttons main-nav">
      <?php foreach($mainCategories as $cat): $list = $foods[$cat]; ?>
        <button class="category-btn" onclick="showCategory('<?= $cat ?>')" id="btn-<?= $cat ?>">
            <img src="<?= $list[0]['img'] ?>"> <span><?= ucfirst($cat) ?></span>
        </button>
      <?php endforeach; ?>
      <button class="category-btn" onclick="toggleDrinks()" id="btn-drinks"><img src="assets/img/coffe1.png"> <span>Drinks</span></button>
      <button class="category-btn" onclick="toggleDesserts()" id="btn-desserts"><img src="assets/img/cake1.png"> <span>Desserts</span></button>
    </div>

    <div class="category-buttons sub-nav" id="drinks-submenu" style="display:none;">
        <?php foreach($drinkCategories as $cat): ?>
        <button class="category-btn sub-btn" onclick="showCategory('<?= $cat ?>')" id="btn-<?= $cat ?>"><span><?= ucfirst($cat) ?></span></button>
        <?php endforeach; ?>
    </div>
    <div class="category-buttons sub-nav" id="desserts-submenu" style="display:none;">
        <?php foreach($dessertCategories as $cat): ?>
        <button class="category-btn sub-btn" onclick="showCategory('<?= $cat ?>')" id="btn-<?= $cat ?>"><span><?= str_replace('_',' ',ucfirst($cat)) ?></span></button>
        <?php endforeach; ?>
    </div>

    <?php foreach($foods as $cat=>$list): ?>
    <div class="carousel-container category" id="<?= $cat ?>">
      <button class="carousel-buttons left" onclick="move('<?= $cat ?>',-1)"><i class="fas fa-chevron-left"></i></button>
      <div class="carousel-mask"><div class="carousel-track">
          <?php foreach($list as $index=>$f): 
              $isAvailable = $f['available'] ?? true;
          ?>
          <div class="carousel-card <?= $index==0?'active':'' ?> <?= !$isAvailable ? 'out-of-stock' : '' ?>">
            <div class="img-wrapper" style="position:relative;">
                <img src="<?= $f['img'] ?>" style="<?= !$isAvailable ? 'filter: grayscale(1) opacity(0.5);' : '' ?>">
                <?php if(!$isAvailable): ?>
                    <div class="stock-overlay">OUT OF STOCK</div>
                <?php endif; ?>
            </div>
            <h3><?= $f['name'] ?></h3>
            <div class="price">RM <?= number_format($f['price'], 2) ?> • <?= $f['points'] ?> pts</div>
            <button class="add-to-cart" onclick="addToCart('<?= $f['name'] ?>','<?= $cat ?>', '<?= $f['img'] ?>')" <?= !$isAvailable ? 'disabled' : '' ?>>
                <?= $isAvailable ? 'Add to Basket <i class="fas fa-cart-shopping"></i>' : 'UNAVAILABLE <i class="fas fa-ban"></i>' ?>
            </button>
          </div>
          <?php endforeach; ?>
      </div></div>
      <button class="carousel-buttons right" onclick="move('<?= $cat ?>',1)"><i class="fas fa-chevron-right"></i></button>
    </div>
    <?php endforeach; ?>
    </section>
</div>

<!-- Surprise Discount Popup -->
<div id="surprise-popup" class="surprise-popup">
    <h2 class="text-gradient">SURPRISE! 🎁</h2>
    <p>You've added more than 5 items! You've unlocked a secret 15% discount voucher for this order!</p>
    <div class="voucher-code">VOUCHER15</div>
    <br>
    <button class="popup-btn" onclick="closeSurprisePopup()">Claim Now & Checkout</button>
</div>

<div id="checkout-modal" class="modal">
  <div class="modal-content bento-modal">
    <span class="close-modal" onclick="closeCheckout()">&times;</span>
    <h1 class="heat-title" style="font-size: 2.5rem; margin-bottom: 30px; text-align: center;">FINAL <span class="text-gradient">CHECKOUT</span></h1>
    
    <div class="checkout-bento-grid">
      <!-- Order Items Column -->
      <div class="bento-box summary-box">
        <h3><i class="fas fa-receipt"></i> Order Summary</h3>
        <div id="checkout-items"></div>
      </div>

      <!-- Voucher Column -->
      <div class="bento-box voucher-box">
        <h3><i class="fas fa-ticket"></i> Select Voucher</h3>
        <p style="font-size: 0.8rem; opacity: 0.7; margin-bottom: 15px;">Choose from your claimed rewards.</p>
        
        <div id="voucher-selector-container">
          <select id="user-voucher-select" onchange="applyVoucherDropdown()" class="voucher-select">
            <option value="">-- Choose a Voucher --</option>
            <!-- Options populated by JS -->
          </select>
        </div>

        <div class="promo-manual" style="margin-top: 20px;">
          <p style="font-size: 0.8rem; margin-bottom: 10px;">Or enter manually:</p>
          <div style="display:flex; gap:10px;">
            <input type="text" id="voucher-code" placeholder="CODE" class="promo-input">
            <button onclick="applyVoucherUI()" class="apply-btn">Apply</button>
          </div>
        </div>
        <div id="voucher-msg"></div>
      </div>

      <!-- Payment & Total Column -->
      <div class="bento-box payment-box">
        <h3><i class="fas fa-credit-card"></i> Payment</h3>
        <div class="payment-options mini">
          <label class="payment-option selected" onclick="selectPayment(this)"><i class="fas fa-credit-card"></i> Card</label>
          <label class="payment-option" onclick="selectPayment(this)"><i class="fas fa-qrcode"></i> QR</label>
        </div>

        <div class="checkout-totals-pro">
          <div class="total-line">
            <span>Subtotal</span>
            <span id="checkout-subtotal">RM 0.00</span>
          </div>
          <div class="total-line discount" id="checkout-discount-row" style="display:none;">
            <span>Voucher Discount</span>
            <span id="checkout-discount-val">-RM 0.00</span>
          </div>
          <div class="total-line grand">
            <span>TOTAL</span>
            <span id="checkout-total-amount">RM 0.00</span>
          </div>
        </div>

        <button class="pay-btn-pro" onclick="processPayment()">
          CONFIRM ORDER <i class="fas fa-fire"></i>
        </button>
        <button class="cancel-btn" onclick="closeCheckout()">
          Cancel Order
        </button>
      </div>
    </div>
  </div>
</div>

<style>
    .carousel-card.out-of-stock {
        cursor: not-allowed;
    }
    .carousel-card.out-of-stock button.add-to-cart {
        background: #333 !important;
        color: #666;
        cursor: not-allowed;
    }
    .stock-overlay {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
        background: rgba(255, 0, 0, 0.8);
        color: white;
        padding: 5px 15px;
        font-weight: 950;
        font-size: 0.8rem;
        border: 2px solid white;
        pointer-events: none;
        z-index: 10;
        white-space: nowrap;
        border-radius: 5px;
    }
</style>
<script src="script.js?v=<?= time() ?>"></script>
<?php include "includes/footer.php"; ?>
