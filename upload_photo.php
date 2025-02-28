<?php
include 'config.php';
include 'functions.php';
global $mysqli;
global $lang;

// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    redirect('login.php');
}

// Sprawdź, czy formularz został przesłany metodą POST i czy przycisk upload_photo został kliknięty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_photo'])) {
    $user_id = $_SESSION['user_id'];
    $user_task_id = intval($_POST['user_task_id']);

    // Sprawdź, czy user_task_id jest prawidłowy i należy do użytkownika
    $stmt = $mysqli->prepare("SELECT 1 FROM user_tasks WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        die(trans('query_error', $lang)); // Użyj klucza 'query_error' z tłumaczeniem
    }
    $stmt->bind_param('ii', $user_task_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    // Jeśli user_task_id nie jest prawidłowy, ustaw wiadomość flash i przekieruj do listy zadań
    if ($stmt->num_rows == 0) {
        set_flash_message(trans('upload_error_1', $lang), 'negative'); // Użyj klucza 'upload_error_1'
        redirect('task_list.php');
    }
    $stmt->close();

    // Sprawdź, czy plik został przesłany bez błędów
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        // Sprawdź, czy typ pliku jest dozwolony
        if (!in_array($_FILES['photo']['type'], $allowed_types)) {
            set_flash_message(trans('upload_error_2', $lang), 'negative'); // Użyj klucza 'upload_error_2'
            redirect('task_list.php');
        }

        $upload_dir = 'uploads/';
        // Sprawdź, czy katalog uploads istnieje, jeśli nie, utwórz go
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $unique_name = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
        $upload_file = $upload_dir . $unique_name;

        // Przenieś przesłany plik do katalogu uploads
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_file)) {
            // Zapisz informacje o przesłanym zdjęciu w bazie danych
            $stmt = $mysqli->prepare("INSERT INTO completed_tasks (user_id, user_task_id, photo) VALUES (?, ?, ?)");
            if (!$stmt) {
                die(trans('query_error', $lang)); // Użyj klucza 'query_error' z tłumaczeniem
            }
            $stmt->bind_param('iis', $user_id, $user_task_id, $unique_name);
            if ($stmt->execute()) {
                set_flash_message(trans('upload_succeed', $lang), 'positive'); // Użyj klucza 'upload_succeed'
                redirect('task_list.php');
            } else {
                set_flash_message(trans('upload_error_3', $lang), 'negative'); // Użyj klucza 'upload_error_3'
                redirect('task_list.php');
            }
            $stmt->close();
        } else {
            set_flash_message(trans('upload_error_4', $lang), 'negative'); // Użyj klucza 'upload_error_4'
            redirect('task_list.php');
        }
    } else {
        // Jeśli plik nie został wybrany lub wystąpił błąd podczas przesyłania, ustaw wiadomość flash
        set_flash_message(trans('upload_error_5', $lang), 'negative'); // Użyj klucza 'upload_error_5'
        redirect('task_list.php');
    }
}
?>