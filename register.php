<?php
include "config/db.php";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email' LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $error = "Induction Failed: Email already legends in the fire!";
    } else {
        mysqli_query($conn, 
            "INSERT INTO users (username, email, password) 
             VALUES ('$username', '$email', '$password')"
        );
        header("Location: login.php?registered=true");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Us | Flavour's Hub</title>
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
            <h1 class="heat-title text-gradient" style="font-size: 5rem; line-height: 0.9;">CLAIM YOUR<br>STATUS</h1>
            <p style="font-size: 1.2rem; color: var(--text-muted); margin-top: 20px;">
                Do not just eat, dominate. Create your account to start earning sparks and climbing the ranks of the elite.
            </p>
        </div>
    </div>

    <div class="auth-form-side">
        <div class="reg-container" style="width: 100%; max-width: 400px;">
            <div class="flame-card">
                <div class="fade-in stagger-1" style="text-align: center;">
                    <div class="logo-icon" style="font-size: 4rem; margin-bottom: 15px; display: inline-block;">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h2 class="heat-title">Hub Induction</h2>
                    <p style="color: var(--text-muted); margin-bottom: 25px;">Become a legend in the flames</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="fade-in" style="background: rgba(255, 68, 68, 0.1); border: 1px solid #ff4444; color: #ff4444; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; text-align: center;">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="fade-in stagger-2">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 8px;">Full Name</label>
                        <input type="text" name="username" required placeholder="John Doe"
                            style="width: 100%; padding: 15px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; outline: none;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 8px;">Email Address</label>
                        <input type="email" name="email" required placeholder="john@example.com"
                            style="width: 100%; padding: 15px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; outline: none;">
                    </div>

                    <div style="margin-bottom: 35px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 8px;">Secure Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            style="width: 100%; padding: 15px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; outline: none;">
                    </div>

                    <button type="submit" name="register" class="btn-lava" style="width: 100%; justify-content: center; padding: 18px;">
                        Create Account <i class="fas fa-user-plus"></i>
                    </button>
                </form>

                <p style="margin-top: 30px; color: var(--text-muted); font-size: 0.95rem; text-align: center;" class="fade-in stagger-3">
                    Already part of the fire? <a href="login.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Induct Here</a>
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
