<?php
require_once 'includes/config.php';
require_once 'includes/advanced-protection.php';
initSecureSession();

// This page is for site admins only - add authentication here if needed
$security = SecurityManager::getInstance();

// Get security statistics
$logFile = __DIR__ . '/logs/security.log';
$blockFile = __DIR__ . '/cache/ratelimit/blocked_ips.json';

$recentLogs = [];
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice(array_reverse($logs), 0, 50);
}

$blockedIPs = [];
if (file_exists($blockFile)) {
    $blockedIPs = json_decode(file_get_contents($blockFile), true) ?? [];
}

// Count events by type
$eventCounts = [
    'ATTACK_DETECTED' => 0,
    'ATTACK_BLOCKED' => 0,
    'RATE_LIMIT_EXCEEDED' => 0,
    'IP_BLOCKED' => 0,
    'CSRF_VIOLATION' => 0
];

foreach ($recentLogs as $log) {
    foreach ($eventCounts as $type => $count) {
        if (strpos($log, $type) !== false) {
            $eventCounts[$type]++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Monitor - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .security-dashboard {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .security-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
        }
        
        .stat-card i {
            font-size: 48px;
            color: var(--cyan-primary);
            margin-bottom: 16px;
        }
        
        .stat-card.danger i {
            color: #ef4444;
        }
        
        .stat-card.warning i {
            color: #f59e0b;
        }
        
        .stat-card.success i {
            color: #10b981;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: white;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .security-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .security-section h2 {
            color: white;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .blocked-ip-item {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .ip-info {
            flex: 1;
        }
        
        .ip-address {
            color: white;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .ip-reason {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .ip-time {
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 4px;
        }
        
        .unblock-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .unblock-btn:hover {
            opacity: 0.9;
        }
        
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 8px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        
        .log-entry.critical {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .log-entry.warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .log-container {
            max-height: 500px;
            overflow-y: auto;
            background: var(--bg-tertiary);
            border-radius: 8px;
            padding: 12px;
        }
    </style>
</head>
<body>
    <div class="security-dashboard">
        <h1 style="color: white; margin-bottom: 32px;">
            <i class="fas fa-shield-alt"></i> Security Monitor
        </h1>
        
        <!-- Security Statistics -->
        <div class="security-stats">
            <div class="stat-card danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="stat-number"><?php echo $eventCounts['ATTACK_DETECTED']; ?></div>
                <div class="stat-label">Attacks Detected</div>
            </div>
            
            <div class="stat-card danger">
                <i class="fas fa-ban"></i>
                <div class="stat-number"><?php echo $eventCounts['ATTACK_BLOCKED']; ?></div>
                <div class="stat-label">Attacks Blocked</div>
            </div>
            
            <div class="stat-card warning">
                <i class="fas fa-clock"></i>
                <div class="stat-number"><?php echo $eventCounts['RATE_LIMIT_EXCEEDED']; ?></div>
                <div class="stat-label">Rate Limit Violations</div>
            </div>
            
            <div class="stat-card danger">
                <i class="fas fa-user-slash"></i>
                <div class="stat-number"><?php echo count($blockedIPs); ?></div>
                <div class="stat-label">Blocked IPs</div>
            </div>
            
            <div class="stat-card warning">
                <i class="fas fa-shield-alt"></i>
                <div class="stat-number"><?php echo $eventCounts['CSRF_VIOLATION']; ?></div>
                <div class="stat-label">CSRF Violations</div>
            </div>
        </div>
        
        <!-- Blocked IPs -->
        <div class="security-section">
            <h2><i class="fas fa-ban"></i> Blocked IP Addresses</h2>
            <?php if (empty($blockedIPs)): ?>
                <p style="color: var(--text-secondary);">No IPs currently blocked.</p>
            <?php else: ?>
                <?php foreach ($blockedIPs as $ip => $data): ?>
                    <div class="blocked-ip-item">
                        <div class="ip-info">
                            <div class="ip-address"><?php echo htmlspecialchars($ip); ?></div>
                            <div class="ip-reason">Reason: <?php echo htmlspecialchars($data['reason']); ?></div>
                            <div class="ip-time">
                                Blocked: <?php echo date('Y-m-d H:i:s', $data['blocked_at']); ?> • 
                                Expires: <?php echo date('Y-m-d H:i:s', $data['expires']); ?>
                                <?php if ($data['expires'] < time()): ?><span style="color: #10b981;"> (Expired)</span><?php endif; ?>
                            </div>
                        </div>
                        <button class="unblock-btn" onclick="unblockIP('<?php echo $ip; ?>')">
                            <i class="fas fa-unlock"></i> Unblock
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Recent Security Events -->
        <div class="security-section">
            <h2><i class="fas fa-list"></i> Recent Security Events (Last 50)</h2>
            <div class="log-container">
                <?php if (empty($recentLogs)): ?>
                    <p style="color: var(--text-secondary);">No security events logged.</p>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <?php
                        $logClass = '';
                        if (strpos($log, 'CRITICAL') !== false) $logClass = 'critical';
                        elseif (strpos($log, 'WARNING') !== false) $logClass = 'warning';
                        ?>
                        <div class="log-entry <?php echo $logClass; ?>"><?php echo htmlspecialchars($log); ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Security Features Summary -->
        <div class="security-section">
            <h2><i class="fas fa-lock"></i> Active Security Features</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
                <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
                    <i class="fas fa-check" style="color: #10b981;"></i> <strong>IP Blocking</strong><br>
                    <small style="color: var(--text-secondary);">Automatic blocking after 5 failed attempts</small>
                </div>
                <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
                    <i class="fas fa-check" style="color: #10b981;"></i> <strong>Rate Limiting</strong><br>
                    <small style="color: var(--text-secondary);">100 requests per minute per IP</small>
                </div>
                <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
                    <i class="fas fa-check" style="color: #10b981;"></i> <strong>SQL Injection Protection</strong><br>
                    <small style="color: var(--text-secondary);">Pattern detection and blocking</small>
                </div>
                <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
                    <i class="fas fa-check" style="color: #10b981;"></i> <strong>XSS Prevention</strong><br>
                    <small style="color: var(--text-secondary);">Input sanitization and CSP headers</small>
                </div>
                <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
                    <i class="fas fa-check" style="color: #10b981;"></i> <strong>CSRF Protection</strong><br>
                    <small style="color: var(--text-secondary);">Token-based form protection</small>
                </div>
                <div style="background: var(--bg-tertiary); padding: 16px; border-radius: 8px;">
                    <i class="fas fa-check" style="color: #10b981;"></i> <strong>Secure Headers</strong><br>
                    <small style="color: var(--text-secondary);">HSTS, CSP, X-Frame-Options, etc.</small>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 32px;">
            <a href="/" style="color: var(--cyan-primary); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Homepage
            </a>
        </div>
    </div>
    
    <script>
        function unblockIP(ip) {
            if (confirm('Are you sure you want to unblock IP: ' + ip + '?')) {
                // Add AJAX call to unblock IP here
                alert('IP unblocking functionality requires backend implementation');
                location.reload();
            }
        }
        
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
