<section class="intro-section">
    <div class="intro-card welcome-card">
        <h2>Dota2ProTracker</h2>
        <p>Dota2ProTracker is a comprehensive statistics and analytics platform specializing in high-level Dota 2 competitive data. Our platform aggregates and analyzes match data exclusively from <strong>7000+ MMR ranked games</strong> and <strong>professional tournament matches</strong>, leveraging the <strong class="highlight">OpenDota API</strong> and <strong class="highlight">STRATZ GraphQL API</strong> for enhanced data enrichment.</p>
        <p>Our advanced analytics engine processes millions of data points to provide accurate insights into the current meta, optimal hero builds across all positions and facets, competitive trends, and strategic recommendations for players seeking to improve their competitive performance.</p>
        <div class="patch-info">
            <span class="patch-badge">Patch 7.39e</span>
            <span class="separator">•</span>
            <span>Released 25 days ago</span>
            <span class="separator">•</span>
            <a href="https://www.dota2.com/patches/7.39e" target="_blank" class="patch-notes">Official Patch Notes</a>
        </div>
    </div>
    
    <div class="intro-card chart-card">
        <div class="chart-header">
            <h3>Radiant vs Dire Win Rate (Pub Matches Since 7.39)</h3>
            <div class="chart-toggle">
                <button class="toggle-btn active" data-type="pubs">Pubs</button>
                <button class="toggle-btn" data-type="pro">Pro</button>
            </div>
        </div>
        <div class="win-rate-stats">
            <div class="stat-item radiant">
                <span class="label">Radiant Win Rate:</span>
                <span class="value success">54.0%</span>
            </div>
            <div class="stat-item dire">
                <span class="label">Dire Win Rate:</span>
                <span class="value danger">46.0%</span>
            </div>
            <div class="stat-item">
                <span class="label">Day: Roshan Bottom</span>
                <span class="separator">•</span>
                <span class="label">Night: Roshan Top</span>
            </div>
        </div>
        <canvas id="winRateChart" width="600" height="250"></canvas>
    </div>
</section>

<section class="hero-carousel-section">
    <div class="hero-carousel">
        <?php 
        $carouselHeroes = $heroStats;
        usort($carouselHeroes, function($a, $b) {
            $aPicks = ($a['1_pick'] ?? 0) + ($a['2_pick'] ?? 0) + ($a['3_pick'] ?? 0) + ($a['4_pick'] ?? 0) + ($a['5_pick'] ?? 0);
            $aWins = ($a['1_win'] ?? 0) + ($a['2_win'] ?? 0) + ($a['3_win'] ?? 0) + ($a['4_win'] ?? 0) + ($a['5_win'] ?? 0);
            $bPicks = ($b['1_pick'] ?? 0) + ($b['2_pick'] ?? 0) + ($b['3_pick'] ?? 0) + ($b['4_pick'] ?? 0) + ($b['5_pick'] ?? 0);
            $bWins = ($b['1_win'] ?? 0) + ($b['2_win'] ?? 0) + ($b['3_win'] ?? 0) + ($b['4_win'] ?? 0) + ($b['5_win'] ?? 0);
            
            $aRating = calculateD2PTRating($aWins, $aPicks);
            $bRating = calculateD2PTRating($bWins, $bPicks);
            return $bRating <=> $aRating;
        });
        
        $topHeroes = array_slice($carouselHeroes, 0, 16);
        foreach ($topHeroes as $hero): 
        ?>
        <a href="hero.php?hero=<?php echo generateHeroSlug($hero['name']); ?>" class="carousel-hero">
            <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>" loading="lazy">
        </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="drafter-promo">
    <h2>D2PT Drafter</h2>
    <p>Master your picks. Secure Victory.</p>
    <a href="drafter.php" class="drafter-btn">Start Drafting</a>
</section>
