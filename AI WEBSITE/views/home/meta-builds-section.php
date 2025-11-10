<?php
$buildHeroes = array_slice($heroStats, 0, 4);
usort($buildHeroes, function($a, $b) {
    $aPicks = $a['1_pick'] ?? 0;
    $bPicks = $b['1_pick'] ?? 0;
    return $bPicks <=> $aPicks;
});
?>

<section class="meta-builds-section">
    <div class="section-header-link">
        <h2 class="section-title">Top Heroes by Pick Rate</h2>
        <a href="#" class="view-all">View All →</a>
    </div>
    
    <div class="builds-grid">
        <?php foreach (array_slice($buildHeroes, 0, 4) as $hero): 
            $picks = $hero['1_pick'] ?? 0;
            $wins = $hero['1_win'] ?? 0;
            $winRate = $picks > 0 ? ($wins / $picks) * 100 : 50;
            
            $pickRate = 0;
            foreach ($heroStats as $hs) {
                $pickRate += ($hs['1_pick'] ?? 0);
            }
            $contestRate = $pickRate > 0 ? ($picks / $pickRate) * 100 : 0;
            
            $items = ['blink', 'black_king_bar', 'assault', 'heart', 'shivas_guard', 'overwhelming_blink'];
        ?>
        <div class="build-card">
            <div class="build-header">
                <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>" class="build-hero-img">
                <div class="build-info">
                    <h3><?php echo $hero['localized_name']; ?></h3>
                    <div class="build-badges">
                        <span class="role-badge">Popular</span>
                        <span class="result-badge <?php echo $winRate > 50 ? 'win' : 'loss'; ?>"><?php echo $winRate > 50 ? 'High WR' : 'Balanced'; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="build-meta-score">
                <div class="score-label">Pick Rate:</div>
                <div class="score-bar">
                    <div class="score-fill" style="width: <?php echo min($contestRate * 10, 100); ?>%">
                        <span><?php echo number_format($contestRate, 1); ?>%</span>
                    </div>
                </div>
            </div>
            
            <div class="build-items">
                <?php foreach ($items as $item): ?>
                <div class="item-icon">
                    <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $item; ?>">
                </div>
                <?php endforeach; ?>
            </div>
            
            <button class="view-build-btn">
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="section-footer">
        <p>Data from OpenDota API showing most picked heroes. Visit <a href="/heroes.php" class="link-primary">Heroes</a> for detailed statistics.</p>
    </div>
</section>
