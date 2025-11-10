<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();
$api = new OpenDotaAPI();

$accountId = $_GET['id'] ?? null;

if (!$accountId) {
    header('Location: /players.php');
    exit;
}

$player = $api->getPlayer($accountId);
$recentMatches = $api->getPlayerRecentMatches($accountId);

$profile = $player['profile'] ?? null;
$personaname = $profile['personaname'] ?? 'Player Profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($personaname); ?> - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <?php if (empty($profile)): ?>
                <div class="no-live-matches">
                    <i class="fas fa-user-slash"></i>
                    <h3>Player Not Found</h3>
                    <p>The requested player profile could not be loaded.</p>
                    <a href="/players.php" class="link-primary">← Back to Players</a>
                </div>
            <?php else: 
                $avatar = $profile['avatarfull'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($personaname) . '&background=random';
                $countryCode = $profile['loccountrycode'] ?? 'UN';
            ?>
                <section class="player-profile-section">
                    <div class="page-header">
                        <h1 class="page-title">
                            <i class="fas fa-user"></i> Player Profile
                        </h1>
                    </div>

                    <div class="intro-grid">
                        <div class="intro-card">
                            <div class="player-avatar-section" style="height: auto;">
                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($personaname); ?>" class="player-avatar-lg" style="width: 100%; height: auto; max-width: 300px; margin: 0 auto; display: block; border-radius: 12px;">
                            </div>
                            <h2 style="text-align: center; margin-top: 20px;"><?php echo htmlspecialchars($personaname); ?></h2>
                            <div class="player-meta" style="justify-content: center; margin-top: 10px;">
                                <div class="meta-item">
                                    <i class="fas fa-flag"></i>
                                    <span><?php echo strtoupper($countryCode); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-id-card"></i>
                                    <span><?php echo formatNumber($accountId); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="intro-card">
                            <h2>Player Statistics</h2>
                            <div class="meta-overview" style="margin-top: 20px;">
                                <?php if (isset($player['rank_tier'])): ?>
                                <div class="meta-card">
                                    <div class="meta-card-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <div class="meta-card-content">
                                        <h3>Rank Tier</h3>
                                        <p class="meta-value"><?php echo $player['rank_tier']; ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($player['leaderboard_rank'])): ?>
                                <div class="meta-card">
                                    <div class="meta-card-icon" style="background: linear-gradient(135deg, #ffd700, #ffed4e);">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="meta-card-content">
                                        <h3>Leaderboard</h3>
                                        <p class="meta-value">#<?php echo formatNumber($player['leaderboard_rank']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($player['mmr_estimate']['estimate'])): ?>
                                <div class="meta-card">
                                    <div class="meta-card-icon" style="background: linear-gradient(135deg, #06b6d4, #14b8a6);">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="meta-card-content">
                                        <h3>Estimated MMR</h3>
                                        <p class="meta-value"><?php echo formatNumber($player['mmr_estimate']['estimate']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div style="margin-top: 20px;">
                                <a href="<?php echo htmlspecialchars($profile['profileurl'] ?? '#'); ?>" target="_blank" class="view-profile-btn">
                                    <i class="fab fa-steam"></i> View Steam Profile
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($recentMatches)): ?>
                    <section class="recent-matches-section" style="margin-top: 40px;">
                        <h2 class="section-title">Recent Matches</h2>
                        <div class="matches-list">
                            <?php foreach (array_slice($recentMatches, 0, 10) as $match): 
                                $isRadiant = $match['player_slot'] < 128;
                                $radiantWin = $match['radiant_win'] ?? false;
                                $playerWon = ($isRadiant && $radiantWin) || (!$isRadiant && !$radiantWin);
                            ?>
                            <div class="match-card">
                                <div class="match-header">
                                    <div class="match-info">
                                        <span><i class="fas fa-clock"></i> <?php echo timeAgo($match['start_time']); ?></span>
                                        <span><i class="fas fa-hourglass-half"></i> <?php echo formatDuration($match['duration']); ?></span>
                                        <span>Match ID: <?php echo $match['match_id']; ?></span>
                                    </div>
                                    <div class="match-result">
                                        <span class="result-badge <?php echo $playerWon ? 'radiant-victory' : 'dire-victory'; ?>">
                                            <?php echo $playerWon ? 'VICTORY' : 'DEFEAT'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div style="padding: 20px;">
                                    <div class="hero-info-cell">
                                        <img src="<?php echo getHeroImage($match['hero_id']); ?>" alt="Hero" class="hero-avatar-sm">
                                        <div>
                                            <div style="font-weight: 600; margin-bottom: 5px;">
                                                <span class="success"><?php echo $match['kills']; ?></span> / 
                                                <span class="danger"><?php echo $match['deaths']; ?></span> / 
                                                <span><?php echo $match['assists']; ?></span>
                                            </div>
                                            <div style="font-size: 13px; color: var(--text-muted);">
                                                <i class="fas fa-coins"></i> <?php echo formatNumber($match['gold_per_min']); ?> GPM
                                                <i class="fas fa-sword" style="margin-left: 10px;"></i> <?php echo formatNumber($match['xp_per_min']); ?> XPM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
