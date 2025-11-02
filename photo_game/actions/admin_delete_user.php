<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || !$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
    exit;
}

if (!isset($_GET['user_id'])) {
    set_flash_message(translate('invalidParameters'), 'negative');
    redirect('../admin/panel.php');
    exit;
}

$user_id = intval($_GET['user_id']);

// Nie można usunąć samego siebie
if ($user_id == $_SESSION['user_id']) {
    set_flash_message(translate('cannotDeleteOwnAccount'), 'negative');
    redirect('../admin/panel.php');
    exit;
}

try {
    $user = $db->getUserById($user_id);
    if (!$user) {
        set_flash_message(translate('noUserFound'), 'negative');
        redirect('../admin/panel.php');
        exit;
    }

    $db->deleteUser($user_id);
    set_flash_message(translate('userDeleted', ['username' => htmlspecialchars($user['username'])]), 'positive');

} catch (Exception $e) {
    error_log('Błąd admin_delete_user.php: ' . $e->getMessage());
    set_flash_message(translate('userDeleteError'), 'negative');
}

redirect('../admin/panel.php');