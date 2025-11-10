<?php
$api = new OpenDotaAPI();
$proPlayers = $api->getProPlayers();
$topProPlayers = array_slice($proPlayers, 0, 3);
?>

<section class="tournament-section">
    <h2 class="section-title">Recent Top Tournament Performances</h2>
    <p class="section-subtitle">Esports for these matches are publicly available</p>
    
    <div class="role-filter-tabs">
        <button class="filter-tab active">All</button>
        <button class="filter-tab"><i class="fas fa-sword"></i> Carry</button>
        <button class="filter-tab"><i class="fas fa-hat-wizard"></i> Mid</button>
        <button class="filter-tab"><i class="fas fa-shield"></i> Offlane</button>
        <button class="filter-tab"><i class="fas fa-hands-helping"></i> Support</button>
        <button class="filter-tab"><i class="fas fa-hand-holding-heart"></i> Hard Support</button>
    </div>
    
    <div class="performances-list">
        <?php foreach ($topProPlayers as $player): 
            $teamName = $player['team_name'] ?? ($player['name'] ?? 'Pro Player');
            $accountId = $player['account_id'] ?? 0;
            
            $playerMatches = [];
            $matchCount = 0;
            foreach ($proMatches as $match) {
                if (isset($match['radiant_team_id']) || isset($match['dire_team_id'])) {
                    $playerMatches[] = $match;
                    $matchCount++;
                    if ($matchCount >= 5) break;
                }
            }
            
            $matchHeroes = [];
            foreach ($playerMatches as $pm) {
                $heroId = $pm['hero_id'] ?? 0;
                foreach ($heroes as $h) {
                    if ($h['id'] == $heroId) {
                        $matchHeroes[] = $h;
                        break;
                    }
                }
            }
            
            if (empty($matchHeroes)) {
                $matchHeroes = array_slice($heroStats, 0, 5);
            }
        ?>
        <div class="performance-card">
            <div class="performance-header">
                <div class="player-info">
                    <div class="player-avatar">
                        <img src="<?php echo $player['avatarfull'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($player['name'] ?? 'Player') . '&background=random'; ?>" alt="Player">
                    </div>
                    <div class="player-details">
                        <div class="tournament-name"><?php echo $teamName; ?></div>
                        <div class="player-name"><?php echo $player['name'] ?? 'Pro Player'; ?></div>
                    </div>
                </div>
                
                <div class="performance-stats">
                    <div class="stat">
                        <span class="stat-value"><?php echo count($playerMatches); ?></span>
                        <span class="stat-label">Matches</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo count($matchHeroes); ?></span>
                        <span class="stat-label">Heroes</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $player['is_pro'] ? 'Pro' : 'Player'; ?></span>
                        <span class="stat-label">Status</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo formatNumber($accountId); ?></span>
                        <span class="stat-label">ID</span>
                    </div>
                </div>
                
                <div class="performance-actions">
                    <button class="action-btn"><i class="fas fa-copy"></i></button>
                    <button class="action-btn"><i class="fas fa-external-link-alt"></i></button>
                </div>
            </div>
            
            <div class="performance-heroes">
                <?php foreach ($matchHeroes as $hero): ?>
                <div class="performance-hero">
                    <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="pagination-controls">
        <button class="page-btn" disabled><i class="fas fa-chevron-left"></i></button>
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

<section class="tournaments-carousel-section">
    <h2 class="section-title">Tournaments</h2>
    
    <div class="carousel-controls">
        <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
    </div>
    
    <div class="tournaments-carousel">
        <?php 
        $tournaments = [
            ['team1' => 'TSpirit', 'team2' => 'Falcons', 'date' => 'October 18 at 03:00 PM'],
            ['team1' => 'Tundra', 'team2' => 'HEROIC', 'date' => 'October 18 at 10:00 PM'],
            ['team1' => 'Aurora', 'team2' => 'Liquid', 'date' => 'October 18 at 10:00 PM'],
            ['team1' => 'Xtreme', 'team2' => 'Ykiros', 'date' => 'October 18 at 10:00 PM'],
        ];
        foreach ($tournaments as $tournament): 
        ?>
        <div class="tournament-card">
            <div class="tournament-time">
                <i class="far fa-calendar"></i>
                <span><?php echo $tournament['date']; ?></span>
            </div>
            <div class="tournament-match">
                <div class="team">
                    <img src="https://ui-avatars.com/api/?name=<?php echo $tournament['team1']; ?>&background=random" alt="<?php echo $tournament['team1']; ?>">
                    <span><?php echo $tournament['team1']; ?></span>
                </div>
                <span class="vs">VS</span>
                <div class="team">
                    <img src="https://ui-avatars.com/api/?name=<?php echo $tournament['team2']; ?>&background=random" alt="<?php echo $tournament['team2']; ?>">
                    <span><?php echo $tournament['team2']; ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
