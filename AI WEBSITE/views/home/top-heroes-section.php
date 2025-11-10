<?php
$rankedHeroes = $heroStats;
usort($rankedHeroes, function($a, $b) {
    $aWinRate = ($a['1_pick'] > 0) ? ($a['1_win'] / $a['1_pick']) : 0;
    $bWinRate = ($b['1_pick'] > 0) ? ($b['1_win'] / $b['1_pick']) : 0;
    return $bWinRate <=> $aWinRate;
});
?>

<section class="top-heroes-section">
    <div class="section-header-tabs">
        <h2 class="section-title">Top Heroes</h2>
        <h2 class="section-title inactive">Top Players</h2>
    </div>
    
    <div class="filter-bar">
        <div class="filter-group">
            <button class="filter-btn active">All</button>
            <button class="filter-btn"><i class="fas fa-sword"></i> Carry</button>
            <button class="filter-btn"><i class="fas fa-hat-wizard"></i> Mid</button>
            <button class="filter-btn"><i class="fas fa-shield"></i> Off</button>
            <button class="filter-btn"><i class="fas fa-hands-helping"></i> Pos 4</button>
            <button class="filter-btn"><i class="fas fa-hand-holding-heart"></i> Pos 5</button>
        </div>
        
        <div class="filter-input">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Type to filter by Hero">
        </div>
        
        <div class="filter-info">
            <span>7000+ MMR Immortal Pub Data</span>
            <select class="data-select">
                <option>Group Facets: ON</option>
                <option>Group Facets: OFF</option>
            </select>
        </div>
    </div>
    
    <div class="data-info-bar">
        Showing 7000+ MMR matches from last 8 days
    </div>
    
    <div class="heroes-table">
        <table>
            <thead>
                <tr>
                    <th>Hero <i class="fas fa-sort"></i></th>
                    <th>Winrate <i class="fas fa-sort-down"></i></th>
                    <th>Matches <i class="fas fa-sort"></i></th>
                    <th>Contest Rate <i class="fas fa-sort"></i></th>
                    <th>DOTA Rating <i class="fas fa-sort"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalMatches = 0;
                foreach ($rankedHeroes as $h) {
                    $totalMatches += ($h['1_pick'] ?? 0);
                }
                
                foreach (array_slice($rankedHeroes, 0, 6) as $index => $hero): 
                    $picks = $hero['1_pick'] ?? 0;
                    $wins = $hero['1_win'] ?? 0;
                    $winRate = $picks > 0 ? ($wins / $picks) * 100 : 0;
                    
                    $contestRate = $totalMatches > 0 ? ($picks / $totalMatches) * 100 : 0;
                    
                    $rating = round($winRate * $picks / 100);
                    $ratingColor = $rating > 1000 ? 'high' : ($rating > 500 ? 'medium' : 'low');
                ?>
                <tr>
                    <td class="hero-cell">
                        <div class="hero-info-cell">
                            <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>" class="hero-avatar-sm">
                            <div class="hero-details">
                                <span class="hero-name"><?php echo $hero['localized_name']; ?></span>
                                <span class="hero-badge">Randomed</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="winrate-cell">
                            <span class="winrate-value <?php echo $winRate >= 52 ? 'success' : ($winRate <= 48 ? 'danger' : ''); ?>">
                                <?php echo number_format($winRate, 2); ?>%
                            </span>
                            <div class="winrate-bar">
                                <div class="winrate-fill" style="width: <?php echo min($winRate, 100); ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="matches-cell"><?php echo formatNumber($picks); ?></td>
                    <td>
                        <div class="contest-cell">
                            <span><?php echo number_format($contestRate, 2); ?>%</span>
                            <div class="contest-bar">
                                <div class="contest-fill" style="width: <?php echo min($contestRate * 10, 100); ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="rating-badge rating-<?php echo $ratingColor; ?>"><?php echo formatNumber($rating); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
