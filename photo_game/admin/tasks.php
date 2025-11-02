<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || !$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
    exit;
}

try {
    $tasks = $db->getAllTasksWithStats();
} catch (Exception $e) {
    error_log('Błąd tasks.php: ' . $e->getMessage());
    set_flash_message(translate('tasksLoadError'), 'negative');
    $tasks = [];
}

// Obsługa dodawania nowego zadania
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message(translate('invalidCSRFToken'), 'negative');
        redirect('tasks.php');
        exit;
    }

    $description = trim($_POST['description']);

    if (empty($description)) {
        set_flash_message(translate('taskDescriptionCannotBeEmpty'), 'negative');
    } else {
        try {
            $db->createTask($description);
            set_flash_message(translate('taskCreated'), 'positive');
            redirect('tasks.php');
            exit;
        } catch (Exception $e) {
            error_log('Błąd dodawania zadania: ' . $e->getMessage());
            set_flash_message(translate('taskCreationError'), 'negative');
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
    <div class="admin-panel">
        <?php display_flash_message(); ?>

        <div class="admin-header">
            <h2>📋 <?php __('manageTasks') ?></h2>
            <p style="opacity: 0.8;"><?php __('manageTasksSubtitle') ?></p>
        </div>

        <!-- Formularz dodawania nowego zadania -->
        <div class="add-task-section">
            <h3>➕ <?php __('createNewTask') ?></h3>
            <form method="post" action="" class="add-task-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-row">
                    <textarea name="description"
                              placeholder="<?php __('taskPlaceholder') ?>"
                              required
                              rows="3"
                              class="task-textarea"></textarea>
                    <button type="submit" name="add_task" class="Button1">➕ <?php __('createTask') ?></button>
                </div>
            </form>
        </div>

        <!-- Lista zadań -->
        <div class="tasks-list-section">
            <h3>📝 <?php __('taskList', ['count' => count($tasks)]) ?></h3>

            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h3><?php __('noTasks') ?></h3>
                    <p><?php __('createSomeTasks') ?></p>
                </div>
            <?php else: ?>
                <div class="tasks-cards">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-admin-card">
                            <div class="task-admin-header">
                                <span class="task-id">#<?php echo $task['id']; ?></span>
                                <div class="task-admin-stats-mini">
                                    <span title="<?php __('timesAssigned') ?>">📌 <?php echo $task['times_assigned']; ?></span>
                                    <span title="<?php __('timesCompleted') ?>">✅ <?php echo $task['times_completed']; ?></span>
                                </div>
                            </div>

                            <div class="task-admin-description">
                                <?php echo htmlspecialchars($task['description']); ?>
                            </div>

                            <div class="task-admin-actions">
                                <a href="edit_task.php?task_id=<?php echo $task['id']; ?>"
                                   class="btn-small btn-edit">
                                    ✏️ <?php __('edit') ?>
                                </a>
                                <a href="../actions/admin_delete_task.php?task_id=<?php echo $task['id']; ?>"
                                   class="btn-small btn-delete"
                                   onclick="return confirm(<?php __('confirmTaskDelete', ['assigned' =>$task['times_assigned'], 'completed' => $task['times_completed']]) ?>);">
                                    🗑️ <?php __('delete') ?>
                                </a>
                            </div>

                            <?php if ($task['times_completed'] > 0): ?>
                                <div class="task-warning">
                                    ⚠️ <?php ___('taskHasCompletionsWarning', $task['times_completed']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="panel.php" class="Button1"><?php __('backToAdminPanel') ?></a>
        </div>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>