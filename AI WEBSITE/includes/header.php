<?php
// Security: Ensure config and security are loaded
if (!defined('SITE_NAME')) {
    die('Direct access not allowed');
}
?>
<header class="site-header">
    <div class="header-banner">
        <img src="https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/backgrounds/featured_hero_bg.jpg" alt="Banner" class="banner-bg">
    </div>
    
    <nav class="main-nav">
        <div class="container">
            <div class="nav-wrapper">
                <div class="nav-brand">
                    <i class="fas fa-gamepad"></i>
                    <span class="brand-text"><?php echo htmlspecialchars(SITE_NAME); ?></span>

                    </span>
                </div>
                
                <ul class="nav-menu">
                <a href="website/" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
    <i class="fas fa-home"></i> Home
</a>
                    <li><a href="heroes.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'heroes.php' ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i> Heroes</a></li>
                    <li><a href="players.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'players.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Players</a></li>
                    <li><a href="matches.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'matches.php' ? 'active' : ''; ?>"><i class="fas fa-trophy"></i> Matches</a></li>
                    <li><a href="tournaments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'tournaments.php' ? 'active' : ''; ?>"><i class="fas fa-medal"></i> Tournaments</a></li>
                    <li><a href="meta.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'meta.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Meta</a></li>
                    <li><a href="live.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'live.php' ? 'active' : ''; ?>"><i class="fas fa-broadcast-tower"></i> Live</a></li>
                </ul>
                
                <div class="nav-search">
                    <input type="text" placeholder="Search for players, account id, heroes" id="searchInput" autocomplete="off">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                    <span class="search-hint">ctrl + K</span>
                </div>
                
                <div class="nav-icons">
                    <a href="#" class="icon-link"><i class="fab fa-discord"></i></a>
                    <a href="#" class="icon-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="icon-link"><i class="fab fa-youtube"></i></a>
                    <a href="security.php" class="icon-link security-link" title="View Security Features">
                    <i class="fas fa-shield-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>
