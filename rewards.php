<?php
include "includes/header.php";

$user_id = $_SESSION['user_id'];
$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT points, badge FROM users WHERE user_id=$user_id"));
$pts = (int)$u['points'];

// Tiers Definition
$tiers = [
    ["name"=>"Newbie", "min"=>0],
    ["name"=>"Rookie Eater", "min"=>50],
    ["name"=>"Snack Starter", "min"=>100],
    ["name"=>"Appetizer Apprentice", "min"=>200],
    ["name"=>"Meal Enthusiast", "min"=>300],
    ["name"=>"Foodie", "min"=>400],
    ["name"=>"Flavor Explorer", "min"=>500],
    ["name"=>"Taste Tester", "min"=>600],
    ["name"=>"Cuisine Cadet", "min"=>700],
    ["name"=>"Dish Discoverer", "min"=>800],
    ["name"=>"Connoisseur", "min"=>900],
    ["name"=>"Food God", "min"=>1000]
];

// Handle Redemption
$message = "";
if (isset($_POST['redeem_id'])) {
    $rid = (int)$_POST['redeem_id'];
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM reward_items WHERE id=$rid"));
    
    if ($item && $pts >= $item['points_cost'] && $item['stock'] > 0) {
        $cost = $item['points_cost'];
        $item_name = mysqli_real_escape_string($conn, $item['name']);

        // --- ENFORCE CLAIM LIMITS ---
        $limits = ['RM10' => 2, 'RM5' => 3, 'RM3' => 5, 'First' => 1];
        $is_valid = true;
        
        foreach($limits as $key => $limit) {
            if (stripos($item['name'], $key) !== false) {
                $cc_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM redemptions WHERE user_id=$user_id AND reward_item_id=$rid");
                $claimed_count = mysqli_fetch_assoc($cc_q)['c'];
                if ($claimed_count >= $limit) {
                    $is_valid = false;
                    $message = "error|You have reached the claim limit for this voucher ($limit/$limit).";
                }
                break;
            }
        }
        
        if ($is_valid) {
            mysqli_begin_transaction($conn);
            $q1 = mysqli_query($conn, "UPDATE users SET points = points - $cost WHERE user_id=$user_id");
            $q2 = mysqli_query($conn, "UPDATE reward_items SET stock = stock - 1 WHERE id=$rid");
            // Generate Voucher Code if it's a voucher
            $v_code = '';
            if($item['category'] == 'voucher') {
                $v_code = "FLAME-" . strtoupper(substr(md5(time() . $user_id . $rid), 0, 8));
            }
            $q3 = mysqli_query($conn, "INSERT INTO redemptions (user_id, reward_item_id, points_spent, voucher_code, status) VALUES ($user_id, $rid, $cost, '$v_code', 'pending')");
            
            if ($q1 && $q2 && $q3) {
                mysqli_commit($conn);
                $pts -= $cost;
                $message = "success|Successfully redeemed " . $item['name'] . "! Code: " . ($v_code ?: "Check Profile");
            } else {
                mysqli_rollback($conn);
                $message = "error|Something went wrong. Please try again.";
            }
        }
    } else {
        if($message == "") $message = "error|Insufficient points or item out of stock.";
    }
}

// Calculate Progress
$currentTier = $tiers[0];
$nextTier = null;
foreach($tiers as $i=>$t) {
    if($pts >= $t['min']) $currentTier = $t;
    else { $nextTier = $t; break; }
}
$percent = 100;
if($nextTier) {
    $range = $nextTier['min'] - $currentTier['min'];
    $gained = $pts - $currentTier['min'];
    $percent = min(100, max(0, ($gained / $range) * 100));
}

$reward_items = mysqli_query($conn, "SELECT * FROM reward_items WHERE stock > 0");
?>

<div class="rewards-wrapper page-fade">
    <?php if ($message): 
        list($type, $msg) = explode('|', $message);
    ?>
    <div class="alert <?= $type ?> animate-slide-down">
        <i class="fas <?= $type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
        <?= $msg ?>
    </div>
    <?php endif; ?>
