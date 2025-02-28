<?php
include 'config.php';
include 'functions.php';
global $mysqli;
global $lang;

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Sprawdź, czy użytkownik ma 5 zadań bez przesłanego zdjęcia
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM user_tasks LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id WHERE user_tasks.user_id = ? AND completed_tasks.id IS NULL");
if (!$stmt) {
    die(trans('query_error', $lang)); // Użyj klucza 'query_error' z tłumaczeniem
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($incomplete_tasks_count);
$stmt->fetch();
$stmt->close();

// Jeśli użytkownik ma 5 zadań bez przesłanego zdjęcia, wyświetl komunikat i przekieruj do dashboardu
if ($incomplete_tasks_count >= 5) {
    set_flash_message(trans('flash_message', $lang), 'negative'); // Użyj klucza 'flash_message'
    redirect('dashboard.php');
}

// Pobierz wszystkie dostępne zadania
$result = $mysqli->query("SELECT * FROM tasks");

// Sprawdź, czy są dostępne zadania
if ($result->num_rows == 0) {
    set_flash_message(trans('flash_message_2', $lang), 'negative'); // Użyj klucza 'flash_message_2'
    redirect('dashboard.php');
} else {
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    $task = $tasks[array_rand($tasks)]; // Wybierz losowe zadanie

    // Zapisz zadanie w bazie danych i przypisz je do użytkownika
    $stmt = $mysqli->prepare("INSERT INTO user_tasks (user_id, task_id) VALUES (?, ?)");
    if (!$stmt) {
        die(trans('query_error', $lang)); // Użyj klucza 'query_error' z tłumaczeniem
    }
    $stmt->bind_param('ii', $user_id, $task['id']);
    if ($stmt->execute()) {
        set_flash_message(trans('flash_message_3', $lang), 'positive'); // Użyj klucza 'flash_message_3'
        redirect('view_task.php');
    } else {
        set_flash_message(trans('flash_message_4', $lang), 'negative'); // Użyj klucza 'flash_message_4'
        redirect('dashboard.php');
    }
    $stmt->close();
}
?>