<?php
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

if (!isset($_GET['photo_id'])) {
    set_flash_message('Nieprawidłowy identyfikator zdjęcia.', 'negative');
    redirect('gallery.php');
}

$photo_id = intval($_GET['photo_id']);

$stmt = $mysqli->prepare("SELECT completed_tasks.photo, completed_tasks.uploaded_at, tasks.description, users.username FROM completed_tasks JOIN user_tasks ON completed_tasks.user_task_id = user_tasks.id JOIN tasks ON user_tasks.task_id = tasks.id JOIN users ON user_tasks.user_id = users.id WHERE completed_tasks.id = ?");
if (!$stmt) {
    die('Błąd zapytania: ' . $mysqli->error);
}
$stmt->bind_param('i', $photo_id);
$stmt->execute();
$result = $stmt->get_result();
$photo = $result->fetch_assoc();
$stmt->close();

if (!$photo) {
    set_flash_message('Zdjęcie nie zostało znalezione.', 'negative');
    redirect('gallery.php');
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podgląd zdjęcia</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Podgląd zdjęcia</h2>
        <img src="uploads/<?php echo htmlspecialchars($photo['photo']); ?>" alt="Zdjęcie zadania"
            style="max-width: 100%;">
        <p><strong>Opis zadania:</strong> <?php echo htmlspecialchars($photo['description']); ?></p>
        <p><strong>Autor:</strong> <?php echo htmlspecialchars($photo['username']); ?></p>
        <p><strong>Godzina publikacji:</strong> <?php echo htmlspecialchars($photo['uploaded_at']); ?></p>
        <a href="gallery.php">Powrót do galerii</a>
    </div>
    <div class="loga">
        <img id="sspg_logo_bottom" src="graphics\02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPG">
        <img id="fut_logo_bottom" src="graphics\logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
    </div>
</body>

</html>

<!-- TODO: zrobić powrót do galerii i do listy zadań oddzielnie -->