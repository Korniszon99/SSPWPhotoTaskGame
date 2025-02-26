<?php
include 'config.php';
include 'functions.php';
global $mysqli;
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT user_tasks.id AS user_task_id, tasks.description, completed_tasks.photo, completed_tasks.id AS photo_id, user_tasks.assigned_at FROM user_tasks JOIN tasks ON user_tasks.task_id = tasks.id LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id WHERE user_tasks.user_id = ? ORDER BY user_tasks.assigned_at ASC");
if (!$stmt) {
    die('Błąd zapytania: ' . $mysqli->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista zadań</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <a href="dashboard.php" class="Button1">Powrót do menu głównego</a>
        <?php display_flash_message(); ?>
        <h2>Twoje zadania:</h2>
        <?php if (empty($tasks)): ?>
            <p>Nie masz przypisanych zadań.</p>
        <?php else: ?>

            <?php foreach ($tasks as $task): ?>
                <div class="task_item">
                    <div class="task_item_meat">
                        <p><strong><?php echo htmlspecialchars($task['description']); ?></strong></p>
                        <?php if ($task['photo']): ?>
                            <a href="view_photo.php?photo_id=<?php echo htmlspecialchars($task['photo_id']); ?>">
                                <img src="uploads/<?php echo htmlspecialchars($task['photo']); ?>" alt="Zdjęcie zadania"
                                    style="max-width: 90%;">
                            </a>
                        <?php else: ?>
                            <form method="post" action="upload_photo.php" enctype="multipart/form-data">
                                <input type="hidden" name="user_task_id" value="<?php echo $task['user_task_id']; ?>">
                                <input type="file" name="photo" required style="font-size: 1.1em;">
                                <button type="submit" name="upload_photo" class="Button1">Prześlij zdjęcie</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
        <a href="dashboard.php" class="Button1">Powrót do menu głównego</a>
    </div>
    <div class="loga">
        <img id="sspg_logo_bottom" src="graphics\02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
        <img id="fut_logo_bottom" src="graphics\logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
    </div>
</body>

</html>