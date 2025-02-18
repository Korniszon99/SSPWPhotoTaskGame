<?php
include 'config.php';
include 'functions.php';

// Sprawdź, czy formularz został przesłany metodą POST i czy przycisk register został kliknięty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Sanityzuj dane wejściowe
    $username = sanitize_input($_POST['username']);
    $password_plain = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Walidacja danych wejściowych
    if (empty($username) || empty($password_plain) || empty($password_confirm)) {
        set_flash_message('Wszystkie pola są wymagane.', 'negative');
    } elseif ($password_plain !== $password_confirm) {
        set_flash_message('Hasła nie są takie same.', 'negative');
    } else {
        // Przygotuj zapytanie SQL do sprawdzenia, czy użytkownik o podanej nazwie już istnieje
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        if (!$stmt) {
            die('Błąd zapytania: ' . $mysqli->error);
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        // Sprawdź, czy użytkownik o podanej nazwie już istnieje
        if ($stmt->num_rows > 0) {
            set_flash_message('Użytkownik o tej nazwie już istnieje!', 'negative');
        } else {
            // Zaszyfruj hasło i dodaj nowego użytkownika do bazy danych
            $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if (!$stmt) {
                die('Błąd zapytania: ' . $mysqli->error);
            }
            $stmt->bind_param('ss', $username, $password_hash);
            if ($stmt->execute()) {
                set_flash_message('Rejestracja zakończona sukcesem! Możesz się teraz zalogować.', 'positive');
                redirect('login.php');
            } else {
                set_flash_message('Błąd rejestracji', 'negative');
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Rejestracja</h2>
        <?php display_flash_message(); ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Nazwa użytkownika" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <input type="password" name="password_confirm" placeholder="Potwierdź hasło" required>
            <button type="submit" name="register" class="Button1">Zarejestruj się</button>
        </form>
        <a href="index.php">Powrót na stronę główną</a>
    </div>
    <div class="loga">
        <img id="sspg_logo_bottom" src="graphics\02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
        <img id="fut_logo_bottom" src="graphics\logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
    </div>
</body>

</html>