<?php
include "includes/header.php";

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE user_id=$user_id"));
$pts = (int)$user['points'];

// 0. Sanity Check for Tables
$table_check_orders = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'cart_orders'")) > 0;
$table_check_redemptions = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'redemptions'")) > 0;
$table_check_rewards = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'reward_items'")) > 0;

$order_count = 0; $redeem_count = 0; $voucher_count = 0;

if($table_check_orders) {
    $order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_orders WHERE user_id=$user_id AND status IN ('confirmed','completed')"))['c'];
}

if($table_check_redemptions) {
    $redeem_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM redemptions WHERE user_id=$user_id"))['c'];
}

if($table_check_redemptions && $table_check_rewards) {
    $voucher_count = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as c 
        FROM redemptions r 
        JOIN reward_items ri ON r.reward_item_id = ri.id 
        WHERE r.user_id=$user_id AND ri.category='voucher'
    "))['c'];
}

// Rank for Top Flame - Exclude Admins
$rank_res = mysqli_query($conn, "SELECT user_id FROM users WHERE role != 'admin' ORDER BY points DESC");
$rank = 0;
while($row = mysqli_fetch_assoc($rank_res)) {
    $rank++;
    if($row['user_id'] == $user_id) break;
}

// 1. Identity Maker (Check if username/fullname is set)
$has_identity = (!empty($user['username']) && $user['username'] != $user['email']);

// Achievement Definitions
$achievements = [
    ["id" => "flame_starter", "title" => "Flame Starter", "desc" => "Reach 50 points to ignite your journey.", "icon" => "fa-seedling", "unlocked" => ($pts >= 50)],
    ["id" => "big_spender", "title" => "Big Spender", "desc" => "Redeem your first reward item from the shop.", "icon" => "fa-shopping-bag", "unlocked" => ($redeem_count >= 1)],
    ["id" => "voucher_hunter", "title" => "Voucher Hunter", "desc" => "Successfully claim a digital voucher.", "icon" => "fa-ticket", "unlocked" => ($voucher_count >= 1)],
    ["id" => "top_flame", "title" => "Top Flame (Rank #1)", "desc" => "Reach the absolute peak of the Hall of Flame.", "icon" => "fa-crown", "unlocked" => ($rank == 1)],
    ["id" => "silver_flame", "title" => "Silver Flame (Rank #2)", "desc" => "Secure the prestigious Rank #2 position.", "icon" => "fa-medal", "unlocked" => ($rank <= 2)],
    ["id" => "bronze_flame", "title" => "Bronze Flame (Rank #3)", "desc" => "Stand tall on the podium at Rank #3.", "icon" => "fa-award", "unlocked" => ($rank <= 3)],
    ["id" => "inferno_enthusiast", "title" => "Inferno Enthusiast", "desc" => "Place 10 confirmed orders to prove your passion.", "icon" => "fa-fire", "unlocked" => ($order_count >= 10)],
    ["id" => "identity_maker", "title" => "Identity Maker", "desc" => "Personalize your profile name for the hall of fame.", "icon" => "fa-signature", "unlocked" => $has_identity],
    ["id" => "fame_seeker", "title" => "Fame Seeker", "desc" => "Engage with the community by earning your first points.", "icon" => "fa-ghost", "unlocked" => ($pts > 0)]
];

$unlocked_count = 0;
foreach($achievements as $a) if($a['unlocked']) $unlocked_count++;
$percent = round(($unlocked_count / count($achievements)) * 100);

// Badge Collection
$badges = [
    ["name" => "Food God", "pts" => 1000, "icon" => "fa-infinity", "color" => "#FFD700", "glow" => "gold"],
    ["name" => "Connoisseur", "pts" => 900, "icon" => "fa-magnifying-glass-location", "color" => "#FF0055"],
    ["name" => "Dish Discoverer", "pts" => 800, "icon" => "fa-map", "color" => "#D500F9"],
    ["name" => "Cuisine Cadet", "pts" => 700, "icon" => "fa-user-graduate", "color" => "#651FFF"],
    ["name" => "Taste Tester", "pts" => 600, "icon" => "fa-vial", "color" => "#2979FF"],
    ["name" => "Flavor Explorer", "pts" => 500, "icon" => "fa-compass", "color" => "#00E5FF"],
    ["name" => "Foodie", "pts" => 400, "icon" => "fa-utensils", "color" => "#1DE9B6"],
    ["name" => "Meal Enthusiast", "pts" => 300, "icon" => "fa-drumstick-bite", "color" => "#00E676"],
    ["name" => "Appetizer Apprentice", "pts" => 200, "icon" => "fa-cookie", "color" => "#76FF03"],
    ["name" => "Snack Starter", "pts" => 100, "icon" => "fa-ice-cream", "color" => "#FFEA00"],
    ["name" => "Rookie Eater", "pts" => 50, "icon" => "fa-baby", "color" => "#FF9100"],
    ["name" => "Newbie", "pts" => 0, "icon" => "fa-seedling", "color" => "#90A4AE"]
];

