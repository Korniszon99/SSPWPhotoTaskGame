<?php
require_once __DIR__ . '/../config/config.php';


if (!isset($_SESSION['username'])) {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];

try {
    // Pobierz wszystkie zadania użytkownika (zrealizowane i niezrealizowane)
    $tasks = $db->getUserTasks($user_id);

    // Pobierz top 3 zdjęcia dla oznaczenia
    $top_photos = $db->getTopRatedPhotos(3);
    $top_photo_ids = array_column($top_photos, 'id');

} catch (Exception $e) {
    error_log('Błąd task_list.php: ' . $e->getMessage());
    set_flash_message(translate('tasksLoadError'), 'negative');
    $tasks = [];
    $top_photo_ids = [];
}
?>

<div class="tasks-section">
    <h2><?php __('yourTasks') ?></h2>

    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔭</div>
            <h3><?php __('noTasksAssigned') ?></h3>
            <p><?php __('noTasksAssignedDescription') ?></p>
        </div>
    <?php else: ?>
        <div class="tasks-grid">
            <?php foreach ($tasks as $task): ?>
                <?php
                // Sprawdź czy to zdjęcie jest w top 3
                $is_top3 = false;
                $top_position = 0;
                if ($task['photo']) {
                    $position = array_search($task['photo_id'], $top_photo_ids);
                    if ($position !== false) {
                        $is_top3 = true;
                        $top_position = $position + 1;
                    }
                }
                ?>
                <div class="task-card <?php echo $is_top3 ? 'top-photo-card top-3' : ''; ?>">
                    <div class="task-status <?php echo $task['photo'] ? 'completed' : ''; ?>"></div>

                    <!-- Medal dla top 3 -->
                    <?php if ($is_top3): ?>
                        <div class="top-photo-position">
                            <?php if ($top_position == 1): ?>
                                🥇
                            <?php elseif ($top_position == 2): ?>
                                🥈
                            <?php elseif ($top_position == 3): ?>
                                🥉
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Nazwa zadania -->
                    <div class="task-description">
                        <?php echo htmlspecialchars($task['description']); ?>
                    </div>

                    <!-- Zdjęcie/formularz -->
                    <?php if ($task['photo']): ?>
                        <div class="task-content-bottom">
                            <a href="/photo_game/tasks/view_photo.php?photo_id=<?php echo htmlspecialchars($task['photo_id']); ?>">
                                <img src="/photo_game/uploads/<?php echo htmlspecialchars($task['photo']); ?>"
                                     alt="<?php __('taskPhoto') ?>"
                                     class="task-image">
                            </a>

                            <?php
                            // Pobierz ocenę zdjęcia
                            $rating_data = $db->getPhotoRating($task['photo_id']);
                            $avg_rating = round($rating_data['avg_rating'], 1);
                            $rating_count = $rating_data['rating_count'];
                            ?>

                            <!-- Ocena -->
                            <?php if ($rating_count > 0): ?>
                                <div class="photo-rating-display">
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
                                    <span class="rating-text">
                                        <?php ___('ratingCount', $rating_count, ['avg' => $avg_rating]) ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="photo-rating-display">
                                    <span class="rating-text" style="opacity: 0.6;">
                                        <?php ___('ratingCount', $rating_count, ['avg' => $avg_rating]) ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="task-completed-badge">
                                <?php __('finished') ?> · <?php echo $db->getCompletedTaskById($task['photo_id'])['uploaded_at'] ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="post" action="/photo_game/actions/upload_photo.php" enctype="multipart/form-data" class="upload-section">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="user_task_id" value="<?php echo $task['user_task_id']; ?>">
                            <div class="file-input-wrapper">
                                <input type="file" name="photo" id="file_<?php echo $task['user_task_id']; ?>" accept="image/*" required>
                                <label for="file_<?php echo $task['user_task_id']; ?>" class="file-input-label">
                                    📷 <?php __('selectPhoto') ?>
                                </label>
                            </div>
                            <button type="submit" name="upload_photo" class="upload-btn"><?php __('submit') ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>