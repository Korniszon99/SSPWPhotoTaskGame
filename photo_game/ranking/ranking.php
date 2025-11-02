<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['username'])) {
    redirect('../auth/login.php');
}

try {
    // Pobierz ranking top 20 użytkowników
    $ranking = $db->getTopUsers(20);
    $current_user_id = $_SESSION['user_id'];
    $current_username = $_SESSION['username'];

    // Znajdź pozycję aktualnego użytkownika w top 20
    $user_position = null;
    $user_in_top20 = false;
    foreach ($ranking as $index => $user) {
        if ($user['username'] === $current_username) {
            $user_position = $index + 1;
            $user_in_top20 = true;
            break;
        }
    }

    // Jeśli użytkownik nie jest w top 20, pobierz jego rzeczywistą pozycję
    $user_data_outside_top20 = null;
    if (!$user_in_top20) {
        $user_position = $db->getUserRankPosition($current_user_id);
        $user_data_outside_top20 = $db->getUserWithCompletedCount($current_user_id);
    }

} catch (Exception $e) {
    error_log('Błąd ranking.php: ' . $e->getMessage());
    set_flash_message(translate('rankingLoadError'), 'negative');
    $ranking = [];
}
?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include '../elements/head.php'; ?>
<body>
<?php include '../elements/header.php'; ?>

<main class="main-content">
    <div class="ranking-container">
        <?php display_flash_message(); ?>

        <div class="ranking-header">
            <h2>🏆 <?php __('userRanking') ?></h2>
            <p style="opacity: 0.8;"><?php __('userRankingDescription') ?></p>
            <div class="ranking-stats">
                <span class="ranking-stat">👥 <?php __('users') ?>: <?php echo $db->getTotalUsersCount(); ?></span>
                <?php if ($user_position): ?>
                    <span class="ranking-stat">📍 <?php __('yourPosition') ?>: #<?php echo $user_position; ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($user_position && $user_position <= 3): ?>
            <div class="user-position-card">
                <h3><?php __('congratulations') ?></h3>
                <div class="user-position-number">
                    <?php
                    $medals = ['🥇', '🥈', '🥉'];
                    echo $medals[$user_position - 1];
                    ?>
                </div>
                <p style="font-size: 1.3rem;"><?php __('youAreTopUser', ['position'=>$user_position]) ?></p>
            </div>
        <?php endif; ?>

        <?php if (empty($ranking)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏆</div>
                <h3><?php __('rankingEmpty') ?></h3>
                <p><?php __('userRankingEmptyDescription') ?></p>
            </div>
        <?php else: ?>
            <div class="ranking-list">
                <?php foreach ($ranking as $index => $user): ?>
                    <?php
                    $position = $index + 1;
                    $is_current = $user['username'] === $current_username;
                    $is_top3 = $position <= 3;
                    ?>
                    <?php if ($db->isUserAdmin($_SESSION['user_id'])): ?>
                        <a href="../admin/edit_user.php?user_id=<?php echo $user['id']; ?>" style="text-decoration: none; color: inherit;">
                    <?php endif; ?>
                    <div class="ranking-item <?php echo $is_current ? 'current-user' : ''; ?> <?php echo $is_top3 ? 'top-3' : ''; ?>">

                        <span class="ranking-position <?php echo $is_top3 ? 'top-' . $position : ''; ?>">
                            #<?php echo $position; ?>
                        </span>

                        <span class="ranking-username">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($is_current): ?>
                                <span style="opacity: 0.7; font-size: 0.9rem;">(<?php __('you') ?>)</span>
                            <?php endif; ?>
                        </span>

                        <span class="ranking-score">
                            <?php echo $user['completed_count']; ?>
                            <span style="font-size: 0.9rem; opacity: 0.8;"><?php echo strtolower(translate('tasks')) ?></span>
                        </span>
                    </div>
                    <?php if ($is_current): ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (!$user_in_top20 && $user_data_outside_top20): ?>
                    <div class="ranking-separator">
                        <span>...</span>
                    </div>

                    <div class="ranking-item current-user outside-top20">
                        <span class="ranking-position">
                            #<?php echo $user_position; ?>
                        </span>

                        <span class="ranking-username">
                            <?php echo htmlspecialchars($current_username); ?>
                            <span style="opacity: 0.7; font-size: 0.9rem;">(<?php __('you') ?>)</span>
                        </span>

                        <span class="ranking-score">
                            <?php echo $user_data_outside_top20['completed_count']; ?>
                            <span style="font-size: 0.9rem; opacity: 0.8;"><?php echo strtolower(translate('tasks')) ?></span>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="../dashboard.php" class="Button1"><?php __('backToDashboard') ?></a>
        </div>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>