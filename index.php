<?php
include 'config.php';
include 'functions.php';
include 'header.php';

// Dodaj global $lang, aby uzyskać dostęp do zmiennej $lang z config.php
global $lang;
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('index_title'); ?></title> <!-- Użyj klucza 'index_title' -->
    <link rel="stylesheet" href="majowka.css">
</head>

<body>
<div class="container">
    <img src="graphics/majowka_2025.png" alt="Logo" style="max-width: 50%;">
    <h1><?php echo trans('index_h1'); ?></h1> <!-- Użyj klucza 'index_h1' -->
    <p><?php echo trans('index_p'); ?></p> <!-- Użyj klucza 'index_p' -->
    <?php
    display_flash_message();

    if (isset($_SESSION['username'])) {
        // Użyj klucza 'dashboard_h2' dla komunikatu powitalnego
        echo '<p>' . sprintf(trans('index_login'), htmlspecialchars($_SESSION['username'])) . '</p>';
        // Użyj klucza 'dashboard_b2' dla linku do zadań
        echo '<a href="dashboard.php">' . trans('index_dashboard') . '</a>';
        // Użyj klucza 'logout' dla linku do wylogowania
        echo '<a href="logout.php">' . trans('logout') . '</a>';
    } else {
        // Użyj klucza 'register' dla linku do rejestracji
        echo '<a href="register.php" class="Button1">' . trans('register') . '</a>';
        // Użyj klucza 'login' dla linku do logowania
        echo '<a href="login.php" class="Button1">' . trans('login') . '</a>';
    }
    ?>
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/loga_sspw_wrs.png" alt="Logo SSPG">
</div>
<div class="SO">
    <img id="shoutout_bartek" src="graphics/Shoutout_Bartosz_Giza.png" alt="BartoszGiza">
</div>
</body>

</html>