// Theme Calculation - ONLY RANK #1 GETS COSMIC
if ($rank == 1) {
    $is_cosmic = true;
    $theme_class = "theme-cosmic";
} else {
    $is_cosmic = false;
    if ($percent >= 80) $theme_class = "theme-neon-purple";
    elseif ($percent >= 70) $theme_class = "theme-deep-indigo";
    elseif ($percent >= 60) $theme_class = "theme-bright-blue";
    elseif ($percent >= 50) $theme_class = "theme-cyan";
    elseif ($percent >= 40) $theme_class = "theme-teal";
    elseif ($percent >= 30) $theme_class = "theme-bright-green";
    elseif ($percent >= 20) $theme_class = "theme-lime";
    elseif ($percent >= 10) $theme_class = "theme-yellow";
    else $theme_class = "theme-base";
}
?>

<style>
/* Dynamic Theme Colors */
.achievement-page .theme-cosmic .heat-title, .theme-cosmic .text-gradient { background: linear-gradient(135deg, #e1bee7, #fff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-neon-purple .heat-title, .theme-neon-purple .text-gradient { background: linear-gradient(135deg, #D500F9, #AA00FF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-deep-indigo .heat-title, .theme-deep-indigo .text-gradient { background: linear-gradient(135deg, #651FFF, #3D5AFE); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-bright-blue .heat-title, .theme-bright-blue .text-gradient { background: linear-gradient(135deg, #2979FF, #00B0FF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-cyan .heat-title, .theme-cyan .text-gradient { background: linear-gradient(135deg, #00E5FF, #18FFFF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-teal .heat-title, .theme-teal .text-gradient { background: linear-gradient(135deg, #1DE9B6, #64FFDA); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-bright-green .heat-title, .theme-bright-green .text-gradient { background: linear-gradient(135deg, #00E676, #69F0AE); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-lime .heat-title, .theme-lime .text-gradient { background: linear-gradient(135deg, #76FF03, #C6FF00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.achievement-page .theme-yellow .heat-title, .theme-yellow .text-gradient { background: linear-gradient(135deg, #FFEA00, #FFD600); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

/* Achievement Card Style - FIXED GLASSY LOOK */
.achievement-card {
    background: rgba(20, 20, 20, 0.6) !important;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    color: #fff !important; /* Force white text */
    padding: 35px;
    border-radius: 30px;
    text-align: center;
    transition: 0.4s;
    position: relative;
    overflow: hidden;
}

.achievement-card.unlocked {
    border-color: var(--tier-color) !important;
    box-shadow: 0 15px 35px rgba(0,0,0,0.6), 0 0 20px var(--tier-glow);
}

.achievement-card h4 { color: #fff !important; font-weight: 800; margin: 0; }
.achievement-card p { color: rgba(255,255,255,0.7) !important; margin: 10px 0 0 0; }

.achievement-card.locked {
    opacity: 0.5;
    filter: grayscale(1);
    border-color: rgba(255,255,255,0.05) !important;
}

.achievement-icon { 
    font-size: 3rem; 
    margin-bottom: 20px; 
    color: var(--tier-color) !important; 
    transition: 0.3s;
}

.achievement-card.unlocked .achievement-icon {
    filter: drop-shadow(0 0 10px var(--tier-glow));
}

/* Badge Collection - FIXED GLASSY LOOK */
.rank-grid-mini {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 20px;
}

.rank-card-mini {
    background: rgba(255, 255, 255, 0.02) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    padding: 25px 15px;
    border-radius: 20px;
    text-align: center;
    transition: 0.3s;
    opacity: 0.4;
    filter: grayscale(1);
}

.rank-card-mini.unlocked {
    opacity: 1;
    filter: none;
    background: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}

.rank-card-mini i { font-size: 1.8rem; margin-bottom: 15px; display: block; color: var(--b-color); }
.rank-card-mini h5 { color: #fff !important; font-weight: 800; margin: 0; }
.rank-card-mini span { color: rgba(255,255,255,0.5) !important; font-size: 0.8rem; }

/* Ascension Tiers Styles */
.ascension-tiers {
    max-width: 1400px; /* Increased from 1200px */
    margin: 80px auto 140px;
    display: flex;
    justify-content: space-between; /* Change to between for maximum stretch */
    position: relative;
    padding: 20px 60px;
}

.ascension-tiers::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.05);
    z-index: 1;
    transform: translateY(-50%);
}

.tier-milestone {
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.02);
    width: 80px; /* Increased from 60px */
    height: 80px; /* Increased from 60px */
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.4s;
}

.tier-milestone.unlocked {
    border-color: var(--m-color);
    box-shadow: 0 0 20px var(--m-glow);
    background: var(--m-color);
    animation: tierPulse 3s infinite ease-in-out;
}

@keyframes tierPulse {
    0%, 100% { box-shadow: 0 0 15px var(--m-glow); transform: scale(1); }
    50% { box-shadow: 0 0 30px var(--m-glow); transform: scale(1.05); }
}

.tier-milestone:hover {
    transform: scale(1.15) translateY(-5px) !important;
    z-index: 10;
}

.tier-milestone.locked:hover {
    filter: grayscale(0.5);
    opacity: 0.8;
}

.tier-milestone i {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.2);
    transition: 0.3s;
}

