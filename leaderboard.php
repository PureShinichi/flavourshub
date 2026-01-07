<?php
include "includes/header.php";

// Self-Healing: Ensure avatar_url exists
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar_url'");
if(mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL");
}

// Fetch Leaderboard (Top 50) - Exclude Admins
$sql = "SELECT username, points, badge, user_id, avatar_url FROM users WHERE role != 'admin' ORDER BY points DESC LIMIT 50";
$res = mysqli_query($conn, $sql);
$rank = 1;
$leaders = [];
while($row = mysqli_fetch_assoc($res)) {
    $row['rank'] = $rank++;
    $leaders[] = $row;
}

function renderAvatar($u, $size='large') {
    $initial = strtoupper(substr($u['username'], 0, 1));
    if(!empty($u['avatar_url'])) {
        return "<div class='avatar-$size' style='background-image: url(\"{$u['avatar_url']}\"); background-size: cover; background-position: center;'></div>";
    }
    return "<div class='avatar-$size'>$initial</div>";
}
?>

<div class="leaderboard-wrapper page-fade"><div class="fire-bg"></div>
<main>
    <section class="leaderboard-header animate-slide-down">
        <h1 class="heat-title" style="font-size: 3.5rem; letter-spacing: -2px;">HALL OF <span class="text-gradient">FLAME</span></h1>
        <p style="font-size: 1.1rem; opacity: 0.8;">The top legends of Flavour's Hub.</p>
    </section>

    <div class="podium-section">
        <!-- Second Place -->
        <?php if(isset($leaders[1])): $u = $leaders[1]; ?>
        <div class="glass-card podium-card silver animate-up stagger-2">
            <div class="rank-badge">#2</div>
            <?= renderAvatar($u, 'large') ?>
            <h3><?= htmlspecialchars($u['username']) ?></h3>
            <span class="pts-pill"><?= number_format($u['points']) ?> PTS</span>
            <small class="badge-tag"><?= $u['badge'] ?></small>
        </div>
        <?php endif; ?>

        <!-- First Place -->
        <?php if(isset($leaders[0])): $u = $leaders[0]; ?>
        <div class="glass-card podium-card gold animate-up stagger-1">
            <div class="crown-icon"><i class="fas fa-crown"></i></div>
            <div class="rank-badge">#1</div>
            <?= renderAvatar($u, 'xl') ?>
            <h2 class="text-gradient-gold"><?= htmlspecialchars($u['username']) ?></h2>
            <div class="pts-pill large"><?= number_format($u['points']) ?> PTS</div>
            <small class="badge-tag" style="color: #ffd700;"><?= $u['badge'] ?></small>
            <div class="fire-particles"></div>
        </div>
        <?php endif; ?>

        <!-- Third Place -->
        <?php if(isset($leaders[2])): $u = $leaders[2]; ?>
        <div class="glass-card podium-card bronze animate-up stagger-3">
            <div class="rank-badge">#3</div>
            <?= renderAvatar($u, 'large') ?>
            <h3><?= htmlspecialchars($u['username']) ?></h3>
            <span class="pts-pill"><?= number_format($u['points']) ?> PTS</span>
            <small class="badge-tag"><?= $u['badge'] ?></small>
        </div>
        <?php endif; ?>
    </div>

    <div class="glass-card list-container animate-up stagger-4">
        <div class="list-header">
            <span>Rank</span>
            <span>User</span>
            <span>Title</span>
            <span style="text-align: right;">Points</span>
        </div>
        <div class="leader-list">
            <?php foreach($leaders as $u): if($u['rank'] <= 3) continue; ?>
            <div class="leader-item <?= $u['user_id'] == $user_id ? 'is-me' : '' ?>">
                <span class="rank">#<?= $u['rank'] ?></span>
                <div class="user-info">
                    <div class="mini-avatar" style="<?= !empty($u['avatar_url']) ? "background-image: url('{$u['avatar_url']}'); background-size: cover;" : "" ?>">
                        <?= empty($u['avatar_url']) ? strtoupper(substr($u['username'],0,1)) : '' ?>
                    </div>
                    <strong><?= htmlspecialchars($u['username']) ?></strong>
                </div>
                <span class="badge-mini"><?= $u['badge'] ?></span>
                <span class="points-val"><?= number_format($u['points']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
/* Revamped Styles */
.leaderboard-header { text-align: center; margin-bottom: 50px; }
.text-gradient-gold { background: linear-gradient(135deg, #ffd700, #ffeb3b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

.podium-section { 
    display: flex; justify-content: center; align-items: flex-end; gap: 30px; margin-bottom: 60px; padding: 0 20px;
}

.podium-card { 
    flex: 1; max-width: 250px; text-align: center; position: relative; 
    display: flex; flex-direction: column; align-items: center;
    padding: 30px 20px; border-radius: 25px; transition: 0.3s;
}

/* Gold Card (Center) */
/* Gold Card (Animated) */
.podium-card.gold { 
    max-width: 320px; padding: 50px 20px;
    background: linear-gradient(180deg, rgba(255, 215, 0, 0.15) 0%, rgba(10, 5, 2, 0.9) 100%);
    border: 1px solid rgba(255, 215, 0, 0.4);
    box-shadow: 0 0 60px rgba(255, 215, 0, 0.25);
    z-index: 10; transform: translateY(-30px);
    animation: cosmicPulse 4s infinite alternate;
}
.podium-card.gold::before {
    content: ''; position: absolute; inset: -2px; z-index: -1;
    background: linear-gradient(45deg, #ff00cc, #ffaf00, #00e676);
    filter: blur(20px); opacity: 0.3;
    animation: cosmicShift 6s linear infinite;
    border-radius: 30px;
}

/* Silver Card (Animated) */
.podium-card.silver { 
    background: linear-gradient(180deg, rgba(192, 192, 192, 0.1) 0%, rgba(10, 5, 2, 0.8) 100%); 
    border: 1px solid rgba(192, 192, 192, 0.3);
    animation: podiumFloat 4s infinite alternate ease-in-out;
    position: relative;
    box-shadow: 0 0 40px rgba(192, 192, 192, 0.2);
}
.podium-card.silver::before {
    content: ''; position: absolute; inset: -10px; z-index: -1;
    background: radial-gradient(circle, #ffffff, #a5a5a5);
    filter: blur(20px); opacity: 0.2;
    animation: smolder 3s infinite alternate ease-in-out;
    border-radius: 50%;
}

/* Bronze Card (Animated) */
.podium-card.bronze { 
    background: linear-gradient(180deg, rgba(205, 127, 50, 0.1) 0%, rgba(10, 5, 2, 0.8) 100%); 
    border: 1px solid rgba(205, 127, 50, 0.3);
    animation: podiumFloat 4s infinite alternate ease-in-out;
    animation-delay: 1s;
    position: relative;
    box-shadow: 0 0 40px rgba(205, 127, 50, 0.2);
}
.podium-card.bronze::before {
    content: ''; position: absolute; inset: -10px; z-index: -1;
    background: radial-gradient(circle, #ff8c00, #cd7f32);
    filter: blur(20px); opacity: 0.3;
    animation: smolder 3s infinite alternate ease-in-out;
    animation-delay: 1s;
    border-radius: 50%;
}

/* Restored Animations */
@keyframes podiumFloat {
    0% { transform: translateY(0); }
    100% { transform: translateY(-15px); }
}
@keyframes smolder {
    0% { opacity: 0.2; transform: scale(0.9); }
    100% { opacity: 0.5; transform: scale(1.1); }
}
@keyframes cosmicPulse { 
    0% { box-shadow: 0 0 40px rgba(255,215,0,0.3); transform: translateY(-30px) scale(1); } 
    100% { box-shadow: 0 0 80px rgba(255,215,0,0.6); transform: translateY(-30px) scale(1.02); } 
}
@keyframes cosmicShift { 0% { filter: hue-rotate(0deg) blur(20px); } 100% { filter: hue-rotate(360deg) blur(20px); } }

/* Avatars */
.avatar-xl { width: 110px; height: 110px; border-radius: 50%; border: 4px solid #ffd700; margin-bottom: 20px; background-color: #222; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; color: #fff; font-weight: 800; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
.avatar-large { width: 80px; height: 80px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); margin-bottom: 15px; background-color: #222; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; }

.crown-icon { position: absolute; top: -35px; color: #ffd700; font-size: 3rem; filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.6)); animation: floatCrown 3s infinite ease-in-out; }
@keyframes floatCrown { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

.rank-badge { position: absolute; top: 15px; right: 15px; width: 30px; height: 30px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }

.mini-avatar { width: 40px; height: 40px; border-radius: 50%; background-color: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-weight: 700; overflow: hidden; }

/* List Item Upgrade */
.list-container { padding: 0 5px; }
.list-header, .leader-item {
    display: grid;
    grid-template-columns: 80px 2fr 1.5fr 150px;
    align-items: center;
    padding: 20px;
    gap: 15px;
}
.list-header {
    border-bottom: 2px solid rgba(255,255,255,0.05);
    font-weight: 800;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.85rem;
}
.leader-item { 
    border-bottom: 1px solid rgba(255,255,255,0.03); 
    transition: 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.leader-item:hover { 
    background: rgba(255,255,255,0.04); 
    transform: scale(1.01); 
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    z-index: 2;
    border-radius: 10px;
    border-bottom-color: transparent;
}
.leader-item.is-me { 
    background: linear-gradient(90deg, rgba(255, 78, 0, 0.15), transparent); 
    border-left: 4px solid #ff4e00; 
    border-bottom: none; 
}

.user-info { display: flex; align-items: center; gap: 15px; }
.mini-avatar { width: 45px; height: 45px; flex-shrink: 0; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
.rank { font-weight: 900; font-size: 1.2rem; color: #555; text-align: center; }
.leader-item:hover .rank { color: white; }

.badge-mini {
    background: rgba(255,255,255,0.05);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #888;
    display: inline-block;
    border: 1px solid rgba(255,255,255,0.05);
}
.leader-item:hover .badge-mini {
    border-color: rgba(255,255,255,0.2);
    color: #ccc;
    background: rgba(255,255,255,0.1);
}

/* Restored & Enhanced Point Styles */
.pts-pill { 
    background: rgba(255, 255, 255, 0.08); 
    padding: 8px 18px; 
    border-radius: 20px; 
    font-weight: 800; 
    color: #fff; 
    margin: 10px 0; 
    border: 1px solid rgba(255, 255, 255, 0.1); 
    backdrop-filter: blur(5px);
    display: inline-block;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.pts-pill.large { 
    font-size: 1.4rem; 
    background: linear-gradient(135deg, #ff4e00, #ff9100); 
    color: white; 
    border: none; 
    box-shadow: 0 10px 25px rgba(255, 78, 0, 0.4); 
    padding: 10px 30px;
    margin-top: 15px;
    animation: pulseGlow 2s infinite ease-in-out;
}
@keyframes pulseGlow { 0% { box-shadow: 0 5px 15px rgba(255, 78, 0, 0.4); } 50% { box-shadow: 0 5px 25px rgba(255, 78, 0, 0.7); } 100% { box-shadow: 0 5px 15px rgba(255, 78, 0, 0.4); } }

.badge-tag { 
    color: rgba(255, 255, 255, 0.6); 
    font-weight: 700; 
    text-transform: uppercase; 
    font-size: 0.75rem; 
    letter-spacing: 1px; 
    display: block; 
    margin-top: 8px; 
}

.points-val { 
    text-align: right; 
    font-weight: 900; 
    font-size: 1.1rem;
    background: linear-gradient(90deg, #ff9100, #ff4e00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 20px rgba(255, 78, 0, 0.3);
}


@media (max-width: 768px) {
    .podium-section { flex-direction: column; align-items: center; }
    .podium-card { width: 100%; max-width: 100%; transform: none !important; }
    .list-header, .leader-item { grid-template-columns: 60px 1fr 100px; }
    .badge-mini { display: none; }
}
</style>

<?php include "includes/footer.php"; ?>
