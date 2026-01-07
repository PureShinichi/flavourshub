<?php
include "includes/header.php";
?>

<div class="page-hub-container">
    <div class="page-hub-wrapper">
        <!-- Hero Section -->
        <div class="profile-hero animate-up" style="margin-bottom: 60px;">
            <div style="z-index: 1; text-align: center; width: 100%;">
                <h1 class="heat-title" style="font-size: 5rem; letter-spacing: -4px; margin-bottom: 20px;">OUR <span class="text-gradient">PASSION</span></h1>
                <p style="color: var(--text-muted); font-size: 1.5rem; max-width: 800px; margin: 0 auto 40px auto; line-height: 1.6;">
                    We're more than just a food ordering platform. We're a community of food lovers dedicated to delivering perfection to your doorstep.
                </p>
                <!-- Fixed Hero Image using bimg1.png which exists in assets/img -->
                <div class="flame-card" style="padding: 0; border: none; border-radius: 40px; box-shadow: 0 0 100px rgba(255, 78, 0, 0.2); overflow: hidden;">
                    <img src="assets/img/bimg1.png" alt="Infernal Feast Hero" style="width: 100%; height: 600px; object-fit: cover; display: block; filter: brightness(0.8) contrast(1.1);">
                </div>
            </div>
        </div>

        <!-- Bento Grid Info -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 80px;">
            <div class="flame-card animate-up stagger-1">
                <div class="icon-circle" style="background: var(--flame-grad); color: white; width: 60px; height: 60px; font-size: 1.5rem; margin-bottom: 25px;">
                    <i class="fas fa-fire"></i>
                </div>
                <h3 class="heat-title" style="font-size: 1.8rem; margin-bottom: 15px;">Pure Taste</h3>
                <p style="color: var(--text-muted); line-height: 1.7;">Every dish is crafted with high-quality ingredients and a dash of "Flame" to ensure a memorable experience.</p>
            </div>
            <div class="flame-card animate-up stagger-2" style="grid-column: span 2; background: linear-gradient(135deg, rgba(255, 78, 0, 0.1), rgba(0,0,0,0.6));">
                <div class="icon-circle" style="background: var(--flame-grad); color: white; width: 60px; height: 60px; font-size: 1.5rem; margin-bottom: 25px;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="heat-title" style="font-size: 1.8rem; margin-bottom: 15px;">Instant Rewards</h3>
                <p style="color: var(--text-muted); line-height: 1.7; max-width: 90%;">Our unique points system rewards your loyalty. Every bite brings you closer to exclusive merchandise and legendary titles. Rise through the ranks and prove your culinary passion.</p>
                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <span class="badge" style="background: rgba(255,255,255,0.05); color: #fff; padding: 10px 20px; border-radius: 30px;">10+ Badges</span>
                    <span class="badge" style="background: rgba(255,255,255,0.05); color: #fff; padding: 10px 20px; border-radius: 30px;">Exclusive Merch</span>
                </div>
            </div>
        </div>

        <!-- Hall of Flame CTA -->
        <div class="flame-card animate-up stagger-3" style="padding: 80px; text-align: center; border-color: var(--primary-glow); background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('assets/img/bimg3.png'); background-size: cover; background-position: center;">
            <h2 class="heat-title" style="font-size: 3.5rem; letter-spacing: -2px; margin-bottom: 20px;">The Path of <span class="text-gradient">Legend</span></h2>
            <p style="color: var(--text-muted); font-size: 1.3rem; max-width: 700px; margin: 0 auto 40px auto; line-height: 1.6;">
                Discover our full badge collection and track your journey to becoming a Food God. Your achievements await in the Hall of Flame.
            </p>
            <a href="achievements.php" class="btn-lava" style="padding: 20px 50px; font-size: 1.2rem; text-transform: uppercase;">View Achievements</a>
        </div>

        <!-- Join Us Section -->
        <div style="text-align: center; padding: 100px 0;">
            <h2 class="heat-title" style="font-size: 3rem; margin-bottom: 30px;">REACH THE <span class="text-gradient">TOP</span></h2>
            <a href="order.php" class="btn-lava" style="padding: 20px 60px; font-size: 1.5rem; text-transform: uppercase;">Start Your Journey</a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
