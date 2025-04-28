<?php
include 'config.php';
include 'functions.php';
global $mysqli, $lang;

// Sprawdź, czy formularz został przesłany metodą POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Sanityzuj dane wejściowe
    $username = sanitize_input($_POST['username']);
    $password_plain = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Walidacja danych wejściowych
    if (empty($username) || empty($password_plain) || empty($password_confirm)) {
        set_flash_message(trans('register_validate_1'), 'negative');
    } elseif ($password_plain !== $password_confirm) {
        set_flash_message(trans('register_validate_2'), 'negative');
    } else {
        // Sprawdź, czy użytkownik już istnieje
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        if (!$stmt) {
            die(trans('register_error_query') . ': ' . $mysqli->error);
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            set_flash_message(trans('register_validate_3'), 'negative');
        } else {
            // Hashowanie hasła i dodanie użytkownika
            $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if (!$stmt) {
                die(trans('register_error_query') . ': ' . $mysqli->error);
            }
            $stmt->bind_param('ss', $username, $password_hash);
            if ($stmt->execute()) {
                set_flash_message(trans('register_complete'), 'positive');
                redirect('login.php');
            } else {
                set_flash_message(trans('register_error_general'), 'negative');
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo trans('register_title'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="majowka.css">
</head>
<body>
<div class="container">
    <h2><?php echo trans('register2'); ?></h2>
    <?php display_flash_message(); ?>
    <form method="post" action="">
        <input type="text" name="username" placeholder="<?php echo trans('username'); ?>" required>
        <input type="password" name="password" placeholder="<?php echo trans('password'); ?>" required>
        <input type="password" name="password_confirm" placeholder="<?php echo trans('password_confirm'); ?>" required>
        <button type="submit" name="register" class="Button1"><?php echo trans('register2'); ?></button>
    </form>
    <a href="index.php"><?php echo trans('login_back'); ?></a>
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
</div>
<div class="SO">
    <img id="shoutout_bartek" src="Shoutout_Bartosz_Giza.png" alt="BartoszGiza">
</div>
</body>
</html>
