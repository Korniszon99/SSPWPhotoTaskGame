<?php
// Nie wywołuj session_start() ponownie, bo jest już w config.php

// Sprawdź, czy formularz zmiany języka został wysłany
if (isset($_POST['language'])) {
    $_SESSION['lang'] = $_POST['language'];

    // Przekieruj użytkownika z powrotem na tę samą stronę, aby uniknąć ponownego wysłania formularza
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Użyj zmiennej $lang zamiast $language, aby zachować spójność z config.php
global $lang;
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'pl';
?>
<header>
    <form method="post" action="">
        <select name="language" onchange="this.form.submit()">
            <option value="pl" <?php echo ($lang == 'pl') ? 'selected' : ''; ?>>Polski</option>
            <option value="en" <?php echo ($lang == 'en') ? 'selected' : ''; ?>>English</option>
        </select>
    </form>
</header>