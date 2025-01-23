<?php
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

if (!isset($_SESSION['task'])) {
    set_flash_message('Nie wylosowano zadania.', 'negative');
    redirect('dashboard.php');
}

$task = $_SESSION['task'];

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zadanie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Twoje zadanie:</h2>
    <p><?php echo htmlspecialchars($task['description']); ?></p>
    <form method="post" action="upload_photo.php" enctype="multipart/form-data">
        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
        <input type="file" name="photo" required>
        <button type="submit" name="upload_photo">Prześlij zdjęcie</button>
    </form>
    <a href="dashboard.php" class="Button1">Powrót do menu głównego</a>
</div>
<div class="loga">
        <img id="sspg_logo_bottom" src="graphics\SSPG-Logo-Pozytyw-Poziom-Niebieski_large_RGB.png" alt="Logo SSPG">
        <img id="fut_logo_bottom" src="graphics\logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
    </div>
</body>
</html>
