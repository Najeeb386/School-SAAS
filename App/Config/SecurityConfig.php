<?php
/**
 * Security Configuration
 * Centralized security settings and utilities
 */

class SecurityConfig {
    
    // Session settings
    const SESSION_TIMEOUT = 1800; // 30 minutes
    const SESSION_SECURE = true; // HTTPS only (set to false for local development)
    const SESSION_HTTP_ONLY = true; // JavaScript cannot access cookies
    const SESSION_SAME_SITE = 'Lax'; // CSRF protection

    // Password settings
    const PASSWORD_MIN_LENGTH = 8;
    const PASSWORD_HASH_ALGO = PASSWORD_BCRYPT;
    const PASSWORD_HASH_COST = 12; // Higher = more secure but slower

    // Login attempt restrictions
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_ATTEMPT_WINDOW = 900; // 15 minutes
    const LOGIN_ATTEMPT_DELAY = [1, 2, 4, 8, 16]; // Progressive delays in seconds

    // CSRF protection
    const CSRF_TOKEN_LENGTH = 32;
    const CSRF_TOKEN_LIFETIME = 3600; // 1 hour

    // Rate limiting
    const RATE_LIMIT_REQUESTS = 100;
    const RATE_LIMIT_WINDOW = 3600; // 1 hour

    /**
     * Generate secure random token
     */
    public static function generateToken($length = self::CSRF_TOKEN_LENGTH) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate CSRF token and store in session
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken();
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        // Check if token exists
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Check if token matches
        if ($token !== $_SESSION['csrf_token']) {
            return false;
        }

        // Check if token is not expired
        if (time() - $_SESSION['csrf_token_time'] > self::CSRF_TOKEN_LIFETIME) {
            return false;
        }

        return true;
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, self::PASSWORD_HASH_ALGO, [
            'cost' => self::PASSWORD_HASH_COST
        ]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) {
        $errors = [];

        // Check length
        if (strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . self::PASSWORD_MIN_LENGTH . ' characters';
        }

        // Check for uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        // Check for lowercase
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        // Check for numbers
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }

    /**
     * Set secure session cookie
     */
    public static function setSecureSessionCookie() {
        $secure = self::SESSION_SECURE && !self::isLocalhost();
        
        session_set_cookie_params([
            'lifetime' => self::SESSION_TIMEOUT,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $secure,
            'httponly' => self::SESSION_HTTP_ONLY,
            'samesite' => self::SESSION_SAME_SITE
        ]);
    }

    /**
     * Check if we're on localhost
     */
    public static function isLocalhost() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent($type, $details = []) {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details
        ];

        $logFile = __DIR__ . '/../../Storage/logs/security.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        error_log(json_encode($log) . PHP_EOL, 3, $logFile);
    }

    /**
     * Sanitize user input
     */
    public static function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check rate limit
     */
    public static function checkRateLimit($identifier, $limit = self::RATE_LIMIT_REQUESTS, $window = self::RATE_LIMIT_WINDOW) {
        $key = 'rate_limit_' . $identifier;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'reset_time' => time() + $window
            ];
        }

        // Reset if window expired
        if (time() > $_SESSION[$key]['reset_time']) {
            $_SESSION[$key] = [
                'count' => 0,
                'reset_time' => time() + $window
            ];
        }

        // Increment count
        $_SESSION[$key]['count']++;

        return $_SESSION[$key]['count'] <= $limit;
    }
}
?>
