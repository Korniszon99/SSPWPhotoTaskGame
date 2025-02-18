<?php
include 'config.php';
include 'functions.php';

// Sprawdź, czy formularz został przesłany metodą POST i czy przycisk login został kliknięty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Sanityzuj dane wejściowe
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    // Sprawdź, czy pola username i password są wypełnione
    if (empty($username) || empty($password)) {
        set_flash_message('Wszystkie pola są wymagane.', 'negative');
        redirect('login.php');
    } else {
        // Przygotuj zapytanie SQL do pobrania użytkownika na podstawie nazwy użytkownika
        $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?");
        if (!$stmt) {
            die('Błąd zapytania: ' . $mysqli->error);
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        // Sprawdź, czy użytkownik istnieje
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();
            // Zweryfikuj hasło
            if (password_verify($password, $password_hash)) {
                // Zaloguj użytkownika
                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user_id;
                set_flash_message('Zalogowano pomyślnie', 'positive');
                redirect('dashboard.php');
            } else {
                set_flash_message('Nieprawidłowa nazwa użytkownika lub hasło!', 'negative');
                redirect('login.php');
            }
        } else {
            set_flash_message('Nieprawidłowa nazwa użytkownika lub hasło!', 'negative');
            redirect('login.php');
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Logowanie</h2>
        <?php display_flash_message(); ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Nazwa użytkownika" required><br />
            <input type="password" name="password" placeholder="Hasło" required><br />
            <button type="submit" name="login" class="Button1">Zaloguj się</button>
        </form>
        <a href="index.php">Powrót na stronę główną</a>
    </div>
    <div class="loga">
        <img id="sspg_logo_bottom" src="graphics\02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
        <img id="fut_logo_bottom" src="graphics\logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
    </div>
</body>

</html>