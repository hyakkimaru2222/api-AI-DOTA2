<?php
// TEST PAGE - View icons without security verification
require_once 'includes/config.php';
require_once 'includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Icon Test - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            padding: 40px;
        }
        .test-section {
            background: var(--bg-card);
            border: 2px solid var(--cyan-primary);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .test-section h2 {
            color: var(--cyan-primary);
            margin-bottom: 20px;
        }
        .icon-showcase {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .icon-item {
            text-align: center;
        }
        .icon-item i {
            font-size: 32px;
            color: var(--cyan-primary);
            margin-bottom: 10px;
            display: block;
        }
        .icon-item span {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
        }
        .header-preview {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: var(--cyan-primary); margin-bottom: 40px;">🛡️ Icon Test Page</h1>
        
        <div class="test-section">
            <h2>Font Awesome Icons Test</h2>
            <div class="icon-showcase">
                <div class="icon-item">
                    <i class="fab fa-discord"></i>
                    <span>Discord</span>
                </div>
                <div class="icon-item">
                    <i class="fab fa-twitter"></i>
                    <span>Twitter</span>
                </div>
                <div class="icon-item">
                    <i class="fab fa-youtube"></i>
                    <span>YouTube</span>
                </div>
                <div class="icon-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                </div>
                <div class="icon-item">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span>Icons Loading!</span>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h2>Header Preview with Icons</h2>
            <div class="header-preview">
                <?php include 'includes/header.php'; ?>
            </div>
        </div>

        <div class="test-section">
            <h2>Nav Icons Code (As Styled)</h2>
            <div class="nav-icons">
                <a href="#" class="icon-link"><i class="fab fa-discord"></i></a>
                <a href="#" class="icon-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="icon-link"><i class="fab fa-youtube"></i></a>
                <a href="/security-monitor.php" class="icon-link security-link" title="View Security Features">
                    <i class="fas fa-shield-alt"></i>
                </a>
            </div>
            <p style="color: var(--text-secondary); margin-top: 20px;">
                ☝️ These are the exact icons from your header, styled with background boxes.
            </p>
        </div>

        <div class="test-section">
            <h2>✅ What You Should See</h2>
            <ul style="color: var(--text-secondary); line-height: 2;">
                <li>✅ 4 icons with dark gray background boxes</li>
                <li>✅ Discord icon (chat bubble/controller)</li>
                <li>✅ Twitter icon (bird)</li>
                <li>✅ YouTube icon (play button)</li>
                <li>✅ Shield icon (glowing green) - this opens security monitor</li>
                <li>✅ Icons turn cyan when you hover over them</li>
                <li>✅ Shield icon has a subtle pulse animation</li>
            </ul>
        </div>

        <div class="test-section">
            <h2>🔍 Debug Information</h2>
            <pre style="background: var(--bg-primary); padding: 15px; border-radius: 6px; color: var(--success); font-size: 12px;">
Font Awesome Loaded: <span id="fa-status">Checking...</span>
Browser Width: <span id="width"></span>px
Header Icons Count: <span id="icon-count"></span>
            </pre>
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <a href="/" style="display: inline-block; background: var(--cyan-primary); color: white; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                ← Back to Homepage
            </a>
        </div>
    </div>

    <script>
        // Check Font Awesome
        document.addEventListener('DOMContentLoaded', function() {
            const faIcon = document.querySelector('.fab.fa-discord');
            const computedStyle = window.getComputedStyle(faIcon, ':before');
            const content = computedStyle.getPropertyValue('content');
            
            document.getElementById('fa-status').textContent = content !== 'none' ? '✅ YES' : '❌ NO';
            document.getElementById('width').textContent = window.innerWidth;
            document.getElementById('icon-count').textContent = document.querySelectorAll('.nav-icons .icon-link').length;
        });
    </script>
</body>
</html>
