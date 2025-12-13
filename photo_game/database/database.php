<?php
/**
 * Centralizuje wszystkie operacje bazodanowe w jednym miejscu
 */
class Database {
    private $pdo;
    private static $instance = null;

    /**
     * Singleton pattern - jedna instancja połączenia w całej aplikacji
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Konstruktor - nawiązuje połączenie i konfiguruje SQLite
     */
    private function __construct() {
        try {
            $db_path = __DIR__ . '/../../../../futopack/database/database.sqlite';
            $this->pdo = new PDO('sqlite:' . $db_path);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Optymalizacja wydajności SQLite
            $this->pdo->exec('PRAGMA journal_mode = WAL');
            $this->pdo->exec('PRAGMA synchronous = NORMAL');
            $this->pdo->exec('PRAGMA cache_size = 10000');
            $this->pdo->exec('PRAGMA temp_store = MEMORY');
            $this->pdo->exec('PRAGMA foreign_keys = ON');

        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Nie można połączyć się z bazą danych");
        }
    }

    /**
     * Zapobiega klonowaniu instancji
     */
    private function __clone() {}

    /**
     * Zapobiega deserializacji instancji
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    // ==================== USERS ====================

    /**
     * Wyszukuje użytkowników po ID lub nazwie
     */
    public function searchUsers($query) {
        // Jeśli query to liczba, szukaj po ID
        if (is_numeric($query)) {
            $stmt = $this->pdo->prepare("
            SELECT 
                users.id,
                users.username,
                users.is_admin,
                COUNT(DISTINCT user_tasks.id) as tasks_assigned,
                COUNT(DISTINCT completed_tasks.id) as tasks_completed
            FROM users
            LEFT JOIN user_tasks ON users.id = user_tasks.user_id
            LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id
            WHERE users.id = ?
            GROUP BY users.id, users.username, users.is_admin
            ORDER BY users.id ASC
        ");
            $stmt->execute([$query]);
        } else {
            // Szukaj po nazwie użytkownika (LIKE)
            $stmt = $this->pdo->prepare("
            SELECT 
                users.id,
                users.username,
                users.is_admin,
                COUNT(DISTINCT user_tasks.id) as tasks_assigned,
                COUNT(DISTINCT completed_tasks.id) as tasks_completed
            FROM users
            LEFT JOIN user_tasks ON users.id = user_tasks.user_id
            LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id
            WHERE users.username LIKE ?
            GROUP BY users.id, users.username, users.is_admin
            ORDER BY users.id ASC
        ");
            $stmt->execute(['%' . $query . '%']);
        }

        return $stmt->fetchAll();
    }

    /**
     * Sprawdza czy użytkownik jest administratorem
     */
    public function isUserAdmin($user_id) {
        $stmt = $this->pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result && $result['is_admin'] == 1;
    }

    /**
     * Zmienia hasło użytkownika (dla admina)
     */
    public function changeUserPassword($user_id, $new_password_hash) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$new_password_hash, $user_id]);
    }

    /**
     * Zmienia hasło użytkownika (dla samego użytkownika - z weryfikacją starego)
     */
    public function changeOwnPassword($user_id, $old_password, $new_password_hash) {
        // Pobierz obecne hasło
        $user = $this->getUserById($user_id);
        if (!$user) {
            throw new Exception('Użytkownik nie znaleziony');
        }

        // Zweryfikuj stare hasło
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();

        if (!password_verify($old_password, $result['password'])) {
            throw new Exception('Nieprawidłowe obecne hasło');
        }

        // Zmień hasło
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$new_password_hash, $user_id]);
    }

    /**
     * Zapisuje informację o ostatnim logowaniu
     */
    public function updateLastLogin($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$user_id]);
    }

    /**
     * Pobiera wszystkich użytkowników (dla panelu admina)
     */
    public function getAllUsers() {
        $stmt = $this->pdo->query("
        SELECT 
            users.id,
            users.username,
            users.is_admin,
            COUNT(DISTINCT user_tasks.id) as tasks_assigned,
            COUNT(DISTINCT completed_tasks.id) as tasks_completed
        FROM users
        LEFT JOIN user_tasks ON users.id = user_tasks.user_id
        LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id
        GROUP BY users.id, users.username, users.is_admin
        ORDER BY users.id ASC
    ");
        return $stmt->fetchAll();
    }

    /**
     * Zmienia nazwę użytkownika
     */
    public function updateUsername($user_id, $new_username) {
        // Sprawdź czy nowa nazwa już istnieje
        if ($this->userExists($new_username)) {
            $current = $this->getUserById($user_id);
            if ($current['username'] !== $new_username) {
                throw new Exception('Użytkownik o tej nazwie już istnieje');
            }
        }

        $stmt = $this->pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        return $stmt->execute([$new_username, $user_id]);
    }

    /**
     * Usuwa użytkownika i wszystkie jego dane
     */
    public function deleteUser($user_id) {
        try {
            $this->beginTransaction();

            // Pobierz wszystkie zdjęcia użytkownika do usunięcia
            $stmt = $this->pdo->prepare("
            SELECT photo FROM completed_tasks WHERE user_id = ?
        ");
            $stmt->execute([$user_id]);
            $photos = $stmt->fetchAll();

            // Usuń rekordy z completed_tasks
            $stmt = $this->pdo->prepare("DELETE FROM completed_tasks WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Usuń rekordy z user_tasks
            $stmt = $this->pdo->prepare("DELETE FROM user_tasks WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Usuń użytkownika
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            $this->commit();

            // Usuń pliki zdjęć
            foreach ($photos as $photo) {
                $file_path = __DIR__ . '/uploads/' . $photo['photo'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Usuwa pojedyncze zdjęcie
     */
    public function deletePhoto($photo_id) {
        try {
            $this->beginTransaction();

            // Pobierz nazwę pliku
            $stmt = $this->pdo->prepare("SELECT photo FROM completed_tasks WHERE id = ?");
            $stmt->execute([$photo_id]);
            $photo = $stmt->fetch();

            if (!$photo) {
                throw new Exception('Zdjęcie nie znalezione');
            }

            // Usuń z bazy
            $stmt = $this->pdo->prepare("DELETE FROM completed_tasks WHERE id = ?");
            $stmt->execute([$photo_id]);

            $this->commit();

            // Usuń plik
            $file_path = __DIR__ . '/uploads/' . $photo['photo'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Pobiera wszystkie zdjęcia użytkownika
     */
    public function getUserPhotos($user_id) {
        $stmt = $this->pdo->prepare("
        SELECT 
            completed_tasks.id,
            completed_tasks.photo,
            completed_tasks.uploaded_at,
            tasks.description
        FROM completed_tasks
        JOIN user_tasks ON completed_tasks.user_task_id = user_tasks.id
        JOIN tasks ON user_tasks.task_id = tasks.id
        WHERE completed_tasks.user_id = ?
        ORDER BY completed_tasks.uploaded_at DESC
    ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    /**
     * Zmienia status administratora użytkownika
     */
    public function toggleAdminStatus($user_id) {
        $stmt = $this->pdo->prepare("
        UPDATE users 
        SET is_admin = CASE WHEN is_admin = 1 THEN 0 ELSE 1 END 
        WHERE id = ?
    ");
        return $stmt->execute([$user_id]);
    }

    /**
     * Pobiera użytkownika po nazwie
     */
    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /**
     * Pobiera użytkownika po ID
     */
    public function getUserById($user_id) {
        $stmt = $this->pdo->prepare("SELECT id, username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    /**
     * Sprawdza czy użytkownik o danej nazwie istnieje
     */
    public function userExists($username) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Tworzy nowego użytkownika
     */
    public function createUser($username, $password_hash) {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password_hash]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Pobiera top N użytkowników z największą liczbą wykonanych zadań
     */
    public function getTopUsers($limit = 5) {
        $stmt = $this->pdo->prepare("
    SELECT 
        users.username,
        users.id,
        COUNT(completed_tasks.id) AS completed_count
    FROM users
    LEFT JOIN user_tasks ON users.id = user_tasks.user_id
    LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id
    GROUP BY users.id, users.username
    ORDER BY completed_count DESC, users.username ASC
    LIMIT ?
");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Pobiera pozycję użytkownika w rankingu (jego numer miejsca)
     */
    public function getUserRankPosition($user_id) {
        $stmt = $this->pdo->prepare("
        WITH ranked_users AS (
            SELECT 
                users.id,
                users.username,
                COUNT(completed_tasks.id) AS completed_count,
                ROW_NUMBER() OVER (ORDER BY COUNT(completed_tasks.id) DESC, users.username ASC) as position
            FROM users
            LEFT JOIN user_tasks ON users.id = user_tasks.user_id
            LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id
            GROUP BY users.id, users.username
        )
        SELECT position
        FROM ranked_users
        WHERE id = ?
    ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result ? $result['position'] : null;
    }

    /**
     * Pobiera dane użytkownika wraz z liczbą wykonanych zadań
     */
    public function getUserWithCompletedCount($user_id) {
        $stmt = $this->pdo->prepare("
        SELECT 
            users.username,
            COUNT(completed_tasks.id) AS completed_count
        FROM users
        LEFT JOIN user_tasks ON users.id = user_tasks.user_id
        LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id
        WHERE users.id = ?
        GROUP BY users.id, users.username
    ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    /**
     * Pobiera łączną liczbę użytkowników
     */
    public function getTotalUsersCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        return $result['count'];
    }

    // ==================== TASKS ====================

    /**
     * Pobiera wszystkie dostępne zadania
     */
    public function getAllTasks() {
        $stmt = $this->pdo->query("SELECT * FROM tasks");
        return $stmt->fetchAll();
    }

    /**
     * Pobiera zadanie po ID
     */
    public function getTaskById($task_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        return $stmt->fetch();
    }

    /**
     * Pobiera liczbę wszystkich zadań
     */
    public function getTasksCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM tasks");
        $result = $stmt->fetch();
        return $result['count'];
    }

    // ==================== TASKS (rozszerzone dla admina) ====================

    /**
     * Tworzy nowe zadanie
     */
    public function createTask($description) {
        $stmt = $this->pdo->prepare("INSERT INTO tasks (description) VALUES (?)");
        $stmt->execute([$description]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Aktualizuje opis zadania
     */
    public function updateTask($task_id, $description) {
        $stmt = $this->pdo->prepare("UPDATE tasks SET description = ? WHERE id = ?");
        return $stmt->execute([$description, $task_id]);
    }

    /**
     * Usuwa zadanie i związane z nim dane
     * Zwraca informacje o usuniętych danych
     */
    public function deleteTask($task_id) {
        try {
            $this->beginTransaction();

            // Sprawdź ile użytkowników ma to zadanie przypisane
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM user_tasks WHERE task_id = ?");
            $stmt->execute([$task_id]);
            $assigned_count = $stmt->fetch()['count'];

            // Sprawdź ile razy zadanie zostało wykonane
            $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM completed_tasks 
            WHERE user_task_id IN (SELECT id FROM user_tasks WHERE task_id = ?)
        ");
            $stmt->execute([$task_id]);
            $completed_count = $stmt->fetch()['count'];

            // Pobierz wszystkie zdjęcia związane z tym zadaniem
            $stmt = $this->pdo->prepare("
            SELECT photo FROM completed_tasks 
            WHERE user_task_id IN (SELECT id FROM user_tasks WHERE task_id = ?)
        ");
            $stmt->execute([$task_id]);
            $photos = $stmt->fetchAll();

            // Usuń completed_tasks
            $stmt = $this->pdo->prepare("
            DELETE FROM completed_tasks 
            WHERE user_task_id IN (SELECT id FROM user_tasks WHERE task_id = ?)
        ");
            $stmt->execute([$task_id]);

            // Usuń user_tasks
            $stmt = $this->pdo->prepare("DELETE FROM user_tasks WHERE task_id = ?");
            $stmt->execute([$task_id]);

            // Usuń zadanie
            $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$task_id]);

            $this->commit();

            // Usuń pliki zdjęć
            foreach ($photos as $photo) {
                $file_path = __DIR__ . '/uploads/' . $photo['photo'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            return [
                'success' => true,
                'assigned_count' => $assigned_count,
                'completed_count' => $completed_count,
                'photos_deleted' => count($photos)
            ];

        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Pobiera szczegółowe statystyki zadania
     */
    public function getTaskStats($task_id) {
        $stmt = $this->pdo->prepare("
        SELECT 
            t.id,
            t.description,
            COUNT(DISTINCT ut.id) as times_assigned,
            COUNT(DISTINCT ct.id) as times_completed
        FROM tasks t
        LEFT JOIN user_tasks ut ON t.id = ut.task_id
        LEFT JOIN completed_tasks ct ON ut.id = ct.user_task_id
        WHERE t.id = ?
        GROUP BY t.id, t.description
    ");
        $stmt->execute([$task_id]);
        return $stmt->fetch();
    }

    /**
     * Pobiera wszystkie zadania ze statystykami
     */
    public function getAllTasksWithStats() {
        $stmt = $this->pdo->query("
        SELECT 
            t.id,
            t.description,
            COUNT(DISTINCT ut.id) as times_assigned,
            COUNT(DISTINCT ct.id) as times_completed
        FROM tasks t
        LEFT JOIN user_tasks ut ON t.id = ut.task_id
        LEFT JOIN completed_tasks ct ON ut.id = ct.user_task_id
        GROUP BY t.id, t.description
        ORDER BY t.id ASC
    ");
        return $stmt->fetchAll();
    }

    // ==================== USER TASKS ====================

    /**
     * Przypisuje zadanie do użytkownika
     */
    public function assignTaskToUser($user_id, $task_id) {
        $stmt = $this->pdo->prepare("INSERT INTO user_tasks (user_id, task_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $task_id]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Pobiera liczbę niezrealizowanych zadań użytkownika
     */
    public function getIncompleteTasksCount($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM user_tasks 
            LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id 
            WHERE user_tasks.user_id = ? AND completed_tasks.id IS NULL
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Pobiera wszystkie zadania użytkownika (zrealizowane i niezrealizowane)
     */
    public function getUserTasks($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                user_tasks.id AS user_task_id, 
                tasks.description, 
                completed_tasks.photo, 
                completed_tasks.id AS photo_id, 
                user_tasks.assigned_at 
            FROM user_tasks 
            JOIN tasks ON user_tasks.task_id = tasks.id 
            LEFT JOIN completed_tasks ON user_tasks.id = completed_tasks.user_task_id 
            WHERE user_tasks.user_id = ? 
            ORDER BY user_tasks.assigned_at ASC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    /**
     * Pobiera niezrealizowane zadania użytkownika, które może jeszcze otrzymać
     * (tj. których jeszcze nie ma przypisanych)
     */
    public function getAvailableTasksForUser($user_id)
    {
        $stmt = $this->pdo->prepare("
        SELECT *
        FROM tasks t
        WHERE t.id NOT IN (
            SELECT ut.task_id
            FROM user_tasks ut
            LEFT JOIN completed_tasks ct ON ct.user_task_id = ut.id
            WHERE ut.user_id = ? AND ct.user_task_id IS NULL
        )
    ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    /**
     * Pobiera niezrealizowane zadania użytkownika
     */
    public function getIncompleteUserTasks($user_id)
    {
        $stmt = $this->pdo->prepare("
        SELECT t.*
        FROM user_tasks ut
        JOIN tasks t ON ut.task_id = t.id
        LEFT JOIN completed_tasks ct ON ut.id = ct.user_task_id
        WHERE ut.user_id = ? AND ct.id IS NULL
    ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Pobiera ostatnio przypisane zadanie użytkownika
     */
    public function getLatestUserTask($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT tasks.description 
            FROM user_tasks 
            JOIN tasks ON user_tasks.task_id = tasks.id 
            WHERE user_tasks.user_id = ? 
            ORDER BY user_tasks.id DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }

    /**
     * Sprawdza czy zadanie należy do użytkownika
     */
    public function userTaskBelongsToUser($user_task_id, $user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM user_tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$user_task_id, $user_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    // ==================== COMPLETED TASKS ====================

    /**
     * Zapisuje wykonane zadanie z zdjęciem
     */
    public function completeTask($user_id, $user_task_id, $photo_filename) {
        $stmt = $this->pdo->prepare("
            INSERT INTO completed_tasks (user_id, user_task_id, photo) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $user_task_id, $photo_filename]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Pobiera łączną liczbę wykonanych zadań (dla paginacji galerii)
     */
    public function getCompletedTasksCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) AS total FROM completed_tasks");
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Pobiera wykonane zadania z paginacją (dla galerii)
     */
    public function getCompletedTasksPaginated($limit, $offset) {
        $stmt = $this->pdo->prepare("
            SELECT 
                completed_tasks.id, 
                completed_tasks.photo,
                completed_tasks.uploaded_at,
                tasks.description, 
                users.username
            FROM completed_tasks 
            JOIN user_tasks ON completed_tasks.user_task_id = user_tasks.id 
            JOIN tasks ON user_tasks.task_id = tasks.id 
            JOIN users ON user_tasks.user_id = users.id 
            ORDER BY completed_tasks.uploaded_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Pobiera szczegóły pojedynczego wykonanego zadania (dla widoku zdjęcia)
     */
    public function getCompletedTaskById($photo_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                completed_tasks.photo, 
                completed_tasks.uploaded_at, 
                tasks.description, 
                users.username 
            FROM completed_tasks 
            JOIN user_tasks ON completed_tasks.user_task_id = user_tasks.id 
            JOIN tasks ON user_tasks.task_id = tasks.id 
            JOIN users ON user_tasks.user_id = users.id 
            WHERE completed_tasks.id = ?
        ");
        $stmt->execute([$photo_id]);
        return $stmt->fetch();
    }

    /**
     * Sprawdza czy zadanie zostało już wykonane
     */
    public function isTaskCompleted($user_task_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM completed_tasks WHERE user_task_id = ?");
        $stmt->execute([$user_task_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    // ==================== UTILITY ====================

    /**
     * Rozpoczyna transakcję
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Zatwierdza transakcję
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Cofa transakcję
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    /**
     * Wykonuje dowolne zapytanie (dla zaawansowanych operacji)
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Optymalizuje bazę danych (VACUUM) - uruchamiaj po wyczyszczeniu danych
     */
    public function vacuum() {
        $this->pdo->exec('VACUUM');
    }

    /**
     * Czyści dane po evencie (zachowuje strukturę i zadania)
     */
    public function cleanupAfterEvent($keep_users = false) {
        try {
            $this->beginTransaction();

            $this->pdo->exec("DELETE FROM completed_tasks");
            $this->pdo->exec("DELETE FROM user_tasks");

            if (!$keep_users) {
                // Usuń wszystkich użytkowników oprócz admina (ID 1)
                $this->pdo->exec("DELETE FROM users WHERE id > 1");
            }

            $this->commit();
            $this->vacuum(); // Optymalizuj po czyszczeniu

            return true;
        } catch (PDOException $e) {
            $this->rollBack();
            error_log("Cleanup error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tworzy backup bazy danych
     */
    public function backup($backup_path) {
        try {
            // Flush WAL przed backupem
            $this->pdo->exec('PRAGMA wal_checkpoint(FULL)');

            $db_path = __DIR__ . '/database.db';
            return copy($db_path, $backup_path);
        } catch (Exception $e) {
            error_log("Backup error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pobiera statystyki bazy danych
     */
    public function getStats() {
        $stats = [];

        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['users'] = $stmt->fetch()['count'];

        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM tasks");
        $stats['tasks'] = $stmt->fetch()['count'];

        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM user_tasks");
        $stats['assigned_tasks'] = $stmt->fetch()['count'];

        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM completed_tasks");
        $stats['completed_tasks'] = $stmt->fetch()['count'];

        // Rozmiar bazy danych
        $db_path = __DIR__ . '/database.db';
        $stats['db_size_mb'] = file_exists($db_path) ? round(filesize($db_path) / 1024 / 1024, 2) : 0;

        return $stats;
    }

    // ==================== PHOTO RATINGS ====================

    /**
     * Dodaje ocenę zdjęcia
     */
    public function ratePhoto($photo_id, $user_id, $rating) {
        // Sprawdź czy użytkownik już ocenił to zdjęcie
        if ($this->hasUserRatedPhoto($photo_id, $user_id)) {
            throw new Exception('Już oceniłeś to zdjęcie');
        }

        // Sprawdź czy zdjęcie istnieje
        $stmt = $this->pdo->prepare("SELECT id FROM completed_tasks WHERE id = ?");
        $stmt->execute([$photo_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Zdjęcie nie istnieje');
        }

        $stmt = $this->pdo->prepare("
        INSERT INTO photo_ratings (photo_id, user_id, rating) 
        VALUES (?, ?, ?)
    ");
        return $stmt->execute([$photo_id, $user_id, $rating]);
    }

    /**
     * Sprawdza czy użytkownik ocenił już dane zdjęcie
     */
    public function hasUserRatedPhoto($photo_id, $user_id) {
        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) as count 
        FROM photo_ratings 
        WHERE photo_id = ? AND user_id = ?
    ");
        $stmt->execute([$photo_id, $user_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Pobiera średnią ocenę zdjęcia i liczbę ocen
     */
    public function getPhotoRating($photo_id) {
        $stmt = $this->pdo->prepare("
        SELECT 
            COALESCE(AVG(rating), 0) as avg_rating,
            COUNT(*) as rating_count
        FROM photo_ratings 
        WHERE photo_id = ?
    ");
        $stmt->execute([$photo_id]);
        return $stmt->fetch();
    }

    /**
     * Pobiera ocenę użytkownika dla danego zdjęcia
     */
    public function getUserPhotoRating($photo_id, $user_id) {
        $stmt = $this->pdo->prepare("
        SELECT rating 
        FROM photo_ratings 
        WHERE photo_id = ? AND user_id = ?
    ");
        $stmt->execute([$photo_id, $user_id]);
        $result = $stmt->fetch();
        return $result ? $result['rating'] : null;
    }

    /**
     * Pobiera najlepiej oceniane zdjęcia
     */
    public function getTopRatedPhotos($limit = 10) {
        $stmt = $this->pdo->prepare("
        SELECT 
            ct.id,
            ct.photo,
            ct.uploaded_at,
            t.description,
            u.username,
            COALESCE(AVG(pr.rating), 0) as avg_rating,
            COUNT(pr.id) as rating_count
        FROM completed_tasks ct
        JOIN user_tasks ut ON ct.user_task_id = ut.id
        JOIN tasks t ON ut.task_id = t.id
        JOIN users u ON ct.user_id = u.id
        LEFT JOIN photo_ratings pr ON ct.id = pr.photo_id
        GROUP BY ct.id, ct.photo, ct.uploaded_at, t.description, u.username
        HAVING rating_count > 0
        ORDER BY avg_rating DESC, rating_count DESC
        LIMIT ?
    ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Pobiera zdjęcia z oceną dla galerii (z filtrowaniem)
     */
    public function getCompletedTasksWithRatings($limit, $offset, $filters = []) {
        $where = [];
        $params = [];

        // Filtr po użytkowniku
        if (!empty($filters['user_id'])) {
            $where[] = "ct.user_id = ?";
            $params[] = $filters['user_id'];
        }

        // Filtr po dacie
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(ct.uploaded_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(ct.uploaded_at) <= ?";
            $params[] = $filters['date_to'];
        }

        // Filtr po zadaniu
        if (!empty($filters['task_id'])) {
            $where[] = "ut.task_id = ?";
            $params[] = $filters['task_id'];
        }

        // Filtr po minimalnej ocenie
        $havingClause = '';
        if (!empty($filters['min_rating'])) {
            $havingClause = "HAVING avg_rating >= " . floatval($filters['min_rating']);
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Sortowanie
        $orderBy = "ct.uploaded_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'rating_desc':
                    $orderBy = "avg_rating DESC, rating_count DESC, ct.uploaded_at DESC";
                    break;
                case 'rating_asc':
                    $orderBy = "avg_rating ASC, ct.uploaded_at DESC";
                    break;
                case 'date_asc':
                    $orderBy = "ct.uploaded_at ASC";
                    break;
                default:
                    $orderBy = "ct.uploaded_at DESC";
            }
        }

        $sql = "
            SELECT 
                ct.id,
                ct.photo,
                ct.uploaded_at,
                t.description,
                u.username,
                COALESCE(AVG(pr.rating), 0) as avg_rating,
                COUNT(pr.id) as rating_count
            FROM completed_tasks ct
            JOIN user_tasks ut ON ct.user_task_id = ut.id
            JOIN tasks t ON ut.task_id = t.id
            JOIN users u ON ct.user_id = u.id
            LEFT JOIN photo_ratings pr ON ct.id = pr.photo_id
            $whereClause
            GROUP BY ct.id, ct.photo, ct.uploaded_at, t.description, u.username
            $havingClause
            ORDER BY $orderBy
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Pobiera liczbę zdjęć z filtrowaniem
     */
    public function getCompletedTasksCountWithFilters($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = "ct.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(ct.uploaded_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(ct.uploaded_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['task_id'])) {
            $where[] = "ut.task_id = ?";
            $params[] = $filters['task_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Filtr po ocenie wymaga subquery
        $havingClause = '';
        if (!empty($filters['min_rating'])) {
            $havingClause = "HAVING avg_rating >= " . floatval($filters['min_rating']);
        }

        $sql = "
        SELECT COUNT(*) as total FROM (
            SELECT 
                ct.id,
                COALESCE(AVG(pr.rating), 0) as avg_rating
            FROM completed_tasks ct
            JOIN user_tasks ut ON ct.user_task_id = ut.id
            LEFT JOIN photo_ratings pr ON ct.id = pr.photo_id
            $whereClause
            GROUP BY ct.id
            $havingClause
        ) as filtered
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }

    // ==================== APP SETTINGS ====================

    /**
     * Pobiera wartość ustawienia
     */
    public function getSetting($key, $default = null) {
        $stmt = $this->pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    }

    /**
     * Ustawia wartość ustawienia
     */
    public function setSetting($key, $value) {
        $stmt = $this->pdo->prepare("
        INSERT OR REPLACE INTO app_settings (setting_key, setting_value, updated_at)
        VALUES (?, ?, CURRENT_TIMESTAMP)
    ");
        return $stmt->execute([$key, $value]);
    }

    /**
     * Pobiera wszystkie ustawienia
     */
    public function getAllSettings() {
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM app_settings");
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Sprawdza czy rejestracja jest włączona
     */
    public function isRegistrationEnabled() {
        return $this->getSetting('registration_enabled', '1') === '1';
    }

    /**
     * Sprawdza czy upload zdjęć jest włączony
     */
    public function isPhotoUploadEnabled() {
        return $this->getSetting('photo_upload_enabled', '1') === '1';
    }

    /**
     * Sprawdza czy ocenianie zdjęć jest włączone
     */
    public function isPhotoRatingEnabled() {
        return $this->getSetting('photo_rating_enabled', '1') === '1';
    }

    /**
     * Zamyka połączenie (opcjonalne, PDO robi to automatycznie)
     */
    public function close() {
        $this->pdo = null;
    }

    /**
     * Sprawdza czy logowanie jest włączone
     */
    public function isLoginEnabled() {
        return $this->getSetting('login_enabled', '1') === '1';
    }

    // ==================== ACCESS CODES ====================

    /**
     * Pobiera aktywny kod dostępu
     */
    public function getActiveAccessCode() {
        $stmt = $this->pdo->prepare("SELECT code FROM access_codes WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['code'] : null;
    }

    /**
     * Weryfikuje kod dostępu
     */
    public function verifyAccessCode($code) {
        if (empty($code)) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM access_codes WHERE code = ? AND is_active = 1");
        $stmt->execute([$code]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Generuje nowy kod dostępu i dezaktywuje stare
     */
    public function regenerateAccessCode() {
        try {
            $this->beginTransaction();

            // Dezaktywuj wszystkie stare kody
            $this->pdo->exec("UPDATE access_codes SET is_active = 0");

            // Wygeneruj nowy kod (8 znaków: litery i cyfry)
            $new_code = $this->generateRandomCode(8);

            // Dodaj nowy kod
            $stmt = $this->pdo->prepare("INSERT INTO access_codes (code, is_active) VALUES (?, 1)");
            $stmt->execute([$new_code]);

            $this->commit();
            return $new_code;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Ustawia niestandardowy kod dostępu
     */
    public function setCustomAccessCode($code) {
        // Walidacja kodu (min 4 znaki, max 20, tylko alfanumeryczne)
        if (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $code)) {
            throw new Exception('Kod musi zawierać 4-20 znaków (tylko litery i cyfry)');
        }

        try {
            $this->beginTransaction();

            // Dezaktywuj wszystkie stare kody
            $this->pdo->exec("UPDATE access_codes SET is_active = 0");

            // Dodaj nowy kod
            $stmt = $this->pdo->prepare("INSERT INTO access_codes (code, is_active) VALUES (?, 1)");
            $stmt->execute([$code]);

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Generuje losowy kod dostępu
     */
    private function generateRandomCode($length = 8) {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Bez 0,O,1,I dla czytelności
        $code = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $max)];
        }

        return $code;
    }

    /**
     * Sprawdza czy wymagany jest kod dostępu
     */
    public function isAccessCodeRequired() {
        return $this->getSetting('require_access_code', '1') === '1';
    }
}
