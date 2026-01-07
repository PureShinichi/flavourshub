<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . "/../config/db.php";

// Update query to fetch avatar
$user_id = $_SESSION['user_id'] ?? null;
$user_data = null;
if ($user_id) {
    // Check if column exists first to act resiliently if DB is lagging
    $check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'avatar_url'");
    $has_avatar = mysqli_num_rows($check) > 0;
    
    $fields = "username, points, badge, role" . ($has_avatar ? ", avatar_url" : "");
    $user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT $fields FROM users WHERE user_id=$user_id"));
}

// Maintenance Mode Check
$maint_q = mysqli_query($conn, "SELECT val FROM settings WHERE key_name='maintenance_mode'");
$is_maint = false;
if($maint_q) {
    $maint_data = mysqli_fetch_assoc($maint_q);
    $is_maint = ($maint_data['val'] ?? '0') == '1';
}

if($is_maint && (!isset($user_data['role']) || $user_data['role'] != 'admin')) {
    include __DIR__ . "/../maintenance.php";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/cosmic.css?v=<?= time() ?>">
    <style>
        :root {
            --primary: #ff4e00;
            --primary-light: #ff8c61;
            --secondary: #0a0a0a;
            --bg: #000000;
            --card: rgba(20, 20, 20, 0.85);
            --text: #ffffff;
            --text-muted: #a0b0c0;
            --glass: rgba(255, 107, 0, 0.05);
            --glass-border: rgba(255, 107, 0, 0.2);
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Nav Header */
        header {
            background: rgba(10, 10, 10, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 2000;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .logo-text { font-weight: 900; font-size: 1.5rem; letter-spacing: -1px; }

        nav { display: flex; gap: 30px; align-items: center; }
        nav a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        nav a:hover, nav a.active { color: var(--primary); }

        .points-pill {
            background: rgba(255, 78, 0, 0.1);
            border: 1px solid var(--primary);
            padding: 8px 18px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            color: var(--primary);
            box-shadow: 0 0 15px rgba(255, 78, 0, 0.2);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .profile-btn {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #ff4e00, #ff9d00);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba( 78, 0, 0.3);
            transition: 0.3s;
            overflow: hidden; /* Ensure image stays in bounds */
            padding: 0; /* Removing padding for image */
        }

        .profile-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 20px rgba(255, 78, 0, 0.5);
        }
        
        .profile-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logout-pill {
            width: 42px;
            height: 42px;
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff4444;
            text-decoration: none;
            transition: 0.3s;
        }

        .logout-pill:hover {
            background: rgba(255, 68, 68, 0.2);
            box-shadow: 0 0 20px rgba(255, 68, 68, 0.6), 0 0 40px rgba(255, 68, 68, 0.4);
            transform: scale(1.1);
        }

        .logout-pill i {
            transition: 0.3s;
        }

        .logout-pill:hover i {
            filter: drop-shadow(0 0 8px rgba(255, 68, 68, 0.8));
        }
    </style>
</head>
<body class="page-fade-in">

<header>
    <a href="dashboard.php" class="logo-container">
        <div class="logo-icon">
            <i class="fas fa-fire"></i>
        </div>
        <span class="logo-text text-gradient">FLAVOUR'S HUB</span>
    </a>

    <nav>
        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="order.php" class="<?= basename($_SERVER['PHP_SELF']) == 'order.php' ? 'active' : '' ?>">Order</a>
        <a href="rewards.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rewards.php' ? 'active' : '' ?>">Rewards</a>
        <a href="achievements.php" class="<?= basename($_SERVER['PHP_SELF']) == 'achievements.php' ? 'active' : '' ?>">Achievements</a>
        <a href="leaderboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : '' ?>">Hall of Flame</a>
        <a href="about.php" class="<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">About</a>
        <a href="contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contact</a>
    </nav>

<?php if (isset($user_data['role']) && $user_data['role'] == 'admin'): ?>
<!-- Admin Side Tab -->
<a href="admin_dashboard.php" class="admin-side-tab" title="Admin Command">
    <i class="fas fa-user-shield" style="transform: rotate(90deg); margin-bottom: 10px;"></i>
    ADMIN PANEL
</a>
<style>
.admin-side-tab {
    position: fixed;
    right: 0;
    top: 50vh !important;
    transform: translateY(-50%);
    margin: 0; /* Reset margins */
    background: rgba(10, 5, 2, 0.9);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 215, 0, 0.3);
    border-right: none;
    padding: 30px 12px;
    border-radius: 20px 0 0 20px;
    writing-mode: vertical-rl;
    text-orientation: mixed;
    color: #ffd700;
    font-weight: 900;
    letter-spacing: 3px;
    font-size: 0.85rem;
    cursor: pointer;
    z-index: 9999;
    text-decoration: none;
    box-shadow: -5px 0 25px rgba(0, 0, 0, 0.5);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.admin-side-tab:hover {
    padding-right: 25px; /* Slide out leftwards visually */
    background: #ffd700;
    color: #000;
    box-shadow: -10px 0 40px rgba(255, 215, 0, 0.4);
    text-shadow: none;
}
.admin-side-tab i {
    font-size: 1.2rem;
    transition: 0.3s;
}
</style>
<?php endif; ?>

    <div class="user-menu">
        <?php if ($user_data): ?>
            <div class="points-pill">
                <i class="fas fa-star"></i>
                <span><?= number_format($user_data['points']) ?></span>
            </div>
            <a href="profile.php" class="profile-btn" title="View Profile">
                <?php if(!empty($user_data['avatar_url'])): ?>
                    <img src="<?= $user_data['avatar_url'] ?>" alt="<?= $user_data['username'] ?>">
                <?php else: ?>
                    <?= strtoupper(substr($user_data['username'], 0, 1)) ?>
                <?php endif; ?>
            </a>
            <a href="logout.php" class="logout-pill" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        <?php else: ?>
            <a href="login.php" style="color: var(--primary); font-weight: 700;">Login</a>
        <?php endif; ?>
    </div>
</header>

<a href="contact.php" class="floating-contact">
    <div class="circling-flame" style="width: 100%; height: 100%;">
        <i class="fas fa-paper-plane"></i>
    </div>
</a>

<div class="sparks-container" id="sparks"></div>
<script>
    function createSpark() {
        const container = document.getElementById('sparks');
        const spark = document.createElement('div');
        spark.className = 'spark';
        const startX = Math.random() * 100;
        const drift = (Math.random() - 0.5) * 200;
        const duration = 2 + Math.random() * 4;
        const delay = Math.random() * 5;
        
        spark.style.left = startX + 'vw';
        spark.style.setProperty('--drift', drift + 'px');
        spark.style.animationDuration = duration + 's';
        spark.style.animationDelay = delay + 's';
        
        container.appendChild(spark);
        setTimeout(() => spark.remove(), (duration + delay) * 1000);
    }
    // Initial sparks
    for(let i=0; i<30; i++) createSpark();
    // Continuous generation
    setInterval(createSpark, 300);
</script>
<main>
