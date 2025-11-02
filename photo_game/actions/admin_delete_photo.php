<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || !$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
    exit;
}

if (!isset($_GET['photo_id']) || !isset($_GET['user_id'])) {
    set_flash_message(translate('invalidParameters'), 'negative');
    redirect('../admin/panel.php');
    exit;
}

$photo_id = intval($_GET['photo_id']);
$user_id = intval($_GET['user_id']);

try {
    $db->deletePhoto($photo_id);
    set_flash_message(translate('photoDeleted'), 'positive');
} catch (Exception $e) {
    error_log('Błąd admin_delete_photo.php: ' . $e->getMessage());
    set_flash_message(translate('photoDeleteError'), 'negative');
}

redirect('../admin/edit_user.php?user_id=' . $user_id);