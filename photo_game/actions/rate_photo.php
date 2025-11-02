<?php
include __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message(translate('unknownMethod'), 'negative');
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_flash_message(translate('invalidCSRFToken'), 'negative');
    exit;
}

$photo_id = isset($_POST['photo_id']) ? intval($_POST['photo_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$user_id = $_SESSION['user_id'];

if ($rating < 1 || $rating > 5) {
    set_flash_message(translate('unknownRatingValue'), 'negative');
    exit;
}

try {
    $db->ratePhoto($photo_id, $user_id, $rating);

    // Pobierz zaktualizowane dane
    $ratingData = $db->getPhotoRating($photo_id);

    echo json_encode([
        'avg_rating' => round($ratingData['avg_rating'], 1),
        'rating_count' => $ratingData['rating_count']
    ]);

} catch (Exception $e) {
    set_flash_message($e->getMessage(), 'negative');
}