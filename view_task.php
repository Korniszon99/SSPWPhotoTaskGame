<?php
include 'config.php';
include 'functions.php';
global $mysqli;
global $lang;

if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT tasks.description FROM user_tasks JOIN tasks ON user_tasks.task_id = tasks.id WHERE user_tasks.user_id = ? ORDER BY user_tasks.id DESC LIMIT 1");
if (!$stmt) {
    die(trans('query_error', $lang)); // Użyj klucza 'query_error'
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($task_description);
$stmt->fetch();
$stmt->close();

if (!$task_description) {
    set_flash_message(trans('view_task_error_1', $lang), 'negative'); // Użyj klucza 'view_task_error_1'
    redirect('dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('view_task_title'); ?></title> <!-- Użyj klucza 'view_task_title' -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
    <?php display_flash_message(); ?>
    <h2><?php echo trans('view_task_h'); ?></h2> <!-- Użyj klucza 'view_task_h' -->
    <p><?php echo htmlspecialchars($task_description); ?></p>
    <a href="dashboard.php" class="Button1"><?php echo trans('back'); ?></a> <!-- Użyj klucza 'back' -->
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
    <img id="fut_logo_bottom" src="graphics/logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
</div>
</body>

</html>