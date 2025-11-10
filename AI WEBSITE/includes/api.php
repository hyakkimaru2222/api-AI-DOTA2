<?php

class OpenDotaAPI {
    private $baseUrl = OPENDOTA_API_BASE;
    private $cacheDir = 'cache/';
    private $logFile = 'cache/api_errors.log';

    public function __construct() {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }
    
    private function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }
        $logMessage .= "\n";
        error_log($logMessage, 3, $this->logFile);
    }

    private function makeRequest($endpoint, $params = []) {
        $queryString = !empty($params) ? '?' . http_build_query($params) : '';
        $url = $this->baseUrl . $endpoint . $queryString;
        
        $cacheKey = md5($url);
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_DURATION) {
            $data = file_get_contents($cacheFile);
            return json_decode($data, true);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: Dota2ProTracker/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $this->logError("cURL error for {$endpoint}", ['error' => $curlError, 'url' => $url]);
            return [];
        }
        
        if ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError("JSON decode error for {$endpoint}", ['error' => json_last_error_msg()]);
                return [];
            }
            file_put_contents($cacheFile, $response);
            return $decoded;
        }
        
        if ($httpCode !== 200) {
            $this->logError("HTTP error for {$endpoint}", ['code' => $httpCode, 'url' => $url]);
        }
        
        return [];
    }

    public function getHeroStats() {
        return $this->makeRequest('/heroStats');
    }

    public function getHeroes() {
        return $this->makeRequest('/heroes');
    }

    public function getProMatches() {
        return $this->makeRequest('/proMatches');
    }

    public function getPublicMatches() {
        return $this->makeRequest('/publicMatches');
    }

    public function getProPlayers() {
        return $this->makeRequest('/proPlayers');
    }

    public function getPlayer($accountId) {
        return $this->makeRequest("/players/{$accountId}");
    }

    public function getPlayerRecentMatches($accountId) {
        return $this->makeRequest("/players/{$accountId}/recentMatches");
    }

    public function getMatch($matchId) {
        return $this->makeRequest("/matches/{$matchId}");
    }

    public function getRankings($hero = null) {
        $endpoint = '/rankings';
        $params = $hero ? ['hero' => $hero] : [];
        return $this->makeRequest($endpoint, $params);
    }

    public function getBenchmarks($heroId) {
        return $this->makeRequest('/benchmarks', ['hero_id' => $heroId]);
    }

    public function getConstants() {
        return $this->makeRequest('/constants');
    }

    public function searchPlayers($query) {
        return $this->makeRequest('/search', ['q' => $query]);
    }

    public function getLiveGames() {
        return $this->makeRequest('/live');
    }
}

class StratzAPI {
    private $graphqlUrl = 'https://api.stratz.com/graphql';
    private $token;
    private $cacheDir = 'cache/';
    private $logFile = 'cache/api_errors.log';

    public function __construct() {
        $this->token = getenv('STRATZ_API_TOKEN') ?: '';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    private function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] STRATZ: {$message}";
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }
        $logMessage .= "\n";
        error_log($logMessage, 3, $this->logFile);
    }

    private function makeGraphQLRequest($query, $variables = []) {
        if (empty($this->token)) {
            $this->logError("No Stratz API token configured");
            return null;
        }

        $cacheKey = md5($query . json_encode($variables));
        $cacheFile = $this->cacheDir . 'stratz_' . $cacheKey . '.json';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_DURATION) {
            $data = file_get_contents($cacheFile);
            return json_decode($data, true);
        }

        $payload = json_encode([
            'query' => $query,
            'variables' => $variables
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->graphqlUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'User-Agent: Dota2ProTracker/1.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logError("cURL error", ['error' => $curlError]);
            return null;
        }

        if ($httpCode === 200 && $response) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError("JSON decode error", ['error' => json_last_error_msg()]);
                return null;
            }
            file_put_contents($cacheFile, $response);
            return $decoded;
        }

        $this->logError("HTTP error", ['code' => $httpCode, 'response' => substr($response, 0, 200)]);
        return null;
    }

    public function getLiveMatches() {
        $query = '{
            live {
                matches {
                    matchId
                    gameTime
                    radiantScore
                    direScore
                    players {
                        steamAccountId
                        heroId
                        kills
                        deaths
                        assists
                        isRadiant
                    }
                }
            }
        }';
        
        $result = $this->makeGraphQLRequest($query);
        return $result['data']['live']['matches'] ?? [];
    }

    public function hasToken() {
        return !empty($this->token);
    }
    
    public function getTokenStatus() {
        return $this->hasToken() ? 'configured' : 'missing';
    }
}
