<?php
include 'config.php';
include 'functions.php';
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strona główna</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <img src="graphics\MessengerGrupa@2x.png" alt="Logo" style="max-width: 50%;">
        <h1>Zjazd FUT PG - gra fotograficzna</h1>
        <p>Witaj, wylosuj zadania, rób zdjęcia i baw się dobrze</p>
        <?php
        display_flash_message();

        if (isset($_SESSION['username'])) {
            echo '<p>Jesteś zalogowany jako ' . htmlspecialchars($_SESSION['username']) . '.</p>';
            echo '<a href="dashboard.php">Przejdź do dashboardu</a>';
            echo '<a href="logout.php">Wyloguj się</a>';
        } else {
            echo '<a href="register.php" class="Button1" >Rejestracja</a>';
            echo '<a href="login.php" class="Button1" >Logowanie</a>';
        }
        ?>
    </div>
    <div class="loga">
        <img id="sspg_logo_bottom" src="graphics\SSPG-Logo-Pozytyw-Poziom-Niebieski_large_RGB.png" alt="Logo SSPG">
        <img id="fut_logo_bottom" src="graphics\logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
    </div>
</body>

</html>