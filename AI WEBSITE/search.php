<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$query = $_GET['q'] ?? '';
$query = sanitizeInput($query);

$results = [
    'heroes' => [],
    'players' => [],
    'query' => $query
];

if (strlen($query) < 2) {
    echo json_encode($results);
    exit;
}

$api = new OpenDotaAPI();

$heroes = $api->getHeroes();
foreach ($heroes as $hero) {
    $localizedName = $hero['localized_name'] ?? '';
    if (stripos($localizedName, $query) !== false) {
        $heroSlug = str_replace('npc_dota_hero_', '', $hero['name']);
        $results['heroes'][] = [
            'id' => $hero['id'],
            'name' => $localizedName,
            'image' => getHeroImage($hero['name']),
            'url' => '/hero.php?hero=' . urlencode($heroSlug)
        ];
        if (count($results['heroes']) >= 5) break;
    }
}

$playerResults = $api->searchPlayers($query);
if (!empty($playerResults)) {
    foreach (array_slice($playerResults, 0, 5) as $player) {
        $results['players'][] = [
            'id' => $player['account_id'] ?? 0,
            'name' => $player['personaname'] ?? $player['name'] ?? 'Unknown',
            'avatar' => $player['avatarmedium'] ?? 'https://ui-avatars.com/api/?name=Player&background=random',
            'url' => '/player.php?id=' . ($player['account_id'] ?? 0)
        ];
    }
}

echo json_encode($results);
