<?php
include "includes/header.php";

// Fetch user data
$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE user_id=$user_id"));
$user_points = (int)$user['points'];

// Recent Orders
$history = mysqli_query($conn, "SELECT * FROM cart_orders WHERE user_id=$user_id AND status='confirmed' ORDER BY created_at DESC LIMIT 4");

// Stats
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_orders WHERE user_id=$user_id AND status='confirmed'"))['c'];
$rank_res = mysqli_query($conn, "SELECT user_id FROM users ORDER BY points DESC");
$rank = 0;
while($row = mysqli_fetch_assoc($rank_res)) {
    $rank++;
    if($row['user_id'] == $user_id) break;
}

// Calculate Tier Progress & Flame Color
$badge_tiers = [
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

$flame_tiers = [
    ["limit" => 1000, "color" => "cosmic"],
    ["limit" => 700,  "color" => "purple"],
    ["limit" => 400,  "color" => "blue"],
    ["limit" => 100,  "color" => "green"],
    ["limit" => 0,    "color" => "red"]
];

$flame_type = "red";
foreach($flame_tiers as $ft) {
    if($user_points >= $ft['limit']) { $flame_type = $ft['color']; break; }
}

// FORCE COSMIC THEME FOR RANK #1
if ($rank == 1) {
    $flame_type = "cosmic";
}
// Any other rank uses the standard Point-Based Flame Tier determined above ($flame_type)
?>
<?php

$next_milestone = "Max Level";
$prev_limit = 0;
foreach($badge_tiers as $bt) {
    if($user_points < $bt['min']) {
        $next_milestone = $bt['min'];
        break;
    }
    $prev_limit = $bt['min'];
}

// Progress Bar Math
$percent = 100;
if(is_numeric($next_milestone)) {
    $range = $next_milestone - $prev_limit;
    if($range > 0) {
        $gained = $user_points - $prev_limit;
        $percent = min(100, max(0, ($gained / $range) * 100));
    }
}
?>

<div class="fire-bg"></div>
<?php if($flame_type == 'cosmic'): ?>
    <div class="cosmic-galaxy-bg"></div>
    <link rel="stylesheet" href="assets/css/cosmic.css?v=<?= time() ?>">
    <style>
        /* DASHBOARD COSMIC THEME OVERRIDES */
        /* Global Card Override */
        .glass-card, .stats-main.flame-cosmic { 
            background: rgba(8, 5, 12, 0.7) !important; 
            border: 1px solid rgba(213, 0, 249, 0.3) !important;
            box-shadow: 0 0 30px rgba(156, 39, 176, 0.15) !important;
        }

        /* Stats Main Box - PREMIUM VOID COSMIC */
        .stats-main.flame-cosmic {
            background: radial-gradient(circle at top right, #1a1a2e, #000000 80%) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 0 80px rgba(100, 0, 200, 0.2), inset 0 0 100px rgba(0,0,0,0.9) !important;
            position: relative;
            overflow: hidden !important;
            animation: deepBreath 6s infinite alternate ease-in-out;
        }
        
        @keyframes deepBreath {
            0% { box-shadow: 0 0 60px rgba(100, 0, 200, 0.2), inset 0 0 50px rgba(0,0,0,0.8); border-color: rgba(255,255,255,0.1); }
            100% { box-shadow: 0 0 100px rgba(130, 50, 250, 0.3), inset 0 0 30px rgba(0,0,0,0.6); border-color: rgba(130, 50, 250, 0.5); }
        }

        /* Ethereal Mist (Replacing Plasma) */
        .stats-main.flame-cosmic::before {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 100%;
            background: radial-gradient(circle at 50% 120%, rgba(120, 50, 255, 0.15), transparent 60%);
            filter: blur(40px);
            z-index: 0;
            animation: mistPulse 8s infinite alternate;
        }
        @keyframes mistPulse { 0% { opacity: 0.3; } 100% { opacity: 0.6; } }

        /* Elegant Text - Platinum & Violet */
        .stats-main.flame-cosmic .points-display {
            background: linear-gradient(135deg, #ffffff, #e0e0e0, #b39ddb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 40px rgba(255, 255, 255, 0.3);
            filter: drop-shadow(0 0 10px rgba(130, 50, 250, 0.5));
        }
        
        .stats-main.flame-cosmic .label {
            color: #9fa8da !important;
            text-shadow: none;
            letter-spacing: 4px;
            font-size: 0.8rem;
        }

        /* Noble Gold Rank */
        .rank-tag-gold {
            background: linear-gradient(135deg, #ffd700, #ffb74d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.4));
            animation: none; /* Removed flow for stability */
        }
        
        /* Refined Ring - Electric Violet */
        .stats-main.flame-cosmic .ring-progress {
            stroke: #b388ff !important;
            filter: drop-shadow(0 0 15px rgba(179, 136, 255, 0.6));
            transition: stroke 0.5s;
        }
        
        .stats-main.flame-cosmic::after { display: none; }

        /* Action Cards & Bento Items */
        .bento-item, .action-card {
            background: linear-gradient(145deg, rgba(20, 10, 30, 0.8), rgba(0,0,0,0.9)) !important;
        }
        .bento-item:hover, .action-card:hover {
            border-color: #d500f9 !important;
            box-shadow: 0 0 40px rgba(213, 0, 249, 0.3) !important;
        }
        
        /* Icons */
        .icon-circle {
            background: rgba(213, 0, 249, 0.1) !important;
            border-color: rgba(213, 0, 249, 0.3) !important;
            color: #e1bee7 !important;
        }

        /* Rank 1 Animation */
        .bounce-crown {
            animation: bounceCrown 2s infinite ease-in-out;
        }
        @keyframes bounceCrown {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px) scale(1.2); }
        }
    </style>
<?php endif; ?>

<div class="dashboard-container">
    <div class="dashboard-wrapper page-fade">
        <div class="welcome-header">
            <h1 class="text-gradient">Welcome back, <?= explode(' ', $user['username'])[0] ?>!</h1>
            <p>Your culinary journey continues. Ready to spice things up?</p>
        </div>

        <div class="glass-card stats-main flame-<?= $flame_type ?>">
            <div class="stats-content">
                <div class="stats-left">
                    <span class="label">Total Points</span>
                    <h2 class="points-display"><?= number_format($user_points) ?></h2>
                    
                    <div class="badge-row" style="<?= $rank == 1 ? 'flex-direction: column; align-items: flex-start; gap: 10px;' : '' ?>">
                        <?php 
                        $badge_class = "";
                        if($user['badge'] == "Food God") $badge_class = "badge-god";
                        elseif(strpos($user['badge'], "Connoisseur") !== false) $badge_class = "badge-elite";
                        ?>
                        <span class="badge-tag <?= $badge_class ?>"><i class="fas fa-medal"></i> <?= $user['badge'] ?></span>
                        
                        <?php if($rank == 1): ?>
                        <div class="rank-tag-gold" style="font-size: 1.5rem; color: #ffd700; font-weight: 900; text-shadow: 0 0 20px rgba(255, 215, 0, 0.6); display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                            <i class="fas fa-crown bounce-crown"></i> RANK #1 <i class="fas fa-crown bounce-crown"></i>
                        </div>
                        <?php else: ?>
                        <span class="rank-tag">Rank #<?= $rank ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stats-right">
                    <div class="progress-ring-container">
                        <!-- Cosmic Ring Fire -->
                        <?php if($flame_type == 'cosmic'): ?>
                            <div class="cosmic-ring-flame"></div>
                        <?php endif; ?>
                        
                        <svg class="ring-svg">
                            <circle class="ring-bg" cx="110" cy="110" r="100"></circle>
                            <circle class="ring-progress" cx="110" cy="110" r="100" style="stroke-dashoffset: calc(630 - (630 * <?= $percent ?> / 100))"></circle>
                        </svg>
                        <div class="big-percent"><?= number_format($percent) ?>%</div>
                        <div class="next-lvl">NEXT: <?= is_numeric($next_milestone) ? number_format($next_milestone) : "MAX" ?></div>
                    </div>
                </div>
            </div>
            <i class="fas fa-fire-alt fire-bg-icon"></i>
        </div>

        <div class="bento-grid">
            <a href="order.php" class="glass-card bento-item action-card order-now">
                <div class="icon-circle"><i class="fas fa-utensils"></i></div>
                <h3>Order Food</h3>
                <p>Browse Menu</p>
                <i class="fas fa-arrow-right arrow"></i>
            </a>

            <a href="rewards.php" class="glass-card bento-item action-card rewards">
                <div class="icon-circle"><i class="fas fa-gift"></i></div>
                <h3>Redeem</h3>
                <p>Vouchers</p>
                <i class="fas fa-arrow-right arrow"></i>
            </a>

            <!-- Row 2: Secondary Stats & Links -->
            <div class="glass-card bento-item mini-stats">
                <div class="mini-info">
                    <span class="mini-label">Recent Orders</span>
                    <span class="mini-value"><?= $total_orders ?></span>
                </div>
                <div class="mini-icon"><i class="fas fa-shopping-bag"></i></div>
            </div>

        </div>

        <!-- Row 3: Recent Activity (Detached for Max Spacing) -->
        <div class="glass-card recent-activity">
            <div class="section-header">
                <h3>Recent Bites</h3>
            </div>
            <div class="activity-list horizontal-scroll">
                <?php if(mysqli_num_rows($history) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($history)): ?>
                    <div class="activity-card">
                        <img src="<?= $row['img'] ?: 'assets/img/bimg1.png' ?>" alt="">
                        <div class="act-details">
                            <span class="act-name"><?= $row['food_name'] ?></span>
                            <span class="act-time"><?= date('M d, H:i', strtotime($row['created_at'])) ?></span>
                        </div>
                        <div class="status-dot"></div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state-wide">
                        <p>No orders yet! Start your journey.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Distanced Secondary Row -->
        <div class="secondary-actions">
            <a href="leaderboard.php" class="glass-card action-card small-action">
                <i class="fas fa-trophy highlight"></i>
                <span>Leaderboard</span>
                <i class="fas fa-chevron-right mini-arrow"></i>
            </a>
            
             <a href="contact.php" class="glass-card action-card small-action contact-card">
                <i class="fas fa-envelope highlight-blue"></i>
                <span>Contact Us</span>
                <i class="fas fa-chevron-right mini-arrow"></i>
            </a>
        </div>
    </div>
</div>

<style>
/* Base Layout */
.dashboard-container { width: 100%; min-height: calc(100vh - 80px); padding: 50px; box-sizing: border-box; }
.dashboard-wrapper { max-width: 1400px; margin: 0 auto; position: relative; z-index: 10; }
.welcome-header { margin-bottom: 60px; text-align: left; }
.welcome-header h1 { font-size: 4rem; margin-bottom: 10px; font-weight: 900; letter-spacing: -2px; line-height: 1; }
.welcome-header p { color: var(--text-muted); font-size: 1.3rem; font-weight: 500; }

/* Grid System */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: repeat(2, 220px);
    gap: 30px;
}

/* Item Placement */
.stats-main   { 
    grid-column: span 3; 
    grid-row: span 1; 
    position: relative; 
    overflow: hidden; 
    border-radius: 35px !important;
    border: 1px solid rgba(255,255,255,0.08);
}

.mini-stats { grid-column: span 1; grid-row: span 1; }

.action-card { 
    grid-column: span 1; 
    grid-row: span 1; 
}

.action-card.order-now { grid-column: span 1; }
.action-card.rewards { grid-column: span 1; }
.recent-activity { grid-column: span 2; grid-row: span 1; }

/* Responsive */
@media (max-width: 1200px) {
    .bento-grid { grid-template-columns: repeat(2, 1fr); grid-template-rows: auto; }
    .stats-main { grid-column: span 2; }
    .recent-activity { grid-column: span 2; }
}
@media (max-width: 800px) {
    .bento-grid { grid-template-columns: 1fr; }
    .stats-main, .recent-activity { grid-column: span 1; }
}

/* Main Stats */
.stats-content { 
    padding: 60px; z-index: 2; position: relative; height: 100%; 
    display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 40px; box-sizing: border-box;
}

.stats-left { z-index: 2; text-align: left; }
.label { text-transform: uppercase; letter-spacing: 4px; font-size: 0.9rem; color: var(--flame-color); font-weight: 800; opacity: 0.9; margin-bottom: 5px; display: block; text-shadow: 0 0 10px var(--flame-glow); }

/* CRAZY NUMBER ANIMATION */
.points-display { 
    font-size: 6rem; font-weight: 900; color: #fff; margin: 0; line-height: 0.9; 
    letter-spacing: -3px; 
    background: linear-gradient(180deg, #fff 20%, var(--flame-color) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 0 20px var(--flame-glow));
    animation: pump 1.5s infinite ease-in-out;
}
@keyframes pump { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }

/* RING CONTAINER */
.stats-right { 
    display: flex; justify-content: center; align-items: center; position: relative; height: 250px; 
}

/* CRAZY BURNING RING */
.progress-ring-container {
    position: relative; width: 220px; height: 220px;
    display: flex; align-items: center; justify-content: center;
}
.ring-svg { transform: rotate(-90deg); width: 100%; height: 100%; overflow: visible; }
.ring-bg { fill: none; stroke: rgba(255,255,255,0.05); stroke-width: 15; }
.ring-progress { 
    fill: none; stroke: var(--flame-color); stroke-width: 15; stroke-linecap: round; 
    stroke-dasharray: 630; stroke-dashoffset: 630; /* 2*pi*r, r=100 -> ~628 */
    filter: drop-shadow(0 0 20px var(--flame-color));
    transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* BURNING FLAME PARTICLES ON RING (Simulated with pseudo) */
.progress-ring-container::after {
    content: ''; position: absolute; inset: -20px;
    background: conic-gradient(from 0deg, transparent 0%, var(--flame-color) 10%, transparent 20%);
    border-radius: 50%; opacity: 0.5;
    animation: spinFire 2s linear infinite;
    mix-blend-mode: screen;
    pointer-events: none;
    mask: radial-gradient(transparent 65%, black 66%);
}
@keyframes spinFire { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* BIG PERCENT TEXT */
.big-percent {
    position: absolute; font-size: 3.5rem; font-weight: 900; color: white;
    text-shadow: 0 0 30px var(--flame-color);
    z-index: 10;
}
.next-lvl {
    position: absolute; bottom: -40px; width: 100%; text-align: center;
    font-size: 0.9rem; color: #aaa; font-weight: 700; letter-spacing: 1px;
}

/* Flame Themes */
.flame-red    { --flame-color: #ff4e00; --flame-grad: linear-gradient(135deg, #ff4e00, #ff0055); --flame-glow: rgba(255, 78, 0, 0.6); }
.flame-green  { --flame-color: #00e676; --flame-grad: linear-gradient(135deg, #00e676, #00c853); --flame-glow: rgba(0, 230, 118, 0.6); }
.flame-blue   { --flame-color: #2979ff; --flame-grad: linear-gradient(135deg, #2979ff, #2962ff); --flame-glow: rgba(41, 121, 255, 0.6); }
.flame-purple { --flame-color: #d500f9; --flame-grad: linear-gradient(135deg, #d500f9, #aa00ff); --flame-glow: rgba(213, 0, 249, 0.6); }
.flame-cosmic { --flame-color: #ffffff; --flame-grad: linear-gradient(135deg, #ffffff, #aaaaaa); --flame-glow: rgba(255, 255, 255, 0.8); background: linear-gradient(135deg, #111, #000); }

/* Animation Overlay */
/* Animation Overlay */
.stats-main::before {
    content: ''; position: absolute; inset: 0; pointer-events: none;
    background: radial-gradient(circle at top right, var(--flame-glow), transparent 70%);
    opacity: 0.6; z-index: 0;
    animation: glowPulse 4s infinite alternate;
}
@keyframes glowPulse { 0% { opacity: 0.4; transform: scale(1); } 100% { opacity: 0.7; transform: scale(1.05); } }

/* Text Shadows & Glows */
.points-display { 
    text-shadow: 0 0 40px var(--flame-glow);
}
.label {
    text-shadow: 0 0 15px var(--flame-glow);
}

/* Fire Pulse for Icon */
.fire-bg-icon {
    animation: firePulse 5s infinite ease-in-out;
}
@keyframes firePulse { 0% { opacity: 0.1; transform: rotate(-15deg) scale(1); } 50% { opacity: 0.2; transform: rotate(-10deg) scale(1.1); } 100% { opacity: 0.1; transform: rotate(-15deg) scale(1); } }

.xp-container { display: none; } /* Hide old bar */


.badge-row { display: flex; gap: 10px; margin-top: 15px; }
.badge-tag, .rank-tag { background: rgba(0,0,0,0.4); padding: 8px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(5px); }
.xp-text { display: flex; justify-content: space-between; font-size: 0.9rem; color: #aaa; font-weight: 600; }

/* Fire Icon - Contained Watermark */
.fire-bg-icon {
    position: absolute; right: -20px; bottom: -40px; font-size: 15rem; 
    color: var(--flame-color); opacity: 0.1; transform: rotate(-15deg);
    z-index: 1; pointer-events: none; filter: blur(2px);
}

/* Base Card Style */
.glass-card {
    background: rgba(20, 20, 20, 0.6);
    backdrop-filter: blur(20px);
    border-radius: 35px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow: hidden;
}
.glass-card:hover { transform: translateY(-5px); border-color: rgba(255, 255, 255, 0.15); box-shadow: 0 20px 40px rgba(0,0,0,0.5); }

/* Action Cards */
.action-card { padding: 35px; display: flex; flex-direction: column; justify-content: space-between; text-decoration: none; color: white; position: relative; }
.action-card h3 { font-size: 1.8rem; margin: 0 0 5px 0; font-weight: 800; }
.action-card p { color: #888; font-size: 1rem; margin: 0; font-weight: 500; }
.icon-circle { width: 70px; height: 70px; background: rgba(255,255,255,0.03); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--primary); margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.05); }
.arrow { color: var(--primary); font-size: 1.5rem; align-self: flex-end; opacity: 0; transform: translateX(-20px); transition: 0.4s; position: absolute; bottom: 35px; right: 35px; }
.action-card:hover .arrow { opacity: 1; transform: translateX(0); }

/* Mini Stats */
.mini-stats { 
    padding: 35px; display: flex; flex-direction: column; justify-content: center; position: relative;
    background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(0,0,0,0.2));
}
.mini-value { font-size: 4rem; font-weight: 900; line-height: 1; color: #fff; letter-spacing: -2px; z-index: 2; }
.mini-label { text-transform: uppercase; font-size: 0.75rem; color: #666; letter-spacing: 2px; font-weight: 800; margin-bottom: 5px; z-index: 2; }
.mini-icon { 
    position: absolute; right: 20px; bottom: 20px; font-size: 5rem; 
    color: white; opacity: 0.03; transform: rotate(15deg); z-index: 1;
}

/* Recent Activity */
.recent-activity { padding: 35px; display: flex; flex-direction: column; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.section-header h3 { margin: 0; font-size: 1.5rem; font-weight: 800; }
.section-header a { color: var(--primary); text-decoration: none; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }

.activity-list { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 10px; }
.activity-card {
    min-width: 280px;
    background: rgba(255,255,255,0.02);
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: 0.3s;
    border: 1px solid rgba(255,255,255,0.03);
}
.activity-card:hover { background: rgba(255,255,255,0.05); }
.activity-card img { width: 60px; height: 60px; border-radius: 15px; object-fit: cover; }
.act-name { font-weight: 800; font-size: 1.1rem; color: #eee; display: block; }
.act-time { font-size: 0.8rem; color: #666; margin-top: 3px; display: block; }
.status-dot { width: 10px; height: 10px; background: #00e676; border-radius: 50%; margin-left: auto; box-shadow: 0 0 10px #00e676; }

/* Secondary Actions */
.secondary-actions { grid-column: span 3; display: flex; gap: 30px; margin-top: 50px; }
.small-action { flex: 1; display: flex; align-items: center; padding: 25px 40px; gap: 20px; text-decoration: none; color: white; min-height: auto; border-radius: 25px; }
.small-action i.highlight { font-size: 2rem; color: #ffd700; width: auto; height: auto; background: none; border: none; }
.small-action i.highlight-blue { font-size: 2rem; color: #2979ff; width: auto; height: auto; background: none; border: none; }
.small-action span { font-weight: 800; font-size: 1.2rem; }
.mini-arrow { margin-left: auto; opacity: 0.3; }

/* Premium Exclusive Card Enhancements */
.order-now { border-left: 4px solid #ff6b35; background: linear-gradient(135deg, rgba(255, 107, 53, 0.05), rgba(0,0,0,0.6)); }
.rewards { border-left: 4px solid #d500f9; background: linear-gradient(135deg, rgba(213, 0, 249, 0.05), rgba(0,0,0,0.6)); }

.order-now:hover, .rewards:hover { box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
.order-now:hover { border-color: #ff6b35; }
.rewards:hover { border-color: #d500f9; }

.order-now .icon-circle { color: #ff6b35; border-color: rgba(255, 107, 53, 0.3); background: rgba(255, 107, 53, 0.05); }
.rewards .icon-circle { color: #d500f9; border-color: rgba(213, 0, 249, 0.3); background: rgba(213, 0, 249, 0.05); }
</style>

<!-- First Time Voucher Popup (Hidden by default) -->
<div id="welcome-popup" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.5s;">
    <div class="glass-card" style="width: 100%; max-width: 500px; text-align: center; padding: 40px; border: 1px solid var(--primary); box-shadow: 0 0 50px rgba(255, 78, 0, 0.3); transform: scale(0.9); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <i class="fas fa-gift" style="font-size: 4rem; color: #ffd700; margin-bottom: 20px; animation: wobble 2s infinite;"></i>
        <h2 class="heat-title" style="font-size: 2.5rem; margin-bottom: 10px;">WELCOME, <span class="text-gradient">CHEF!</span></h2>
        <p style="color: #ccc; font-size: 1.1rem; margin-bottom: 30px;">
            To kickstart your fiery journey, here is an exclusive <strong style="color:white;">RM 5.00 VOUCHER</strong> for your first order!
        </p>
        
        <div class="voucher-ticket" style="background: white; color: black; padding: 15px; border-radius: 8px; font-weight: 800; font-size: 1.5rem; letter-spacing: 2px; margin-bottom: 30px; border: 2px dashed #333; position: relative;">
            FLAME-WELCOME
            <div style="position: absolute; left: -10px; top: 50%; width: 20px; height: 20px; background: #1a1a1a; border-radius: 50%; transform: translateY(-50%);"></div>
            <div style="position: absolute; right: -10px; top: 50%; width: 20px; height: 20px; background: #1a1a1a; border-radius: 50%; transform: translateY(-50%);"></div>
        </div>

        <button onclick="claimWelcomeVoucher()" class="btn-lava" style="width: 100%; justify-content: center; font-size: 1.2rem; padding: 18px;">
            CLAIM REWARD <i class="fas fa-fire ml-2"></i>
        </button>
        <p style="font-size: 0.8rem; margin-top: 15px; color: #666; cursor: pointer;" onclick="closeWelcomePopup()">No thanks, I hate free food.</p>
    </div>
</div>

<script>
    const isFirstLogin = <?= isset($user['first_login']) ? $user['first_login'] : 0 ?>;
    
    document.addEventListener("DOMContentLoaded", () => {
        if(isFirstLogin == 1) {
            setTimeout(() => {
                const popup = document.getElementById('welcome-popup');
                popup.style.display = 'flex';
                // Force reflow
                popup.offsetHeight; 
                popup.style.opacity = '1';
                popup.querySelector('.glass-card').style.transform = 'scale(1)';
            }, 1000); // 1s delay for effect
        }
    });

    function closeWelcomePopup() {
        const popup = document.getElementById('welcome-popup');
        popup.style.opacity = '0';
        popup.querySelector('.glass-card').style.transform = 'scale(0.8)';
        setTimeout(() => {
            popup.style.display = 'none';
        }, 500);
    }

    function claimWelcomeVoucher() {
        const btn = document.querySelector('#welcome-popup .btn-lava');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> CLAIMING...';
        
        const f = new FormData();
        f.append('action', 'claim_first_voucher');
        
        fetch('order.php', { method: 'POST', body: f })
        .then(r => r.json())
        .then(d => {
            if(d.status === 'success') {
                btn.innerHTML = 'CLAIMED! <i class="fas fa-check"></i>';
                btn.style.background = '#00e676';
                setTimeout(() => {
                    closeWelcomePopup();
                    // Optional: Show a subtle notification that it's in their profile
                    alert("Voucher added to your Profile! Check the 'My Vouchers' section.");
                    location.reload(); // Reload to update profile/session data if needed
                }, 1000);
            } else {
                alert(d.error || "Failed to claim.");
                btn.innerHTML = 'TRY AGAIN';
            }
        });
    }
</script>

<?php include "includes/footer.php"; ?>
