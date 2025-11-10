<?php
$facets = [
    'juggernaut' => ['icon' => 'agility', 'name' => 'Bladeform', 'desc' => 'Juggernaut gains Agility and Movement Speed as long as he\'s not taking damage.'],
    'queen_of_pain' => ['icon' => 'twin_hearts', 'name' => 'Bondage', 'desc' => 'Queen of Pain returns spell damage back to enemies. Succubus\'s Spell Lifesteal also applies to Pure & Magical reflected damage.'],
    'natures_prophet' => ['icon' => 'healing', 'name' => 'Soothing Saplings', 'desc' => 'After Sprout is cast, all trees in a 1200 radius heal nearby allies.'],
    'earthshaker' => ['icon' => 'area_of_effect', 'name' => 'Tectonic Buildup', 'desc' => 'Aftershock radius is increased by 40 for every level of Echo Slam.'],
    'pudge' => ['icon' => 'meat', 'name' => 'Fresh Meat', 'desc' => 'Dismember increases Strength when dealing damage to heroes.'],
    'disruptor' => ['icon' => 'area_of_effect', 'name' => 'Thunderstorm', 'desc' => 'Thunder Strike additionally hits all enemies within Kinetic Field. Thunder Strike Slow duration is doubled.'],
];
?>

<section class="heroes-by-role-section">
    <h2 class="section-title">Most Successful Heroes by Role</h2>
    <p class="section-subtitle">Top 6 Heroes by D2PT Rating with a win rate of 50% or higher. Showing number of matches, win rate and D2PT Rating. Data based on last 8 days.</p>
    
    <div class="role-tabs">
        <button class="role-tab active" data-role="overall">
            <span class="tab-label">Overall</span>
        </button>
        <button class="role-tab" data-role="1">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%23f59e0b'/%3E%3Ctext x='16' y='22' font-size='18' font-weight='bold' text-anchor='middle' fill='white'%3E1%3C/text%3E%3C/svg%3E" alt="Carry" class="pos-icon">
            <span class="tab-label">Carry</span>
        </button>
        <button class="role-tab" data-role="2">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%2306b6d4'/%3E%3Ctext x='16' y='22' font-size='18' font-weight='bold' text-anchor='middle' fill='white'%3E2%3C/text%3E%3C/svg%3E" alt="Mid" class="pos-icon">
            <span class="tab-label">Mid</span>
        </button>
        <button class="role-tab" data-role="3">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%238b5cf6'/%3E%3Ctext x='16' y='22' font-size='18' font-weight='bold' text-anchor='middle' fill='white'%3E3%3C/text%3E%3C/svg%3E" alt="Offlane" class="pos-icon">
            <span class="tab-label">Offlane</span>
        </button>
        <button class="role-tab" data-role="4">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%23fbbf24'/%3E%3Ctext x='16' y='22' font-size='18' font-weight='bold' text-anchor='middle' fill='white'%3E4%3C/text%3E%3C/svg%3E" alt="Support 4" class="pos-icon">
            <span class="tab-label">Support (4)</span>
        </button>
        <button class="role-tab" data-role="5">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='%23ec4899'/%3E%3Ctext x='16' y='22' font-size='18' font-weight='bold' text-anchor='middle' fill='white'%3E5%3C/text%3E%3C/svg%3E" alt="Support 5" class="pos-icon">
            <span class="tab-label">Support (5)</span>
        </button>
    </div>
    
    <div class="role-content active" data-role-content="overall">
        <div class="hero-cards-grid">
            <?php
            $overallHeroes = array_filter($heroStats, function($h) {
                $totalPicks = ($h['1_pick'] ?? 0) + ($h['2_pick'] ?? 0) + ($h['3_pick'] ?? 0) + ($h['4_pick'] ?? 0) + ($h['5_pick'] ?? 0);
                $totalWins = ($h['1_win'] ?? 0) + ($h['2_win'] ?? 0) + ($h['3_win'] ?? 0) + ($h['4_win'] ?? 0) + ($h['5_win'] ?? 0);
                $winRate = $totalPicks > 0 ? ($totalWins / $totalPicks) * 100 : 0;
                return $totalPicks > 500 && $winRate >= 50;
            });
            
            usort($overallHeroes, function($a, $b) {
                $aPicks = ($a['1_pick'] ?? 0) + ($a['2_pick'] ?? 0) + ($a['3_pick'] ?? 0) + ($a['4_pick'] ?? 0) + ($a['5_pick'] ?? 0);
                $aWins = ($a['1_win'] ?? 0) + ($a['2_win'] ?? 0) + ($a['3_win'] ?? 0) + ($a['4_win'] ?? 0) + ($a['5_win'] ?? 0);
                $bPicks = ($b['1_pick'] ?? 0) + ($b['2_pick'] ?? 0) + ($b['3_pick'] ?? 0) + ($b['4_pick'] ?? 0) + ($b['5_pick'] ?? 0);
                $bWins = ($b['1_win'] ?? 0) + ($b['2_win'] ?? 0) + ($b['3_win'] ?? 0) + ($b['4_win'] ?? 0) + ($b['5_win'] ?? 0);
                
                $aRating = calculateD2PTRating($aWins, $aPicks);
                $bRating = calculateD2PTRating($bWins, $bPicks);
                return $bRating <=> $aRating;
            });
            
            foreach (array_slice($overallHeroes, 0, 6) as $hero):
                $totalPicks = ($hero['1_pick'] ?? 0) + ($hero['2_pick'] ?? 0) + ($hero['3_pick'] ?? 0) + ($hero['4_pick'] ?? 0) + ($hero['5_pick'] ?? 0);
                $totalWins = ($hero['1_win'] ?? 0) + ($hero['2_win'] ?? 0) + ($hero['3_win'] ?? 0) + ($hero['4_win'] ?? 0) + ($hero['5_win'] ?? 0);
                $winRate = $totalPicks > 0 ? ($totalWins / $totalPicks) * 100 : 0;
                $rating = calculateD2PTRating($totalWins, $totalPicks);
                $facet = getFacetForHero($hero['localized_name'], $facets);
            ?>
            <a href="hero.php?hero=<?php echo generateHeroSlug($hero['name']); ?>" class="hero-card-d2pt">
                <div class="hero-card-header">
                    <img src="<?php echo getHeroMiniIcon($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>" class="hero-mini-icon">
                    <div class="facet-badge" title="<?php echo htmlspecialchars($facet['desc']); ?>">
                        <i class="fas fa-gem"></i>
                    </div>
                </div>
                <div class="hero-card-facet">
                    <?php echo $hero['localized_name']; ?>: <?php echo $facet['name']; ?>
                </div>
                <div class="hero-card-facet-desc"><?php echo $facet['desc']; ?></div>
                <div class="hero-card-name"><?php echo $hero['localized_name']; ?></div>
                <div class="hero-card-stats">
                    <span class="stat-matches"><?php echo formatNumber($totalPicks); ?></span>
                    <span class="stat-winrate <?php echo $winRate >= 52 ? 'high' : ''; ?>"><?php echo number_format($winRate, 1); ?>%</span>
                    <span class="stat-rating"><?php echo formatNumber($rating); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php for ($role = 1; $role <= 5; $role++): ?>
    <div class="role-content" data-role-content="<?php echo $role; ?>">
        <div class="hero-cards-grid">
            <?php
            $roleHeroes = getTopHeroesByRole($heroStats, $role);
            foreach ($roleHeroes as $hero):
                $picks = $hero[$role . '_pick'] ?? 0;
                $wins = $hero[$role . '_win'] ?? 0;
                $winRate = $picks > 0 ? ($wins / $picks) * 100 : 0;
                $rating = calculateD2PTRating($wins, $picks);
                $facet = getFacetForHero($hero['localized_name'], $facets);
            ?>
            <a href="hero.php?hero=<?php echo generateHeroSlug($hero['name']); ?>" class="hero-card-d2pt">
                <div class="hero-card-header">
                    <img src="<?php echo getHeroMiniIcon($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>" class="hero-mini-icon">
                    <div class="facet-badge" title="<?php echo htmlspecialchars($facet['desc']); ?>">
                        <i class="fas fa-gem"></i>
                    </div>
                </div>
                <div class="hero-card-facet">
                    <?php echo $hero['localized_name']; ?>: <?php echo $facet['name']; ?>
                </div>
                <div class="hero-card-facet-desc"><?php echo $facet['desc']; ?></div>
                <div class="hero-card-name"><?php echo $hero['localized_name']; ?></div>
                <div class="hero-card-stats">
                    <span class="stat-matches"><?php echo formatNumber($picks); ?></span>
                    <span class="stat-winrate <?php echo $winRate >= 52 ? 'high' : ''; ?>"><?php echo number_format($winRate, 1); ?>%</span>
                    <span class="stat-rating"><?php echo formatNumber($rating); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endfor; ?>
</section>
