<?php
include __DIR__ . '/../config/config.php';
if (!isset($_SESSION['username'])) {
    redirect('../auth/login.php');
}

try {
    $top_photos = $db->getTopRatedPhotos(50);
} catch (Exception $e) {
    error_log('Błąd top_rated.php: ' . $e->getMessage());
    set_flash_message(translate('rankingLoadError'), 'negative');
    $top_photos = [];
}
?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include '../elements/head.php'; ?>
<body>
<?php include '../elements/header.php'; ?>

<main class="main-content">
    <div class="ranking-container">
        <?php display_flash_message(); ?>

        <div class="ranking-header">
            <h2>⭐ <?php __('photoRankingTitle') ?></h2>
            <p style="opacity: 0.8;"><?php __('photoRankingDescription') ?></p>
        </div>

        <?php if (empty($top_photos)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">⭐</div>
                <h3><?php __('rankingEmpty') ?></h3>
                <p><?php __('photoRankingEmptyDescription') ?></p>
                <a href="../gallery.php" class="Button1" style="margin-top: 1rem;"><?php __('goToGallery') ?></a>
            </div>
        <?php else: ?>
            <div class="top-photos-grid">
                <?php foreach ($top_photos as $index => $photo): ?>
                    <?php
                    $position = $index + 1;
                    $is_top3 = $position <= 3;
                    $avg_rating = round($photo['avg_rating'], 1);
                    ?>
                    <div class="top-photo-card <?php echo $is_top3 ? 'top-3' : ''; ?>">
                        <div class="top-photo-position">
                            <?php if ($position == 1): ?>
                                🥇
                            <?php elseif ($position == 2): ?>
                                🥈
                            <?php elseif ($position == 3): ?>
                                🥉
                            <?php else: ?>
                                #<?php echo $position; ?>
                            <?php endif; ?>
                        </div>

                        <a href="../tasks/view_photo.php?photo_id=<?php echo $photo['id']; ?>">
                            <img src="../uploads/<?php echo htmlspecialchars($photo['photo']); ?>"
                                 alt="<?php __('photo') ?>">
                        </a>

                        <div class="top-photo-info">
                            <div class="photo-rating-big">
                                <div class="stars-display">
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
                                <span class="rating-number"><?php echo $avg_rating; ?></span>
                                <span class="rating-count"><?php ___('ratingCountWithoutAvg', $photo['rating_count']) ?></span>
                            </div>

                            <p class="photo-description"><?php echo htmlspecialchars($photo['description']); ?></p>
                            <p class="photo-author">👤 <?php echo htmlspecialchars($photo['username']); ?></p>
                            <p class="photo-date">📅 <?php echo htmlspecialchars($photo['uploaded_at']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="../gallery.php" class="Button1"><?php __('gallery') ?></a>
            <a href="../dashboard.php" class="Button1"><?php __('dashboard') ?></a>
        </div>
    </div>
</main>

<?php include '../elements/footer.php'; ?>
</body>
</html>