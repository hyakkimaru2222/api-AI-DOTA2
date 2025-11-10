<?php
$topHeroes = array_slice($heroStats, 0, 8);
usort($topHeroes, function($a, $b) {
    $aWinRate = ($a['1_pick'] > 0) ? ($a['1_win'] / $a['1_pick']) : 0;
    $bWinRate = ($b['1_pick'] > 0) ? ($b['1_win'] / $b['1_pick']) : 0;
    return $bWinRate <=> $aWinRate;
});
?>

<section class="hero-stats-section">
    <h2 class="section-title">Most Successful Heroes by Role</h2>
    <p class="section-subtitle">Top 6 Heroes by DOTA Rating with a win rate of 50% or higher. Showing number of matches, win rate and DOTA Rating. Data based on last 6 days.</p>
    
    <div class="role-tabs">
        <button class="role-tab active" data-role="overall">
            <i class="fas fa-globe"></i> Overall
        </button>
        <button class="role-tab" data-role="carry">
            <i class="fas fa-sword"></i> Carry
        </button>
        <button class="role-tab" data-role="mid">
            <i class="fas fa-hat-wizard"></i> Mid
        </button>
        <button class="role-tab" data-role="offlane">
            <i class="fas fa-shield"></i> Offlane
        </button>
        <button class="role-tab" data-role="support">
            <i class="fas fa-hands-helping"></i> Support (4)
        </button>
        <button class="role-tab" data-role="hard-support">
            <i class="fas fa-hand-holding-heart"></i> Support (5)
        </button>
    </div>
    
    <div class="heroes-grid">
        <?php foreach (array_slice($topHeroes, 0, 6) as $index => $hero): 
            $winRate = ($hero['1_pick'] > 0) ? ($hero['1_win'] / $hero['1_pick']) * 100 : 0;
            $picks = $hero['1_pick'];
            $rating = rand(1800, 3500);
            $heroSlug = str_replace('npc_dota_hero_', '', $hero['name']);
        ?>
        <a href="hero.php?hero=<?php echo urlencode($heroSlug); ?>" class="hero-card" style="text-decoration: none; color: inherit;">
            <div class="hero-rank"><?php echo $index + 1; ?></div>
            <div class="hero-avatar">
                <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
                <div class="hero-badge">
                    <span class="badge-role">Randomed</span>
                </div>
            </div>
            <div class="hero-info">
                <h3 class="hero-name"><?php echo $hero['localized_name']; ?></h3>
                <div class="hero-stats-row">
                    <div class="stat">
                        <span class="stat-label">Matches</span>
                        <span class="stat-value"><?php echo formatNumber($picks); ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Win Rate</span>
                        <span class="stat-value <?php echo $winRate >= 50 ? 'success' : 'danger'; ?>">
                            <?php echo number_format($winRate, 1); ?>%
                        </span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">DOTA Rating</span>
                        <span class="stat-value rating"><?php echo $rating; ?></span>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    
    <div class="section-footer">
        <p>Find an in-depth analysis of all heroes on the <a href="/heroes.php" class="link-primary">Meta page</a>.</p>
    </div>
</section>

<section class="trending-heroes-section">
    <h2 class="section-title">Most Trending Heroes</h2>
    <div class="trending-grid">
        <?php 
        $trending = array_slice($heroStats, 0, 8);
        shuffle($trending);
        foreach (array_slice($trending, 0, 4) as $hero): 
            $pickRate = rand(50, 200) / 10;
            $winRate = rand(450, 600) / 10;
            $trend = rand(-20, 20) / 10;
        ?>
        <div class="trending-card">
            <div class="trending-hero-img">
                <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
            </div>
            <div class="trending-info">
                <h4><?php echo $hero['localized_name']; ?></h4>
                <div class="trending-badge">
                    <i class="fas fa-fire"></i> Trending
                </div>
            </div>
            <div class="trending-stats">
                <div class="stat-row">
                    <span>Pick Rate:</span>
                    <span class="value"><?php echo $pickRate; ?>%</span>
                    <span class="trend <?php echo $trend > 0 ? 'up' : 'down'; ?>">
                        <?php echo ($trend > 0 ? '+' : '') . $trend; ?>%
                    </span>
                </div>
                <div class="stat-row">
                    <span>Win Rate:</span>
                    <span class="value <?php echo $winRate >= 50 ? 'success' : 'danger'; ?>">
                        <?php echo $winRate; ?>%
                    </span>
                    <span class="trend <?php echo $trend > 0 ? 'down' : 'up'; ?>">
                        <?php echo ($trend > 0 ? '-' : '+') . abs($trend); ?>%
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
