<?php
include 'config.php';
include 'functions.php';
include 'header.php';
global $lang;

global $mysqli;
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

// Ustawienia stronicowania
$photos_per_page = 24;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $photos_per_page;

// Pobierz łączną liczbę zdjęć
$result = $mysqli->query("SELECT COUNT(*) AS total FROM completed_tasks");
$total_photos = $result->fetch_assoc()['total'];
$total_pages = ceil($total_photos / $photos_per_page);

// Pobierz zdjęcia dla bieżącej strony, sortując je według daty przesłania w porządku malejącym
$stmt = $mysqli->prepare("SELECT completed_tasks.id, completed_tasks.photo, tasks.description, users.username FROM completed_tasks JOIN user_tasks ON completed_tasks.user_task_id = user_tasks.id JOIN tasks ON user_tasks.task_id = tasks.id JOIN users ON user_tasks.user_id = users.id ORDER BY completed_tasks.uploaded_at DESC LIMIT ? OFFSET ?");
if (!$stmt) {
    die('Błąd zapytania: ' . $mysqli->error);
}
$stmt->bind_param('ii', $photos_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$photos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo trans('gallery_title'); ?></title>
    <link rel="stylesheet" href="majowka.css">
</head>

<body>
<div class="container">
    <?php display_flash_message(); ?>
    <h2><?php echo trans('gallery_title'); ?></h2>
    <a href="dashboard.php" class="Button1"><?php echo trans('gallery_b1'); ?></a>
    <?php if (empty($photos)): ?>
        <p><?php echo trans('gallery_empty'); ?></p>
    <?php else: ?>
        <div class="gallery">
            <?php foreach ($photos as $photo): ?>
                <div class="gallery-item">
                    <a href="view_photo.php?photo_id=<?php echo htmlspecialchars($photo['id']); ?>">
                        <img src="uploads/<?php echo htmlspecialchars($photo['photo']); ?>" alt="<?php echo trans('description'); ?>" style="max-width: 100%;">
                    </a>
                    <p><strong><?php echo trans('description'); ?></strong> <?php echo htmlspecialchars($photo['description']); ?></p>
                    <p><strong><?php echo trans('author'); ?></strong> <?php echo htmlspecialchars($photo['username']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="Button2"><?php echo trans('gallery_bt1'); ?></a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo ' class="Button2Active" '; ?> class="Button2"> <?php echo $i; ?> </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="Button2"><?php echo trans('gallery_bt2'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <a href="dashboard.php" class="Button1"><?php echo trans('gallery_bt3'); ?></a>
</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/02_LOGOSSPW_WYPEŁNIENIE-PODSTAWOWE_RGB_RASTER.png" alt="Logo SSPW">
</div>
</body>

</html>