<?php
include __DIR__ . '/config/config.php';

if (!isset($_SESSION['username'])) {
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];

try {
    $tasks = $db->getUserTasks($user_id);
    $incomplete_count = $db->getIncompleteTasksCount($user_id);
    $total_tasks = count($tasks);
    $completed_count = $total_tasks - $incomplete_count;
} catch (Exception $e) {
    error_log('Błąd dashboard.php: ' . $e->getMessage());
    $tasks = [];
    $incomplete_count = 0;
    $total_tasks = 0;
    $completed_count = 0;
}
?>

<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include 'elements/head.php' ?>
<body>
<?php include 'elements/header.php' ?>

<main class="main-content">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h1><?php __('welcome', ['username' => htmlspecialchars($_SESSION['username'])]) ?></h1>
        <p><?php __('dashboardSubtitle') ?></p>

        <?php display_flash_message(); ?>

        <div class="user-stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $completed_count; ?></div>
                <div class="stat-label"><?php __('finished') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $incomplete_count; ?></div>
                <div class="stat-label"><?php __('pending') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_tasks; ?></div>
                <div class="stat-label"><?php __('total') ?></div>
            </div>
        </div>
    </div>

    <?php include 'elements/user_task_list_grid.php'; ?>
</main>
<?php include 'elements/footer.php'; ?>
</body>
</html>