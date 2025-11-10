<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';
initSecureSession();

// Log security page access
$security = SecurityManager::getInstance();
$security->logSecurityEvent('SECURITY_PAGE_VIEW', 'User viewed security page', 'INFO');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Features - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="security-page">
                <div class="security-hero">
                    <i class="fas fa-shield-alt security-hero-icon"></i>
                    <h1>Website Security & Protection</h1>
                    <p class="security-subtitle">Your safety is our top priority. This website employs multiple layers of security to protect against attacks and ensure your data is safe.</p>
                </div>

                <!-- Active Protection Status -->
                <div class="protection-status">
                    <h2><i class="fas fa-check-circle"></i> Active Protection Systems</h2>
                    <div class="status-grid">
                        <div class="status-card active">
                            <i class="fas fa-shield-virus"></i>
                            <h3>DDoS Protection</h3>
                            <span class="status-badge">ACTIVE</span>
                            <p>Rate limiting: 100 requests/minute per IP</p>
                        </div>
                        <div class="status-card active">
                            <i class="fas fa-lock"></i>
                            <h3>Secure Sessions</h3>
                            <span class="status-badge">ACTIVE</span>
                            <p>HttpOnly, SameSite=Strict, Auto-regeneration</p>
                        </div>
                        <div class="status-card active">
                            <i class="fas fa-user-shield"></i>
                            <h3>Attack Detection</h3>
                            <span class="status-badge">ACTIVE</span>
                            <p>SQL injection & XSS pattern monitoring</p>
                        </div>
                        <div class="status-card active">
                            <i class="fas fa-file-code"></i>
                            <h3>Input Sanitization</h3>
                            <span class="status-badge">ACTIVE</span>
                            <p>All user inputs cleaned and validated</p>
                        </div>
                        <div class="status-card active">
                            <i class="fas fa-server"></i>
                            <h3>Security Headers</h3>
                            <span class="status-badge">ACTIVE</span>
                            <p>CSP, X-Frame-Options, HSTS enabled</p>
                        </div>
                        <div class="status-card active">
                            <i class="fas fa-file-medical-alt"></i>
                            <h3>Security Logging</h3>
                            <span class="status-badge">ACTIVE</span>
                            <p>All suspicious activity logged</p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Features -->
                <div class="security-features">
                    <h2>Security Features Explained</h2>
                    
                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Rate Limiting & DDoS Protection</h3>
                            <p>Prevents attackers from overwhelming the server with too many requests:</p>
                            <ul>
                                <li><strong>IP-based limiting:</strong> Maximum 100 requests per minute per IP address</li>
                                <li><strong>Automatic blocking:</strong> Excessive requests receive 429 error with retry timer</li>
                                <li><strong>Protection against:</strong> Brute force attacks, credential stuffing, DDoS attempts</li>
                                <li><strong>Gradual reset:</strong> Counters reset after time window expires</li>
                            </ul>
                        </div>
                    </div>

                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-cookie-bite"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Secure Session Management</h3>
                            <p>Advanced session security protects your browsing session:</p>
                            <ul>
                                <li><strong>HttpOnly Cookies:</strong> JavaScript cannot access cookies, preventing XSS theft</li>
                                <li><strong>SameSite=Strict:</strong> Cookies only sent with same-site requests, blocking CSRF</li>
                                <li><strong>Secure Flag:</strong> Cookies only transmitted over HTTPS connections</li>
                                <li><strong>Auto-regeneration:</strong> Session IDs regenerated on first access and every 30 minutes</li>
                                <li><strong>Custom session name:</strong> Non-default name (D2PT_SESSION) for added obscurity</li>
                                <li><strong>Session-only cookies:</strong> Cookies expire when browser closes</li>
                            </ul>
                        </div>
                    </div>

                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-biohazard"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Attack Detection & Prevention</h3>
                            <p>Real-time monitoring for malicious activity:</p>
                            <ul>
                                <li><strong>SQL Injection Detection:</strong> Monitors for UNION SELECT, OR 1=1, and other SQL patterns</li>
                                <li><strong>XSS Prevention:</strong> Detects &lt;script&gt; tags, javascript: URLs, and eval() attempts</li>
                                <li><strong>Path Traversal Protection:</strong> Blocks ../, /etc/passwd, and directory navigation attacks</li>
                                <li><strong>Code Injection Detection:</strong> Monitors for base64_decode, shell commands, and RCE attempts</li>
                                <li><strong>Automatic logging:</strong> All suspicious patterns logged with IP, timestamp, and user agent</li>
                                <li><strong>Future blocking:</strong> Can be configured to automatically block detected attacks</li>
                            </ul>
                        </div>
                    </div>

                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Input Sanitization & Validation</h3>
                            <p>All user inputs are thoroughly cleaned:</p>
                            <ul>
                                <li><strong>HTML stripping:</strong> Removes all HTML tags from inputs</li>
                                <li><strong>Special character encoding:</strong> Converts special characters to safe entities</li>
                                <li><strong>Type-specific filtering:</strong> Email, URL, integer, and float validation</li>
                                <li><strong>Recursive sanitization:</strong> Arrays and nested data properly cleaned</li>
                                <li><strong>Output escaping:</strong> All displayed data escaped to prevent XSS</li>
                                <li><strong>UTF-8 encoding:</strong> Proper character encoding prevents injection</li>
                            </ul>
                        </div>
                    </div>

                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Security Headers</h3>
                            <p>HTTP headers that instruct browsers to enforce security:</p>
                            <ul>
                                <li><strong>Content-Security-Policy:</strong> Controls which resources can be loaded (scripts, styles, images)</li>
                                <li><strong>X-Frame-Options: DENY:</strong> Prevents clickjacking by blocking iframe embedding</li>
                                <li><strong>X-Content-Type-Options: nosniff:</strong> Prevents MIME-type sniffing attacks</li>
                                <li><strong>X-XSS-Protection:</strong> Enables browser's built-in XSS filter</li>
                                <li><strong>Referrer-Policy:</strong> Controls how much referrer information is sent</li>
                                <li><strong>Permissions-Policy:</strong> Disables unnecessary browser features (geolocation, camera, mic)</li>
                                <li><strong>HSTS (on HTTPS):</strong> Forces all connections to use HTTPS for 1 year</li>
                            </ul>
                        </div>
                    </div>

                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Security Logging & Monitoring</h3>
                            <p>Comprehensive logging for security analysis:</p>
                            <ul>
                                <li><strong>Event logging:</strong> All security events recorded with timestamps</li>
                                <li><strong>IP tracking:</strong> Source IP addresses logged for all suspicious activity</li>
                                <li><strong>User agent logging:</strong> Browser/bot identification for pattern analysis</li>
                                <li><strong>Severity levels:</strong> INFO, WARNING, and CRITICAL categorization</li>
                                <li><strong>Protected logs:</strong> Log files not accessible via web browser (.htaccess protection)</li>
                                <li><strong>Structured format:</strong> Easy to parse and analyze for security audits</li>
                            </ul>
                        </div>
                    </div>

                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="feature-content">
                            <h3>File & Directory Protection</h3>
                            <p>Server-level protection via .htaccess:</p>
                            <ul>
                                <li><strong>Directory listing disabled:</strong> Cannot browse server directories</li>
                                <li><strong>Sensitive files blocked:</strong> .htaccess, .ini, .log, .md files not accessible</li>
                                <li><strong>Protected directories:</strong> /includes/, /logs/, /cache/ blocked from web access</li>
                                <li><strong>Git files hidden:</strong> .git directory and files not accessible</li>
                                <li><strong>File upload validation:</strong> MIME type checking, size limits, extension validation</li>
                                <li><strong>Server signature removal:</strong> Hides server version information</li>
                            </ul>
                        </div>
                    </div>

                    <div class="feature-section">
                        <div class="feature-icon-box">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="feature-content">
                            <h3>PHP Security Hardening</h3>
                            <p>Server-side PHP configuration for security:</p>
                            <ul>
                                <li><strong>expose_php disabled:</strong> Hides PHP version from headers</li>
                                <li><strong>allow_url_include disabled:</strong> Prevents remote file inclusion attacks</li>
                                <li><strong>Error display controlled:</strong> Errors logged but not shown in production</li>
                                <li><strong>Strict session mode:</strong> Rejects uninitialized session IDs</li>
                                <li><strong>Error logging:</strong> All PHP errors logged to secure location</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- What This Means For You -->
                <div class="user-benefits">
                    <h2>What This Means For You</h2>
                    <div class="benefits-grid">
                        <div class="benefit-card">
                            <i class="fas fa-user-check"></i>
                            <h4>Safe Browsing</h4>
                            <p>Your session and data are protected from theft and manipulation</p>
                        </div>
                        <div class="benefit-card">
                            <i class="fas fa-shield-alt"></i>
                            <h4>Attack Prevention</h4>
                            <p>Multiple layers of protection against hackers and malicious bots</p>
                        </div>
                        <div class="benefit-card">
                            <i class="fas fa-lock"></i>
                            <h4>Privacy Protected</h4>
                            <p>Your browsing activity and data stay private and secure</p>
                        </div>
                        <div class="benefit-card">
                            <i class="fas fa-tachometer-alt"></i>
                            <h4>Always Available</h4>
                            <p>DDoS protection ensures the site stays online and responsive</p>
                        </div>
                    </div>
                </div>

                <!-- Security Indicator -->
                <div class="security-indicator-section">
                    <h2>Security Indicator</h2>
                    <p>Look for the green "Secured" badge in the header and the security icon in the navigation. These indicate all protection systems are active.</p>
                    <div class="indicator-preview">
                        <div class="nav-brand-preview">
                            <i class="fas fa-gamepad"></i>
                            <span class="brand-text">Dota2ProTracker</span>
                            <span class="security-badge">
                                <i class="fas fa-shield-alt"></i> Secured
                            </span>
                        </div>
                    </div>
                </div>

                <div class="back-to-home">
                    <a href="/" class="back-home-btn">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
