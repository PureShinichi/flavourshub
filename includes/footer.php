</main>

<footer>
    <div class="footer-content">
        <div class="footer-logo">
            <div class="logo-icon border-0 p-0" style="font-size: 2rem;">
                <i class="fas fa-fire"></i>
            </div>
            <h3>Flavour's Hub</h3>
        </div>
        <p>&copy; <?= date('Y') ?> Flavour's Hub. All rights reserved.</p>
        <div class="footer-links">
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-facebook"></i></a>
        </div>
    </div>
</footer>

<style>
footer {
    border-top: 1px solid var(--glass-border);
    padding: 60px 5% 30px 5%;
    margin-top: 80px;
    background: rgba(10, 10, 20, 0.5);
}

.footer-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    text-align: center;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 15px;
}



.footer-logo h3 {
    margin: 0;
    font-weight: 800;
}

.footer-content p {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.footer-links {
    display: flex;
    gap: 20px;
    font-size: 1.5rem;
}

.footer-links a {
    color: var(--text-muted);
    transition: 0.3s;
}

.footer-links a:hover {
    color: var(--primary);
    transform: translateY(-3px);
}
</style>

</body>
</html>
