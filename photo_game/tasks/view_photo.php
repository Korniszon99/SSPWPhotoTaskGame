<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['username'])) {
    redirect('../auth/login.php');
}

if (!isset($_GET['photo_id'])) {
    set_flash_message(translate('invalidParameters'), 'negative');
    redirect('../gallery.php');
}

$photo_id = intval($_GET['photo_id']);

try {
    // Pobierz dane zdjęcia
    $photo = $db->getCompletedTaskById($photo_id);

    if (!$photo) {
        set_flash_message(translate('photoNotFound'), 'negative');
        redirect('../gallery.php');
        exit;
    }

    // Pobierz ocenę zdjęcia
    $ratingData = $db->getPhotoRating($photo_id);
    $avg_rating = round($ratingData['avg_rating'], 1);
    $rating_count = $ratingData['rating_count'];

    // Sprawdź czy użytkownik już ocenił
    $user_has_rated = $db->hasUserRatedPhoto($photo_id, $_SESSION['user_id']);
    $user_rating = $user_has_rated ? $db->getUserPhotoRating($photo_id, $_SESSION['user_id']) : null;

    // Sprawdź czy zdjęcie jest w top 3
    $top_photos = $db->getTopRatedPhotos(3);
    $top_position = null;
    foreach ($top_photos as $index => $top_photo) {
        if ($top_photo['id'] == $photo_id) {
            $top_position = $index + 1;
            break;
        }
    }

    // Sprawdź czy ocenianie jest włączone
    $rating_enabled = $db->isPhotoRatingEnabled();

} catch (Exception $e) {
    error_log('Błąd view_photo.php: ' . $e->getMessage());
    set_flash_message(translate('photoLoadError'), 'negative');
    redirect('../gallery.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include '../elements/head.php' ?>
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
<body>
<?php include '../elements/header.php' ?>

<main class="main-content">
    <?php display_flash_message() ?>

    <div class="photo-detail">
        <h2 style="text-align: center; margin-bottom: 2rem;">📸 <?php __('photoPreview') ?></h2>

        <?php if ($top_position): ?>
            <div class="user-position-card" style="margin-bottom: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">
                    <?php if ($top_position == 1): ?>
                        🥇
                    <?php elseif ($top_position == 2): ?>
                        🥈
                    <?php elseif ($top_position == 3): ?>
                        🥉
                    <?php endif; ?>
                </div>
                <h3><?php __('photoInTop', ['position' => $top_position]) ?></h3>
                <p style="opacity: 0.9; margin-top: 0.5rem;">
                    <?php ___('photoInTopSpecial', $top_position) ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="<?php echo $top_position ? 'top-photo-card top-3' : ''; ?>"
             style="<?php echo $top_position ? 'padding: 1.5rem; border-radius: 20px;' : ''; ?>">

            <img src="../uploads/<?php echo htmlspecialchars($photo['photo']); ?>"
                 alt="<?php __('taskPhoto') ?>"
                 class="photo-detail-image">

            <!-- Ocena zdjęcia -->
            <div class="photo-rating-display" data-photo-id="<?php echo $photo_id; ?>" style="text-align: center; margin: 2rem 0; padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
                <div class="stars-display" style="justify-content: center; margin-bottom: 1rem; font-size: 2rem;">
                    <?php
                    $full_stars = floor($avg_rating);
                    $has_half = ($avg_rating - $full_stars) >= 0.5;

                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $full_stars) {
                            echo '<span class="star filled">★</span>';
                        } elseif ($i == $full_stars + 1 && $has_half) {
                            echo '<span class="star half">★</span>';
                        } else {
                            echo '<span class="star">☆</span>';
                        }
                    }
                    ?>
                </div>
                <div class="rating-text" style="font-size: 1.2rem;">
                    <?php if ($rating_count > 0): ?>
                        <span class="rating-number" style="font-size: 2rem; color: #ffd700;"><?php echo $avg_rating; ?></span>
                        <span class="rating-count" style="font-size: 1rem;">
                            <?php ___('ratingCountWithoutAvg', $rating_count) ?>
                        </span>
                    <?php else: ?>
                        <span style="opacity: 0.7;">
                            <?php ___('ratingCountWithoutAvg', $rating_count) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ocenianie - taka sama struktura jak w gallery.php -->
            <div class="gallery-item" data-photo-id="<?php echo $photo_id; ?>" style="background: transparent; padding: 0; border: none;">
                <ratings></ratings>
                <?php if ($user_has_rated): ?>
                    <!-- Użytkownik już ocenił -->
                    <div class="user-rating-info" style="margin: 1.5rem 0; font-size: 1.1rem;">
                        <?php __('yourRating') ?>:
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $user_rating ? '★' : '☆';
                        }
                        ?>
                    </div>
                <?php elseif ($rating_enabled): ?>
                    <!-- Użytkownik może ocenić -->
                    <div class="photo-rating-input" style="margin: 1.5rem 0;">
                        <p style="font-size: 1.1rem; margin-bottom: 0.8rem; font-weight: 500;">
                            <?php __('rate') ?>:
                        </p>
                        <div class="stars-input" data-photo-id="<?php echo $photo_id; ?>" style="justify-content: center;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star-input" data-rating="<?php echo $i; ?>">☆</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Ocenianie wyłączone -->
                    <div style="text-align: center; padding: 1rem; background: rgba(255, 165, 0, 0.2); border-radius: 10px; margin: 1.5rem 0; font-size: 1rem;">
                        ⚠️ <?php __('ratingDisabled') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="photo-info" style="margin-top: 2rem;">
                <div class="photo-info-item" style="margin-bottom: 1.5rem;">
                    <div class="photo-info-label" style="font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem; opacity: 0.7;">
                        <?php __('taskDescription') ?>:
                    </div>
                    <div class="photo-info-value" style="font-size: 1.2rem; line-height: 1.6;">
                        <?php echo htmlspecialchars($photo['description']); ?>
                    </div>
                </div>

                <div class="photo-info-item" style="margin-bottom: 1.5rem;">
                    <div class="photo-info-label" style="font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem; opacity: 0.7;">
                        <?php __('author') ?>:
                    </div>
                    <div class="photo-info-value" style="font-size: 1.1rem;">
                        <?php echo htmlspecialchars($photo['username']); ?>
                    </div>
                </div>

                <div class="photo-info-item">
                    <div class="photo-info-label" style="font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem; opacity: 0.7;">
                        <?php __('dateCompleted') ?>:
                    </div>
                    <div class="photo-info-value" style="font-size: 1.1rem;">
                        <?php echo htmlspecialchars($photo['uploaded_at']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="back-buttons" style="margin-top: 2rem;">
            <a href="../gallery.php" class="Button1"><?php __('backToGallery') ?></a>
            <?php if ($top_position): ?>
                <a href="../ranking/top_rated.php" class="Button1" style="background: linear-gradient(45deg, #FFD700, #FFA500);">
                    <?php __('seeRanking') ?>
                </a>
            <?php endif; ?>
            <a href="task_list.php" class="Button1"><?php __('myTasks') ?></a>
            <a href="../dashboard.php" class="Button1"><?php __('dashboard') ?></a>
        </div>
    </div>
</main>

<?php include '../elements/footer.php'; ?>

<script src="../js/ratings.js"></script>
</body>
</html>