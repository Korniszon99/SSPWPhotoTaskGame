<?php
include __DIR__ . '/../config/config.php';

// Sprawdź, czy formularz został przesłany metodą POST i czy przycisk login został kliknięty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // RATE LIMITING
    $check = $rateLimiter->check('login');
    if (is_array($check) && !$check['allowed']) {
        set_flash_message(
            translatePlural('loginRateLimitReached', $check['retry_after']),
            'negative'
        );
        redirect('login.php');
        exit;
    }

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message(translate('invalidCSRFToken'), 'negative');
        redirect('login.php');
        exit;
    }

    // Sanityzuj dane wejściowe
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $access_code = sanitize_input(strtoupper($_POST['access_code']) ?? '');

    // Sprawdź, czy pola username i password są wypełnione
    if (empty($username) || empty($password)) {
        set_flash_message(translate('allFieldsRequired'), 'negative');
        redirect('login.php');
    } else {
        try {
            $user = $db->getUserByUsername($username);

            // Zaloguj użytkownika
            if ($user && password_verify($password, $user['password'])) {
                // Sprawdź czy użytkownik jest adminem
                $is_admin = $db->isUserAdmin($user['id']);

                // Sprawdź kod dostępu (jeśli wymagany)
                if ($db->isAccessCodeRequired() && !$is_admin) {
                    if (empty($access_code)) {
                        set_flash_message(translate('codeIsRequired'), 'negative');
                        redirect('login.php');
                        exit;
                    }

                    if (!$db->verifyAccessCode($access_code)) {
                        set_flash_message(translate('invalidCode'), 'negative');
                        redirect('login.php');
                        exit;
                    }
                }

                // Jeśli logowanie jest wyłączone i użytkownik NIE jest adminem - blokuj
                if (!$db->isLoginEnabled() && !$is_admin) {
                    set_flash_message(translate('loginDisabled'), 'negative');
                    redirect('login.php');
                    exit;
                }

                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user['id'];
                regenerateCSRFToken();
                set_flash_message(translate('loggedIn'), 'positive');
                redirect('../dashboard.php');
            } else {
                set_flash_message(translate('invalidCredentials'), 'negative');
                redirect('login.php');
            }
        } catch (Exception $e) {
            error_log('Błąd login.php: ' . $e->getMessage());
            set_flash_message(translate('loginError'), 'negative');
            redirect('login.php');
        }
    }
}

// Sprawdź czy kod dostępu jest wymagany
$access_code_required = $db->isAccessCodeRequired();
?>
<!DOCTYPE html>
<html lang="<?php __('locale')?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="../manifest.json">
    <meta name="theme-color" content="#8b5cf6">
    <link rel="apple-touch-icon" href="../graphics/logo-fut.png">
    <title><?php __('appTitleFull')?></title>
    <link rel="stylesheet" href="../style.css">
    <script src="../js/pwa.js" defer></script>
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
        <h1><?php __('loggingIn')?></h1>
        <?php display_flash_message(); ?>

        <form method="post" action="" style="display: flex; flex-direction: column; align-items: center; gap: 1rem; margin-top: 2rem;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <?php if ($access_code_required): ?>
                <input type="text"
                       name="access_code"
                       placeholder="<?php __('accessCode') ?>"
                       required
                       style="text-transform: uppercase;"
                       autocomplete="off">
            <?php endif; ?>

            <input type="text" name="username" placeholder="<?php __('username') ?>" required>
            <input type="password" name="password" placeholder="<?php __('password') ?>" required>

            <button type="submit" name="login" class="Button1"><?php __('login') ?></button>
        </form>

        <?php if ($access_code_required): ?>
            <p style="margin-top: 1rem; opacity: 0.7; font-size: 0.9rem;">
                ℹ️ <?php __('accessCodeDescription') ?>
            </p>
        <?php endif; ?>

        <p style="margin-top: 2rem;">
            <a href="../index.php"><?php __('backToMainPage') ?></a>
        </p>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>