<?php

class AdvancedProtection {
    
    // Honeypot field detection - catches bots that auto-fill forms
    public static function generateHoneypot($fieldName = 'website') {
        return '<div style="position:absolute;left:-9999px;"><input type="text" name="' . $fieldName . '" value="" tabindex="-1" autocomplete="off"></div>';
    }
    
    public static function checkHoneypot($fieldName = 'website') {
        return empty($_POST[$fieldName]);
    }
    
    // Browser fingerprinting - detect suspicious changes
    public static function generateFingerprint() {
        $components = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''
        ];
        return hash('sha256', implode('|', $components));
    }
    
    public static function validateFingerprint($storedFingerprint) {
        $currentFingerprint = self::generateFingerprint();
        return hash_equals($storedFingerprint, $currentFingerprint);
    }
    
    // Request signature - ensure requests haven't been tampered with
    public static function generateRequestSignature($data, $secret) {
        ksort($data);
        $string = http_build_query($data);
        return hash_hmac('sha256', $string, $secret);
    }
    
    public static function verifyRequestSignature($data, $signature, $secret) {
        $expectedSignature = self::generateRequestSignature($data, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    
    // Detect headless browsers and automation tools
    public static function detectBot() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget',
            'python', 'java', 'go-http-client', 'okhttp', 'httpclient',
            'headless', 'phantom', 'selenium', 'webdriver', 'puppeteer'
        ];
        
        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }
        
        // Check for missing common browser headers
        $requiredHeaders = ['HTTP_ACCEPT', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_ACCEPT_ENCODING'];
        foreach ($requiredHeaders as $header) {
            if (!isset($_SERVER[$header])) {
                return true;
            }
        }
        
        return false;
    }
    
    // Challenge-response system for suspicious requests
    public static function generateChallenge() {
        $challenge = bin2hex(random_bytes(16));
        $_SESSION['security_challenge'] = $challenge;
        $_SESSION['challenge_time'] = time();
        return $challenge;
    }
    
    public static function verifyChallenge($response) {
        if (!isset($_SESSION['security_challenge']) || !isset($_SESSION['challenge_time'])) {
            return false;
        }
        
        // Challenge expires after 5 minutes
        if (time() - $_SESSION['challenge_time'] > 300) {
            unset($_SESSION['security_challenge'], $_SESSION['challenge_time']);
            return false;
        }
        
        $expectedResponse = hash('sha256', $_SESSION['security_challenge']);
        $isValid = hash_equals($expectedResponse, $response);
        
        // Clear challenge after verification
        unset($_SESSION['security_challenge'], $_SESSION['challenge_time']);
        
        return $isValid;
    }
    
    // Geo-blocking (basic IP range check)
    public static function isIPInRange($ip, $range) {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }
    
    // Check if IP is in blocked country ranges (example)
    public static function isIPBlocked($ip) {
        // Add your blocked IP ranges here
        $blockedRanges = [
            // Example: '192.168.1.0/24'
        ];
        
        foreach ($blockedRanges as $range) {
            if (self::isIPInRange($ip, $range)) {
                return true;
            }
        }
        
        return false;
    }
    
    // Time-based request validation (prevent replay attacks)
    public static function generateTimestamp() {
        return time();
    }
    
    public static function validateTimestamp($timestamp, $maxAge = 300) {
        $now = time();
        $age = abs($now - $timestamp);
        return $age <= $maxAge;
    }
    
    // Nonce generation and validation (one-time tokens)
    public static function generateNonce() {
        $nonce = bin2hex(random_bytes(16));
        if (!isset($_SESSION['used_nonces'])) {
            $_SESSION['used_nonces'] = [];
        }
        $_SESSION['used_nonces'][$nonce] = time();
        
        // Clean old nonces (older than 1 hour)
        foreach ($_SESSION['used_nonces'] as $n => $t) {
            if (time() - $t > 3600) {
                unset($_SESSION['used_nonces'][$n]);
            }
        }
        
        return $nonce;
    }
    
    public static function validateNonce($nonce) {
        if (!isset($_SESSION['used_nonces'][$nonce])) {
            return false;
        }
        
        // Check if nonce is still valid (within 1 hour)
        if (time() - $_SESSION['used_nonces'][$nonce] > 3600) {
            unset($_SESSION['used_nonces'][$nonce]);
            return false;
        }
        
        // Nonce can only be used once
        unset($_SESSION['used_nonces'][$nonce]);
        return true;
    }
    
    // Detect proxy/VPN usage (basic check)
    public static function isProxy() {
        $proxyHeaders = [
            'HTTP_VIA',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_PROXY_CONNECTION'
        ];
        
        foreach ($proxyHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                return true;
            }
        }
        
        return false;
    }
}
