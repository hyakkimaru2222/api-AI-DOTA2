<?php
$recentMatches = array_slice($proMatches, 0, 4);
?>

<section class="recent-matches-section">
    <h2 class="section-title">Recent High MMR Matches</h2>
    
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
    
    <div class="matches-list">
        <?php foreach ($recentMatches as $match): 
            $radiantWin = $match['radiant_win'] ?? (rand(0, 1) == 1);
            $duration = $match['duration'] ?? rand(1800, 3600);
            $startTime = $match['start_time'] ?? time() - rand(3600, 86400);
        ?>
        <div class="match-card">
            <div class="match-header">
                <div class="match-info">
                    <span class="match-rank">7000+ MMR</span>
                    <span class="match-time"><?php echo timeAgo($startTime); ?></span>
                    <span class="match-duration"><?php echo floor($duration / 60); ?> Pros</span>
                </div>
                <div class="match-result">
                    <span class="result-badge <?php echo $radiantWin ? 'radiant-victory' : 'dire-victory'; ?>">
                        <?php echo $radiantWin ? 'Radiant Victory' : 'Dire Victory'; ?>
                    </span>
                    <button class="open-match-btn">Open Match</button>
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
                            <div class="hero-items">
                                <?php for ($j = 0; $j < 6; $j++): ?>
                                <div class="item-slot"></div>
                                <?php endfor; ?>
                            </div>
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
                            <div class="hero-items">
                                <?php for ($j = 0; $j < 6; $j++): ?>
                                <div class="item-slot"></div>
                                <?php endfor; ?>
                            </div>
                            <div class="hero-kda"><?php echo $kda; ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
