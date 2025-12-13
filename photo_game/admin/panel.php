<?php
include __DIR__ . '/../config/config.php';
// Sprawdź czy użytkownik jest zalogowany i jest adminem
if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}

if (!$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
}

// Obsługa wyszukiwania
$search_query = '';
$search_active = false;

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $search_active = true;
}

try {
    if ($search_active) {
        $users = $db->searchUsers($search_query);
    } else {
        $users = $db->getAllUsers();
    }
} catch (Exception $e) {
    error_log('Błąd panel.php: ' . $e->getMessage());
    set_flash_message(translate('usersLoadError'), 'negative');
    $users = [];
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
            <h2>🔐 <?php __('adminPanel') ?></h2>
            <p style="opacity: 0.8;"><?php __('adminPanelSubtitle') ?></p>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $db->getTotalUsersCount(); ?></div>
                <div class="stat-label"><?php __('users') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $db->getCompletedTasksCount(); ?></div>
                <div class="stat-label"><?php __('photos') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $db->getTasksCount(); ?></div>
                <div class="stat-label"><?php __('tasks') ?></div>
            </div>
        </div>

        <div class="users-table-container">
            <div class="users-header">
                <h3><?php __('userList') ?></h3>

                <!-- Formularz wyszukiwania -->
                <form method="get" action="panel.php" class="search-form">
                    <div class="search-wrapper">
                        <input type="text"
                               name="search"
                               placeholder="🔍 <?php __('searchByIdOrUsername') ?>"
                               value="<?php echo htmlspecialchars($search_query); ?>"
                               class="search-input">
                        <button type="submit" class="search-btn"><?php __('search') ?></button>
                        <?php if ($search_active): ?>
                            <a href="panel.php" class="clear-search-btn" title="<?php __('clearSearch') ?>">✕</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if ($search_active): ?>
                <div class="search-info">
                    <?php ___('usersSearchResults', count($users), ['query' => htmlspecialchars($search_query)]); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <?php echo $search_active ? '🔍' : '👥'; ?>
                    </div>
                    <h3>
                        <?php $search_active ? __('noUsersFound') : __('noUsers'); ?>
                    </h3>
                    <?php if ($search_active): ?>
                        <p><?php __('tryOtherSearch')?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Wersja desktop - tabela -->
                <div class="users-table desktop-only">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php __('username') ?></th>
                            <th><?php __('assigned') ?></th>
                            <th><?php __('completed') ?></th>
                            <th><?php __('admin') ?></th>
                            <th><?php __('actions') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?php echo $user['is_admin'] ? 'admin-row' : ''; ?>">
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span style="opacity: 0.6; font-size: 0.9rem;">(<?php __('you') ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['tasks_assigned']; ?></td>
                                <td><?php echo $user['tasks_completed']; ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="admin-badge">✓ <?php __('admin') ?></span>
                                    <?php else: ?>
                                        <span class="user-badge"><?php __('user') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons-cell">
                                    <a href="edit_user.php?user_id=<?php echo $user['id']; ?>"
                                       class="btn-small btn-edit">✏️ <?php __('edit') ?></a>

                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="../actions/admin_delete_user.php?user_id=<?php echo $user['id']; ?>"
                                           class="btn-small btn-delete"
                                           onclick="return confirm(<?php __('confirmUserDelete', ['username' => htmlspecialchars($user['username'])]) ?>);">
                                            🗑️ <?php __('delete') ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Wersja mobile - karty -->
                <div class="users-cards mobile-only">
                    <?php foreach ($users as $user): ?>
                        <div class="user-mobile-card <?php echo $user['is_admin'] ? 'admin-row' : ''; ?>">
                            <div class="user-mobile-header">
                                <div>
                                    <div class="user-mobile-name">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span style="opacity: 0.6; font-size: 0.9rem;">(<?php __('you') ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.85rem; opacity: 0.7;">ID: <?php echo $user['id']; ?></div>
                                </div>
                                <?php if ($user['is_admin']): ?>
                                    <span class="admin-badge">✓ <?php __('admin') ?></span>
                                <?php else: ?>
                                    <span class="user-badge"><?php __('user') ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="user-mobile-stats">
                                <div class="user-mobile-stat">
                                    <strong><?php echo $user['tasks_assigned']; ?></strong>
                                    <span><?php __('assigned') ?></span>
                                </div>
                                <div class="user-mobile-stat">
                                    <strong><?php echo $user['tasks_completed']; ?></strong>
                                    <span><?php __('completed') ?></span>
                                </div>
                            </div>

                            <div class="user-mobile-actions">
                                <a href="edit_user.php?user_id=<?php echo $user['id']; ?>"
                                   class="btn-small btn-edit">✏️ <?php __('edit') ?></a>

                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="../actions/admin_delete_user.php?user_id=<?php echo $user['id']; ?>"
                                       class="btn-small btn-delete"
                                       onclick="return confirm(<?php __('confirmUserDelete', ['username' => htmlspecialchars($user['username'])]) ?>);">
                                        🗑️ <?php __('delete') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="../dashboard.php" class="Button1"><?php __('backToDashboard') ?></a>
        </div>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>