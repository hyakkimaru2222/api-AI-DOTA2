<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();

$api = new OpenDotaAPI();
$heroName = $_GET['hero'] ?? '';

if (empty($heroName)) {
    header('Location: heroes.php');
    exit;
}

$heroes = $api->getHeroes();
$hero = null;
foreach ($heroes as $h) {
    $cleanName = str_replace('npc_dota_hero_', '', $h['name']);
    if ($cleanName === $heroName || strtolower($h['localized_name']) === strtolower($heroName)) {
        $hero = $h;
        break;
    }
}

if (!$hero) {
    header('Location: heroes.php');
    exit;
}

$heroStats = $api->getHeroStats();
$stats = null;
foreach ($heroStats as $s) {
    if ($s['id'] === $hero['id']) {
        $stats = $s;
        break;
    }
}

// Calculate total stats across all roles
$totalMatches = ($stats['1_pick'] ?? 0) + ($stats['2_pick'] ?? 0) + ($stats['3_pick'] ?? 0) + ($stats['4_pick'] ?? 0) + ($stats['5_pick'] ?? 0);
$totalWins = ($stats['1_win'] ?? 0) + ($stats['2_win'] ?? 0) + ($stats['3_win'] ?? 0) + ($stats['4_win'] ?? 0) + ($stats['5_win'] ?? 0);
$totalWinRate = $totalMatches > 0 ? ($totalWins / $totalMatches) * 100 : 0;

// Define facets for the hero (hardcoded for now, would need API data)
$heroSlug = strtolower(str_replace(' ', '_', $hero['localized_name']));
$facetsData = [
    'morphling' => [
        ['name' => 'Ebb', 'icon' => 'ebb', 'matches' => 1426, 'winrate' => 47.5],
        ['name' => 'Flow', 'icon' => 'flow', 'matches' => 28874, 'winrate' => 44.2],
    ],
    'juggernaut' => [
        ['name' => 'Bladestorm', 'icon' => 'spinning', 'matches' => 438, 'winrate' => 53.7],
        ['name' => 'Bladeform', 'icon' => 'agility', 'matches' => 2897, 'winrate' => 53.9],
    ],
    'axe' => [
        ['name' => 'One Man Army', 'icon' => 'strength', 'matches' => 120200, 'winrate' => 51.7],
        ['name' => 'Berserker', 'icon' => 'attack', 'matches' => 472400, 'winrate' => 51.8],
    ],
];

// Get facets for this hero, or empty array if not defined
$facets = $facetsData[$heroSlug] ?? [];

// Role data
$roles = [
    1 => ['name' => 'Carry', 'icon' => 'pos_1', 'color' => '#f59e0b'],
    2 => ['name' => 'Mid', 'icon' => 'pos_2', 'color' => '#06b6d4'],
    3 => ['name' => 'Offlane', 'icon' => 'pos_3', 'color' => '#8b5cf6'],
    4 => ['name' => 'Support', 'icon' => 'pos_4', 'color' => '#fbbf24'],
    5 => ['name' => 'Hard Support', 'icon' => 'pos_5', 'color' => '#ec4899'],
];

$roleStats = [];
$mostPlayedRole = 1;
$maxPicks = 0;

