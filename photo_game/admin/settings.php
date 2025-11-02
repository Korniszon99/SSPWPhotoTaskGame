<?php
include __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}

// Sprawdź czy użytkownik jest adminem
if (!$db->isUserAdmin($_SESSION['user_id'])) {
    set_flash_message(translate('accessDenied'), 'negative');
    redirect('../dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))) {
    set_flash_message(translate('invalidCSRFToken'), 'negative');
    redirect('settings.php');
    exit;
}

// Obsługa generowania nowego kodu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regenerate_code'])) {
    try {
        $new_code = $db->regenerateAccessCode();
        set_flash_message("Nowy kod dostępu: <strong>$new_code</strong> - zapisz go!", 'positive');
        redirect('settings.php');
    } catch (Exception $e) {
        error_log('Błąd generowania kodu: ' . $e->getMessage());
        set_flash_message(translate('codeGenerationError'), 'negative');
    }
}

// Obsługa ustawiania niestandardowego kodu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_custom_code'])) {


    $custom_code = strtoupper(sanitize_input($_POST['custom_code']));

    if (empty($custom_code)) {
        set_flash_message(translate('codeCantBeEmpty'), 'negative');
    } else {
        try {
            $db->setCustomAccessCode($custom_code);
            set_flash_message("Ustawiono niestandardowy kod: <strong>$custom_code</strong>", 'positive');
            redirect('settings.php');
        } catch (Exception $e) {
            set_flash_message(translate('error') . ': ' . $e->getMessage(), 'negative');
        }
    }
}

// Obsługa zapisywania ustawień
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        $db->setSetting('registration_enabled', isset($_POST['registration_enabled']) ? '1' : '0');
        $db->setSetting('login_enabled', isset($_POST['login_enabled']) ? '1' : '0');
        $db->setSetting('photo_upload_enabled', isset($_POST['photo_upload_enabled']) ? '1' : '0');
        $db->setSetting('photo_rating_enabled', isset($_POST['photo_rating_enabled']) ? '1' : '0');
        $db->setSetting('require_access_code', isset($_POST['require_access_code']) ? '1' : '0');

        set_flash_message(translate('settingsSaved'), 'positive');
        redirect('settings.php');
    } catch (Exception $e) {
        error_log('Błąd zapisywania ustawień: ' . $e->getMessage());
        set_flash_message(translate('settingsSaveError', ['error' => $e->getMessage()]), 'negative');
    }
}

// Pobierz aktualne ustawienia i kod
try {
    $settings = $db->getAllSettings();
    $current_code = $db->getActiveAccessCode();
} catch (Exception $e) {
    error_log('Błąd settings.php: ' . $e->getMessage());
    $settings = [
        'registration_enabled' => '1',
        'photo_upload_enabled' => '1',
        'photo_rating_enabled' => '1',
        'require_access_code' => '1'
    ];
    $current_code = null;
}
?>
<!DOCTYPE html>
<html lang="<?php __('locale') ?>">
<?php include '../elements/head.php'; ?>
<body>
<?php include '../elements/header.php'; ?>

