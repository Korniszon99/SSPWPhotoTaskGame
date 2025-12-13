<?php
include __DIR__ . '/../config/config.php';
// Sprawdź, czy użytkownik jest zalogowany
if (!isset($_SESSION['username'])) {
    redirect('../auth/login.php');
}

// Sprawdź czy upload jest włączony
if (!$db->isPhotoUploadEnabled()) {
    set_flash_message(translate('photosAddingDisabled'), 'negative');
    redirect('../tasks/task_list.php');
    exit;
}

// Sprawdź, czy formularz został przesłany metodą POST i czy przycisk upload_photo został kliknięty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_photo'])) {
    // RATE LIMITING
    $check = $rateLimiter->check('upload_photo');
    if (is_array($check) && !$check['allowed']) {
        set_flash_message(
            translatePlural('photoRateLimitReached', $check['retry_after']),
            'negative'
        );
        redirect('../tasks/task_list.php');
        exit;
    }

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_flash_message(translate('invalidCSRFToken'), 'negative');
        redirect('../tasks/task_list.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $user_task_id = intval($_POST['user_task_id']);
    $uploaded_file_path = null;

    try {
        //  Sprawdź, czy zadanie należy do użytkownika
        if (!$db->userTaskBelongsToUser($user_task_id, $user_id)) {
            set_flash_message(translate('taskNotAssigned'), 'negative');
            redirect('../tasks/task_list.php');
            exit;
        }

        // Sprawdź, czy plik został przesłany poprawnie
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            set_flash_message(translate('photoNoFile'), 'negative');
            redirect('../tasks/task_list.php');
            exit;
        }

        // Sprawdź rozmiar i typ pliku
        $max_size = 10 * 1024 * 1024; // 10MB przed kompresją
        if ($_FILES['photo']['size'] > $max_size) {
            set_flash_message(translate('photoMaxSizeExceeded', ['size' => '10MB']), 'negative');
            redirect('../tasks/task_list.php');
            exit;
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            set_flash_message(translate('photoInvalidType', ['types' => 'JPG, PNG, GIF, WEBP']), 'negative');
            redirect('../tasks/task_list.php');
            exit;
        }

        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $unique_name = uniqid() . '_' . bin2hex(random_bytes(8)) . '.jpg'; // Zawsze zapisujemy jako JPG
        $upload_file = $upload_dir . $unique_name;

        // Kompresuj i zapisz obraz
        $compressed = compressImage($_FILES['photo']['tmp_name'], $upload_file, $file_extension);

        if (!$compressed) {
            set_flash_message(translate('photoCompressError'), 'negative');
            redirect('../tasks/task_list.php');
            exit;
        }

        // Zapisz ścieżkę przesłanego pliku do późniejszego usunięcia w razie błędu
        $uploaded_file_path = $upload_file;

        // Transakcja bazy danych
        $db->beginTransaction();

        try {
            // Sprawdź czy zadanie nie zostało już wykonane (double-check)
            if ($db->isTaskCompleted($user_task_id)) {
                // Rollback i usuń plik
                $db->rollBack();
                if (file_exists($uploaded_file_path)) {
                    unlink($uploaded_file_path);
                }
                set_flash_message(translate('taskAlreadyCompleted'), 'negative');
                redirect('../tasks/task_list.php');
                exit;
            }

            // Zapisz w bazie
            $db->completeTask($user_id, $user_task_id, $unique_name);

            // Commit transakcji
            $db->commit();

            set_flash_message(translate('photoUploadSuccess'), 'positive');
            redirect('../tasks/task_list.php');
            exit;

        } catch (Exception $db_error) {
            // Rollback transakcji
            $db->rollBack();

            // Usuń plik jeśli wystąpił błąd bazy
            if ($uploaded_file_path && file_exists($uploaded_file_path)) {
                unlink($uploaded_file_path);
                error_log("Usunięto plik po błędzie bazy: $uploaded_file_path");
            }

            throw $db_error; // Rzuć dalej do zewnętrznego catch
        }

    } catch (Exception $e) {
        error_log('Błąd upload_photo.php: ' . $e->getMessage());

        // Dodatkowy cleanup
        if ($uploaded_file_path && file_exists($uploaded_file_path)) {
            unlink($uploaded_file_path);
        }

        set_flash_message(translate('photoUploadError'), 'negative');
        redirect('../tasks/task_list.php');
        exit;
    }
}

/**
 * Kompresuje i zmienia rozmiar obrazu
 */
function compressImage($source, $destination, $original_extension, $quality = 85, $max_width = 1920, $max_height = 1920) {
    try {
        // Pobierz informacje o obrazie
        $info = getimagesize($source);
        if (!$info) {
            return false;
        }

        list($width, $height) = $info;
        $mime = $info['mime'];

        // Wczytaj obraz w zależności od typu
        switch ($original_extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'png':
                $image = imagecreatefrompng($source);
                break;
            case 'gif':
                $image = imagecreatefromgif($source);
                break;
            case 'webp':
                $image = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        // Oblicz nowe wymiary (zachowaj proporcje)
        $ratio = min($max_width / $width, $max_height / $height);

        // Jeśli obraz jest mniejszy niż max, nie zmieniaj rozmiaru
        if ($ratio >= 1) {
            $new_width = $width;
            $new_height = $height;
        } else {
            $new_width = round($width * $ratio);
            $new_height = round($height * $ratio);
        }

        // Utwórz nowy obraz
        $new_image = imagecreatetruecolor($new_width, $new_height);

        // Zachowaj przezroczystość dla PNG
        if ($original_extension == 'png') {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }

        // Skopiuj i zmień rozmiar
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        // Zapisz jako JPEG z kompresją
        $result = imagejpeg($new_image, $destination, $quality);

        // Zwolnij pamięć
        imagedestroy($image);
        imagedestroy($new_image);

        return $result;

    } catch (Exception $e) {
        error_log('Błąd kompresji obrazu: ' . $e->getMessage());
        return false;
    }
}
?>
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">