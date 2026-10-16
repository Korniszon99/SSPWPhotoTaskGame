# SSPW Photo Task Game

**A browser-based photography challenge game with tasks, uploads, ratings and rankings.**

PHP 8.3 · PDO · SQLite · JavaScript · PWA

Participants register, draw photo tasks and upload their results. Administrators manage tasks, users and game settings; the gallery and ranking show activity and photo ratings. Related application repository: [PWGame](https://github.com/Korniszon99/PWGame).

This is an event application, not a computer vision pipeline: photos are submitted and rated by people, without an implemented recognition model.

## Local demo

Install PHP 8.3 with `pdo_sqlite`, `fileinfo` and the image extensions used by your upload workflow. From the repository root:

```bash
export ADMIN_USERNAME=admin
export ADMIN_PASSWORD="$(php -r 'echo bin2hex(random_bytes(24));')"
export ACCESS_CODE="$(php -r 'echo bin2hex(random_bytes(16));')"
php -S 127.0.0.1:8000 -t photo_game
```

Keep the generated password and access code in your local environment; they are not published by these commands. Open <http://127.0.0.1:8000/>. A fresh SQLite database uses the tracked `photo_game/database/schema.sql`. There is no default admin password or public registration code. Add tasks through the admin panel before playing.

The PHP built-in server is for local development. `.env.example` documents variables; this application reads the process environment and does **not** automatically load `.env` files.

## Verification

```bash
find photo_game tests -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/bootstrap.php
```

CI checks PHP syntax and a clean SQLite bootstrap, including absence of default privileged accounts. A lint pass does not verify the complete game flow.

## Deployment boundary

Use HTTPS and a configured Apache/PHP or equivalent hosting setup. Do not expose database files, configuration, logs or debug endpoints. Apache rules deny access to database/config directories and executable uploads; configure equivalent rules yourself on Nginx or other servers.

Set `APP_DEBUG=0`. Admin credentials are used only to create a missing account, never to reset an existing password on every request. Remove bootstrap variables after creating the account. Back up SQLite and uploads, enforce upload limits, and verify registration/login/upload/rating/admin flows in staging.

The database class contains support for external PDO connections, but schema creation and several SQL statements are SQLite-specific. **Only the SQLite bootstrap is covered by the new automated check; do not assume MySQL/PostgreSQL/SQL Server compatibility.** Existing deployed default accounts need password rotation separately. [Security notes](SECURITY.md).
