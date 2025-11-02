<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || !$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
}

if (!isset($_GET['user_id'])) {
    set_flash_message(translate('invalidParameters'), 'negative');
    redirect('panel.php');
}

$user_id = intval($_GET['user_id']);

try {
    $user = $db->getUserById($user_id);
    if (!$user) {
        set_flash_message(translate('noUserFound'), 'negative');
        redirect('panel.php');
    }

    $photos = $db->getUserPhotos($user_id);
    $is_admin = $db->isUserAdmin($user_id);
} catch (Exception $e) {
    error_log('Błąd edit_user.php: ' . $e->getMessage());
    set_flash_message(translate('errorOccurred', ['error' => $e->getMessage()]), 'negative');
    redirect('panel.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))) {
    set_flash_message(translate('invalidCSRFToken'), 'negative');
    redirect('edit_user.php?user_id=' . $user_id);
    exit;
}

// Obsługa formularza zmiany nazwy
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_username'])) {
    $new_username = sanitize_input($_POST['new_username']);

    if (empty($new_username)) {
        set_flash_message(translate('usernameCantBeEmpty'), 'negative');
    } else {
        try {
            $db->updateUsername($user_id, $new_username);
            set_flash_message(translate('usernameChanged'), 'positive');
            redirect('edit_user.php?user_id=' . $user_id);
        } catch (Exception $e) {
            set_flash_message(translate('error').': ' . $e->getMessage(), 'negative');
        }
    }
}

// Obsługa toggleowania statusu admina
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_admin'])) {
    if ($user_id == $_SESSION['user_id']) {
        set_flash_message(translate('cannotChangeOwnPermissions'), 'negative');
    } else {
        try {
            $db->toggleAdminStatus($user_id);
            set_flash_message(translate('adminStatusChanged'), 'positive');
            redirect('edit_user.php?user_id=' . $user_id);
        } catch (Exception $e) {
            set_flash_message(translate('adminStatusChangeError'), 'negative');
        }
    }
}

// Obsługa zmiany hasła przez admina
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_user_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        set_flash_message(translate('allFieldsRequired'), 'negative');
    } elseif ($new_password !== $confirm_password) {
        set_flash_message(translate('passwordsDoNotMatch'), 'negative');
    } else {
        try {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $db->changeUserPassword($user_id, $new_password_hash);
            set_flash_message(translate('passwordChanged'), 'positive');
            redirect('edit_user.php?user_id=' . $user_id);
            exit;
        } catch (Exception $e) {
            error_log('Błąd zmiany hasła: ' . $e->getMessage());
            set_flash_message(translate('passwordChangeError'), 'negative');
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
    <div class="admin-edit-user">
        <?php display_flash_message(); ?>

        <h2>✏️ <?php __('editingUser', ['username' => htmlspecialchars($user['username'])]) ?></h2>

        <div class="edit-section">
            <h3><?php __('changingUsername') ?></h3>
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <input type="text" name="new_username"
                           value="<?php echo htmlspecialchars($user['username']); ?>"
                           required
                           style="flex: 1; min-width: 200px;">
                    <button type="submit" name="change_username" class="Button1">💾 <?php __('save') ?></button>
                </div>
            </form>
        </div>

        <!-- Sekcja zmiany hasła -->
        <div class="edit-section">
            <h3>🔑 <?php __('changingPassword') ?></h3>
            <p style="opacity: 0.8; margin-bottom: 1rem;">
                <?php __('settingNewPasswordInfo') ?>
            </p>
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 0.5rem; opacity: 0.9;"><?php __('newPassword') ?>:</label>
                        <input type="password" name="new_password" required
                               placeholder="<?php __('enterNewPassword') ?>"
                               style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.05); color: #fff;">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 0.5rem; opacity: 0.9;"><?php __('confirmPassword') ?>:</label>
                        <input type="password" name="confirm_password" required
                               placeholder="<?php __('confirmNewPassword') ?>"
                               style="width: 100%; padding: 0.8rem; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.05); color: #fff;">
                    </div>
                    <button type="submit" name="change_user_password" class="Button1" style="white-space: nowrap;">
                        🔑 <?php __('changePassword') ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if ($user_id != $_SESSION['user_id']): ?>
            <div class="edit-section">
                <h3><?php __('adminPermissions') ?></h3>
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <p>
                        <?php __('actualStatus') ?>:
                        <?php if ($is_admin): ?>
                            <span class="admin-badge">✓ <?php __('admin') ?></span>
                        <?php else: ?>
                            <span class="user-badge"><?php __('user') ?></span>
                        <?php endif; ?>
                    </p>
                    <button type="submit" name="toggle_admin" class="Button1">
                        <?php echo $is_admin ? '⬇️ '.translate('revokePermissions') : '⬆️ '.translate('grantPermissions'); ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="edit-section">
            <h3><?php __('userPhotos', ['count' => count($photos)]) ?></h3>

            <?php if (empty($photos)): ?>
                <p style="opacity: 0.7;"><?php __('noUserPhotos') ?></p>
            <?php else: ?>
                <div class="admin-photos-grid">
                    <?php foreach ($photos as $photo): ?>
                        <div class="admin-photo-item">
                            <img src="/photo_game/uploads/<?php echo htmlspecialchars($photo['photo']); ?>"
                                 alt="<?php __('photo') ?>">
                            <div class="admin-photo-info">
                                <p><strong><?php echo htmlspecialchars($photo['description']); ?></strong></p>
                                <p style="font-size: 0.9rem; opacity: 0.7;">
                                    <?php echo htmlspecialchars($photo['uploaded_at']); ?>
                                </p>
                                <a href="../actions/admin_delete_photo.php?photo_id=<?php echo $photo['id']; ?>&user_id=<?php echo $user_id; ?>"
                                   class="btn-small btn-delete"
                                   onclick="return confirm(<?php __('confirmPhotoDelete') ?>);">
                                    🗑️ <?php __('delete') ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="panel.php" class="Button1"><?php __('backToAdminPanel') ?></a>
            <?php if ($user_id != $_SESSION['user_id']): ?>
                <a href="../actions/admin_delete_user.php?user_id=<?php echo $user_id; ?>"
                   class="Button1"
                   style="background: linear-gradient(45deg, #ff4d4d, #ff6b6b);"
                   onclick="return confirm(<?php __('confirmUserDelete', ['username' => htmlspecialchars($user['username'])]) ?>);">
                    🗑️ <?php __('deleteUser') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>