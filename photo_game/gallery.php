<?php
include __DIR__ . '/config/config.php';
if (!isset($_SESSION['username'])) {
    redirect('auth/login.php');
}

// Ustawienia stronicowania
$photos_per_page = 24;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $photos_per_page;

// Filtry
$filters = [];
if (!empty($_GET['user_id'])) {
    $filters['user_id'] = intval($_GET['user_id']);
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}
if (!empty($_GET['task_id'])) {
    $filters['task_id'] = intval($_GET['task_id']);
}
if (!empty($_GET['min_rating'])) {
    $filters['min_rating'] = floatval($_GET['min_rating']);
}
if (!empty($_GET['sort'])) {
    $filters['sort'] = $_GET['sort'];
}

try {
    // Pobierz łączną liczbę zdjęć z filtrowaniem
    $total_photos = $db->getCompletedTasksCountWithFilters($filters);
    $total_pages = ceil($total_photos / $photos_per_page);

    // Pobierz zdjęcia dla bieżącej strony z filtrowaniem
    $photos = $db->getCompletedTasksWithRatings($photos_per_page, $offset, $filters);

    // Pobierz listę użytkowników do filtrowania
    $all_users = $db->getAllUsers();

    // Pobierz listę zadań do filtrowania
    $all_tasks = $db->getAllTasks();

} catch (Exception $e) {
    error_log('Błąd gallery.php: ' . $e->getMessage());
    set_flash_message(translate('galleryLoadError'), 'negative');
    $photos = [];
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include 'elements/head.php' ?>
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
<body>
<?php include 'elements/header.php' ?>

<main class="main-content">
    <?php display_flash_message() ?>

    <div class="tasks-section">
        <h2><?php __('gallery') ?></h2>
        <p style="text-align: center; opacity: 0.8; margin-bottom: 2rem;">
            <?php __('gallerySubtitle') ?>
        </p>

        <!-- Filtry -->
        <div class="filters-section">
            <button class="filters-toggle Button1" onclick="toggleFilters()">
                🔍 <?php __('filters') ?> <?php echo !empty($filters) ? '(' . count($filters) . ')' : ''; ?>
            </button>

            <div class="filters-container" id="filtersContainer" style="<?php echo !empty($filters) ? '' : 'display: none;'; ?>">
                <form method="get" action="gallery.php" class="filters-form">
                    <div class="filters-grid">
                        <!-- Sortowanie -->
                        <div class="filter-item">
                            <label><?php __('sort') ?>:</label>
                            <select name="sort">
                                <option value=""><?php __('newest') ?></option>
                                <option value="date_asc" <?php echo (isset($filters['sort']) && $filters['sort'] == 'date_asc') ? 'selected' : ''; ?>><?php __('oldest') ?></option>
                                <option value="rating_desc" <?php echo (isset($filters['sort']) && $filters['sort'] == 'rating_desc') ? 'selected' : ''; ?>><?php __('highestRating') ?></option>
                                <option value="rating_asc" <?php echo (isset($filters['sort']) && $filters['sort'] == 'rating_asc') ? 'selected' : ''; ?>><?php __('lowestRating') ?></option>
                            </select>
                        </div>

                        <!-- Zadanie -->
                        <div class="filter-item">
                            <label><?php __('task') ?>:</label>
                            <select name="task_id">
                                <option value=""><?php __('all') ?></option>
                                <?php foreach ($all_tasks as $task): ?>
                                    <option value="<?php echo $task['id']; ?>"
                                        <?php echo (isset($filters['task_id']) && $filters['task_id'] == $task['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(substr($task['description'], 0, 50)) . (strlen($task['description']) > 50 ? '...' : ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Minimalna ocena -->
                        <div class="filter-item">
                            <label><?php __('minRating') ?>:</label>
                            <select name="min_rating">
                                <option value=""><?php __('all') ?></option>
                                <option value="4" <?php echo (isset($filters['min_rating']) && $filters['min_rating'] == 4) ? 'selected' : ''; ?>>⭐ 4+</option>
                                <option value="3" <?php echo (isset($filters['min_rating']) && $filters['min_rating'] == 3) ? 'selected' : ''; ?>>⭐ 3+</option>
                                <option value="2" <?php echo (isset($filters['min_rating']) && $filters['min_rating'] == 2) ? 'selected' : ''; ?>>⭐ 2+</option>
                                <option value="1" <?php echo (isset($filters['min_rating']) && $filters['min_rating'] == 1) ? 'selected' : ''; ?>>⭐ 1+</option>
                            </select>
                        </div>
                    </div>

                    <div class="filters-actions">
                        <button type="submit" class="Button1"><?php __('filter') ?></button>
                        <a href="gallery.php" class="Button1" style="background: rgba(255, 77, 77, 0.3);"><?php __('clearFilters') ?></a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($photos)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🖼️</div>
                <h3><?php __('noPhotosFound') ?></h3>
                <p><?php !empty($filters) ? __('noPhotosFoundDescription') : __('galleryEmpty'); ?></p>
                <?php if (!empty($filters)): ?>
                    <a href="gallery.php" class="Button1" style="margin-top: 1rem;"><?php __('clearFilters') ?></a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="gallery">
                <?php foreach ($photos as $photo): ?>
                    <div class="gallery-item" data-photo-id="<?php echo $photo['id']; ?>">
                        <a href="tasks/view_photo.php?photo_id=<?php echo htmlspecialchars($photo['id']); ?>">
                            <img src="uploads/<?php echo htmlspecialchars($photo['photo']); ?>"
                                 alt="<?php __('taskPhoto') ?>">
                        </a>

                        <p><strong><?php echo htmlspecialchars($photo['description']); ?></strong></p>
                        <p style="opacity: 0.7; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($photo['username']); ?> •
                            <?php echo $photo['uploaded_at'] ?>
                        </p>

                        <!-- Ocena -->
                        <div class="photo-rating-display">
                            <div class="stars-display">
                                <?php
                                $avg_rating = round($photo['avg_rating'], 1);
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
                            <span class="rating-text">
                                <?php ___('ratingCount', $photo['rating_count'] ?? 0, ['avg' => $avg_rating]) ?>
                            </span>
                        </div>
                        <ratings></ratings>
                        <!-- Gwiazdki do oceniania -->
                        <?php if ($db->hasUserRatedPhoto($photo['id'], $_SESSION['user_id'])): ?>
                            <!-- Użytkownik już ocenił - pokaż jego ocenę -->
                            <div class="user-rating-info">
                                <?php __('yourRating') ?>:
                                <?php
                                $user_rating = $db->getUserPhotoRating($photo['id'], $_SESSION['user_id']);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $user_rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                        <?php elseif ($db->isPhotoRatingEnabled()): ?>
                            <!-- Użytkownik nie ocenił i ocenianie jest włączone -->
                            <div class="photo-rating-input">
                                <p style="font-size: 0.9rem; margin-bottom: 0.5rem;"><?php __('rate') ?>:</p>
                                <div class="stars-input" data-photo-id="<?php echo $photo['id']; ?>">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star-input" data-rating="<?php echo $i; ?>">☆</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Użytkownik nie ocenił i ocenianie jest wyłączone -->
                            <div style="text-align: center; padding: 0.8rem; background: rgba(255, 165, 0, 0.2); border-radius: 8px; margin: 1rem 0; font-size: 0.9rem;">
                                ⚠️ <?php __('ratingDisabled') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    // Zachowaj filtry w paginacji
                    $filter_params = http_build_query(array_diff_key($_GET, ['page' => '']));
                    $filter_params = $filter_params ? '&' . $filter_params : '';
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $filter_params; ?>">← <?php __('previous') ?></a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $filter_params; ?>"
                           class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $filter_params; ?>"><?php __('next') ?> →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'elements/footer.php'; ?>

<script src="js/ratings.js"></script>
<script>
    function toggleFilters() {
        const container = document.getElementById('filtersContainer');
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
    }
</script>
</body>
</html>