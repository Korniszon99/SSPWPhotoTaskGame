<?php
include 'config.php';
include 'functions.php';
include 'header.php';
global $lang;

if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Pobierz 5 użytkowników z największą liczbą zrealizowanych zadań, jeśli użytkownik ma ID 6
// Użytkownik o ID 6 to była osoba z podglądem do wyników, więc zostawiam to zakomentowane

// $top_users = [];
// if ($user_id == 6) {
//     $stmt = $mysqli->prepare("
//         SELECT users.username, COUNT(completed_tasks.id) AS completed_count
//         FROM users
//         JOIN user_tasks ON users.id = user_tasks.user_id
//         JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id
//         GROUP BY users.id
//         ORDER BY completed_count DESC
//         LIMIT 5
//     ");
//     if (!$stmt) {
//         die('Błąd zapytania: ' . $mysqli->error);
//     }
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $top_users = $result->fetch_all(MYSQLI_ASSOC);
//     $stmt->close();
// }
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo trans('dashboard_title'); ?></title> <!-- Użyj klucza 'dashboard_title' -->
    <link rel="stylesheet" href="majowka.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<div class="container">
    <h2><?php echo sprintf(trans('dashboard_h2'), htmlspecialchars($_SESSION['username'])); ?></h2>
    <!-- Użyj klucza 'dashboard_h2' -->
    <?php display_flash_message(); ?>
    <a href="draw_task.php" class="Button1"><?php echo trans('dashboard_b1'); ?></a><br/>
    <!-- Użyj klucza 'dashboard_b1' -->
    <a href="task_list.php" class="Button1"><?php echo trans('dashboard_b2'); ?></a><br/>
    <!-- Użyj klucza 'dashboard_b2' -->
    <a href="gallery.php" class="Button1"><?php echo trans('dashboard_b3'); ?></a><br/>
    <!-- Użyj klucza 'dashboard_b3' -->
    <a href="logout.php" class="Button1"><?php echo trans('logout'); ?></a> <!-- Użyj klucza 'logout' -->

    <!--
        Dalsza część kodu, która wyświetla top 5 użytkowników z największą liczbą zrealizowanych zadań
        <?php if ($user_id == 6 && !empty($top_users)): ?>
            <h3><?php echo trans('dashboard_h3'); ?></h3>
    <ul>
        <?php foreach ($top_users as $user): ?>
            <li><?php echo htmlspecialchars($user['username']); ?> - <?php echo htmlspecialchars($user['completed_count']); ?> <?php echo trans('dashboard_li'); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?> -->

</div>
<div class="loga">
    <img id="sspg_logo_bottom" src="graphics/loga_sspw_wrs.png" alt="Logo SSPG">
</div>
<div class="SO">
    <img id="shoutout_bartek" src="graphics/Shoutout_Bartosz_Giza.png" alt="BartoszGiza">
</div>
</body>

</html>