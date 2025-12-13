<?php require_once __DIR__. '/../config/config.php'?>

<header class="header" role="banner">
    <div class="logo">
        <a href="/photo_game/dashboard.php" aria-label="<?php __('appTitle')?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
            <img src="/photo_game/graphics/logo-fut.png" alt="<?php __('appTitleFull')?>">
            <span><?php __('appTitle')?></span>
        </a>
    </div>
    <div class="header-right">
        <nav class="language-switcher" role="navigation" aria-label="Language selector">
            <a href="?lang=pl"
               class="<?= $currentLang === 'pl' ? 'active' : '' ?>"
               lang="pl"
               hreflang="pl"
                <?= $currentLang === 'pl' ? 'aria-current="true"' : '' ?>>
                <img src="/photo_game/graphics/pl.svg" alt="" class="flag-icon" role="presentation">
                <span>PL</span>
            </a>
            <a href="?lang=en"
               class="<?= $currentLang === 'en' ? 'active' : '' ?>"
               lang="en"
               hreflang="en"
                <?= $currentLang === 'en' ? 'aria-current="true"' : '' ?>>
                <img src="/photo_game/graphics/en.svg" alt="" class="flag-icon" role="presentation">
                <span>EN</span>
            </a>
            <?php $otherLang = $currentLang === 'pl' ? 'en' : 'pl' ?>
            <a href="?lang=<?= $otherLang ?>"
               class="mobile active"
               lang="<?= $otherLang ?>"
               hreflang="<?= $otherLang ?>"
                <?= $currentLang === 'en' ? 'aria-current="true"' : '' ?>>
                <img src="/photo_game/graphics/<?= $currentLang ?>.svg" alt="" class="flag-icon" role="presentation">
            </a>
        </nav>
        <button class="hamburger"
                onclick="toggleMenu()"
                aria-label="<?php __('menu')?>"
                aria-expanded="false"
                aria-controls="main-menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<!-- Main Navigation -->
<nav class="sidebar" id="main-menu" role="navigation" aria-label="Main menu">
    <div class="sidebar-header"></div>

    <ul>
        <li>
            <a href="/photo_game/dashboard.php" class="menu-item">
                <span class="menu-icon" aria-hidden="true">🏠</span>
                <span><?php __('dashboard')?></span>
            </a>
        </li>
        <li>
            <a href="/photo_game/actions/draw_task.php" class="menu-item">
                <span class="menu-icon" aria-hidden="true">🎲</span>
                <span><?php __('drawTask')?></span>
            </a>
        </li>
        <li>
            <a href="/photo_game/tasks/task_list.php" class="menu-item">
                <span class="menu-icon" aria-hidden="true">📋</span>
                <span><?php __('yourTasks')?></span>
            </a>
        </li>
        <li>
            <a href="/photo_game/gallery.php" class="menu-item">
                <span class="menu-icon" aria-hidden="true">🖼️</span>
                <span><?php __('gallery')?></span>
            </a>
        </li>
        <li>
            <a href="/photo_game/ranking/ranking.php" class="menu-item">
                <span class="menu-icon" aria-hidden="true">🏆</span>
                <span><?php __('userRanking')?></span>
            </a>
        </li>
        <li>
            <a href="/photo_game/ranking/top_rated.php" class="menu-item">
                <span class="menu-icon" aria-hidden="true">⭐</span>
                <span><?php __('photoRanking')?></span>
            </a>
        </li>
    </ul>

    <?php if (isset($_SESSION['user_id']) && $db->isUserAdmin($_SESSION['user_id'])): ?>
        <div class="sidebar-header"></div>
        <section class="admin-section">
            <h3 class="sr-only"><?php __('adminPanel')?></h3>
            <ul>
                <li>
                    <a href="/photo_game/admin/panel.php" class="menu-item">
                        <span class="menu-icon" aria-hidden="true">🔒</span>
                        <span><?php __('adminPanel')?></span>
                    </a>
                </li>
                <li>
                    <a href="/photo_game/admin/tasks.php" class="menu-item">
                        <span class="menu-icon" aria-hidden="true">📋</span>
                        <span><?php __('manageTasks')?></span>
                    </a>
                </li>
                <li>
                    <a href="/photo_game/admin/settings.php" class="menu-item">
                        <span class="menu-icon" aria-hidden="true">⚙️</span>
                        <span><?php __('settings')?></span>
                    </a>
                </li>
            </ul>
        </section>
    <?php endif; ?>
    <div class="sidebar-header"></div>
    <ul>
        <li>
            <a href="/photo_game/auth/logout.php" class="menu-item">
                <span class="menu-icon" aria-hidden="true">🚪</span>
                <span><?php __('logout')?></span>
            </a>
        </li>
    </ul>
</nav>

<!-- Overlay -->
<div class="overlay" onclick="toggleMenu()" role="presentation"></div>