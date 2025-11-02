<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || !$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
    exit;
}

if (!isset($_GET['task_id'])) {
    set_flash_message(translate('invalidParameters'), 'negative');
    redirect('tasks.php');
    exit;
}

$task_id = intval($_GET['task_id']);

try {
    $task = $db->getTaskStats($task_id);
    if (!$task) {
        set_flash_message(translate('noTaskFound'), 'negative');
        redirect('tasks.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Błąd edit_task.php: ' . $e->getMessage());
    set_flash_message(translate('errorOccurred', ['error' => $e->getMessage()]), 'negative');
    redirect('tasks.php');
    exit;
}

// Obsługa formularza edycji
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_task'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message(translate('invalidCSRFToken'), 'negative');
        redirect('edit_task.php?task_id=' . $task_id);
        exit;
    }

    $description = trim($_POST['description']);

    if (empty($description)) {
        set_flash_message(translate('taskDescriptionCannotBeEmpty'), 'negative');
    } else {
        try {
            $db->updateTask($task_id, $description);
            set_flash_message(translate('taskUpdated'), 'positive');
            redirect('tasks.php');
            exit;
        } catch (Exception $e) {
            error_log('Błąd aktualizacji zadania: ' . $e->getMessage());
            set_flash_message(translate('taskUpdateError'), 'negative');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include '../elements/head.php'; ?>
<body>
<?php include '../elements/header.php'; ?>

<main class="main-content">
    <div class="admin-edit-task">
        <?php display_flash_message(); ?>

        <h2>✏️ <?php __('editingTask', ['taskId' => $task['id']]) ?></h2>

        <div class="edit-section">
            <h3><?php __('taskStats') ?></h3>
            <div class="task-stats-display">
                <div class="stat-item">
                    <span class="stat-icon">📌</span>
                    <span class="stat-number"><?php echo $task['times_assigned']; ?></span>
                    <span class="stat-label"><?php __('timesAssigned') ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">✅</span>
                    <span class="stat-number"><?php echo $task['times_completed']; ?></span>
                    <span class="stat-label"><?php __('timesCompleted') ?></span>
                </div>
            </div>

            <?php if ($task['times_completed'] > 0): ?>
                <div class="task-warning" style="margin-top: 1rem;">
                    ℹ️ <?php ___('taskAlreadyCompletedStats', $task['times_completed']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="edit-section">
            <h3><?php __('editTaskDescription') ?></h3>
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <textarea name="description"
                          required
                          rows="4"
                          class="task-textarea"><?php echo htmlspecialchars($task['description']); ?></textarea>
                <button type="submit" name="update_task" class="Button1" style="margin-top: 1rem;">
                    💾 <?php __('save') ?>
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="tasks.php" class="Button1"><?php __('backToTaskList') ?></a>
            <a href="../actions/admin_delete_task.php?task_id=<?php echo $task_id; ?>"
               class="Button1"
               style="background: linear-gradient(45deg, #ff4d4d, #ff6b6b);"
               onclick="return confirm(<?php __('confirmTaskDelete', ['assigned' => $task['times_assigned'], 'completed' => $task['times_completed']]) ?>);">
                🗑️ <?php __('deleteTask') ?>
            </a>
        </div>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>