<main class="main-content">
    <div class="admin-panel">
        <?php display_flash_message(); ?>

        <div class="admin-header">
            <h2>⚙️ <?php __('settings') ?></h2>
            <p style="opacity: 0.8;"><?php __('settingsSubtitle') ?></p>
        </div>

        <!-- Sekcja kodu dostępu -->
        <div class="settings-section settings-container">
            <h3>🔐 <?php __('accessCode') ?></h3>

            <?php if ($current_code): ?>
                <div class="code-display">
                    <div style="font-size: 1rem; opacity: 0.9;"><?php __('actualAccessCode') ?>:</div>
                    <div class="code-value" id="access-code"><?php echo htmlspecialchars($current_code); ?></div>
                    <div style="font-size: 0.9rem; opacity: 0.8; margin-top: 0.5rem;">
                        <?php __('shareCode') ?>
                    </div>

                    <div class="code-actions">
                        <button onclick="copyCode()" class="copy-btn">
                            📋 <?php __('copyCode') ?>
                        </button>
                        <form method="post" style="display: inline;" onsubmit="return confirm(<?php __('regenerateCodeWarning') ?>);">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" name="regenerate_code" class="copy-btn">
                                🔄 <?php __('generateNewCode') ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="code-display">
                    <p><?php __('noAccessCode') ?></p>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" name="regenerate_code" class="copy-btn">
                            🔄 <?php __('generateNewCode') ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Formularz niestandardowego kodu -->
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="margin-bottom: 1rem; color: #666;"><?php __('orSetOwnCode') ?>:</p>
                <form method="post" class="custom-code-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="text"
                           name="custom_code"
                           class="custom-code-input"
                           placeholder="<?php __('exampleCode') ?>"
                           pattern="[A-Za-z0-9]{4,20}"
                           title="<?php __('codeInputTitle') ?>"
                           required>
                    <button type="submit" name="set_custom_code" class="Button1">
                        <?php __('setCode') ?>
                    </button>
                </form>
                <p style="font-size: 0.85rem; color: #999; margin-top: 0.5rem;">
                    <?php __('invalidCodeFormat') ?>
                </p>
            </div>
        </div>

        <!-- Pozostałe ustawienia -->
        <div class="settings-container">
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="settings-section">
                    <h3>🔐 <?php __('requireAccessCode') ?></h3>
                    <div class="setting-item">
                        <label class="switch">
                            <input type="checkbox" name="require_access_code"
                                <?php echo ($settings['require_access_code'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div class="setting-info">
                            <strong><?php __('requireAccessCodeDescription') ?></strong>
                            <p><?php __('requireAccessCodeDescriptionSubtitle') ?></p>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>📝 <?php __('userRegistration') ?></h3>
                    <div class="setting-item">
                        <label class="switch">
                            <input type="checkbox" name="registration_enabled"
                                <?php echo ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div class="setting-info">
                            <strong><?php __('userRegistrationDescription') ?></strong>
                            <p><?php __('userRegistrationDescriptionSubtitle') ?></p>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>🔓 <?php __('userLogin') ?></h3>
                    <div class="setting-item">
                        <label class="switch">
                            <input type="checkbox" name="login_enabled"
                                <?php echo ($settings['login_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div class="setting-info">
                            <strong><?php __('userLoginDescription') ?></strong>
                            <p><?php __('userLoginDescriptionSubtitle') ?></p>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>📸 <?php __('photoUploads') ?></h3>
                    <div class="setting-item">
                        <label class="switch">
                            <input type="checkbox" name="photo_upload_enabled"
                                <?php echo ($settings['photo_upload_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div class="setting-info">
                            <strong><?php __('photoUploadsDescription') ?></strong>
                            <p><?php __('photoUploadsDescriptionSubtitle') ?></p>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>⭐ <?php __('photoRatings') ?></h3>
                    <div class="setting-item">
                        <label class="switch">
                            <input type="checkbox" name="photo_rating_enabled"
                                <?php echo ($settings['photo_rating_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div class="setting-info">
                            <strong><?php __('photoRatingsDescription') ?></strong>
                            <p><?php __('photoRatingsDescriptionSubtitle') ?></p>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <button type="submit" name="save_settings" class="Button1" style="font-size: 1.1rem; padding: 1rem 2rem;">
                        <?php __('saveSettings') ?>
                    </button>
                </div>
            </form>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="panel.php" class="Button1"><?php __('backToAdminPanel') ?></a>
            </div>
        </div>
    </div>
</main>
<?php include '../elements/footer.php'; ?>
<script>
    function copyCode() {
        const code = document.getElementById('access-code').textContent;

        navigator.clipboard.writeText(code).then(() => {
            // Pokaż komunikat
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = <?php __('copiedToClipboard') ?>;
            btn.style.background = 'rgba(76, 175, 80, 0.3)';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = 'rgba(255,255,255,0.2)';
            }, 2000);
        }).catch(err => {
            alert(<?php __('copyError') ?>);
        });
    }
</script>
</body>
</html>