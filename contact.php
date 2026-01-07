<?php
include "includes/header.php";

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    // Defensive check: Ensure table exists
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `contacts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) DEFAULT NULL,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `message` text NOT NULL,
      `rating` int(11) DEFAULT 0,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $u_id = $_SESSION['user_id'] ?? 'NULL';

    $q = "INSERT INTO contacts (user_id, name, email, message, rating) VALUES ($u_id, '$name', '$email', '$message', $rating)";
    if (mysqli_query($conn, $q)) {
        $msg = "success";
    } else {
        $msg = "error";
    }
}
?>

<div class="contact-wrapper page-fade-in">
    <div style="text-align: center; margin-bottom: 50px;">
        <h1 class="hero-title">Experience <span class="text-gradient">Feedback</span></h1>
        <p class="hero-sub" style="margin: 0 auto; max-width: 600px;">Tell us about your journey at Flavour's Hub. Your voice fuels our flames.</p>
    </div>

    <?php if($msg == "success"): ?>
        <div class="glass-card animate-up" style="border-color: #00e676; margin-bottom: 30px; text-align: center; padding: 30px;">
            <i class="fas fa-check-circle" style="font-size: 3rem; color: #00e676; margin-bottom: 15px;"></i>
            <h2 style="margin: 0;">Message Sent!</h2>
            <p style="color: var(--text-muted);">Thank you for the feedback. Your points are growing!</p>
            <a href="dashboard.php" class="btn-premium" style="display: inline-flex; margin-top: 20px;">Back to Dashboard</a>
        </div>
    <?php endif; ?>

    <div class="contact-grid">
        <div class="glass-card info-card">
            <h3>Contact Info</h3>
            <div class="info-list">
                <div class="info-item">
                    <div class="icon-box"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <p class="info-label">Location</p>
                        <p class="info-val">123 Flavor Lane, Spice City</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="icon-box"><i class="fas fa-phone"></i></div>
                    <div>
                        <p class="info-label">Phone</p>
                        <p class="info-val">+60 12-345 6789</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="icon-box"><i class="fas fa-envelope"></i></div>
                    <div>
                        <p class="info-label">Email</p>
                        <p class="info-val">support@flavourshub.com</p>
                    </div>
                </div>
            </div>
            
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>

        <div class="glass-card form-card" id="contact-form-container">
            <h3>Send a Message</h3>
            <form action="contact.php" method="POST" style="margin-top: 25px;">
                <div class="rating-box">
                    <p style="margin-bottom: 15px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; font-size: 0.8rem;">How was your experience?</p>
                    <div class="star-rating" id="star-rating">
                        <input type="radio" name="rating" value="5" id="5"><label for="5" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="4" id="4"><label for="4" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="3" id="3"><label for="3" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="2" id="2"><label for="2" class="fas fa-star"></label>
                        <input type="radio" name="rating" value="1" id="1"><label for="1" class="fas fa-star"></label>
                    </div>
                    <div id="flame-aura"></div>
                </div>

                <div class="input-grid">
                    <input type="text" name="name" placeholder="Your Name" required value="<?= $_SESSION['username'] ?? '' ?>">
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <textarea name="message" placeholder="Your Feedback / Message" rows="5" required></textarea>
                
                <button type="submit" name="send_message" class="btn-premium submit-btn">
                    <span>Dispatch Message</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.contact-wrapper { max-width: 1100px; margin: 0 auto; padding: 60px 20px; position: relative; }
.contact-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; align-items: start; }

.info-card { padding: 40px; }
.info-list { margin-top: 40px; }
.info-item { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
.icon-box { 
    width: 50px; height: 50px; border-radius: 15px; background: rgba(255, 78, 0, 0.1); 
    display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.2rem;
    border: 1px solid rgba(255, 78, 0, 0.2);
}
.info-label { font-weight: 800; margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
.info-val { color: var(--text-muted); margin: 0; font-size: 1rem; }

.social-links { display: flex; gap: 15px; margin-top: 40px; }
.social-links a { 
    width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.05); 
    display: flex; align-items: center; justify-content: center; color: white; transition: 0.3s;
}
.social-links a:hover { background: var(--primary); transform: translateY(-3px); }

.form-card { padding: 40px; position: relative; overflow: hidden; }
.input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-card input, .form-card textarea {
    width: 100%; padding: 18px; border-radius: 15px; 
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); 
    color: white; font-family: inherit; transition: 0.3s;
}
.form-card input:focus, .form-card textarea:focus {
    background: rgba(255,255,255,0.06); border-color: var(--primary); outline: none;
}
.submit-btn { width: 100%; justify-content: center; padding: 18px; font-size: 1.1rem; }

/* Star Rating System */
.rating-box { margin-bottom: 30px; text-align: center; position: relative; }
.star-rating {
    display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px;
}
.star-rating input { display: none; }
.star-rating label {
    font-size: 2.5rem; color: rgba(255,255,255,0.1); cursor: pointer; transition: 0.3s;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #ffc107;
    text-shadow: 0 0 20px rgba(255, 193, 7, 0.5);
}

/* Flame Aura for 5 Stars */
#flame-aura {
    position: absolute; inset: 0; z-index: -1; pointer-events: none; opacity: 0; transition: 0.5s;
    background: radial-gradient(circle, rgba(255, 78, 0, 0.15) 0%, transparent 70%);
}
.stars-5 #flame-aura { opacity: 1; }
.stars-5 h1 { animation: textFlame 1.5s infinite alternate; }

@keyframes textFlame {
    from { text-shadow: 0 0 10px var(--primary); }
    to { text-shadow: 0 0 30px var(--primary), 0 0 60px #ff0000; }
}

@media (max-width: 768px) {
    .contact-grid { grid-template-columns: 1fr; }
}
</style>

<script>
document.querySelectorAll('.star-rating input').forEach(radio => {
    radio.addEventListener('change', (e) => {
        const val = e.target.value;
        const container = document.getElementById('contact-form-container');
        if (val == 5) {
            container.classList.add('stars-5');
            triggerGlobalFlames();
        } else {
            container.classList.remove('stars-5');
        }
    });
});

function triggerGlobalFlames() {
    // Inject extra sparks for a moment
    for(let i=0; i<50; i++) {
        setTimeout(createSpark, i * 20);
    }
}
</script>

<?php include "includes/footer.php"; ?>

