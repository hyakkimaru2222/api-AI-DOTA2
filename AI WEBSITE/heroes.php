<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();
$api = new OpenDotaAPI();

$heroStats = $api->getHeroStats();
$heroes = $api->getHeroes();

$heroRolesMap = buildHeroRolesMap($heroes);

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
    <title>Heroes - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="heroes-page-section">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-user-shield"></i> Dota 2 Heroes Statistics
                    </h1>
                    <p class="page-subtitle">Comprehensive hero statistics from professional and high MMR matches</p>
                </div>

                <div class="filter-bar">
                    <div class="filter-group">
                        <button class="filter-btn active" data-role="all">All</button>
                        <button class="filter-btn" data-role="Carry"><i class="fas fa-sword"></i> Carry</button>
                        <button class="filter-btn" data-role="Mid"><i class="fas fa-hat-wizard"></i> Mid</button>
                        <button class="filter-btn" data-role="Offlane"><i class="fas fa-shield"></i> Offlane</button>
                        <button class="filter-btn" data-role="Support"><i class="fas fa-hands-helping"></i> Support</button>
                        <button class="filter-btn" data-role="Hard Support"><i class="fas fa-hand-holding-heart"></i> Hard Support</button>
                    </div>
                    
                    <div class="filter-input">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search heroes..." id="heroSearch">
                    </div>
                </div>

                <div class="heroes-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Hero <i class="fas fa-sort"></i></th>
                                <th>Winrate <i class="fas fa-sort-down"></i></th>
                                <th>Matches <i class="fas fa-sort"></i></th>
                                <th>Pick Rate <i class="fas fa-sort"></i></th>
                                <th>Contest Rate <i class="fas fa-sort"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalMatches = 0;
                            foreach ($heroStats as $h) {
                                $totalMatches += ($h['1_pick'] ?? 0);
                            }
                            
                            foreach ($heroStats as $index => $hero): 
                                $picks = $hero['1_pick'] ?? 0;
                                $wins = $hero['1_win'] ?? 0;
                                $winRate = $picks > 0 ? ($wins / $picks) * 100 : 0;
                                $contestRate = $totalMatches > 0 ? ($picks / $totalMatches) * 100 : 0;
                                $heroRole = getHeroRole($hero['id'], $heroRolesMap);
                            ?>
                            <?php 
                                $heroSlug = str_replace('npc_dota_hero_', '', $hero['name']);
                            ?>
                            <tr class="hero-row" data-role="<?php echo htmlspecialchars($heroRole); ?>" onclick="window.location='hero.php?hero=<?php echo urlencode($heroSlug); ?>'" style="cursor: pointer;">
                                <td class="hero-cell">
                                    <a href="hero.php?hero=<?php echo urlencode($heroSlug); ?>" class="hero-info-cell" style="display: flex; align-items: center; gap: 15px; text-decoration: none; color: inherit;">
                                        <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>" class="hero-avatar-sm">
                                        <div class="hero-details">
                                            <span class="hero-name"><?php echo $hero['localized_name']; ?></span>
                                            <span class="hero-badge"><?php echo $heroRole; ?></span>
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <div class="winrate-cell">
                                        <span class="winrate-value <?php echo $winRate >= 52 ? 'success' : ($winRate <= 48 ? 'danger' : ''); ?>">
                                            <?php echo number_format($winRate, 2); ?>%
                                        </span>
                                        <div class="winrate-bar">
                                            <div class="winrate-fill" style="width: <?php echo min($winRate, 100) . "%"; ?>"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="matches-cell"><?php echo formatNumber($picks); ?></td>
                                <td><?php echo number_format($contestRate, 2); ?>%</td>
                                <td>
                                    <div class="contest-cell">
                                        <span><?php echo number_format($contestRate, 2); ?>%</span>
                                        <div class="contest-bar">
                                            <div class="contest-fill" style="width: <?php echo min($contestRate * 10, 100) . "%"; ?>"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const role = this.getAttribute('data-role');
            const rows = document.querySelectorAll('.hero-row');
            
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            rows.forEach(row => {
                if (role === 'all' || row.getAttribute('data-role') === role) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    </script>
</body>
</html>
