<?php

class SecurityManager {
    private static $instance = null;
    private $storageDir;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->storageDir = __DIR__ . '/../cache/ratelimit';
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }
    
    // Rate Limiting - Prevent DDoS and brute force attacks (file-based persistence with locking)
    public function checkRateLimit($identifier, $maxRequests = 60, $timeWindow = 60) {
        $now = time();
        $key = md5($identifier);
        $filePath = $this->storageDir . '/' . $key . '.json';
        
        // Open file with exclusive lock for atomic read-modify-write
        $fp = fopen($filePath, 'c+');
        if (!$fp) {
            // If we can't open file, allow request but log error
            error_log("Failed to open rate limit file: $filePath");
            return true;
        }
        
        // Acquire exclusive lock
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            error_log("Failed to acquire lock on rate limit file: $filePath");
            return true;
        }
        
        // Read existing data
        $content = stream_get_contents($fp);
        $data = $content ? json_decode($content, true) : null;
        
        // Initialize or reset if window expired
        if (!$data || ($now - $data['start']) > $timeWindow) {
            $data = ['count' => 1, 'start' => $now];
            
            // Write and release
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }
        
        // Increment counter
        $data['count']++;
        
        // Check if limit exceeded BEFORE writing
        if ($data['count'] > $maxRequests) {
            // Release lock and file
            flock($fp, LOCK_UN);
            fclose($fp);
            
            $this->logSecurityEvent('RATE_LIMIT_EXCEEDED', "IP exceeded $maxRequests requests in {$timeWindow}s (actual: {$data['count']})", 'WARNING');
            http_response_code(429);
            header('Retry-After: ' . ($timeWindow - ($now - $data['start'])));
            die(json_encode(['error' => 'Too many requests. Please slow down.']));
        }
        
        // Write updated counter atomically
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));
        
        // Release lock and close
        flock($fp, LOCK_UN);
        fclose($fp);
        
        return true;
    }
    
    // Clean up old rate limit files (call periodically)
    public function cleanupRateLimitFiles($maxAge = 3600) {
        $files = glob($this->storageDir . '/*.json');
        $now = time();
        foreach ($files as $file) {
            if (($now - filemtime($file)) > $maxAge) {
                unlink($file);
            }
        }
    }
    
    // Validate and sanitize all inputs
    public function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return $this->sanitizeInput($item, $type);
            }, $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'string':
            default:
                $input = trim($input);
                $input = strip_tags($input);
                $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return $input;
        }
    }
    
    // Prevent XSS attacks
    public function escapeOutput($output) {
        if (is_array($output)) {
            return array_map([$this, 'escapeOutput'], $output);
        }
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    // CSRF Token Management
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        // Regenerate token every 1 hour
        if (time() - ($_SESSION['csrf_token_time'] ?? 0) > 3600) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    public function verifyCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Validate request origin
    public function validateOrigin() {
        $allowedOrigins = [
            $_SERVER['HTTP_HOST'] ?? '',
            parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST)
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        $originHost = parse_url($origin, PHP_URL_HOST);
        
        if (!empty($origin) && !in_array($originHost, $allowedOrigins)) {
            http_response_code(403);
            die('Invalid origin');
        }
        
        return true;
    }
    
    // Security headers
    public function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME-sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
               "img-src 'self' data: https: http:; " .
               "font-src 'self' data: https://cdnjs.cloudflare.com; " .
               "connect-src 'self' https://api.opendota.com https://api.stratz.com; " .
               "frame-ancestors 'none';";
        header("Content-Security-Policy: $csp");
        
        // Permissions Policy
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
        
        // HSTS for HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    // Log security events
    public function logSecurityEvent($type, $message, $severity = 'INFO') {
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $logEntry = "[$timestamp] [$severity] [$type] IP: $ip | $message | UA: $userAgent\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    // Validate file uploads (for future use)
    public function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid file upload');
        }
        
        // Check for upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('File size exceeds limit');
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file uploaded');
            default:
                throw new RuntimeException('Unknown upload error');
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('File size exceeds maximum allowed');
        }
        
        // Verify MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            throw new RuntimeException('Invalid file type');
        }
        
        return true;
    }
    
    // IP Blocking - Block malicious IPs automatically
    public function isIPBlocked($ip) {
        $blockFile = $this->storageDir . '/blocked_ips.json';
        if (!file_exists($blockFile)) {
            return false;
        }
        
        $blocked = json_decode(file_get_contents($blockFile), true) ?? [];
        
        // Check if IP is blocked and block hasn't expired
        if (isset($blocked[$ip]) && $blocked[$ip]['expires'] > time()) {
            return true;
        }
        
        return false;
    }
    
    public function blockIP($ip, $duration = 3600, $reason = 'Suspicious activity') {
        $blockFile = $this->storageDir . '/blocked_ips.json';
        $blocked = file_exists($blockFile) ? json_decode(file_get_contents($blockFile), true) : [];
        
        $blocked[$ip] = [
            'reason' => $reason,
            'blocked_at' => time(),
            'expires' => time() + $duration
        ];
        
        file_put_contents($blockFile, json_encode($blocked, JSON_PRETTY_PRINT));
        $this->logSecurityEvent('IP_BLOCKED', "IP $ip blocked for $duration seconds. Reason: $reason", 'CRITICAL');
    }
    
    public function unblockIP($ip) {
        $blockFile = $this->storageDir . '/blocked_ips.json';
        if (!file_exists($blockFile)) {
            return;
        }
        
        $blocked = json_decode(file_get_contents($blockFile), true) ?? [];
        unset($blocked[$ip]);
        file_put_contents($blockFile, json_encode($blocked, JSON_PRETTY_PRINT));
    }
    
    // Track failed attempts per IP
    public function trackFailedAttempt($ip, $type = 'general') {
        $key = md5($ip . '_' . $type);
        $filePath = $this->storageDir . '/' . $key . '_failed.json';
        
        $data = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : ['count' => 0, 'first' => time()];
        $data['count']++;
        $data['last'] = time();
        
        file_put_contents($filePath, json_encode($data));
        
        // Auto-block after 5 failed attempts within 5 minutes
        if ($data['count'] >= 5 && (time() - $data['first']) < 300) {
            $this->blockIP($ip, 1800, "Multiple failed attempts ($type)");
            return true; // IP was blocked
        }
        
        return false;
    }
    
    // Check for suspicious activity - Enhanced pattern detection
    public function detectSuspiciousActivity($blockOnDetection = true) {
        $suspiciousPatterns = [
            // SQL Injection
            'union\s+(all\s+)?select',
            'or\s+1\s*=\s*1',
            'or\s+\'1\'\s*=\s*\'1',
            ';\s*drop\s+table',
            ';\s*delete\s+from',
            'exec(\s|\+)+(s|x)p\w+',
            
            // XSS
            '<script[^>]*>',
            'javascript:',
            'onerror\s*=',
            'onload\s*=',
            'onclick\s*=',
            '<iframe',
            
            // Code Injection
            'eval\s*\(',
            'base64_decode',
            'system\s*\(',
            'exec\s*\(',
            'passthru\s*\(',
            
            // Path Traversal
            '\.\./\.\./',
            '\.\.\\\\\.\.\\\\',
            '/etc/passwd',
            '/etc/shadow',
            'c:\\\\windows',
            
            // Command Injection
            ';\s*cat\s+',
            ';\s*ls\s+',
            ';\s*wget\s+',
            ';\s*curl\s+',
            '/bin/bash',
            '/bin/sh',
        ];
        
        $requestData = array_merge($_GET, $_POST);
        $detectedThreats = [];
        
        foreach ($requestData as $key => $value) {
            $combined = $key . '=' . (is_string($value) ? $value : json_encode($value));
            
            foreach ($suspiciousPatterns as $pattern) {
                // Use # as delimiter to avoid conflicts with / in patterns
                if (@preg_match('#' . $pattern . '#i', $combined)) {
                    $threat = "Pattern '$pattern' in parameter '$key'";
                    $detectedThreats[] = $threat;
                    $this->logSecurityEvent('ATTACK_DETECTED', $threat, 'CRITICAL');
                }
            }
        }
        
        // Block if threats detected and blocking enabled
        if (!empty($detectedThreats) && $blockOnDetection) {
            $this->logSecurityEvent('ATTACK_BLOCKED', 'Blocked request with ' . count($detectedThreats) . ' threat patterns', 'CRITICAL');
            http_response_code(403);
            die('Forbidden: Malicious request detected');
        }
        
        return empty($detectedThreats);
    }
}

