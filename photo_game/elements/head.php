<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

// Jeśli logowanie jest wyłączone i użytkownik NIE jest adminem - blokuj
if (!$db->isLoginEnabled() && !$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('gameDisabled'), 'negative');
    redirect('/photo_game/auth/logout.php');
    exit;
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/photo_game/manifest.json">
    <meta name="theme-color" content="#8b5cf6">
    <link rel="apple-touch-icon" href="/photo_game/graphics/logo-fut.png">
    <title>FUT Photo Game</title>
    <link rel="stylesheet" href="/photo_game/style.css">
    <script src="/photo_game/js/pwa.js" defer></script>
</head>
<script>
    function toggleMenu() {
        const hamburger = document.querySelector('.hamburger');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.overlay');

        hamburger.classList.toggle('active');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Dla każdego inputu typu file w sekcji upload
        document.querySelectorAll('.upload-section input[type="file"]').forEach(input => {
            const label = input.nextElementSibling; // zakładamy, że label jest zaraz po input
            const defaultText = label.textContent;

            input.addEventListener('change', (e) => {
                const fileName = e.target.files.length ? e.target.files[0].name : '';
                label.textContent = fileName || defaultText;
            });
        });
    });
</script>
