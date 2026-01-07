<?php
session_start();
include "config/db.php";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($query);

    // Check for Hashed or Plaintext (for easier student management)
    $is_valid = false;
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $is_valid = true;
        } elseif ($password === $user['password']) {
            $is_valid = true;
        }
    }

    if ($is_valid) {
        // Maintenance Check
        $maint_q = mysqli_query($conn, "SELECT val FROM settings WHERE key_name='maintenance_mode'");
        $is_maint = (mysqli_fetch_assoc($maint_q)['val'] ?? '0') == '1';

        if ($is_maint && $user['role'] != 'admin') {
            header("Location: maintenance.php");
            exit();
        }

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Flavour's Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page page-transition-enter">

    <div class="auth-hero">
        <a href="index.php" class="btn-lava back-home">
            <i class="fas fa-arrow-left"></i> Back to Hub
        </a>
        <div class="hero-content fade-in" style="max-width: 600px;">
            <h1 class="heat-title text-gradient" style="font-size: 5rem; line-height: 0.9;">ENTER THE<br>INFERNO</h1>
            <p style="font-size: 1.2rem; color: var(--text-muted); margin-top: 20px;">
                Your seat at the Hall of Flame awaits. Sign in to track your sparks, claim rewards, and dominate the leaderboard.
            </p>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="login-container" style="width: 100%; max-width: 400px;">
            <div class="flame-card <?= isset($error) ? 'error-shake' : '' ?>">
                <div class="fade-in stagger-1" style="text-align: center;">
                    <div class="logo-icon" style="font-size: 4rem; margin-bottom: 15px; display: inline-block;">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h2 class="heat-title">Hub Access</h2>
                    <p style="color: var(--text-muted); margin-bottom: 35px;">Re-ignite your passion for flavour</p>
                </div>

                <?php if(isset($error)): ?>
                    <p style="color: #ff4444; margin-bottom: 20px; font-weight: 600; text-align: center;" class="fade-in"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" class="fade-in stagger-2">
                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 8px;">Email Address</label>
                        <input type="email" name="email" required placeholder="name@example.com" 
                            style="width: 100%; padding: 15px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; outline: none;">
                    </div>

                    <div style="margin-bottom: 35px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 8px;">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            style="width: 100%; padding: 15px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; outline: none;">
                    </div>

                    <button type="submit" name="login" class="btn-lava" style="width: 100%; justify-content: center; padding: 18px;">
                        Sign In <i class="fas fa-sign-in-alt"></i>
                    </button>
                </form>

                <p style="margin-top: 30px; color: var(--text-muted); font-size: 0.95rem; text-align: center;" class="fade-in stagger-3">
                    New to the Hub? <a href="register.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Join the Fire</a>
                </p>
            </div>
        </div>
    </div>

    <canvas id="emberCanvas" class="ember-canvas"></canvas>

    <script>
        const canvas = document.getElementById('emberCanvas');
        const ctx = canvas.getContext('2d');
        let w, h, particles = [];

        function init() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
            particles = [];
            for (let i = 0; i < 150; i++) particles.push(new Particle());
        }

        class Particle {
            constructor() { this.reset(); }
            reset() {
                this.x = Math.random() * w;
                this.y = h + Math.random() * 20;
                this.size = Math.random() * 2 + 1;
                this.speedX = Math.random() * 2 - 1;
                this.speedY = Math.random() * -2 - 0.5;
                this.alpha = 1;
                this.color = Math.random() > 0.5 ? '#ff4e00' : '#ec9f05';
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                this.alpha -= 0.003;
                if (this.alpha <= 0 || this.y < -20) this.reset();
            }
            draw() {
                ctx.globalAlpha = this.alpha;
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function animate() {
            ctx.clearRect(0,0,w,h);
            particles.forEach(p => { p.update(); p.draw(); });
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', init);
        init();
        animate();
    </script>
</body>
</html>