foreach ($roles as $pos => $role) {
    $picks = $stats[$pos . '_pick'] ?? 0;
    $wins = $stats[$pos . '_win'] ?? 0;
    $wr = $picks > 0 ? ($wins / $picks) * 100 : 0;
    
    $roleStats[$pos] = [
        'picks' => $picks,
        'wins' => $wins,
        'winrate' => $wr
    ];
    
    if ($picks > $maxPicks) {
        $maxPicks = $picks;
        $mostPlayedRole = $pos;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hero['localized_name']); ?> - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Hero Header -->
            <div class="d2pt-hero-header">
                <img src="<?php echo getHeroImage($hero['name']); ?>" alt="<?php echo $hero['localized_name']; ?>" class="d2pt-hero-img">
                <div class="d2pt-hero-info">
                    <h1><?php echo htmlspecialchars($hero['localized_name']); ?></h1>
                    <div class="d2pt-hero-stats-header">
                        <div class="stat-box success">
                            <div class="stat-value"><?php echo formatNumber($totalMatches); ?></div>
                            <div class="stat-label">matches</div>
                        </div>
                        <div class="stat-box <?php echo $totalWinRate >= 50 ? 'success' : 'danger'; ?>">
                            <div class="stat-value"><?php echo number_format($totalWinRate, 0); ?>%</div>
                            <div class="stat-label">winrate</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Stats Notice -->
            <p class="role-notice">Role stats are based on 7000-8500 MMR matches from the last 8 days. Click on Tabs to switch roles.</p>

            <!-- Position Tabs -->
            <div class="d2pt-position-tabs">
                <button class="d2pt-pos-tab" data-position="all">
                    <div class="pos-label-row">
                        <span class="pos-label-text">All Roles</span>
                    </div>
                    <div class="pos-stats-row">
                        <span class="pos-matches"><?php echo formatNumber($totalMatches); ?></span>
                        <span class="pos-winrate <?php echo $totalWinRate >= 50 ? 'success' : 'danger'; ?>">
                            <?php echo number_format($totalWinRate, 0); ?>%
                        </span>
                    </div>
                    <div class="pos-label-small">matches / win rate</div>
                </button>

                <?php foreach ($roles as $pos => $role): 
                    $picks = $roleStats[$pos]['picks'];
                    $wr = $roleStats[$pos]['winrate'];
                    $isMostPlayed = $pos === $mostPlayedRole;
                ?>
                <button class="d2pt-pos-tab <?php echo $isMostPlayed ? 'active' : ''; ?>" data-position="<?php echo $pos; ?>">
                    <div class="pos-label-row">
                        <i class="fas fa-sword pos-icon"></i>
                        <span class="pos-label-text"><?php echo $role['name']; ?></span>
                    </div>
                    <div class="pos-stats-row">
                        <span class="pos-matches"><?php echo formatNumber($picks); ?></span>
                        <span class="pos-winrate <?php echo $wr >= 50 ? 'success' : 'danger'; ?>">
                            <?php echo number_format($wr, 0); ?>%
                        </span>
                    </div>
                    <div class="pos-label-small">matches / win rate</div>
                    <?php if ($isMostPlayed && $picks > 0): ?>
                    <div class="most-played-badge">Most Played</div>
                    <?php endif; ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Mobile Position Selector -->
            <div class="d2pt-position-mobile">
                <button class="mobile-pos-btn" data-position="all">
                    <span>All</span>
                    <div class="mobile-stats">
                        <span><?php echo formatNumber($totalMatches); ?></span>
                        <span class="<?php echo $totalWinRate >= 50 ? 'success' : 'danger'; ?>">
                            <?php echo number_format($totalWinRate, 0); ?>%
                        </span>
                    </div>
                    <?php if ($totalMatches === $maxPicks): ?><div class="mobile-badge">Most Played</div><?php endif; ?>
                </button>
                <?php foreach ($roles as $pos => $role): 
                    $picks = $roleStats[$pos]['picks'];
                    $wr = $roleStats[$pos]['winrate'];
                ?>
                <button class="mobile-pos-btn <?php echo $pos === $mostPlayedRole ? 'active' : ''; ?>" data-position="<?php echo $pos; ?>">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='15' fill='<?php echo urlencode($role['color']); ?>'/%3E%3Ctext x='16' y='22' font-size='18' font-weight='bold' text-anchor='middle' fill='white'%3E<?php echo $pos; ?>%3C/text%3E%3C/svg%3E" class="mobile-pos-icon" alt="<?php echo $role['name']; ?>">
                    <div class="mobile-stats">
                        <span><?php echo formatNumber($picks); ?></span>
                        <span class="<?php echo $wr >= 50 ? 'success' : 'danger'; ?>">
                            <?php echo number_format($wr, 0); ?>%
                        </span>
                    </div>
                    <?php if ($pos === $mostPlayedRole && $picks > 0): ?><div class="mobile-badge">Most Played</div><?php endif; ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Role Content Sections -->
            <?php foreach (array_merge(['all'], array_keys($roles)) as $position): 
                $isActive = ($position === $mostPlayedRole) || ($position === 'all' && $maxPicks === 0);
                $posPicks = $position === 'all' ? $totalMatches : ($roleStats[$position]['picks'] ?? 0);
                $posWins = $position === 'all' ? $totalWins : ($roleStats[$position]['wins'] ?? 0);
                $posWinRate = $posPicks > 0 ? ($posWins / $posPicks) * 100 : 0;
                $roleName = $position === 'all' ? 'All Roles' : $roles[$position]['name'];
            ?>
            <div class="d2pt-role-content" data-role-section="<?php echo $position; ?>" style="display: <?php echo $isActive ? 'block' : 'none'; ?>;">
                <h2 class="role-content-title">
                    <?php echo htmlspecialchars($hero['localized_name']); ?> stats for 
                    <?php if ($position !== 'all'): ?>
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='<?php echo urlencode($roles[$position]['color']); ?>'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3C/svg%3E" class="role-icon-inline" alt="">
                    <?php endif; ?>
                    <?php echo $roleName; ?>
                </h2>

                <div class="view-toggle">
                    <button class="view-btn active" data-view="normal">Normal View</button>
                    <button class="view-btn" data-view="table">Table View</button>
                    <button class="view-btn" data-view="fullscreen"><i class="fas fa-expand"></i></button>
                </div>

                <!-- Facet Statistics -->
                <section class="facet-statistics">
                    <h3>Facet Statistics</h3>
                    <div class="facet-grid">
                        <div class="facet-card all-facets">
                            <div class="facet-icon-circle">All</div>
                            <div class="facet-name">All Facets</div>
                            <div class="facet-stats">
                                <?php
                                // Calculate total across all facets for this hero
                                $totalFacetMatches = array_sum(array_column($facets, 'matches'));
                                $totalFacetWins = 0;
                                foreach ($facets as $f) {
                                    $totalFacetWins += ($f['matches'] * $f['winrate'] / 100);
                                }
                                $allFacetsWR = $totalFacetMatches > 0 ? ($totalFacetWins / $totalFacetMatches) * 100 : $totalWinRate;
                                // Use total hero stats if no facets defined
                                $displayMatches = $totalFacetMatches > 0 ? $totalFacetMatches : $totalMatches;
                                $displayWR = $totalFacetMatches > 0 ? $allFacetsWR : $totalWinRate;
                                ?>
                                <span class="facet-matches"><?php echo formatNumber($displayMatches); ?> matches</span>
                                <span class="facet-wr <?php echo $displayWR >= 50 ? 'success' : 'danger'; ?>">
                                    <?php echo number_format($displayWR, 1); ?>%
                                </span>
                            </div>
                        </div>
                        <?php foreach ($facets as $facet): ?>
                        <div class="facet-card">
                            <div class="facet-icon-circle"><i class="fas fa-gem"></i></div>
                            <div class="facet-name"><?php echo htmlspecialchars($facet['name']); ?></div>
                            <div class="facet-stats">
                                <span class="facet-matches"><?php echo formatNumber($facet['matches']); ?> matches</span>
                                <span class="facet-wr <?php echo $facet['winrate'] >= 50 ? 'success' : 'danger'; ?>">
                                    <?php echo number_format($facet['winrate'], 1); ?>%
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Performance Trends -->
                <section class="performance-trends">
                    <h3>Performance Trends (Last 8 Weeks)</h3>
                    <div class="trends-grid">
                        <div class="trend-chart-box">
                            <div class="trend-header">
                                <span>Win Rate</span>
                                <span class="trend-change <?php echo $posWinRate >= 50 ? 'positive' : 'negative'; ?>">
                                    <?php echo number_format($posWinRate - 50, 1); ?>%
                                </span>
                            </div>
                            <canvas id="winRateTrend" height="100"></canvas>
                            <div class="trend-footer">
                                <span class="trend-start"><?php echo number_format($posWinRate * 0.95, 1); ?>%</span>
                                <span class="trend-current">7.39e</span>
                                <span class="trend-end"><?php echo number_format($posWinRate, 1); ?>%</span>
                            </div>
                        </div>
                        <div class="trend-chart-box">
                            <div class="trend-header">
                                <span>Pick Rate</span>
                                <span class="trend-change">-7.6%</span>
                            </div>
                            <canvas id="pickRateTrend" height="100"></canvas>
                            <div class="trend-footer">
                                <span class="trend-start">7.6%</span>
                                <span class="trend-current">7.39e</span>
                                <span class="trend-end">0.0%</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Stats Grid -->
                <div class="stats-grid-4col">
                    <!-- Lane Performance -->
                    <div class="stat-card">
                        <h4>Lane Performance</h4>
                        <div class="lane-stat-row">
                            <span class="lane-label">Lane Advantage:</span>
                            <span class="lane-value positive">+0.3%</span>
                        </div>
                        <div class="lane-stat-row">
                            <span class="lane-label">Lane Wins</span>
                            <span class="lane-value">36.1%</span>
                        </div>
                        <div class="lane-stat-row">
                            <span class="lane-label">Lane Draws</span>
                            <span class="lane-value">46.9%</span>
                        </div>
                        <div class="lane-stat-row">
                            <span class="lane-label">Lane Losses</span>
                            <span class="lane-value">23.3%</span>
                        </div>
                    </div>

                    <!-- Radiant vs Dire -->
                    <div class="stat-card">
                        <h4>Radiant vs Dire</h4>
                        <div class="side-bar radiant-bar">
                            <div class="side-label">Radiant</div>
                            <div class="bar-fill" style="width: 50.7%"></div>
                            <div class="side-value">50.7%</div>
                        </div>
                        <div class="side-bar dire-bar">
                            <div class="side-label">Dire</div>
                            <div class="bar-fill" style="width: 42.1%"></div>
                            <div class="side-value">42.1%</div>
                        </div>
                    </div>

                    <!-- Networth -->
                    <div class="stat-card">
                        <h4>Networth</h4>
                        <div class="networth-timeline">
                            <div class="nw-marker">
                                <div class="nw-dot"></div>
                                <div class="nw-label">minute 10</div>
                                <div class="nw-value">4167</div>
                            </div>
                            <div class="nw-marker">
                                <div class="nw-dot"></div>
                                <div class="nw-label">minute 15</div>
                                <div class="nw-value">6586</div>
                            </div>
                            <div class="nw-marker">
                                <div class="nw-dot"></div>
                                <div class="nw-label">minute 20</div>
                                <div class="nw-value">9676</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pick Phases -->
                    <div class="stat-card">
                        <h4>Pick Phases</h4>
                        <div class="pick-phase-row">
                            <span class="phase-label">1st Pick</span>
                            <span class="phase-matches">54 matches</span>
                            <span class="phase-wr success">50.0%</span>
                        </div>
                        <div class="pick-phase-row">
                            <span class="phase-label">2nd Pick</span>
                            <span class="phase-matches">542 matches</span>
                            <span class="phase-wr">46.7%</span>
                        </div>
                        <div class="pick-phase-row">
                            <span class="phase-label">Lastpick</span>
                            <span class="phase-matches">232 matches</span>
                            <span class="phase-wr">44.8%</span>
                        </div>
                    </div>

                    <!-- Match Duration Win Rate -->
                    <div class="stat-card full-width">
                        <h4>Match Duration Win Rate</h4>
                        <div class="duration-bars">
                            <div class="duration-segment">
                                <div class="duration-label">12-35</div>
                                <div class="duration-bar">
                                    <div class="duration-fill" style="width: 45.8%; background: #ec4899;"></div>
                                </div>
                                <div class="duration-stat">
                                    <span class="dur-wr">45.8%</span>
                                    <span class="dur-matches">453</span>
                                </div>
                            </div>
                            <div class="duration-segment">
                                <div class="duration-label">35-50</div>
                                <div class="duration-bar">
                                    <div class="duration-fill" style="width: 47.0%; background: #f59e0b;"></div>
                                </div>
                                <div class="duration-stat">
                                    <span class="dur-wr">47.0%</span>
                                    <span class="dur-matches">328</span>
                                </div>
                            </div>
                            <div class="duration-segment">
                                <div class="duration-label">50+</div>
                                <div class="duration-bar">
                                    <div class="duration-fill" style="width: 48.1%; background: #10b981;"></div>
                                </div>
                                <div class="duration-stat">
                                    <span class="dur-wr">48.1%</span>
                                    <span class="dur-matches">52</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Tabs (Builds, Meta Analysis, etc.) -->
                <div class="d2pt-main-tabs">
                    <button class="d2pt-main-tab active" data-main-tab="builds">Builds</button>
                    <button class="d2pt-main-tab" data-main-tab="meta">Meta Analysis</button>
                    <button class="d2pt-main-tab" data-main-tab="matchups">Matchups & Synergies</button>
                    <button class="d2pt-main-tab" data-main-tab="itemstats">Item Stats</button>
                    <button class="d2pt-main-tab" data-main-tab="offmeta">Off-Meta Builds</button>
                </div>

                <!-- Builds Tab Content -->
                <div class="d2pt-main-content active" data-main-content="builds">
                    <p class="builds-notice">Hero Builds use 14 days of data.</p>

                    <!-- Facet Selector -->
                    <section class="choose-facet">
                        <h3>CHOOSE A FACET</h3>
                        <div class="toggle-group-builds">
                            <span>Group Builds</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="facet-selector-grid">
                            <?php foreach ($facets as $idx => $facet): ?>
                            <div class="facet-select-card <?php echo $idx === 0 ? 'active' : ''; ?>">
                                <div class="facet-select-matches"><?php echo formatNumber($facet['matches']); ?> matches</div>
                                <div class="facet-select-wr <?php echo $facet['winrate'] >= 50 ? 'success' : 'danger'; ?>">
                                    <?php echo number_format($facet['winrate'], 1); ?>% WR
                                </div>
                                <div class="facet-select-icon"><i class="fas fa-gem"></i></div>
                                <div class="facet-select-name"><?php echo strtoupper($facet['name']); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Available Builds -->
                    <section class="available-builds">
                        <h3>Available Builds for Selected Facet</h3>
                        <div class="build-card-main">
                            <div class="build-header">
                                <div class="build-number">BUILD 1</div>
                                <div class="build-stats-header">1426 matches • 48% win</div>
                            </div>
                            <div class="build-abilities-preview">
                                <?php 
                                // Sample ability progression - these will show when images load
                                $buildAbilities = ['berserkers_call', 'counter_helix', 'battle_hunger', 'counter_helix', 'berserkers_call', 'culling_blade', 'berserkers_call'];
                                foreach ($buildAbilities as $ability): 
                                ?>
                                <img src="<?php echo getAbilityImage($ability, $hero['localized_name']); ?>" 
                                     class="ability-icon-sm" alt="" 
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2245%22 height=%2232%22%3E%3Crect fill=%22%231a2332%22 width=%22100%25%22 height=%22100%25%22/%3E%3C/svg%3E'">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>

                    <!-- Abilities & Talents -->
                    <section class="abilities-talents">
                        <h3>ABILITIES & TALENTS</h3>
                        <p class="section-subtitle">Most common first 10 Levels for this Build</p>

                        <div class="ability-builds-grid">
                            <div class="ability-build-option">
                                <div class="ability-build-header">
                                    <div class="build-badge most-popular">Most Popular</div>
                                    <div class="build-meta">224 matches • 53.6% win rate</div>
                                </div>
                                <div class="ability-sequence">
                                    <?php 
                                    $popularBuild = ['berserkers_call', 'counter_helix', 'berserkers_call', 'counter_helix', 'berserkers_call', 'culling_blade', 'berserkers_call', 'counter_helix', 'counter_helix', 'battle_hunger'];
                                    for ($lvl = 1; $lvl <= 10; $lvl++): 
                                        $ability = $popularBuild[$lvl - 1];
                                    ?>
                                    <div class="ability-level-slot">
                                        <img src="<?php echo getAbilityImage($ability, $hero['localized_name']); ?>" 
                                             class="ability-icon" alt=""
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2236%22%3E%3Crect fill=%22%231a2332%22 width=%22100%25%22 height=%22100%25%22/%3E%3C/svg%3E'">
                                        <div class="ability-level"><?php echo $lvl; ?></div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="ability-build-option">
                                <div class="ability-build-header">
                                    <div class="build-badge highest-wr">Highest Win Rate</div>
                                    <div class="build-meta">40 matches • 62.5% win rate</div>
                                </div>
                                <div class="ability-sequence">
                                    <?php 
                                    $highWRBuild = ['counter_helix', 'berserkers_call', 'counter_helix', 'battle_hunger', 'counter_helix', 'culling_blade', 'counter_helix', 'berserkers_call', 'berserkers_call', 'berserkers_call'];
                                    for ($lvl = 1; $lvl <= 10; $lvl++): 
                                        $ability = $highWRBuild[$lvl - 1];
                                    ?>
                                    <div class="ability-level-slot">
                                        <img src="<?php echo getAbilityImage($ability, $hero['localized_name']); ?>" 
                                             class="ability-icon" alt=""
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2236%22%3E%3Crect fill=%22%231a2332%22 width=%22100%25%22 height=%22100%25%22/%3E%3C/svg%3E'">
                                        <div class="ability-level"><?php echo $lvl; ?></div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Talents -->
                        <div class="talents-tree">
                            <div class="talent-row">
                                <div class="talent-level">25</div>
                                <div class="talent-option left">
                                    <div class="talent-text">+35 Strength</div>
                                    <div class="talent-pick-rate">(64.7% pick rate)</div>
                                </div>
                                <div class="talent-option right">
                                    <div class="talent-text">-40% Waveform Cooldown</div>
                                </div>
                            </div>
                            <div class="talent-row">
                                <div class="talent-level">20</div>
                                <div class="talent-option left">
                                    <div class="talent-text">+15 Agility</div>
                                    <div class="talent-pick-rate">(99.2% pick rate)</div>
                                </div>
                                <div class="talent-option right">
                                    <div class="talent-text">-3s Adaptive Strike Cooldown</div>
                                </div>
                            </div>
                            <div class="talent-row">
                                <div class="talent-level">15</div>
                                <div class="talent-option left">
                                    <div class="talent-text">+75 Attack Range</div>
                                    <div class="talent-pick-rate">(85.3% pick rate)</div>
                                </div>
                                <div class="talent-option right">
                                    <div class="talent-text">+250 Waveform Range</div>
                                </div>
                            </div>
                            <div class="talent-row">
                                <div class="talent-level">10</div>
                                <div class="talent-option left">
                                    <div class="talent-text">+12s Morph Duration</div>
                                    <div class="talent-pick-rate">(64.8% pick rate)</div>
                                </div>
                                <div class="talent-option right">
                                    <div class="talent-text">+15% Magic Resistance</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Starting Items -->
                    <section class="starting-items">
                        <h3>STARTING ITEMS</h3>
                        <p class="section-subtitle">Different common options</p>

                        <div class="starting-builds-grid">
                            <div class="starting-build">
                                <div class="starting-meta">1040 matches • 47.2% win rate</div>
                                <div class="starting-items-row">
                                    <img src="<?php echo getItemImage('tango'); ?>" class="item-icon" alt="Tango">
                                    <img src="<?php echo getItemImage('tango'); ?>" class="item-icon" alt="Tango">
                                    <img src="<?php echo getItemImage('tango'); ?>" class="item-icon" alt="Tango">
                                    <img src="<?php echo getItemImage('branches'); ?>" class="item-icon" alt="Branch">
                                    <img src="<?php echo getItemImage('circlet'); ?>" class="item-icon" alt="Circlet">
                                    <img src="<?php echo getItemImage('slippers'); ?>" class="item-icon" alt="Slippers">
                                </div>
                            </div>
                            <div class="starting-build">
                                <div class="starting-meta">133 matches • 44.4% win rate</div>
                                <div class="starting-items-row">
                                    <img src="<?php echo getItemImage('tango'); ?>" class="item-icon" alt="Tango">
                                    <img src="<?php echo getItemImage('tango'); ?>" class="item-icon" alt="Tango">
                                    <img src="<?php echo getItemImage('tango'); ?>" class="item-icon" alt="Tango">
                                    <img src="<?php echo getItemImage('faerie_fire'); ?>" class="item-icon" alt="Faerie Fire">
                                    <img src="<?php echo getItemImage('clarity'); ?>" class="item-icon" alt="Clarity">
                                    <img src="<?php echo getItemImage('clarity'); ?>" class="item-icon" alt="Clarity">
                                </div>
                            </div>
                            <div class="starting-build">
                                <div class="starting-meta">42 matches • 59.5% win rate</div>
                                <div class="starting-items-row">
                                    <img src="<?php echo getItemImage('tango'); ?>" class="item-icon" alt="Tango">
                                    <img src="<?php echo getItemImage('quelling_blade'); ?>" class="item-icon" alt="Quelling Blade">
                                    <img src="<?php echo getItemImage('branches'); ?>" class="item-icon" alt="Branch">
                                    <img src="<?php echo getItemImage('circlet'); ?>" class="item-icon" alt="Circlet">
                                    <img src="<?php echo getItemImage('slippers'); ?>" class="item-icon" alt="Slippers">
                                    <img src="<?php echo getItemImage('faerie_fire'); ?>" class="item-icon" alt="Faerie Fire">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Core Item Build -->
                    <section class="core-item-build">
                        <h3>CORE ITEM BUILD</h3>
                        <p class="section-subtitle">Most common sequence of Items. Core items are bought in >50% of matches</p>

                        <div class="item-timeline">
                            <?php 
                            $coreItems = [
                                ['item' => 'power_treads', 'time' => '6m', 'core' => true],
                                ['item' => 'wraith_band', 'time' => '9m', 'core' => true],
                                ['item' => 'dragon_lance', 'time' => '10m', 'core' => true],
                                ['item' => 'yasha', 'time' => '14m', 'core' => true],
                                ['item' => 'manta', 'time' => '18m', 'core' => true],
                                ['item' => 'disperser', 'time' => '22m', 'core' => true],
                                ['item' => 'satanic', 'time' => '27m', 'core' => true],
                                ['item' => 'butterfly', 'time' => '30m', 'core' => true],
                                ['item' => 'skadi', 'time' => '31m', 'core' => false],
                                ['item' => 'silver_edge', 'time' => '33m', 'core' => false],
                                ['item' => 'greater_crit', 'time' => '33m', 'core' => false],
                                ['item' => 'monkey_king_bar', 'time' => '35m', 'core' => false],
                                ['item' => 'swift_blink', 'time' => '39m', 'core' => false],
                            ];
                            
                            foreach ($coreItems as $itemData): 
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-time"><?php echo $itemData['time']; ?></div>
                                <div class="timeline-marker <?php echo $itemData['core'] ? 'core' : ''; ?>">
                                    <?php if ($itemData['core']): ?><div class="core-badge">CORE</div><?php endif; ?>
                                </div>
                                <div class="timeline-item-box">
                                    <img src="<?php echo getItemImage($itemData['item']); ?>" class="timeline-item-img" alt="">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Item Stats Table -->
                    <section class="item-stats-section">
                        <h3>ITEM STATS</h3>
                        <p class="section-subtitle">Stats of all Items for this Build</p>

                        <div class="item-filter-bar">
                            <button class="item-filter-btn active">Table View</button>
                            <button class="item-filter-btn">Normal View</button>
                            <button class="item-filter-btn">Show Core Items (≥50% purchase rate)</button>
                        </div>

                        <table class="item-stats-table">
                            <thead>
                                <tr>
                                    <th>Item <i class="fas fa-sort"></i></th>
                                    <th>Purchase Rate <i class="fas fa-sort"></i></th>
                                    <th>Average Time <i class="fas fa-sort"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $itemStats = [
                                    ['item' => 'butterfly', 'rate' => 100, 'time' => '27m', 'range' => '23m-32m', 'core' => true],
                                    ['item' => 'magic_wand', 'rate' => 100, 'time' => '2m', 'range' => '-2m-7m', 'core' => true],
                                    ['item' => 'power_treads', 'rate' => 100, 'time' => '5m', 'range' => '4m-7m', 'core' => true],
                                    ['item' => 'ultimate_scepter', 'rate' => 100, 'time' => '30m', 'range' => '25m-34m', 'core' => true],
                                    ['item' => 'manta', 'rate' => 100, 'time' => '20m', 'range' => '17m-23m', 'core' => true],
                                    ['item' => 'yasha', 'rate' => 100, 'time' => '16m', 'range' => '15m-18m', 'core' => true],
                                    ['item' => 'blink', 'rate' => 58.6, 'time' => '32m', 'range' => '26m-38m', 'core' => false],
                                    ['item' => 'swift_blink', 'rate' => 32.8, 'time' => '39m', 'range' => '34m-45m', 'core' => false],
                                    ['item' => 'wraith_band', 'rate' => 18.1, 'time' => '2m', 'range' => '0m-4m', 'core' => false],
                                ];
                                
                                foreach ($itemStats as $item): 
                                ?>
                                <tr>
                                    <td class="item-name-cell">
                                        <img src="<?php echo getItemImage($item['item']); ?>" class="table-item-icon" alt="">
                                        <span><?php echo ucwords(str_replace('_', ' ', $item['item'])); ?></span>
                                        <?php if ($item['core']): ?>
                                        <span class="core-label">CORE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($item['rate'], 1); ?>%</td>
                                    <td>
                                        <div class="time-display">
                                            <div class="avg-time"><?php echo $item['time']; ?></div>
                                            <div class="time-range">(<?php echo $item['range']; ?>)</div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>

                    <!-- Neutral Items -->
                    <section class="neutral-items-section">
                        <h3>NEUTRAL ITEMS</h3>
                        <p class="section-subtitle">Most common Neutral Item choices</p>

                        <?php 
                        $neutralTiers = [
                            1 => [
                                ['item' => 'possessed_mask', 'rate' => 14.8, 'wr' => 53.7],
                                ['item' => 'arcane_ring', 'rate' => 33.9, 'wr' => 56.4],
                                ['item' => 'faded_broach', 'rate' => 14.8, 'wr' => 29.4],
                                ['item' => 'essence_ring', 'rate' => 22.2, 'wr' => 22.2],
                                ['item' => 'unstable_wand', 'rate' => 33.9, 'wr' => 33.9],
                                ['item' => 'keen_optic', 'rate' => 0.9, 'wr' => 100.0],
                            ],
                            2 => [
                                ['item' => 'grove_bow', 'rate' => 53.9, 'wr' => 55.1],
                                ['item' => 'vambrace', 'rate' => 16.4, 'wr' => 40.7],
                                ['item' => 'pupils_gift', 'rate' => 14.8, 'wr' => 46.9],
                                ['item' => 'ring_of_aquila', 'rate' => 8.3, 'wr' => 53.6],
                            ],
                            3 => [
                                ['item' => 'elven_tunic', 'rate' => 55.0, 'wr' => 46.6],
                                ['item' => 'dragon_scale', 'rate' => 31.3, 'wr' => 43.1],
                            ],
                            4 => [
                                ['item' => 'stormcrafter', 'rate' => 42.7, 'wr' => 46.7],
                                ['item' => 'trickster_cloak', 'rate' => 36.8, 'wr' => 43.7],
                            ],
                            5 => [
                                ['item' => 'desolator_2', 'rate' => 100.0, 'wr' => 60.0],
                            ],
                        ];
                        
                        foreach ($neutralTiers as $tier => $items): 
                        ?>
                        <div class="neutral-tier">
                            <div class="tier-header">Tier <?php echo $tier; ?></div>
                            <div class="neutral-items-grid">
                                <?php foreach ($items as $neutralItem): ?>
                                <div class="neutral-item-card">
                                    <img src="<?php echo getItemImage($neutralItem['item']); ?>" class="neutral-item-img" alt="">
                                    <div class="neutral-rate"><?php echo number_format($neutralItem['rate'], 1); ?>%</div>
                                    <div class="neutral-wr <?php echo $neutralItem['wr'] >= 50 ? 'success' : 'danger'; ?>">
                                        <?php echo number_format($neutralItem['wr'], 1); ?>% wr
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </section>
                </div>

                <!-- Other Tab Contents (Placeholders) -->
                <div class="d2pt-main-content" data-main-content="meta">
                    <p class="coming-soon">Meta Analysis coming soon...</p>
                </div>
                <div class="d2pt-main-content" data-main-content="matchups">
                    <p class="coming-soon">Matchups & Synergies coming soon...</p>
                </div>
                <div class="d2pt-main-content" data-main-content="itemstats">
                    <p class="coming-soon">Item Stats coming soon...</p>
                </div>
                <div class="d2pt-main-content" data-main-content="offmeta">
                    <p class="coming-soon">Off-Meta Builds coming soon...</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/hero-page.js"></script>
</body>
</html>
