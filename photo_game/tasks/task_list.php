<?php include __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include '../elements/head.php'; ?>
<body>
<?php include '../elements/header.php'; ?>

<main class="main-content">
    <?php display_flash_message(); ?>

    <?php include '../elements/user_task_list_grid.php' ?>
</main>
<?php include '../elements/footer.php'; ?>
</body>
</html>