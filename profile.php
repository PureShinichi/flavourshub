<?php
include "includes/header.php";

// Fetch deeper user stats
$user_id = $_SESSION['user_id'];
$q = mysqli_query($conn, "SELECT * FROM users WHERE user_id=$user_id");
$user = mysqli_fetch_assoc($q);

// Handle Profile Update (Username Only)
if(isset($_POST['update_profile'])) {
    $new_name = mysqli_real_escape_string($conn, $_POST['username']);
    if(!empty($new_name)) {
        mysqli_query($conn, "UPDATE users SET username='$new_name' WHERE user_id=$user_id");
        $user['username'] = $new_name;
        $_SESSION['username'] = $new_name; // Sync session
        $msg = "Profile updated!";
    }
}

// Handle Avatar Upload
$msg = "";
if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['avatar']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if(in_array($ext, $allowed)) {
        $new_name = "avatar_" . $user_id . "_" . time() . "." . $ext;
        $dest_dir = "assets/uploads/avatars/";
        if (!file_exists($dest_dir)) {
            mkdir($dest_dir, 0777, true);
        }
        $dest = $dest_dir . $new_name;
        if(move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
            mysqli_query($conn, "UPDATE users SET avatar_url='$dest' WHERE user_id=$user_id");
            $msg = "Avatar updated!";
            // Refresh user data
            $user['avatar_url'] = $dest; 
        } else {
            $msg = "Upload failed.";
        }
    } else {
        $msg = "Invalid file type.";
    }
}

// Order history count
$oq = mysqli_query($conn, "SELECT COUNT(*) as total FROM cart_orders WHERE user_id=$user_id AND status='confirmed'");
$order_count = mysqli_fetch_assoc($oq)['total'];

// Redemption history count
$rq = mysqli_query($conn, "SELECT COUNT(*) as total FROM redemptions WHERE user_id=$user_id");
$redeem_count = mysqli_fetch_assoc($rq)['total'];

// Gamified Rank Theme Logic
$pts = $user['points'];
$rank_theme = "rank-starter";
if ($pts >= 1000) $rank_theme = "rank-god cosmic-mode";
elseif ($pts >= 900) $rank_theme = "rank-elite cosmic-mode"; 
elseif ($pts >= 800) $rank_theme = "rank-neon";
elseif ($pts >= 600) $rank_theme = "rank-diamond";
elseif ($pts >= 400) $rank_theme = "rank-gold";
elseif ($pts >= 200) $rank_theme = "rank-silver";
?>

