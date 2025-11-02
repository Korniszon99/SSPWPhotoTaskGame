<?php
include __DIR__ . '/../config/config.php';

if (!isset($_SESSION['username'])) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
try {
    // Pobierz ostatnie przypisane zadanie użytkownika
    $task = $db->getLatestUserTask($user_id);

    if (!$task) {
        set_flash_message(translate('taskNotAssigned'), 'negative');
        redirect('../dashboard.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Błąd view_task.php: ' . $e->getMessage());
    set_flash_message(translate('taskLoadError'), 'negative');
    redirect('../dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include '../elements/head.php'; ?>
<body>
<?php include '../elements/header.php'; ?>

<main class="main-content">
    <div class="task-detail">
        <?php display_flash_message(); ?>

        <div class="success-message">
            <h3>✨ <?php __('congratulations') ?></h3>
            <p><?php __('newTaskAssigned') ?></p>
        </div>

        <div class="task-card-big">
            <div class="task-icon">🎯</div>
            <h2><?php __('yourNewTask') ?>:</h2>
            <div class="task-description-big">
                <?php echo htmlspecialchars($task['description']); ?>
            </div>
        </div>

        <div class="action-buttons">
            <a href="task_list.php" class="Button1"><?php __('myTasks') ?></a>
            <a href="../actions/draw_task.php" class="Button1"><?php __('drawAgain') ?></a>
            <a href="../dashboard.php" class="Button1"><?php __('backToDashboard') ?></a>
        </div>

        <p style="margin-top: 2rem; opacity: 0.7; font-size: 0.95rem;">
            💡 <?php __('newTaskHint') ?>
        </p>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>