// Initialize security globally
function initSecurity() {
    $security = SecurityManager::getInstance();
    
    // Get client IP
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // CHECK 1: Is IP blocked?
    if ($security->isIPBlocked($clientIP)) {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode([
            'error' => 'Access Denied',
            'message' => 'Your IP has been temporarily blocked due to suspicious activity. Please try again later.'
        ]));
    }
    
    // CHECK 2: Set security headers (prevent XSS, clickjacking, etc.)
    $security->setSecurityHeaders();
    
    // CHECK 3: Rate limiting (prevents DDoS/brute force)
    $security->checkRateLimit($clientIP, 100, 60); // 100 requests per minute
    
    // CHECK 4: Detect and BLOCK suspicious activity (SQL injection, XSS, etc.)
    $isSafe = $security->detectSuspiciousActivity(true); // Block malicious requests
    if (!$isSafe) {
        // Activity was suspicious - track failed attempt
        $security->trackFailedAttempt($clientIP, 'attack');
    }
    
    // CHECK 5: Validate origin for POST/PUT/DELETE requests
    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
        $security->validateOrigin();
    }
    
    // CHECK 6: Verify CSRF token for state-changing requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['no_csrf_check'])) {
        // Skip CSRF check for API endpoints or if explicitly disabled
        if (!str_contains($_SERVER['REQUEST_URI'], '/api/') && isset($_POST['csrf_token'])) {
            if (!verify_csrf($_POST['csrf_token'])) {
                $security->logSecurityEvent('CSRF_VIOLATION', 'Invalid CSRF token', 'WARNING');
                http_response_code(403);
                die('Invalid security token. Please refresh and try again.');
            }
        }
    }
    
    // Cleanup old rate limit files periodically (1% chance per request)
    if (rand(1, 100) === 1) {
        $security->cleanupRateLimitFiles();
    }
    
    // Log legitimate access
    $security->logSecurityEvent('ACCESS', $_SERVER['REQUEST_URI'] ?? '/', 'INFO');
    
    // NOTE: Do NOT auto-sanitize $_GET/$_POST globally as it corrupts legitimate data
    // Instead, sanitize/escape at the point of use:
    // - Use sanitizeInput() when validating user input
    // - Use escapeOutput() or e() when displaying data in HTML
    // - Use proper type validation for API parameters
    
    return $security;
}

// Helper function to get CSRF token for forms
function csrf_token() {
    $security = SecurityManager::getInstance();
    return $security->generateCSRFToken();
}

// Helper function to verify CSRF token
function verify_csrf($token) {
    $security = SecurityManager::getInstance();
    return $security->verifyCSRFToken($token);
}

// Helper to escape output
function e($value) {
    $security = SecurityManager::getInstance();
    return $security->escapeOutput($value);
}
