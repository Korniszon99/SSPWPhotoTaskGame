<?php
include 'config.php';
include 'functions.php';
global $mysqli;
global $lang;

if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

if (!isset($_GET['photo_id'])) {
    set_flash_message(trans('view_error_1', $lang), 'negative'); // Użyj klucza 'view_error_1'
    redirect('gallery.php');
}

$photo_id = intval($_GET['photo_id']);

$stmt = $mysqli->prepare("SELECT completed_tasks.photo, completed_tasks.uploaded_at, tasks.description, users.username FROM completed_tasks JOIN user_tasks ON completed_tasks.user_task_id = user_tasks.id JOIN tasks ON user_tasks.task_id = tasks.id JOIN users ON user_tasks.user_id = users.id WHERE completed_tasks.id = ?");
if (!$stmt) {
    die(trans('query_error', $lang)); // Użyj klucza 'query_error'
}
$stmt->bind_param('i', $photo_id);
$stmt->execute();
$result = $stmt->get_result();
$photo = $result->fetch_assoc();
$stmt->close();

if (!$photo) {
    set_flash_message(trans('view_error_2', $lang), 'negative'); // Użyj klucza 'view_error_2'
    redirect('gallery.php');
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('view_h'); ?></title> <!-- Użyj klucza 'view_h' -->
    <link rel="stylesheet" href="majowka.css">
</head>

<body>
<div class="container">
    <h2><?php echo trans('view_h'); ?></h2> <!-- Użyj klucza 'view_h' -->
    <img src="uploads/<?php echo htmlspecialchars($photo['photo']); ?>" alt="<?php echo trans('task_photo'); ?>" style="max-width: 100%;"> <!-- Użyj klucza 'task_photo' -->
    <p><strong><?php echo trans('description'); ?>:</strong> <?php echo htmlspecialchars($photo['description']); ?></p> <!-- Użyj klucza 'description' -->
    <p><strong><?php echo trans('author'); ?>:</strong> <?php echo htmlspecialchars($photo['username']); ?></p> <!-- Użyj klucza 'author' -->
    <p><strong><?php echo trans('hour'); ?>:</strong> <?php echo htmlspecialchars($photo['uploaded_at']); ?></p> <!-- Użyj klucza 'hour' -->
    <a href="gallery.php"><?php echo trans('view_back'); ?></a> <!-- Użyj klucza 'view_back' -->
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
    <img id="fut_logo_bottom" src="graphics/logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
</div>
</body>

</html>