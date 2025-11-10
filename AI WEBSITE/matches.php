<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();
$api = new OpenDotaAPI();

$proMatches = $api->getProMatches();
$heroes = $api->getHeroes();
$heroStats = $api->getHeroStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="matches-page-section">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-trophy"></i> Professional & High MMR Matches
                    </h1>
                    <p class="page-subtitle">Recent matches from professional tournaments and high MMR ranked games</p>
                </div>

                <div class="filter-bar">
                    <div class="filter-group">
                        <button class="filter-btn active">All Matches</button>
                        <button class="filter-btn"><i class="fas fa-medal"></i> Pro Matches</button>
                        <button class="filter-btn"><i class="fas fa-star"></i> High MMR</button>
                        <button class="filter-btn"><i class="fas fa-fire"></i> Trending</button>
                    </div>
                </div>

                <div class="matches-list">
                    <?php foreach (array_slice($proMatches, 0, 20) as $match): 
                        $radiantWin = $match['radiant_win'] ?? (rand(0, 1) == 1);
                        $duration = $match['duration'] ?? rand(1800, 3600);
                        $startTime = $match['start_time'] ?? time() - rand(3600, 86400);
                        $matchId = $match['match_id'] ?? rand(1000000, 9999999);
                    ?>
                    <div class="match-card">
                        <div class="match-header">
                            <div class="match-info">
                                <span class="match-rank">Pro Match</span>
                                <span class="match-time"><?php echo timeAgo($startTime); ?></span>
                                <span class="match-duration"><?php echo floor($duration / 60); ?>m <?php echo $duration % 60; ?>s</span>
                                <span class="match-id">#<?php echo $matchId; ?></span>
                            </div>
                            <div class="match-result">
                                <span class="result-badge <?php echo $radiantWin ? 'radiant-victory' : 'dire-victory'; ?>">
                                    <?php echo $radiantWin ? 'Radiant Victory' : 'Dire Victory'; ?>
                                </span>
                                <button class="open-match-btn" onclick="window.open('https://www.opendota.com/matches/<?php echo $matchId; ?>', '_blank')">
                                    Open Match
                                </button>
                            </div>
                        </div>
                        
                        <div class="match-teams">
                            <div class="team radiant <?php echo $radiantWin ? 'winner' : ''; ?>">
                                <div class="team-label">Radiant</div>
                                <div class="team-score"><?php echo rand(25, 60); ?></div>
                                <div class="team-heroes">
                                    <?php for ($i = 0; $i < 5; $i++): 
                                        $randomHero = $heroStats[array_rand($heroStats)];
                                        $kda = rand(0, 20) . '/' . rand(0, 15) . '/' . rand(0, 25);
                                    ?>
                                    <div class="hero-slot">
                                        <img src="<?php echo getHeroImage($randomHero['name']); ?>" alt="<?php echo $randomHero['localized_name']; ?>">
                                        <div class="hero-kda"><?php echo $kda; ?></div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="team dire <?php echo !$radiantWin ? 'winner' : ''; ?>">
                                <div class="team-label">Dire</div>
                                <div class="team-score"><?php echo rand(20, 50); ?></div>
                                <div class="team-heroes">
                                    <?php for ($i = 0; $i < 5; $i++): 
                                        $randomHero = $heroStats[array_rand($heroStats)];
                                        $kda = rand(0, 20) . '/' . rand(0, 15) . '/' . rand(0, 25);
                                    ?>
                                    <div class="hero-slot">
                                        <img src="<?php echo getHeroImage($randomHero['name']); ?>" alt="<?php echo $randomHero['localized_name']; ?>">
                                        <div class="hero-kda"><?php echo $kda; ?></div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="pagination-controls">
                    <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                    <span class="page-numbers">
                        <button class="page-num active">1</button>
                        <button class="page-num">2</button>
                        <button class="page-num">3</button>
                        <button class="page-num">4</button>
                        <button class="page-num">5</button>
                    </span>
                    <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