<div class="fire-bg"></div>
<main>
    <section class="rewards-hero">
        <div class="hero-text">
            <h1 class="text-gradient">Rewards Hub</h1>
            <p>Exchange your hard-earned points for exclusive Flavour's Hub merch!</p>
        </div>
        
        <div class="flame-card status-card">
            <div class="status-info">
                <div class="tier-info">
                    <span class="label">Current Level</span>
                    <h3 class="text-gradient"><?= $currentTier['name'] ?></h3>
                </div>
                <div class="points-info">
                    <span class="label">Available Points</span>
                    <h2 class="pts-val heat-title"><?= number_format($pts) ?> Pts</h2>
                </div>
            </div>
            
            <div class="progress-section">
                <?php if($nextTier): ?>
                <div class="progress-labels">
                    <span><?= $currentTier['name'] ?></span>
                    <span><?= $nextTier['name'] ?> (<?= number_format($nextTier['min']) ?>)</span>
                </div>
                <div class="progress-bar-main" style="background: rgba(255,255,255,0.05); height: 12px; border-radius: 6px; overflow: hidden; margin: 10px 0;">
                    <div class="fill" style="width: <?= $percent ?>%; height: 100%; background: var(--flame-grad); box-shadow: 0 0 15px var(--primary);"></div>
                </div>
                <p class="next-hint">You need <strong><?= number_format($nextTier['min'] - $pts) ?></strong> more points to level up!</p>
                <?php else: ?>
                <p class="max-level text-gradient" style="font-weight: 900;">MAX LEVEL REACHED! You're a true Food God.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="shop-section">
        <h2 class="section-title">Rewards Shop</h2>
        <div class="items-grid">
            <?php while($item = mysqli_fetch_assoc($reward_items)): ?>
            <div class="flame-card item-card">
                <div class="item-img" style="background-image: url('<?= $item['image_url'] ?>')">
                    <span class="category-tag"><?= strtoupper($item['category']) ?></span>
                </div>
                <div class="item-body">
                    <h3 class="text-gradient"><?= $item['name'] ?></h3>
                    <p><?= $item['description'] ?></p>
                    <div class="price-row">
                        <span class="cost"><i class="fas fa-star"></i> <?= number_format($item['points_cost']) ?> pts</span>
                        <span class="stock"><?= $item['stock'] ?> in stock</span>
                    </div>
                    <?php 
                    // Calculate Claim Status for Buttons
                    $can_claim = true;
                    $claim_msg = "Redeem Now";
                    $btn_class = "btn-lava";
                    
                    if ($pts < $item['points_cost']) {
                        $can_claim = false;
                        $claim_msg = "Not Enough Points";
                        $btn_class = "btn-lava disabled";
                    }

                    // Check Claim Limits
                    $limits = [
                        'RM10' => 2,
                        'RM5' => 3, 
                        'RM3' => 5,
                        'First' => 1
                    ];
                    
                    $claimed_count = 0;
                    // Count previous redemptions of this item by user
                    $cc_q = mysqli_query($conn, "SELECT COUNT(*) as c FROM redemptions WHERE user_id=$user_id AND reward_item_id=" . $item['id']);
                    $claimed_count = mysqli_fetch_assoc($cc_q)['c'];

                    foreach($limits as $key => $limit) {
                        if (stripos($item['name'], $key) !== false) {
                            if ($claimed_count >= $limit) {
                                $can_claim = false;
                                $claim_msg = "Claim Limit Reached ($limit/$limit)";
                                $btn_class = "btn-lava disabled";
                            } else {
                                // Optional: Show remaining claims? 
                                // $claim_msg .= " (" . ($claimed_count) . "/$limit)";
                            }
                            break;
                        }
                    }
                    ?>

                    <?php if ($can_claim): ?>
                        <form method="POST">
                            <input type="hidden" name="redeem_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="<?= $btn_class ?> redeem-btn" style="width: 100%; justify-content: center; padding: 12px;"><?= $claim_msg ?></button>
                        </form>
                    <?php else: ?>
                        <button class="<?= $btn_class ?>" disabled style="width: 100%; justify-content: center; padding: 12px; background: #333; box-shadow: none; opacity: 0.5; cursor: not-allowed;"><?= $claim_msg ?></button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="badges-section">
        <h2 class="section-title">Badge Collection</h2>
        <div class="badge-grid">
            <?php foreach($tiers as $t): 
                $unlocked = $pts >= $t['min'];
            ?>
            <div class="badge-item <?= $unlocked ? 'unlocked' : 'locked' ?>">
                <div class="badge-icon">
                    <i class="fas <?= $unlocked ? 'fa-medal' : 'fa-lock' ?>"></i>
                </div>
                <h4><?= $t['name'] ?></h4>
                <div class="pts-req"><?= number_format($t['min']) ?> Pts</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.rewards-wrapper { padding: 40px 5%; padding-bottom: 50px; max-width: 1400px; margin: 0 auto; }
