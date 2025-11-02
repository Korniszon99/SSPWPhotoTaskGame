<?php
/**
 * Prosty rate limiter oparty o sesję
 */
class RateLimiter {
    private $limits = [
        'draw_task' => ['max' => 5, 'window' => 60], // 5 losowań na minutę
        'upload_photo' => ['max' => 5, 'window' => 60], // 5 uploadów na minutę
        'login' => ['max' => 5, 'window' => 300], // 5 prób logowania na 5 minut
    ];

    /**
     * Sprawdza czy akcja jest dozwolona
     */
    public function check($action) {
        if (!isset($this->limits[$action])) {
            return true; // Brak limitu dla tej akcji
        }

        $limit = $this->limits[$action];
        $key = "rate_limit_$action";

        // Inicjalizuj tracking dla akcji
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'first_attempt' => time()
            ];
        }

        $data = $_SESSION[$key];
        $elapsed = time() - $data['first_attempt'];

        // Jeśli minęło okno czasowe - resetuj licznik
        if ($elapsed > $limit['window']) {
            $_SESSION[$key] = [
                'count' => 1,
                'first_attempt' => time()
            ];
            return true;
        }

        // Sprawdź limit
        if ($data['count'] >= $limit['max']) {
            $remaining = $limit['window'] - $elapsed;
            return [
                'allowed' => false,
                'retry_after' => $remaining
            ];
        }

        // Zwiększ licznik
        $_SESSION[$key]['count']++;
        return true;
    }

    /**
     * Resetuje licznik dla akcji (np. po udanym logowaniu)
     */
    public function reset($action) {
        $key = "rate_limit_$action";
        unset($_SESSION[$key]);
    }
}