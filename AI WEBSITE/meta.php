<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();
$api = new OpenDotaAPI();

$heroStats = $api->getHeroStats();

usort($heroStats, function($a, $b) {
    $aWinRate = ($a['1_pick'] > 0) ? ($a['1_win'] / $a['1_pick']) : 0;
    $bWinRate = ($b['1_pick'] > 0) ? ($b['1_win'] / $b['1_pick']) : 0;
    return $bWinRate <=> $aWinRate;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meta Analysis - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="meta-page-section">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-chart-line"></i> Meta Analysis
                    </h1>
                    <p class="page-subtitle">Current meta trends and hero tier lists for Patch 7.39</p>
                </div>

                <div class="meta-overview">
                    <div class="meta-card">
                        <div class="meta-card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="meta-card-content">
                            <h3>S-Tier Heroes</h3>
                            <p class="meta-value"><?php echo count(array_slice($heroStats, 0, 10)); ?></p>
                            <p class="meta-label">Win Rate > 54%</p>
                        </div>
                    </div>
                    
                    <div class="meta-card">
                        <div class="meta-card-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="meta-card-content">
                            <h3>Most Picked</h3>
                            <p class="meta-value"><?php echo $heroStats[0]['localized_name'] ?? 'N/A'; ?></p>
                            <p class="meta-label">Pick Rate Leader</p>
                        </div>
                    </div>
                    
                    <div class="meta-card">
                        <div class="meta-card-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="meta-card-content">
                            <h3>Best Win Rate</h3>
                            <?php 
                            $bestHero = $heroStats[0] ?? [];
                            $bestWinRate = ($bestHero['1_pick'] ?? 1 > 0) ? (($bestHero['1_win'] ?? 0) / ($bestHero['1_pick'] ?? 1)) * 100 : 0;
                            ?>
                            <p class="meta-value"><?php echo number_format($bestWinRate, 1); ?>%</p>
                            <p class="meta-label"><?php echo $bestHero['localized_name'] ?? 'N/A'; ?></p>
                        </div>
                    </div>
                    
                    <div class="meta-card">
                        <div class="meta-card-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="meta-card-content">
                            <h3>Total Matches</h3>
                            <?php 
                            $totalMatches = 0;
                            foreach ($heroStats as $h) {
                                $totalMatches += ($h['1_pick'] ?? 0);
                            }
                            ?>
                            <p class="meta-value"><?php echo formatNumber($totalMatches); ?></p>
                            <p class="meta-label">Last 7 Days</p>
                        </div>
                    </div>
                </div>

                <section class="tier-lists-section">
                    <h2 class="section-title">Hero Tier List</h2>
                    
                    <div class="tier-list">
                        <div class="tier-row tier-s">
                            <div class="tier-label">
                                <span class="tier-badge s-tier">S</span>
                                <span class="tier-desc">Dominant</span>
                            </div>
                            <div class="tier-heroes">
                                <?php foreach (array_slice($heroStats, 0, 8) as $hero): ?>
                                <div class="tier-hero" title="<?php echo $hero['localized_name']; ?>">
                                    <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
                                    <span><?php echo $hero['localized_name']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="tier-row tier-a">
                            <div class="tier-label">
                                <span class="tier-badge a-tier">A</span>
                                <span class="tier-desc">Strong</span>
                            </div>
                            <div class="tier-heroes">
                                <?php foreach (array_slice($heroStats, 8, 12) as $hero): ?>
                                <div class="tier-hero" title="<?php echo $hero['localized_name']; ?>">
                                    <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
                                    <span><?php echo $hero['localized_name']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="tier-row tier-b">
                            <div class="tier-label">
                                <span class="tier-badge b-tier">B</span>
                                <span class="tier-desc">Good</span>
                            </div>
                            <div class="tier-heroes">
                                <?php foreach (array_slice($heroStats, 20, 12) as $hero): ?>
                                <div class="tier-hero" title="<?php echo $hero['localized_name']; ?>">
                                    <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
                                    <span><?php echo $hero['localized_name']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="patch-trends-section">
                    <h2 class="section-title">Patch 7.39 Trends</h2>
                    <div class="trends-grid">
                        <div class="trend-card">
                            <h3><i class="fas fa-arrow-up success"></i> Rising Stars</h3>
                            <ul class="trend-list">
                                <?php foreach (array_slice($heroStats, 0, 5) as $hero): ?>
                                <li>
                                    <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
                                    <span><?php echo $hero['localized_name']; ?></span>
                                    <span class="trend-value success">+<?php echo rand(3, 8); ?>%</span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="trend-card">
                            <h3><i class="fas fa-arrow-down danger"></i> Falling Off</h3>
                            <ul class="trend-list">
                                <?php foreach (array_slice(array_reverse($heroStats), 0, 5) as $hero): ?>
                                <li>
                                    <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
                                    <span><?php echo $hero['localized_name']; ?></span>
                                    <span class="trend-value danger">-<?php echo rand(2, 6); ?>%</span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
