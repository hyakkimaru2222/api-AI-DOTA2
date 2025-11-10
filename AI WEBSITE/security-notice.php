<?php
require_once 'includes/config.php';
initSecureSession();

// If already acknowledged, redirect to home
if (isset($_SESSION['security_acknowledged'])) {
    header('Location: /');
    exit;
}

// Handle acknowledgment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acknowledge'])) {
    $_SESSION['security_acknowledged'] = true;
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Required - Dota2ProTracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #0a0e1a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .verification-container {
            max-width: 500px;
            width: 100%;
            background: #1a2332;
            border: 1px solid #2d3748;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .verification-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #2d3748;
        }
        
        .site-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .site-info h1 {
            font-size: 1.25rem;
            color: #e5e7eb;
            margin: 0 0 0.25rem 0;
        }
        
        .site-info p {
            font-size: 0.875rem;
            color: #9ca3af;
            margin: 0;
        }
        
        .verification-box {
            background: #0f1621;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            margin: 0 auto 1rem;
            border: 4px solid #2d3748;
            border-top-color: #10b981;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .verification-box h2 {
            color: #e5e7eb;
            font-size: 1.25rem;
            margin: 0 0 0.5rem 0;
        }
        
        .verification-box p {
            color: #9ca3af;
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.6;
        }
        
        .security-info {
            background: #0f1621;
            border: 1px solid #2d3748;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .security-info h3 {
            color: #06b6d4;
            font-size: 0.875rem;
            margin: 0 0 0.75rem 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .security-badges {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
        
        .badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #1a2332;
            border-radius: 4px;
            font-size: 0.8rem;
            color: #d1d5db;
        }
        
        .badge i {
            color: #10b981;
            font-size: 1rem;
        }
        
        .verify-btn {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .verify-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }
        
        .footer-text {
            text-align: center;
            color: #6b7280;
            font-size: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #2d3748;
        }
        
        .footer-text i {
            color: #10b981;
            margin-right: 0.25rem;
        }
        
        .checkmark {
            display: inline-block;
            width: 20px;
            height: 20px;
            background: #10b981;
            border-radius: 50%;
            position: relative;
            margin-right: 0.5rem;
        }
        
        .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.875rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <div class="site-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="site-info">
                <h1>Dota2ProTracker</h1>
                <p>dota2protracker.com</p>
            </div>
        </div>
        
        <div class="verification-box">
            <div class="spinner"></div>
            <h2>Verifying you are human</h2>
            <p>This process is automatic. Your browser will redirect shortly.</p>
        </div>
        
        <div class="security-info">
            <h3><i class="fas fa-lock"></i> Security Check</h3>
            <div class="security-badges">
                <div class="badge">
                    <i class="fas fa-shield-virus"></i>
                    <span>DDoS Protection</span>
                </div>
                <div class="badge">
                    <i class="fas fa-user-shield"></i>
                    <span>Bot Detection</span>
                </div>
                <div class="badge">
                    <i class="fas fa-lock"></i>
                    <span>Encrypted Session</span>
                </div>
                <div class="badge">
                    <i class="fas fa-check-circle"></i>
                    <span>Secure Connection</span>
                </div>
            </div>
        </div>
        
        <form method="POST">
            <button type="submit" name="acknowledge" class="verify-btn">
                <span class="checkmark"></span>
                <span>I'm not a robot - Continue</span>
            </button>
        </form>
        
        <div class="footer-text">
            <i class="fas fa-shield-alt"></i>
            Protected by D2PT Security • Your connection is secure and encrypted
        </div>
    </div>
</body>
</html>
