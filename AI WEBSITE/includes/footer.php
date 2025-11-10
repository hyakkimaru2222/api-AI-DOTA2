<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?php echo SITE_NAME; ?></h3>
                <p><?php echo SITE_TAGLINE; ?></p>
                <p class="footer-note">Data powered by <a href="https://www.opendota.com" target="_blank">OpenDota API</a></p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/heroes.php">Heroes</a></li>
                    <li><a href="/players.php">Players</a></li>
                    <li><a href="/matches.php">Matches</a></li>
                    <li><a href="/meta.php">Meta Analysis</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Resources</h4>
                <ul>
                    <li><a href="/security.php"><i class="fas fa-shield-alt"></i> Security Features</a></li>
                    <li><a href="https://docs.opendota.com" target="_blank">API Documentation</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Connect</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-discord"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                    <a href="#"><i class="fab fa-twitch"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. Dota 2 is a registered trademark of Valve Corporation.</p>
        </div>
    </div>
</footer>

<!-- Security & Session Info -->
<div class="security-indicator">
    <i class="fas fa-shield-alt"></i>
    <span class="security-text">
        Secure Session • 
        Input Sanitized • 
        Security Headers
    </span>
</div>
