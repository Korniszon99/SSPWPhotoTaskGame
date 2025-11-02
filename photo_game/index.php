<?php
include __DIR__ . '/config/config.php';

if (isset($_SESSION['username'])) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#8b5cf6">
    <link rel="apple-touch-icon" href="graphics/logo-fut.png">
    <title><?php __('appTitleFull') ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="js/pwa.js" defer></script>
</head>
<body>
<!-- Header -->
<header class="header" role="banner">
    <div class="logo">
        <a href="/photo_game/dashboard.php" aria-label="<?php __('appTitle')?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
            <img src="/photo_game/graphics/logo-fut.png" alt="<?php __('appTitleFull')?>">
            <span><?php __('appTitle')?></span>
        </a>
    </div>
    <div class="header-right">
        <nav class="language-switcher" role="navigation" aria-label="Language selector">
            <a href="?lang=pl"
               class="<?= $currentLang === 'pl' ? 'active' : '' ?>"
               lang="pl"
               hreflang="pl"
                <?= $currentLang === 'pl' ? 'aria-current="true"' : '' ?>>
                <img src="/photo_game/graphics/pl.svg" alt="" class="flag-icon" role="presentation">
                <span>PL</span>
            </a>
            <a href="?lang=en"
               class="<?= $currentLang === 'en' ? 'active' : '' ?>"
               lang="en"
               hreflang="en"
                <?= $currentLang === 'en' ? 'aria-current="true"' : '' ?>>
                <img src="/photo_game/graphics/en.svg" alt="" class="flag-icon" role="presentation">
                <span>EN</span>
            </a>
            <?php $otherLang = $currentLang === 'pl' ? 'en' : 'pl' ?>
            <a href="?lang=<?= $otherLang ?>"
               class="mobile active"
               lang="<?= $otherLang ?>"
               hreflang="<?= $otherLang ?>"
                <?= $currentLang === 'en' ? 'aria-current="true"' : '' ?>>
                <img src="/photo_game/graphics/<?= $currentLang ?>.svg" alt="" class="flag-icon" role="presentation">
            </a>
        </nav>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <div class="welcome-section">
        <h1><?php __('appTitleFull') ?></h1>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-top: 1rem;"><?php __('welcomeMessage') ?></p>

        <?php display_flash_message(); ?>

        <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem; align-items: center;">
            <a href="auth/register.php" class="Button1"><?php __('signup') ?></a>
            <a href="auth/login.php" class="Button1"><?php __('login') ?></a>
        </div>
    </div>
</main>

<?php include 'elements/footer.php'; ?>
</body>
</html>