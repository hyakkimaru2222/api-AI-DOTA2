<?php
require_once 'includes/config.php';
require_once 'includes/api.php';
require_once 'includes/helpers.php';
initSecureSession();

// Redirect to security notice if not acknowledged
if (!isset($_SESSION['security_acknowledged'])) {
    header('Location: security-notice.php');
    exit;
}

$api = new OpenDotaAPI();

$heroStats = $api->getHeroStats();
$proMatches = $api->getProMatches();
$publicMatches = $api->getPublicMatches();
$heroes = $api->getHeroes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dota2ProTracker - Professional Dota 2 Statistics & Analytics</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <?php include 'views/home/intro-section.php'; ?>
            
            <?php include 'views/home/heroes-by-role-section.php'; ?>
            
            <?php include 'views/home/recent-matches-section.php'; ?>
            
            <?php include 'views/home/meta-builds-section.php'; ?>
            
            <?php include 'views/home/tournament-section.php'; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
