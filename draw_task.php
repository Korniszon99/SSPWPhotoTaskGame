<?php
include 'config.php';
include 'functions.php';
global $mysqli;
global $lang;

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Check if the user has 5 tasks without a submitted photo
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM user_tasks LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id WHERE user_tasks.user_id = ? AND completed_tasks.id IS NULL");
if (!$stmt) {
    die(trans('query_error', $lang)); // Use the 'query_error' translation key
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($incomplete_tasks_count);
$stmt->fetch();
$stmt->close();

// If the user has 5 tasks without a submitted photo, display a message and redirect to the dashboard
if ($incomplete_tasks_count >= 5) {
    set_flash_message(trans('flash_message', $lang), 'negative'); // Use the 'flash_message' translation key
    redirect('dashboard.php');
}

// Fetch all available tasks for the current language
$stmt = $mysqli->prepare("SELECT * FROM tasks WHERE language = ?");
if (!$stmt) {
    die(trans('query_error', $lang)); // Use the 'query_error' translation key
}
$stmt->bind_param('s', $lang);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are available tasks
if ($result->num_rows == 0) {
    set_flash_message(trans('flash_message_2', $lang), 'negative'); // Use the 'flash_message_2' translation key
    redirect('dashboard.php');
} else {
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    $task = $tasks[array_rand($tasks)]; // Select a random task

    // Save the task in the database and assign it to the user
    $stmt = $mysqli->prepare("INSERT INTO user_tasks (user_id, task_id) VALUES (?, ?)");
    if (!$stmt) {
        die(trans('query_error', $lang)); // Use the 'query_error' translation key
    }
    $stmt->bind_param('ii', $user_id, $task['id']);
    if ($stmt->execute()) {
        set_flash_message(trans('flash_message_3', $lang), 'positive'); // Use the 'flash_message_3' translation key
        redirect('view_task.php');
    } else {
        set_flash_message(trans('flash_message_4', $lang), 'negative'); // Use the 'flash_message_4' translation key
        redirect('dashboard.php');
    }
    $stmt->close();
}
?>