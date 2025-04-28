<?php
include 'config.php';
include 'functions.php';
include 'header.php'; // Dodaj header.php, aby mieć dostęp do funkcji trans i zmiennej $lang
global $mysqli;
global $lang;

if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT user_tasks.id AS user_task_id, tasks.description, completed_tasks.photo, completed_tasks.id AS photo_id, user_tasks.assigned_at FROM user_tasks JOIN tasks ON user_tasks.task_id = tasks.id LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id WHERE user_tasks.user_id = ? ORDER BY user_tasks.assigned_at ASC");
if (!$stmt) {
    die(trans('query_error', $lang)); // Użyj klucza 'query_error' z tłumaczeniem
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('list_title'); ?></title> <!-- Użyj klucza 'list_title' -->
    <link rel="stylesheet" href="majowka.css">
</head>

<body>
<div class="container">
    <a href="dashboard.php" class="Button1"><?php echo trans('back'); ?></a> <!-- Użyj klucza 'back' -->
    <?php display_flash_message(); ?>
    <h2><?php echo trans('list_h2'); ?></h2> <!-- Użyj klucza 'list_h2' -->
    <?php if (empty($tasks)): ?>
        <p><?php echo trans('list_empty'); ?></p> <!-- Użyj klucza 'list_empty' -->
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="task_item">
                <div class="task_item_meat">
                    <p><strong><?php echo htmlspecialchars($task['description']); ?></strong></p>
                    <?php if ($task['photo']): ?>
                        <a href="view_photo.php?photo_id=<?php echo htmlspecialchars($task['photo_id']); ?>">
                            <img src="uploads/<?php echo htmlspecialchars($task['photo']); ?>" alt="<?php echo trans('task_photo'); ?>" style="max-width: 90%;"> <!-- Użyj klucza 'task_photo' -->
                        </a>
                    <?php else: ?>
                        <form method="post" action="upload_photo.php" enctype="multipart/form-data">
                            <input type="hidden" name="user_task_id" value="<?php echo $task['user_task_id']; ?>">
                            <input type="file" name="photo" required style="font-size: 1.1em;">
                            <button type="submit" name="upload_photo" class="Button1"><?php echo trans('upload_photo'); ?></button> <!-- Użyj klucza 'upload_photo' -->
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <a href="dashboard.php" class="Button1"><?php echo trans('back'); ?></a> <!-- Użyj klucza 'back' -->
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
</div>
<div class="SO">
    <img id="shoutout_bartek" src="graphics/Shoutout_Bartosz_Giza.png" alt="BartoszGiza">
</div>
</body>
</html>