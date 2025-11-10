<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();

$api = new OpenDotaAPI();
$heroes = $api->getHeroes();
$heroStats = $api->getHeroStats();

// Organize heroes by primary attribute for filtering
$herosByAttr = [
    'str' => [],
    'agi' => [],
    'int' => [],
    'all' => []
];

foreach ($heroes as $hero) {
    $herosByAttr['all'][] = $hero;
    $attr = $hero['primary_attr'] ?? 'str';
    if (isset($herosByAttr[$attr])) {
        $herosByAttr[$attr][] = $hero;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D2PT Drafter - Professional Draft Analysis</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container-fluid">
            <div class="drafter-container">
                <!-- Header -->
                <div class="drafter-header">
                    <h1><i class="fas fa-chess"></i> D2PT Drafter</h1>
                    <p class="drafter-desc">Professional drafting tool with real-time analytics, counter-picks, and win probability</p>
                    <div class="drafter-controls">
                        <button class="btn-reset" onclick="resetDraft()"><i class="fas fa-redo"></i> Reset Draft</button>
                        <button class="btn-swap" onclick="swapSides()"><i class="fas fa-exchange-alt"></i> Swap Sides</button>
                    </div>
                </div>

                <div class="drafter-grid">
                    <!-- Left: Draft Board -->
                    <div class="draft-board">
                        <!-- Phase Indicator -->
                        <div class="phase-indicator">
                            <div class="current-phase">
                                <span class="phase-label">Current Phase:</span>
                                <span class="phase-name" id="phaseName">Ban Phase 1</span>
                            </div>
                            <div class="turn-indicator">
                                <span id="turnText">Radiant's Turn to Ban</span>
                            </div>
                        </div>

                        <!-- Teams Display -->
                        <div class="teams-display">
                            <!-- Radiant Team -->
                            <div class="team-section radiant-team">
                                <div class="team-header">
                                    <h3><i class="fas fa-sun"></i> Radiant</h3>
                                    <span class="win-prob" id="radiantWinProb">50%</span>
                                </div>
                                
                                <div class="team-bans">
                                    <div class="bans-label">Bans</div>
                                    <div class="ban-slots" id="radiantBans">
                                        <div class="ban-slot empty"></div>
                                        <div class="ban-slot empty"></div>
                                        <div class="ban-slot empty"></div>
                                        <div class="ban-slot empty"></div>
                                    </div>
                                </div>

                                <div class="team-picks">
                                    <div class="picks-grid" id="radiantPicks">
                                        <div class="pick-slot empty" data-position="carry">
                                            <span class="position-label">Carry</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="mid">
                                            <span class="position-label">Mid</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="offlane">
                                            <span class="position-label">Offlane</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="support4">
                                            <span class="position-label">Support</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="support5">
                                            <span class="position-label">Support</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dire Team -->
                            <div class="team-section dire-team">
                                <div class="team-header">
                                    <h3><i class="fas fa-moon"></i> Dire</h3>
                                    <span class="win-prob" id="direWinProb">50%</span>
                                </div>
                                
                                <div class="team-bans">
                                    <div class="bans-label">Bans</div>
                                    <div class="ban-slots" id="direBans">
                                        <div class="ban-slot empty"></div>
                                        <div class="ban-slot empty"></div>
                                        <div class="ban-slot empty"></div>
                                        <div class="ban-slot empty"></div>
                                    </div>
                                </div>

                                <div class="team-picks">
                                    <div class="picks-grid" id="direPicks">
                                        <div class="pick-slot empty" data-position="carry">
                                            <span class="position-label">Carry</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="mid">
                                            <span class="position-label">Mid</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="offlane">
                                            <span class="position-label">Offlane</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="support4">
                                            <span class="position-label">Support</span>
                                        </div>
                                        <div class="pick-slot empty" data-position="support5">
                                            <span class="position-label">Support</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Hero Selection & Analytics -->
                    <div class="selection-panel">
                        <!-- Hero Pool -->
                        <div class="hero-pool-section">
                            <div class="pool-header">
                                <h3>Hero Selection</h3>
                                <div class="filter-controls">
                                    <input type="text" id="heroSearch" placeholder="Search heroes..." class="search-input">
                                    <div class="attr-filters">
                                        <button class="attr-btn active" data-attr="all">All</button>
                                        <button class="attr-btn" data-attr="str"><img src="https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/icons/hero_strength.png" alt="STR" width="20"></button>
                                        <button class="attr-btn" data-attr="agi"><img src="https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/icons/hero_agility.png" alt="AGI" width="20"></button>
                                        <button class="attr-btn" data-attr="int"><img src="https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/icons/hero_intelligence.png" alt="INT" width="20"></button>
                                    </div>
                                </div>
                            </div>
                            <div class="hero-pool-grid" id="heroPool">
                                <?php foreach ($heroes as $hero): ?>
                                    <div class="hero-pool-card" 
                                         data-hero-id="<?php echo $hero['id']; ?>"
                                         data-hero-name="<?php echo htmlspecialchars($hero['localized_name']); ?>"
                                         data-attr="<?php echo $hero['primary_attr'] ?? 'str'; ?>"
                                         onclick="selectHero(<?php echo $hero['id']; ?>, '<?php echo htmlspecialchars($hero['localized_name']); ?>')">
                                        <img src="https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/<?php echo str_replace('npc_dota_hero_', '', $hero['name']); ?>.png" 
                                             alt="<?php echo htmlspecialchars($hero['localized_name']); ?>"
                                             loading="lazy">
                                        <span class="hero-name-label"><?php echo htmlspecialchars($hero['localized_name']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Analytics Tabs -->
                        <div class="analytics-section">
                            <div class="analytics-tabs">
                                <button class="tab-btn active" data-tab="suggestions">Suggestions</button>
                                <button class="tab-btn" data-tab="counters">Counters</button>
                                <button class="tab-btn" data-tab="synergy">Synergy</button>
                                <button class="tab-btn" data-tab="lanes">Lanes</button>
                            </div>

                            <div class="analytics-content">
                                <!-- Suggestions Tab -->
                                <div class="tab-panel active" id="suggestions">
                                    <h4><i class="fas fa-lightbulb"></i> Recommended Picks</h4>
                                    <div id="recommendedHeroes" class="suggestion-list">
                                        <p class="info-text">Start drafting to see hero recommendations</p>
                                    </div>
                                </div>

                                <!-- Counters Tab -->
                                <div class="tab-panel" id="counters">
                                    <h4><i class="fas fa-shield-alt"></i> Counter Picks</h4>
                                    <div id="counterPicks" class="counter-list">
                                        <p class="info-text">Enemy picks will show counter suggestions here</p>
                                    </div>
                                </div>

                                <!-- Synergy Tab -->
                                <div class="tab-panel" id="synergy">
                                    <h4><i class="fas fa-users"></i> Team Synergy Analysis</h4>
                                    <div id="synergyAnalysis">
                                        <div class="synergy-score">
                                            <div class="score-circle" id="synergyScore">0</div>
                                            <div class="score-label">Synergy Rating</div>
                                        </div>
                                        <div id="synergyDetails" class="synergy-details">
                                            <p class="info-text">Pick heroes to analyze team synergy</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lanes Tab -->
                                <div class="tab-panel" id="lanes">
                                    <h4><i class="fas fa-road"></i> Lane Matchups</h4>
                                    <div id="laneMatchups" class="lane-analysis">
                                        <p class="info-text">Lane predictions will appear as heroes are picked</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Initialize drafter data
        const heroes = <?php echo json_encode($heroes); ?>;
        const heroStats = <?php echo json_encode($heroStats); ?>;
    </script>
    <script src="assets/js/drafter.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
