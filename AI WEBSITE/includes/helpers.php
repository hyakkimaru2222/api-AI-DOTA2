<?php

function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'k';
    }
    return $number;
}

function formatWinRate($wins, $total) {
    if ($total == 0) return '0.0%';
    return number_format(($wins / $total) * 100, 1) . '%';
}

function getHeroImage($heroName) {
    $cleanName = str_replace('npc_dota_hero_', '', $heroName);
    return "https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/{$cleanName}.png";
}

function getHeroMiniIcon($heroName) {
    $cleanName = str_replace('npc_dota_hero_', '', $heroName);
    return "https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/icons/{$cleanName}.png";
}

function generateHeroSlug($heroName) {
    $cleanName = str_replace('npc_dota_hero_', '', $heroName);
    return urlencode(ucwords(str_replace('_', ' ', $cleanName)));
}

function calculateD2PTRating($wins, $picks) {
    if ($picks == 0) return 0;
    $winRate = ($wins / $picks) * 100;
    return round(($winRate / 100) * $picks);
}

function getTopHeroesByRole($heroStats, $role, $limit = 6) {
    $roleKey = $role . '_pick';
    $roleWinKey = $role . '_win';
    
    $filtered = array_filter($heroStats, function($h) use ($roleKey, $roleWinKey) {
        $picks = $h[$roleKey] ?? 0;
        $wins = $h[$roleWinKey] ?? 0;
        $winRate = $picks > 0 ? ($wins / $picks) * 100 : 0;
        return $picks > 100 && $winRate >= 50.0;
    });
    
    usort($filtered, function($a, $b) use ($roleKey, $roleWinKey) {
        $aRating = calculateD2PTRating($a[$roleWinKey] ?? 0, $a[$roleKey] ?? 0);
        $bRating = calculateD2PTRating($b[$roleWinKey] ?? 0, $b[$roleKey] ?? 0);
        return $bRating <=> $aRating;
    });
    
    return array_slice($filtered, 0, $limit);
}

function getFacetForHero($heroName, $facets) {
    $slug = strtolower(str_replace([' ', '\''], ['_', ''], $heroName));
    return $facets[$slug] ?? ['icon' => 'default', 'name' => '', 'desc' => ''];
}

function getItemImage($itemName) {
    // Clean item name: remove parentheses, apostrophes, and convert to lowercase with underscores
    $cleanName = preg_replace('/\([^)]*\)/', '', $itemName); // Remove (text in parentheses)
    $cleanName = strtolower(str_replace([' ', '-', "'", '.'], ['_', '_', '', ''], trim($cleanName)));
    return "https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/items/{$cleanName}.png";
}

function getFacetIcon($facetName, $heroName) {
    // Create facet icon path with hero prefix
    $heroSlug = strtolower(str_replace([' ', '-', "'"], ['_', '_', ''], $heroName));
    $facetSlug = strtolower(str_replace([' ', '-', "'"], ['_', '_', ''], $facetName));
    
    // Try multiple CDN patterns
    $cdnUrl = "https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/heroes/facets/{$heroSlug}_{$facetSlug}.png";
    
    return $cdnUrl;
}

function getAbilityImage($abilityName, $heroName = '') {
    // Clean ability name and ensure lowercase with underscores
    $cleanName = strtolower(str_replace([' ', '-', "'"], ['_', '_', ''], $abilityName));
    
    // If ability name doesn't already have underscore (not hero_ability format), add hero prefix
    if ($heroName && strpos($cleanName, '_') === false) {
        $heroSlug = strtolower(str_replace([' ', '-', "'"], ['_', '_', ''], $heroName));
        $cleanName = $heroSlug . '_' . $cleanName;
    }
    
    // Use correct Steam CDN path: dota_react/abilities with hero-prefixed names
    $cdnUrl = "https://cdn.cloudflare.steamstatic.com/apps/dota2/images/dota_react/abilities/{$cleanName}.png";
    
    return $cdnUrl;
}

function timeAgo($timestamp) {
    $time = time() - $timestamp;
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' mins ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 604800) return floor($time / 86400) . ' days ago';
    
    return date('M d, Y', $timestamp);
}

function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    }
    return sprintf('%d:%02d', $minutes, $secs);
}

function getRoleColor($role) {
    $roles = [
        'Carry' => '#e74c3c',
        'Mid' => '#3498db',
        'Offlane' => '#f39c12',
        'Support' => '#2ecc71',
        'Hard Support' => '#9b59b6'
    ];
    return $roles[$role] ?? '#95a5a6';
}

function getHeroRole($heroId, $roles = null) {
    if ($roles === null || !isset($roles[$heroId])) {
        return 'Versatile';
    }
    return $roles[$heroId];
}

function buildHeroRolesMap($heroes) {
    $rolesMap = [];
    foreach ($heroes as $hero) {
        $roles = $hero['roles'] ?? [];
        if (empty($roles)) {
            $rolesMap[$hero['id']] = 'Versatile';
            continue;
        }
        
        if (in_array('Carry', $roles)) {
            $rolesMap[$hero['id']] = 'Carry';
        } elseif (in_array('Mid', $roles) || in_array('Nuker', $roles)) {
            $rolesMap[$hero['id']] = 'Mid';
        } elseif (in_array('Initiator', $roles) || in_array('Durable', $roles)) {
            $rolesMap[$hero['id']] = 'Offlane';
        } elseif (in_array('Support', $roles)) {
            if (in_array('Disabler', $roles) || count($roles) > 2) {
                $rolesMap[$hero['id']] = 'Hard Support';
            } else {
                $rolesMap[$hero['id']] = 'Support';
            }
        } else {
            $primaryRole = $roles[0];
            $rolesMap[$hero['id']] = ucfirst(strtolower($primaryRole));
        }
    }
    return $rolesMap;
}

function calculatePickRate($picks, $totalMatches) {
    if ($totalMatches == 0) return '0.0%';
    return number_format(($picks / $totalMatches) * 100, 1) . '%';
}

function getDifficultyStars($difficulty) {
    $stars = '';
    for ($i = 0; $i < 3; $i++) {
        $stars .= $i < $difficulty ? '★' : '☆';
    }
    return $stars;
}

function sanitizeInput($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function escapeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
