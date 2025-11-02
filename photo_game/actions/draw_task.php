<?php
include __DIR__ . '/../config/config.php';

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
    exit;
}

// RATE LIMITING
$check = $rateLimiter->check('draw_task');
if (is_array($check) && !$check['allowed']) {
    set_flash_message(translatePlural('taskDrawRateLimitReached', $check['retry_after']),
        'negative'
    );
    redirect('../dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Sprawdź liczbę niezrealizowanych zadań użytkownika
    $incomplete_tasks_count = $db->getIncompleteTasksCount($user_id);

    // Jeśli użytkownik ma 5 niezakończonych zadań — zablokuj losowanie
    if ($incomplete_tasks_count >= 5) {
        set_flash_message(
            translate('drawTaskLimit', ['count' => 5]),
            'negative'
        );
        redirect('../dashboard.php');
        exit;
    }

    // Pobierz wszystkie dostępne zadania
    $tasks = $db->getAllTasks();

    if (empty($tasks)) {
        set_flash_message(translate('noTasksAvailable'), 'negative');
        redirect('../dashboard.php');
        exit;
    }

    // Znajdzdź zadania, które użytkownik już ma przypisane i są niezakończone
    // aby nie przypisywać ich ponownie
    $available_tasks = $db->getAvailableTasksForUser($user_id);

    if (empty($available_tasks)) {
        set_flash_message(translate('allTasksDrawn'), 'negative');
        redirect('../dashboard.php');
        exit;
    }

    // Przypisz zadanie użytkownikowi
    $task = $available_tasks[array_rand($available_tasks)];
    $db->assignTaskToUser($user_id, $task['id']);

    redirect('../tasks/view_task.php');
    exit;

} catch (Exception $e) {
    error_log('Błąd draw_task.php: ' . $e->getMessage());
    set_flash_message(translate('taskDrawError'), 'negative');
    redirect('../dashboard.php');
    exit;
}