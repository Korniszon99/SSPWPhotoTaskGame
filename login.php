<?php
include 'config.php';
include 'functions.php';
global $mysqli, $lang;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        set_flash_message(trans('login_error_empty'), 'negative');
        redirect('login.php');
    } else {
        $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?");
        if (!$stmt) {
            die(trans('db_query_error') . ': ' . $mysqli->error);
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();
            if (password_verify($password, $password_hash)) {
                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user_id;
                set_flash_message(trans('login_success'), 'positive');
                redirect('dashboard.php');
            } else {
                set_flash_message(trans('login_error_invalid'), 'negative');
                redirect('login.php');
            }
        } else {
            set_flash_message(trans('login_error_invalid'), 'negative');
            redirect('login.php');
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('login'); ?></title>
    <link rel="stylesheet" href="majowka.css">
</head>
<body>
<div class="container">
    <h2><?php echo trans('login'); ?></h2>
    <?php display_flash_message(); ?>
    <form method="post" action="">
        <input type="text" name="username" placeholder="<?php echo trans('username'); ?>" required><br />
        <input type="password" name="password" placeholder="<?php echo trans('password'); ?>" required><br />
        <button type="submit" name="login" class="Button1"><?php echo trans('login2'); ?></button>
    </form>
    <a href="index.php"><?php echo trans('login_back'); ?></a>
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/loga_sspw_wrs.png" alt="Logo SSPG">
</div>
<div class="SO">
    <img id="shoutout_bartek" src="graphics/Shoutout_Bartosz_Giza.png" alt="BartoszGiza">
</div>
</body>
</html>
