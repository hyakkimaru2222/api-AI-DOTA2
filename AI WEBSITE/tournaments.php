<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();
$api = new OpenDotaAPI();

$proMatches = $api->getProMatches();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournaments - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="tournaments-page-section">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-medal"></i> Dota 2 Tournaments
                    </h1>
                    <p class="page-subtitle">Upcoming and ongoing professional Dota 2 tournaments</p>
                </div>

                <div class="filter-bar">
                    <div class="filter-group">
                        <button class="filter-btn active">All Tournaments</button>
                        <button class="filter-btn"><i class="fas fa-play-circle"></i> Ongoing</button>
                        <button class="filter-btn"><i class="fas fa-calendar-alt"></i> Upcoming</button>
                        <button class="filter-btn"><i class="fas fa-check-circle"></i> Completed</button>
                    </div>
                </div>

                <div class="tournaments-carousel">
                    <?php 
                    $tournaments = [
                        ['team1' => 'Team Spirit', 'team2' => 'Falcons', 'date' => 'October 18 at 03:00 PM', 'league' => 'DreamLeague S24'],
                        ['team1' => 'Tundra Esports', 'team2' => 'HEROIC', 'date' => 'October 18 at 10:00 PM', 'league' => 'ESL One'],
                        ['team1' => 'Team Aurora', 'team2' => 'Team Liquid', 'date' => 'October 18 at 10:00 PM', 'league' => 'BetBoom League'],
                        ['team1' => 'Xtreme Gaming', 'team2' => 'Ykiros', 'date' => 'October 19 at 02:00 PM', 'league' => 'DPC WEU'],
                        ['team1' => 'Gaimin Gladiators', 'team2' => 'OG', 'date' => 'October 19 at 05:00 PM', 'league' => 'DPC WEU'],
                        ['team1' => 'PSG.LGD', 'team2' => 'Royal Never Give Up', 'date' => 'October 20 at 08:00 AM', 'league' => 'DPC CN'],
                    ];
                    foreach ($tournaments as $tournament): 
                    ?>
                    <div class="tournament-card">
                        <div class="tournament-league">
                            <i class="fas fa-trophy"></i>
                            <?php echo htmlspecialchars($tournament['league']); ?>
                        </div>
                        <div class="tournament-time">
                            <i class="far fa-calendar"></i>
                            <span><?php echo htmlspecialchars($tournament['date']); ?></span>
                        </div>
                        <div class="tournament-match">
                            <div class="team">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($tournament['team1']); ?>&background=random" alt="<?php echo $tournament['team1']; ?>">
                                <span><?php echo htmlspecialchars($tournament['team1']); ?></span>
                            </div>
                            <span class="vs">VS</span>
                            <div class="team">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($tournament['team2']); ?>&background=random" alt="<?php echo $tournament['team2']; ?>">
                                <span><?php echo htmlspecialchars($tournament['team2']); ?></span>
                            </div>
                        </div>
                        <button class="tournament-watch-btn">
                            <i class="fas fa-eye"></i> Watch Stream
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <section class="tournament-leaderboard">
                    <h2 class="section-title">Tournament Leaderboard</h2>
                    <div class="leaderboard-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Team</th>
                                    <th>Points</th>
                                    <th>Wins</th>
                                    <th>Losses</th>
                                    <th>Win Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $teams = [
                                    ['name' => 'Team Spirit', 'points' => 2800, 'wins' => 45, 'losses' => 12],
                                    ['name' => 'Gaimin Gladiators', 'points' => 2650, 'wins' => 42, 'losses' => 15],
                                    ['name' => 'Tundra Esports', 'points' => 2500, 'wins' => 40, 'losses' => 17],
                                    ['name' => 'Team Liquid', 'points' => 2350, 'wins' => 38, 'losses' => 19],
                                    ['name' => 'Falcons', 'points' => 2200, 'wins' => 35, 'losses' => 22],
                                ];
                                foreach ($teams as $rank => $team):
                                    $winRate = ($team['wins'] / ($team['wins'] + $team['losses'])) * 100;
                                ?>
                                <tr>
                                    <td><span class="rank-badge rank-<?php echo $rank + 1; ?>"><?php echo $rank + 1; ?></span></td>
                                    <td>
                                        <div class="team-cell">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($team['name']); ?>&background=random" alt="<?php echo $team['name']; ?>">
                                            <span><?php echo htmlspecialchars($team['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><strong><?php echo $team['points']; ?></strong></td>
                                    <td class="success"><?php echo $team['wins']; ?></td>
                                    <td class="danger"><?php echo $team['losses']; ?></td>
                                    <td>
                                        <span class="winrate-value <?php echo $winRate >= 60 ? 'success' : ''; ?>">
                                            <?php echo number_format($winRate, 1); ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
