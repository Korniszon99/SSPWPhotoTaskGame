<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || !$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
    exit;
}

if (!isset($_GET['task_id'])) {
    set_flash_message(translate('invalidParameters'), 'negative');
    redirect('../admin/tasks.php');
    exit;
}

$task_id = intval($_GET['task_id']);

try {
    $task = $db->getTaskById($task_id);
    if (!$task) {
        set_flash_message(translate('noTaskFound'), 'negative');
        redirect('../admin/tasks.php');
        exit;
    }

    $result = $db->deleteTask($task_id);

    $message = translate('taskDeleted');
    if ($result['assigned_count'] > 0 || $result['completed_count'] > 0) {
        $message .= translate('taskDeletedSummary', [
            'assigned' => $result['assigned_count'],
            'completed' => $result['completed_count'],
            'photos' => $result['photos_deleted']
        ]);
    }

    set_flash_message($message, 'positive');

} catch (Exception $e) {
    error_log('Błąd admin_delete_task.php: ' . $e->getMessage());
    set_flash_message(translate('taskDeleteError'), 'negative');
}

redirect('../admin/tasks.php');