.tier-milestone.unlocked i {
    color: #000;
}

.tier-info {
    position: absolute;
    top: 95px; /* Adjusted for larger 80px circles */
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    width: 120px;
}

.tier-info span {
    display: block;
    font-size: 0.7rem;
    font-weight: 800;
    color: #444;
    text-transform: uppercase;
}

.tier-info strong {
    display: block;
    font-size: 0.8rem;
    color: #fff;
    margin-top: 2px;
    opacity: 0.2;
}

.tier-milestone.unlocked + .tier-info span {
    color: var(--m-color);
}

.tier-milestone.unlocked + .tier-info strong {
    opacity: 1;
}

/* Congrats Popup Styles */
#congrats-popup {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.9);
    z-index: 10000;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: 0.5s;
    backdrop-filter: blur(15px);
}

.congrats-card {
    background: rgba(15, 15, 15, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 60px;
    border-radius: 40px;
    text-align: center;
    max-width: 500px;
    width: 90%;
    transform: scale(0.8) translateY(20px);
    transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 0 100px rgba(255, 78, 0, 0.2);
    position: relative;
    overflow: hidden;
}

#congrats-popup.show { opacity: 1; display: flex; }
#congrats-popup.show .congrats-card { transform: scale(1) translateY(0); }

.congrats-icon {
    font-size: 5rem;
    color: var(--tier-color);
    margin-bottom: 30px;
    filter: drop-shadow(0 0 20px var(--tier-glow));
    animation: floatingIcon 3s infinite ease-in-out;
}

@keyframes floatingIcon {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

.flaming-continue {
    background: linear-gradient(45deg, #ff4e00, #ff0055);
    color: #fff;
    border: none;
    padding: 18px 45px;
    font-size: 1.2rem;
    font-weight: 800;
    border-radius: 15px;
    cursor: pointer;
    margin-top: 30px;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    box-shadow: 0 0 20px rgba(255, 78, 0, 0.4);
    transition: 0.3s;
    overflow: hidden;
}

.flaming-continue:hover {
    transform: scale(1.05);
    box-shadow: 0 0 40px rgba(255, 78, 0, 0.8);
}

.flaming-continue::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
    animation: flameFlow 2s infinite linear;
}

@keyframes flameFlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.achievement-card { cursor: pointer; }
</style>

<?php if($is_cosmic): ?>
<div class="cosmic-galaxy-bg"></div>
<?php endif; ?>

<div class="page-hub-container achievement-page <?= $theme_class ?> <?= $is_cosmic ? 'cosmic-mode' : '' ?>">
    <?php
    // Dynamic Color Logic - PRIORITIZE COSMIC RANK 1
    if ($is_cosmic) {
        $tier_color = "#e1bee7"; $tier_glow = "rgba(225, 190, 231, 0.8)"; $tier_name = "COSMIC";
    } elseif ($percent >= 80) {
        $tier_color = "#D500F9"; $tier_glow = "rgba(213, 0, 249, 0.6)"; $tier_name = "PURPLE";
    } elseif ($percent >= 70) {
        $tier_color = "#2979FF"; $tier_glow = "rgba(41, 121, 255, 0.6)"; $tier_name = "BLUE";
    } elseif ($percent >= 50) {
        $tier_color = "#00E676"; $tier_glow = "rgba(0, 230, 118, 0.6)"; $tier_name = "GREEN";
    } elseif ($percent >= 30) {
        $tier_color = "#FFEA00"; $tier_glow = "rgba(255, 234, 0, 0.6)"; $tier_name = "YELLOW";
    } elseif ($percent >= 10) {
        $tier_color = "#FF9100"; $tier_glow = "rgba(255, 145, 0, 0.6)"; $tier_name = "ORANGE";
    } else {
        $tier_color = "#ff4e00"; $tier_glow = "rgba(255, 78, 0, 0.6)"; $tier_name = "FLAME";
    }
    ?>

    <style>
        :root {
            --tier-color: <?= $tier_color ?>;
            --tier-glow: <?= $tier_glow ?>;
        }
    </style>

    <div class="page-hub-wrapper">
        <div class="achievement-banner animate-up">
            <h1 class="heat-title" style="margin: 0; font-size: 2.8rem; color: var(--tier-color) !important; text-shadow: 0 0 20px var(--tier-glow);">
                <?= $is_cosmic ? '<span style="color: #e1bee7">COSMIC</span> ACHIEVEMENTS' : 'HALL OF <span style="background: linear-gradient(135deg, #fff, var(--tier-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">ACHIEVEMENTS</span>' ?>
            </h1>
            <p style="margin: 5px 0 0 0; opacity: 0.8; color: #aaa;">
                <?= $is_cosmic ? 'You have evolved into a cosmic entity of the feast.' : 'Track your legendary progress and unlock exclusive status.' ?>
            </p>
        </div>

        <div class="flame-hero-hero animate-up" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 120px 0 80px; position: relative; z-index: 10;">

            <style>
                :root {
                    --tier-color: <?= $tier_color ?>;
                    --tier-glow: <?= $tier_glow ?>;
                }
                
                /* THE HERO CONTAINER (Atmospheric Nebula Drift) */
                .flame-hero-hero {
                    background: radial-gradient(circle at 50% 50%, rgba(var(--tier-rgb, 255,100,0), 0.05), transparent 70%);
                    animation: nebulaDrift 10s infinite alternate ease-in-out;
                }
                @keyframes nebulaDrift {
                    0% { background-position: 0% 0%; }
                    100% { background-position: 10% 10%; filter: hue-rotate(10deg); }
                }

                /* ANIMATION 1: OUTER ORBITAL RINGS (Outside the frame) */
                .outer-orbital-ring {
                    position: absolute; border: 1px solid var(--tier-glow);
                    border-radius: 50%; width: 600px; height: 150px;
                    pointer-events: none; opacity: 0.15;
                    transform: rotateX(70deg) rotateY(var(--ry, 0deg));
                    animation: ringRotate 20s linear infinite;
                }
                @keyframes ringRotate { from { transform: rotateX(70deg) rotateZ(0deg); } to { transform: rotateX(70deg) rotateZ(360deg); } }

                /* ANIMATION 2: SOLAR FLARE BURSTS (Background flares) */
                .solar-flare {
                    position: absolute; width: 100px; height: 100px;
                    background: radial-gradient(circle, var(--tier-color), transparent 70%);
                    border-radius: 50%; opacity: 0; filter: blur(20px);
                    animation: flarePulse 6s infinite;
                }
                @keyframes flarePulse {
                    0%, 100% { opacity: 0; transform: scale(0.5); }
                    50% { opacity: 0.2; transform: scale(2); }
                }

                /* THE FRAME */
                .flame-aura-frame {
                    position: relative;
                    padding: 85px;
                    border-radius: 90px;
                    background: radial-gradient(circle at center, rgba(0,0,0,0.85), transparent);
                    border: 2px solid rgba(255,255,255,0.06);
                    box-shadow: 
                        inset 0 0 60px rgba(0,0,0,1),
                        0 0 70px var(--tier-glow);
                    animation: framePulse 4s infinite ease-in-out;
                    margin-bottom: 40px;
                    overflow: hidden;
                    display: flex; align-items: center; justify-content: center;
                }

                /* ANIMATION 3: ENERGY TENDRILS (Inside the frame) */
                .energy-tendril {
                    position: absolute; width: 100%; height: 100%; top: 0; left: 0;
                    pointer-events: none; opacity: 0.4; stroke: var(--tier-color);
                    fill: none; stroke-width: 1; stroke-dasharray: 100;
                    animation: tendrilFlow 4s linear infinite;
                }
                @keyframes tendrilFlow { from { stroke-dashoffset: 200; } to { stroke-dashoffset: 0; } }

                /* Previous Animations... */
                .heat-shockwave {
                    position: absolute; border: 2px solid var(--tier-color);
                    border-radius: 50%; opacity: 0; animation: shockwaveExpand 3.5s infinite;
                    pointer-events: none;
                }
                @keyframes shockwaveExpand {
                    0% { width: 40px; height: 40px; opacity: 0.5; border-width: 5px; }
                    100% { width: 450px; height: 450px; opacity: 0; border-width: 0px; }
                }

                @keyframes framePulse {
                    0%, 100% { border-color: rgba(255,255,255,0.05); transform: scale(1); }
                    50% { border-color: var(--tier-color); transform: scale(1.03); filter: brightness(1.2); }
                }

                /* ANIMATION 5: PULSING REACTOR HALO (Double pulse) */
                .reactor-halo {
                    position: absolute; inset: 10px; border: 4px solid var(--tier-glow);
                    border-radius: 80px; opacity: 0.2; pointer-events: none;
                    animation: haloPulse 1.5s infinite alternate ease-in-out;
                }
                @keyframes haloPulse { 0% { opacity: 0.1; transform: scale(0.98); } 100% { opacity: 0.4; transform: scale(1.02); } }

                /* ANIMATION 6: SACRED VORTEX (Behind the flame) */
                .sacred-vortex {
                    position: absolute; width: 300px; height: 300px;
                    border: 1px dashed var(--tier-color); border-radius: 50%;
                    opacity: 0.05; animation: vortexSpin 30s linear infinite;
                }
                @keyframes vortexSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

                /* ANIMATION 7: CHRONO-SCAN (Sweep line) */
                .chrono-scan {
                    position: absolute; width: 100%; height: 2px;
                    background: linear-gradient(90deg, transparent, var(--tier-color), transparent);
                    top: -10%; left: 0; opacity: 0.5; filter: blur(2px);
                    animation: scanSlide 8s infinite linear;
                }
                @keyframes scanSlide { 0% { top: -10%; } 50% { top: 110%; } 100% { top: -10%; } }

                /* ANIMATION 8: ASH MOTES (Drifting particles) */
                .ash-mote {
                    position: absolute; width: 2px; height: 2px; background: #fff;
                    border-radius: 50%; opacity: 0.3; animation: ashDrift 15s infinite linear;
                }
                @keyframes ashDrift {
                    0% { transform: translate(0, 0) rotate(0deg); opacity: 0; }
                    20% { opacity: 0.3; }
                    80% { opacity: 0.3; }
                    100% { transform: translate(100px, -200px) rotate(360deg); opacity: 0; }
                }

                .percent-v4 {
                    position: absolute; bottom: 20%; font-size: 5.8rem; font-weight: 950;
                    color: #fff; text-shadow: 0 0 40px #000, 0 0 25px var(--tier-glow);
                    z-index: 20; letter-spacing: -4px;
                }
            </style>
            
            <!-- OUTSIDE ANIMATIONS -->
            <div class="outer-orbital-ring" style="--ry: 0deg; width: 650px; animation-duration: 15s;"></div>
            <div class="outer-orbital-ring" style="--ry: 45deg; width: 550px; animation-duration: 25s; opacity: 0.1;"></div>
            
            <div class="solar-flare" style="top: 10%; left: 20%; animation-delay: 0s;"></div>
            <div class="solar-flare" style="bottom: 10%; right: 20%; animation-delay: 3s;"></div>

            <!-- Ash Motes Cloud -->
            <?php for($i=0; $i<10; $i++): ?>
                <div class="ash-mote" style="top: <?=rand(20,80)?>%; left: <?=rand(20,80)?>%; animation-delay: <?=rand(0,15)?>s;"></div>
            <?php endfor; ?>

            <!-- SVG FILTERS -->
            <svg style="position: absolute; width: 0; height: 0;">
                <defs>
                    <filter id="plasma-goo">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="6" result="blur" />
                        <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo" />
                        <feComposite in="SourceGraphic" in2="goo" operator="atop"/>
                    </filter>
                    <filter id="heat-haze">
                        <feTurbulence type="turbulence" baseFrequency="0.05" numOctaves="2" seed="5">
                            <animate attributeName="seed" from="1" to="100" dur="10s" repeatCount="indefinite" />
                        </feTurbulence>
                        <feDisplacementMap in="SourceGraphic" scale="5" />
                    </filter>
                </defs>
            </svg>

            <div class="flame-aura-frame">
                <div class="sacred-vortex"></div>
                <div class="reactor-halo"></div>
                <div class="chrono-scan"></div>

                <!-- 3. Energy Tendrils (Inside) -->
                <svg viewBox="0 0 200 200" style="position: absolute; inset: 0; width: 100%; height: 100%;">
                    <path class="energy-tendril" d="M100,50 Q130,100 100,150" style="animation-duration: 3s;"></path>
                    <path class="energy-tendril" d="M100,50 Q70,100 100,150" style="animation-duration: 5s;"></path>
                    <path class="energy-tendril" d="M50,100 Q100,70 150,100" style="animation-duration: 4s; opacity: 0.2;"></path>
                </svg>

                <!-- 1. Heat Shockwaves -->
                <div class="heat-shockwave shockwave-1"></div>
                <div class="heat-shockwave shockwave-2"></div>

                <!-- 2. Orbital Energy Bits -->
                <div class="orbital-container" style="animation-duration: 4s;">
                    <div class="energy-bit" style="top: 0; left: 50%; transform: translateX(-50%);"></div>
                </div>
                <div class="orbital-container" style="animation-duration: 6s; opacity: 0.6;">
                    <div class="energy-bit" style="bottom: 0; left: 50%; transform: translateX(-50%);"></div>
                </div>

                <!-- 3. Electricity Arcs -->
                <div class="electric-arc" style="--r: 45deg; animation-delay: 0.1s;"></div>
                <div class="electric-arc" style="--r: -30deg; animation-delay: 0.5s;"></div>
                <div class="electric-arc" style="--r: 120deg; animation-delay: 0.8s;"></div>

                <div class="burning-container-v4 <?= $percent >= 100 ? 'max-burn' : '' ?>" style="filter: url(#plasma-goo);">
                    <!-- 4. Rising Embers -->
                    <div class="embers-v4">
                        <div class="p-spark" style="left: 40%; --sx: -50px; animation-delay: 0s;"></div>
                        <div class="p-spark" style="left: 60%; --sx: 60px; animation-delay: 0.3s;"></div>
                        <div class="p-spark" style="left: 50%; --sx: -20px; animation-delay: 0.6s;"></div>
                    </div>

                    <!-- REALISTIC MORPHING SVG FLAME -->
                    <div class="flame-stack" style="position: relative; width: 220px; height: 320px; filter: url(#heat-haze);">
                        <svg viewBox="0 0 100 130" style="width: 100%; height: 100%; overflow: visible;">
                            <defs>
                                <linearGradient id="mainFlameGrad" x1="0%" y1="100%" x2="0%" y2="0%">
                                    <stop offset="0%" style="stop-color:var(--tier-color);stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:var(--tier-color);stop-opacity:0">
                                        <animate attributeName="offset" values="0.7;1.1;0.7" dur="2s" repeatCount="indefinite" />
                                    </stop>
                                </linearGradient>
                            </defs>
                            
                            <?php 
                            $p_base = "M50,0 C30,30 5,60 5,95 C5,115 25,130 50,130 C75,130 95,115 95,95 C95,60 70,30 50,0 Z";
                            $p_lick_l = "M40,5 C20,35 -5,65 -5,100 C-5,120 20,135 50,135 C80,135 105,120 105,100 C105,65 70,35 40,5 Z";
                            $p_lick_r = "M60,5 C40,35 95,65 95,100 C95,120 70,135 40,135 C10,135 -15,120 -15,100 C-15,65 30,35 60,5 Z";
                            $p_tall = "M50,-15 C35,25 15,55 15,90 C15,110 35,125 50,125 C65,125 85,110 85,90 C85,55 65,25 50,-15 Z";
                            ?>
                            
                            <path class="f-svg-layer f-back" fill="url(#mainFlameGrad)" style="opacity: 0.2;">
                                <animate attributeName="d" values="<?= $p_base ?>;<?= $p_tall ?>;<?= $p_lick_l ?>;<?= $p_lick_r ?>;<?= $p_base ?>" dur="5s" repeatCount="indefinite" />
                            </path>

                            <path class="f-svg-layer f-mid" fill="url(#mainFlameGrad)" style="opacity: 0.4;">
                                <animate attributeName="d" values="<?= $p_lick_r ?>;<?= $p_base ?>;<?= $p_tall ?>;<?= $p_lick_l ?>;<?= $p_lick_r ?>" dur="4s" repeatCount="indefinite" />
                            </path>

                            <path class="f-svg-layer f-inner" fill="url(#mainFlameGrad)" style="opacity: 0.7; filter: brightness(1.2);">
                                <animate attributeName="d" values="<?= $p_tall ?>;<?= $p_lick_l ?>;<?= $p_lick_r ?>;<?= $p_base ?>;<?= $p_tall ?>" dur="3s" repeatCount="indefinite" />
                            </path>

                            <path class="f-svg-layer f-core" d="<?= $p_base ?>" fill="#fff" style="opacity: 0.5; filter: blur(3px);">
                                <animate attributeName="d" values="<?= $p_base ?>;<?= $p_tall ?>;<?= $p_base ?>" dur="1.5s" repeatCount="indefinite" />
                            </path>
                        </svg>
                    </div>

                    <div class="percent-v4"><?= $percent ?>%</div>
                </div>

                <style>
                    .f-svg-layer { mix-blend-mode: screen; }
                    
                    @keyframes corePulse {
                        0%, 100% { transform: scale(0.35) translateY(35%); opacity: 0.5; }
                        50% { transform: scale(0.42) translateY(30%); opacity: 0.8; }
                    }

                    .flame-stack svg { filter: drop-shadow(0 0 30px var(--tier-glow)); }
                </style>
            </div>

            <div class="achievement-banner" style="text-align: center;">
                <h1 class="heat-title" style="margin: 0; font-size: 3.5rem; color: var(--tier-color) !important; text-shadow: 0 0 20px var(--tier-glow);">
                    <?= $tier_name ?> <span style="background: linear-gradient(135deg, #fff, var(--tier-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">ASCENSION</span>
                </h1>
                <p style="opacity: 0.6; color: #fff; max-width: 500px; margin: 15px auto 0;">
                    Currently at <strong><?= $percent ?>%</strong> completion. You have reached the <?= $tier_name ?> status.
                </p>
            </div>

            <div style="text-align: center; margin-top: 50px; margin-bottom: -10px;">
                <h2 class="heat-title" style="font-size: 1.5rem; opacity: 0.6; tracking-spacing: 2px;">— PROGRESS TIER LIST —</h2>
            </div>

            <!-- Ascension Tiers List -->
            <div class="ascension-tiers">
                <?php
                $milestones = [
                    ["p" => 10,  "label" => "ORANGE", "icon" => "fa-fire",  "color" => "#FF9100", "glow" => "rgba(255, 145, 0, 0.6)"],
                    ["p" => 30,  "label" => "YELLOW", "icon" => "fa-bolt",  "color" => "#FFEA00", "glow" => "rgba(255, 234, 0, 0.6)"],
                    ["p" => 50,  "label" => "GREEN",  "icon" => "fa-leaf",  "color" => "#00E676", "glow" => "rgba(0, 230, 118, 0.6)"],
                    ["p" => 70,  "label" => "BLUE",   "icon" => "fa-water", "color" => "#2979FF", "glow" => "rgba(41, 121, 255, 0.6)"],
                    ["p" => 80,  "label" => "PURPLE", "icon" => "fa-ghost", "color" => "#D500F9", "glow" => "rgba(213, 0, 249, 0.6)"],
                    ["p" => 100, "label" => "COSMIC", "icon" => "fa-star",  "color" => "#e1bee7", "glow" => "rgba(225, 190, 231, 0.8)"]
                ];

                foreach($milestones as $idx => $m):
                    $m_unlocked = ($percent >= $m['p']);
                ?>
                <div class="animate-up stagger-<?= $idx + 1 ?>" style="position: relative; --m-color: <?= $m['color'] ?>; --m-glow: <?= $m['glow'] ?>;">
                    <div class="tier-milestone <?= $m_unlocked ? 'unlocked' : 'locked' ?>">
                        <i class="fas <?= $m['icon'] ?>"></i>
                    </div>
                    <div class="tier-info">
                        <span><?= $m['label'] ?></span>
                        <strong><?= $m['p'] ?>%</strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quests Grid -->
        <div class="achievement-grid" style="margin-top: 50px;">
            <?php foreach($achievements as $index => $a): ?>
            <div class="achievement-card <?= $a['unlocked'] ? 'unlocked' : 'locked' ?> animate-up stagger-<?= $index+2 ?>" style="padding: 30px; border-radius: 25px; margin-bottom: 20px;">
                <div class="achievement-icon" style="font-size: 2rem; margin-bottom: 15px;">
                    <i class="fas <?= $a['icon'] ?>"></i>
                </div>
                <div class="achievement-info">
                    <h4 style="margin: 0; font-weight: 800;"><?= $a['title'] ?></h4>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem; opacity: 0.7;"><?= $a['desc'] ?></p>
                </div>
                <?php if($a['unlocked']): ?>
                    <div style="position: absolute; top: 15px; right: 15px; color: #00e676; font-size: 1.2rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Badge Collection Section -->
        <div class="hall-of-flame-section animate-up stagger-5" style="margin-top: 80px; padding: 40px; border-radius: 35px; background: rgba(255,255,255,0.02);">
            <h2 class="heat-title" style="margin-bottom: 30px; font-size: 2rem; color: var(--tier-color) !important;">
                <i class="fas fa-award" style="color: var(--tier-color); text-shadow: 0 0 10px var(--tier-glow);"></i> Badge <span style="background: linear-gradient(135deg, #fff, var(--tier-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Collection</span>
            </h2>
            
            <div class="rank-grid-mini">
                <?php foreach($badges as $b): 
                    $is_unlocked = ($pts >= $b['pts']);
                ?>
                <div class="rank-card-mini <?= $is_unlocked ? 'unlocked' : 'locked' ?>">
                    <i class="fas <?= $b['icon'] ?>" style="<?= $is_unlocked ? 'color: '.$b['color'] : '' ?>; font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
                    <h5 style="margin: 0; font-size: 0.9rem; font-weight: 800;"><?= $b['name'] ?></h5>
                    <span style="font-size: 0.75rem; opacity: 0.6;"><?= $b['pts'] ?> Pts</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 80px; padding-bottom: 50px;">
            <a href="order.php" class="btn-lava" style="padding: 15px 40px; border-radius: 15px;">
                Earn More Sparks <i class="fas fa-bolt"></i>
            </a>
        </div>
    </div>
</div>

<!-- Congratulations Popup -->
<div id="congrats-popup">
    <div class="congrats-card">
        <div class="congrats-icon">
            <i class="fas fa-crown"></i>
        </div>
        <h2 class="heat-title" style="font-size: 2.5rem; margin-bottom: 10px;">CONGRATULATIONS!</h2>
        <p id="congrats-desc" style="color: rgba(255,255,255,0.7); font-size: 1.1rem; line-height: 1.6;">
            You have unlocked the <strong>Top Flame</strong> achievement! Your dedication has etched your name into the Hall of Flame.
        </p>
        <button class="flaming-continue" onclick="closeCongrats()">
            Continue <i class="fas fa-fire ml-2"></i>
        </button>
    </div>
</div>

<script>
function showCongrats(title, icon) {
    const popup = document.getElementById('congrats-popup');
    const desc = document.getElementById('congrats-desc');
    const iconEl = popup.querySelector('.congrats-icon i');
    
    iconEl.className = 'fas ' + icon;
    desc.innerHTML = `You have unlocked the <strong>${title}</strong> achievement! Your legendary progress continues to burn bright.`;
    
    popup.style.display = 'flex';
    setTimeout(() => popup.classList.add('show'), 10);
}

function closeCongrats() {
    const popup = document.getElementById('congrats-popup');
    popup.classList.remove('show');
    setTimeout(() => popup.style.display = 'none', 500);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.achievement-card').forEach(card => {
        card.addEventListener('click', () => {
            const title = card.querySelector('h4').innerText;
            const icon = card.querySelector('.achievement-icon i').classList[1];
            showCongrats(title, icon);
        });
    });
});
</script>

<?php include "includes/footer.php"; ?>
