<?php
/*
 * Przekierowuje użytkownika na podany URL.
 */
function redirect($url)
{
    header("Location: $url");
    exit();
}

/*
 * Wyświetla wiadomość flash, jeśli jest ustawiona.
 */
function display_flash_message()
{
    if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
        $message = $_SESSION['flash']['message'];
        $type = $_SESSION['flash']['type']; // 'positive' lub 'negative'
        echo '<div class="flash-message ' . htmlspecialchars($type) . '">' . htmlspecialchars($message) . '</div>';
        unset($_SESSION['flash']);
    }
}

/**
 * Ustawia wiadomość flash.
 * np. set_flash_message('Zadanie zostało przypisane!', 'positive');
 * np. set_flash_message('Błąd podczas przypisywania zadania.', 'negative');
 */
function set_flash_message($message, $type = 'positive')
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Sanityzuje dane wejściowe, usuwając niepotrzebne spacje i konwertując specjalne znaki na encje HTML.
 */
function sanitize_input($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Wybór języka i guess
 */
function trans($key) {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'pl'; // Domyślnie język polski
    static $translations = [];
    if (empty($translations[$lang])) {
        $file = __DIR__ . "/lang/$lang.php";
        if (file_exists($file)) {
            $translations[$lang] = include $file;
        } else {
            $translations[$lang] = [];
        }
    }
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}
?>