.rewards-hero { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 60px; align-items: center; }

@media (max-width: 900px) {
    .rewards-hero { grid-template-columns: 1fr; text-align: center; }
}

.hero-text h1 { font-size: 3.5rem; margin-bottom: 20px; }
.hero-text p { font-size: 1.2rem; color: var(--text-muted); line-height: 1.6; }

.status-card { background: linear-gradient(135deg, rgba(255, 78, 0, 0.2), var(--secondary)); border: 2px solid var(--primary); box-shadow: 0 0 30px var(--primary-glow); }
.status-info { display: flex; justify-content: space-between; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; }
.label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 700; }
.pts-val { color: var(--primary); margin: 0; }
.tier-info h3 { margin: 5px 0 0 0; font-weight: 800; }

.progress-labels { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 10px; font-weight: 600; }
.progress-bar-main { height: 12px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; }
.progress-bar-main .fill { height: 100%; background: linear-gradient(90deg, var(--primary), #ff9f43); box-shadow: 0 0 15px var(--primary-glow); }
.next-hint { font-size: 0.9rem; margin-top: 15px; color: var(--text-muted); }

.section-title { margin: 60px 0 30px 0; font-size: 2rem; font-weight: 800; }

/* Items Grid */
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
.item-card { padding: 0; overflow: hidden; flex-direction: column; }
.item-img { height: 220px; background-size: cover; background-position: center; position: relative; }
.category-tag { position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.6); padding: 5px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; backdrop-filter: blur(5px); }
.item-body { padding: 25px; }
.item-body h3 { margin-bottom: 10px; }
.item-body p { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; height: 3em; overflow: hidden; }
.price-row { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; border-top: 1px solid var(--glass-border); padding-top: 15px; }
.cost { font-weight: 800; color: var(--primary); font-size: 1.1rem; }
.stock { font-size: 0.8rem; color: #777; }
.redeem-btn { width: 100%; justify-content: center; }
.btn-premium.disabled { background: #333; cursor: not-allowed; box-shadow: none; opacity: 0.5; }

/* Badge Grid */
.badge-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 20px; }
.badge-item { background: var(--card); border: 1px solid var(--glass-border); border-radius: 20px; padding: 20px; text-align: center; transition: 0.4s; }
.badge-item.locked { opacity: 0.3; filter: grayscale(1); }
.badge-item.unlocked { border-color: var(--primary); background: rgba(255, 107, 53, 0.05); }
.badge-item:hover.unlocked { transform: translateY(-5px) scale(1.05); }
.badge-icon { font-size: 2rem; margin-bottom: 10px; color: var(--primary); }
.badge-item h4 { font-size: 0.9rem; margin: 0; }
.pts-req { font-size: 0.75rem; color: var(--text-muted); margin-top: 5px; }

/* Alerts */
.alert { padding: 15px 25px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px; font-weight: 600; }
.alert.success { background: rgba(0, 200, 83, 0.1); border: 1px solid var(--success); color: #00c853; }
.alert.error { background: rgba(255, 68, 68, 0.1); border: 1px solid #ff4444; color: #ff4444; }

.animate-slide-down { animation: slideDown 0.5s ease-out; }
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<?php include "includes/footer.php"; ?>
