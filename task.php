<?php
include 'config.php';
include 'functions.php';
global $lang;

if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

if (!isset($_SESSION['task'])) {
    set_flash_message(trans('task_no_selection'), 'negative');
    redirect('dashboard.php');
}

$task = $_SESSION['task'];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('task_title'); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2><?php echo trans('task_your_task'); ?></h2>
    <p><?php echo htmlspecialchars($task['description']); ?></p>
    <form method="post" action="upload_photo.php" enctype="multipart/form-data">
        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
        <input type="file" name="photo" required>
        <button type="submit" name="upload_photo"> <?php echo trans('task_upload_photo'); ?> </button>
    </form>
    <a href="dashboard.php" class="Button1"> <?php echo trans('task_return_dashboard'); ?> </a>
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
    <img id="fut_logo_bottom" src="graphics/logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
</div>
</body>
</html>
