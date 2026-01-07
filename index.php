<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flavour's Hub | The Infernal Feast</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #000;
            overflow-x: hidden;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            position: relative;
            z-index: 10;
        }

        .hero-content {
            max-width: 900px;
        }

        .logo-glow {
            width: 120px;
            margin-bottom: 30px;
            filter: drop-shadow(0 0 20px var(--primary));
            animation: pulse-glow 3s infinite alternate;
        }

        @keyframes pulse-glow {
            0% { filter: drop-shadow(0 0 10px var(--primary)); transform: scale(1); }
            100% { filter: drop-shadow(0 0 30px var(--primary)); transform: scale(1.05); }
        }

        .main-title {
            font-size: clamp(4rem, 12vw, 8rem);
            line-height: 0.85;
            margin-bottom: 25px;
            font-weight: 900;
        }

        .tagline {
            font-size: clamp(1rem, 3vw, 1.5rem);
            color: var(--text-muted);
            margin-bottom: 50px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .feature-grid {
            padding: 100px 5%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .float {
            animation: float 6s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .corner-flame {
            position: fixed;
            bottom: -50px;
            width: 100%;
            height: 200px;
            background: linear-gradient(to top, rgba(255, 78, 0, 0.2), transparent);
            filter: blur(50px);
            z-index: 1;
        }
    </style>
</head>
<body class="page-transition-enter">

    <canvas id="emberCanvas" class="ember-canvas"></canvas>
    <div class="infernal-bg"></div>

    <section class="hero-section">
        <div class="hero-content fade-in stagger-1">
            <div class="logo-icon" style="font-size: 8rem; margin-bottom: 30px;">
                <i class="fas fa-fire"></i>
            </div>
            <h1 class="main-title heat-title text-gradient">FLAVOUR'S<br>HUB</h1>
            <p class="tagline">Where every bite sparks a reward. Join the elite community of food enthusiasts and conquer the Hall of Flame.</p>
            
            <div class="cta-group fade-in stagger-2">
                <a href="login.php" class="btn-lava">Enter the Hub <i class="fas fa-sign-in-alt"></i></a>
                <a href="register.php" class="btn-lava btn-lava-secondary">Join the Feast <i class="fas fa-user-plus"></i></a>
            </div>
        </div>
    </section>

    <div class="feature-grid">
        <div class="flame-card float stagger-1">
            <i class="fas fa-fire-alt" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px;"></i>
            <h3>The Hall of Flame</h3>
            <p style="color: var(--text-muted);">Climb the global leaderboard and earn legendary status. Only the truly dedicated reach the top.</p>
        </div>
        <div class="flame-card float stagger-2" style="animation-delay: 1s;">
            <i class="fas fa-star" style="font-size: 2.5rem; color: #ff9d00; margin-bottom: 20px;"></i>
            <h3>Earn Sparks</h3>
            <p style="color: var(--text-muted);">Order your favorite dishes and earn points with every purchase. Watch your influence grow.</p>
        </div>
        <div class="flame-card float stagger-3" style="animation-delay: 2s;">
            <i class="fas fa-gift" style="font-size: 2.5rem; color: #ec9f05; margin-bottom: 20px;"></i>
            <h3>Legendary Rewards</h3>
            <p style="color: var(--text-muted);">Redeem your hard-earned points for exclusive merchandise, vouchers, and secret menu items.</p>
        </div>
    </div>

    <div class="corner-flame"></div>

    <a href="contact.php" class="floating-contact">
        <div class="circling-flame" style="width: 100%; height: 100%;">
            <i class="fas fa-paper-plane"></i>
        </div>
    </a>

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
            constructor() {
                this.reset();
            }
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
            particles.forEach(p => {
                p.update();
                p.draw();
            });
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', init);
        init();
        animate();

        // Reveal animations on scroll
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.flame-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(50px)';
            card.style.transition = 'all 1s cubic-bezier(0.16, 1, 0.3, 1)';
            observer.observe(card);
        });
    </script>
</body>
</html>