<style>
    /* Rank Themes */
    /* Rank Themes */
    .rank-god .page-hub-wrapper { border: none; box-shadow: none; }
    .rank-god .heat-title { background: linear-gradient(135deg, #ffd700, #ffeb3b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 20px rgba(255, 215, 0, 0.5); }
    
    .rank-elite .page-hub-wrapper { border: none; box-shadow: none; }
    .rank-elite .heat-title { background: linear-gradient(135deg, #d500f9, #aa00ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .rank-neon .page-hub-wrapper { border: none; box-shadow: none; }
    
    /* Avatar Edit */
    .profile-avatar-wrapper { position: relative; width: 160px; height: 160px; display: flex; align-items: center; justify-content: center; }
    .profile-avatar-main { 
        position: relative; width: 100%; height: 100%; border-radius: 50%; overflow: hidden; 
        transition: 0.3s; background-size: cover; background-position: center; z-index: 2;
        border: 4px solid var(--secondary); /* Border to separate image from background */
        display: flex; align-items: center; justify-content: center;
        background-color: var(--secondary);
    }
    
    /* Burning Ring Behind Avatar */
    .profile-avatar-wrapper::before {
        content: ''; position: absolute; inset: -10px; border-radius: 50%;
        background: conic-gradient(from 0deg, var(--primary), #ec9f05, var(--primary));
        z-index: 1; filter: blur(15px); opacity: 0.6;
        animation: spinAvatarFire 3s linear infinite;
    }
    @keyframes spinAvatarFire { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    
    .edit-overlay {
        position: absolute; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: 0.3s; color: white; font-size: 1.5rem;
    }
    .profile-avatar-main:hover .edit-overlay { opacity: 1; }
</style>

<div class="page-hub-container <?= $rank_theme ?>">
    <div class="page-hub-wrapper">
        <!-- Hero Profile Section -->
        <div class="profile-hero animate-up">
            <div class="profile-avatar-wrapper">
                <form method="POST" enctype="multipart/form-data" id="avatar-form">
                    <label for="avatar-input" class="profile-avatar-main" style="cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <?php if(!empty($user['avatar_url'])): ?>
                            <img src="<?= $user['avatar_url'] ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <div style="font-size: 4rem; font-weight: 800; color: #fff;"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div class="edit-overlay"><i class="fas fa-camera"></i></div>
                    </label>
                    <input type="file" name="avatar" id="avatar-input" style="display:none;" onchange="document.getElementById('avatar-form').submit()">
                </form>
            </div>
            <div style="z-index: 1; flex: 1;">
                <div id="view-mode">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <h1 class="heat-title" style="margin: 0; font-size: 3.5rem; letter-spacing: -2px;"><?= htmlspecialchars($user['username']) ?></h1>
                        <button onclick="toggleEdit()" style="background:none; border:none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;"><i class="fas fa-pen"></i></button>
                    </div>
                    <p style="color: var(--text-muted); font-size: 1.2rem; margin: 5px 0 15px 0;">Member since <?= date('Y') ?></p>
                </div>

                <div id="edit-mode" style="display: none;">
                    <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required 
                               style="background: rgba(255,255,255,0.1); border: 1px solid var(--primary); color: white; font-size: 2rem; padding: 5px 15px; border-radius: 10px; font-family: inherit; font-weight: 800; max-width: 300px;">
                        <button type="submit" name="update_profile" class="btn-lava" style="padding: 10px 20px; font-size: 1rem;">SAVE</button>
                        <button type="button" onclick="toggleEdit()" style="background: none; border: 1px solid #555; color: #ccc; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700;">CANCEL</button>
                    </form>
                </div>

                <div class="points-pill" style="display: inline-flex; font-size: 1rem; padding: 10px 25px;">
                    <i class="fas fa-medal"></i>
                    <span><?= $user['badge'] ?> Member</span>
                </div>
            </div>
        </div>

        <script>
        function toggleEdit() {
            const view = document.getElementById('view-mode');
            const edit = document.getElementById('edit-mode');
            if(view.style.display === 'none') {
                view.style.display = 'block';
                edit.style.display = 'none';
            } else {
                view.style.display = 'none';
                edit.style.display = 'block';
            }
        }
        </script>

        <!-- Bento Stats Grid -->
        <div class="profile-stats-grid">
            <!-- Points Card -->
            <div class="flame-card animate-up stagger-1" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                <div class="icon-circle" style="background: var(--flame-grad); color: white; width: 80px; height: 80px; font-size: 2rem;">
                    <i class="fas fa-fire"></i>
                </div>
                <span class="stat-label">Current Balance</span>
                <h2 class="stat-value" style="font-size: 3.5rem; color: var(--primary-light); margin: 10px 0;"><?= number_format($user['points']) ?></h2>
                <p style="color: var(--text-muted); font-weight: 600;">FLAME POINTS</p>
                <a href="rewards.php" class="btn-lava" style="margin-top: 25px; width: 100%; justify-content: center;">
                    Spend Points <i class="fas fa-shopping-cart"></i>
                </a>
            </div>

            <!-- Achievements Card (NEW) -->
            <a href="achievements.php" class="flame-card animate-up stagger-2" style="text-decoration: none; color: inherit; transition: 0.3s; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 class="heat-title" style="font-size: 1.5rem; margin-bottom: 20px;">
                        <i class="fas fa-trophy" style="color: #ffd700;"></i> Achievements
                    </h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5;">Track your legendary progress and unlock exclusive fiery status titles.</p>
                </div>
                
                <?php
                // achievement logic synced from achievements.php
                $order_count = 0; $redeem_count = 0; $voucher_count = 0;
                
                // Sanity Check for Tables
                $t_orders = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'cart_orders'")) > 0;
                $t_redemptions = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'redemptions'")) > 0;
                $t_rewards = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'reward_items'")) > 0;

                if($t_orders) {
                    $order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_orders WHERE user_id=$user_id AND status IN ('confirmed','completed')"))['c'];
                }
                if($t_redemptions) {
                    $redeem_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM redemptions WHERE user_id=$user_id"))['c'];
                }
                if($t_redemptions && $t_rewards) {
                    $voucher_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM redemptions r JOIN reward_items ri ON r.reward_item_id = ri.id WHERE r.user_id=$user_id AND ri.category='voucher'"))['c'];
                }
                
                // Rank for Top Flame - Exclude Admins
                $rank_res = mysqli_query($conn, "SELECT user_id FROM users WHERE role != 'admin' ORDER BY points DESC");
                $rank = 0;
                while($row = mysqli_fetch_assoc($rank_res)) {
                    $rank++;
                    if($row['user_id'] == $user_id) break;
                }

                $has_identity = (!empty($user['username']) && $user['username'] != $user['email']);

                $ach_checks = [
                    ($pts >= 50),                 // Flame Starter
                    ($redeem_count >= 1),         // Big Spender
                    ($voucher_count >= 1),        // Voucher Hunter
                    ($rank == 1),                 // Top Flame
                    ($rank <= 2),                 // Silver Flame
                    ($rank <= 3),                 // Bronze Flame
                    ($order_count >= 10),         // Inferno Enthusiast
                    $has_identity,                // Identity Maker
                    ($pts > 0)                    // Fame Seeker
                ];

                $unlocked = 0;
                foreach($ach_checks as $check) if($check) $unlocked++;
                $total_ach = count($ach_checks);
                $unlocked_pct = ($unlocked / $total_ach) * 100;
                ?>

                <div style="margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 8px;">
                        <span>Progress</span>
                        <span><?= $unlocked ?>/<?= $total_ach ?></span>
                    </div>
                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden;">
                        <div style="width: <?= $unlocked_pct ?>%; height: 100%; background: var(--flame-grad); box-shadow: 0 0 10px var(--primary);"></div>
                    </div>
                </div>
            </a>

            <!-- Activity Summary -->
            <div class="flame-card animate-up stagger-3">
                <h3 class="heat-title" style="font-size: 1.5rem; margin-bottom: 25px;">
                    <i class="fas fa-chart-bar" style="color: var(--primary);"></i> Activity Summary
                </h3>
                
                <div class="profile-stats-pill" style="margin-bottom: 20px;">
                    <span class="stat-label">Orders Confirmed</span>
                    <span class="stat-value"><?= $order_count ?></span>
                </div>

                <div class="profile-stats-pill" style="margin-bottom: 20px;">
                    <span class="stat-label">Items Redeemed</span>
                    <span class="stat-value"><?= $redeem_count ?></span>
                </div>

                <div class="profile-stats-pill">
                    <span class="stat-label">Member Since</span>
                    <span class="stat-value"><?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                </div>
            </div>
        </div>

        <!-- My Vouchers Section (NEW) -->
        <div class="voucher-section animate-up stagger-4" style="margin-top: 60px;">
            <h2 class="voucher-section-title heat-title">
                <i class="fas fa-ticket-alt"></i> My <span class="text-gradient">Vouchers</span>
            </h2>
            
            <div class="voucher-grid">
                <?php
                $vouchers = mysqli_query($conn, "
                    SELECT r.*, ri.name, ri.points_cost, ri.category 
                    FROM redemptions r 
                    JOIN reward_items ri ON r.reward_item_id = ri.id 
                    WHERE r.user_id = $user_id AND ri.category = 'voucher'
                    ORDER BY r.redeemed_at DESC
                ");
                
                if(mysqli_num_rows($vouchers) > 0):
                    while($v = mysqli_fetch_assoc($vouchers)):
                        $v_code = !empty($v['voucher_code']) ? $v['voucher_code'] : "FLAME-" . strtoupper(substr(md5($v['id']), 0, 8));
                        $is_used = ($v['status'] == 'used');
                ?>
                <div class="voucher-holo-card" style="<?= $is_used ? 'opacity: 0.5; filter: grayscale(1);' : '' ?>">
                    <span class="v-brand">LIMITED VOUCHER</span>
                    <span class="v-value"><?= htmlspecialchars($v['name']) ?></span>
                    <span class="v-desc">
                        <?= $is_used ? '<strong>USED</strong>' : 'Redeemed on ' . date('M d, Y', strtotime($v['redeemed_at'])) ?>
                    </span>
                    <div class="v-code-box">
                        <span class="v-code" style="<?= $is_used ? 'text-decoration: line-through;' : '' ?>"><?= $v_code ?></span>
                    </div>
                    <?php if(!$is_used): ?>
                    <i class="fas fa-check-circle" style="position: absolute; top: 10px; right: 10px; color: #00e676;"></i>
                    <?php endif; ?>
                </div>
                <?php endwhile; else: ?>
                <div class="flame-card" style="grid-column: 1 / -1; text-align: center; padding: 50px; opacity: 0.6;">
                    <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 20px; color: var(--text-muted);"></i>
                    <h3>No Vouchers Yet</h3>
                    <p>Redeem your points for vouchers in the Reward Hub!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include "includes/footer.php"; ?>
