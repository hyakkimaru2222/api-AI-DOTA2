<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();
$api = new OpenDotaAPI();

$proPlayers = $api->getProPlayers();

usort($proPlayers, function($a, $b) {
    return ($b['is_pro'] ?? 0) <=> ($a['is_pro'] ?? 0);
});

$proPlayers = array_slice($proPlayers, 0, 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Players - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="players-page-section">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-users"></i> Professional Dota 2 Players
                    </h1>
                    <p class="page-subtitle">Top professional players from around the world</p>
                </div>

                <div class="filter-bar">
                    <div class="filter-group">
                        <button class="filter-btn active">All Regions</button>
                        <button class="filter-btn"><i class="fas fa-globe-americas"></i> Americas</button>
                        <button class="filter-btn"><i class="fas fa-globe-europe"></i> Europe</button>
                        <button class="filter-btn"><i class="fas fa-globe-asia"></i> Asia</button>
                        <button class="filter-btn"><i class="fas fa-globe"></i> SEA</button>
                    </div>
                    
                    <div class="filter-input">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search players..." id="playerSearch">
                    </div>
                </div>

                <div class="players-grid">
                    <?php foreach ($proPlayers as $player): 
                        $name = $player['name'] ?? 'Unknown Player';
                        $teamName = $player['team_name'] ?? 'No Team';
                        $avatar = $player['avatarfull'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
                        $countryCode = $player['loccountrycode'] ?? 'UN';
                        $accountId = $player['account_id'] ?? 0;
                    ?>
                    <div class="player-card">
                        <div class="player-avatar-section">
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="player-avatar-lg">
                            <div class="player-status <?php echo $player['is_pro'] ? 'pro' : ''; ?>">
                                <?php echo $player['is_pro'] ? 'PRO' : 'Player'; ?>
                            </div>
                        </div>
                        
                        <div class="player-info-section">
                            <h3 class="player-name-display"><?php echo htmlspecialchars($name); ?></h3>
                            <p class="player-team"><?php echo htmlspecialchars($teamName); ?></p>
                            
                            <div class="player-meta">
                                <div class="meta-item">
                                    <i class="fas fa-flag"></i>
                                    <span><?php echo strtoupper($countryCode); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-id-card"></i>
                                    <span><?php echo formatNumber($accountId); ?></span>
                                </div>
                            </div>
                            
                            <a href="/player.php?id=<?php echo $accountId; ?>" class="view-profile-btn">
                                <i class="fas fa-user"></i> View Profile
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
