<?php
include 'config.php';
include 'functions.php';

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
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Menu główne</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <h2>Witaj, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <?php display_flash_message(); ?>
        <a href="draw_task.php" class="Button1">Losuj zadanie</a><br />
        <a href="task_list.php" class="Button1">Zobacz swoje zadania</a><br />
        <a href="gallery.php" class="Button1">Galeria zdjęć</a><br />
        <a href="logout.php" class="Button1">Wyloguj się</a>

        <!-- 
        Dalsza część kodu, która wyświetla top 5 użytkowników z największą liczbą zrealizowanych zadań
        <?php if ($user_id == 6 && !empty($top_users)): ?>
            <h3>Top 5 użytkowników z największą liczbą zrealizowanych zadań:</h3>
            <ul>
                <?php foreach ($top_users as $user): ?>
                    <li><?php echo htmlspecialchars($user['username']); ?> - <?php echo htmlspecialchars($user['completed_count']); ?> zadań</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?> -->
        
    </div>
    <div class="loga">
        <img id="sspg_logo_bottom" src="graphics/SSPG-Logo-Pozytyw-Poziom-Niebieski_large_RGB.png" alt="Logo SSPG">
        <img id="fut_logo_bottom" src="graphics/logo-FUT-PL-poziom-kolor-RGB.png" alt="Logo FUT">
    </div>
</body>

</html>