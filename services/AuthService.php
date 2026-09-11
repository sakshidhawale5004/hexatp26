<?php

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../models/User.php';

/**
 * AuthService
 * 
 * Authentication and authorization service.
 * Handles login, logout, session management, CSRF protection, and rate limiting.
 * 
 * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.10
 */
class AuthService {
    private UserRepository $userRepo;
    private mysqli $conn;
    
    // Session configuration
    const SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    const SESSION_NAME = 'hexatp_cms_session';
    
    // Rate limiting configuration
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 900; // 15 minutes in seconds
    
    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct(mysqli $connection) {
        $this->conn = $connection;
        $this->userRepo = new UserRepository($connection);
        
        // Configure session
        $this->configureSession();
    }
    
    /**
     * Configure PHP session settings
     * 
     * @return void
     */
    private function configureSession(): void {
        // Set session name
        session_name(self::SESSION_NAME);
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires when browser closes)
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    /**
     * Authenticate a user with username and password
     * 
     * @param string $username The username
     * @param string $password The password
     * @return array Result array with 'success', 'user', and optional 'error'
     */
    public function login(string $username, string $password): array {
        // Check rate limiting
        if ($this->isLockedOut($username)) {
            return [
                'success' => false,
                'error' => 'Too many failed login attempts. Please try again in 15 minutes.'
            ];
        }
        
        // Find user by username
        $user = $this->userRepo->findByUsername($username);
        
        if (!$user) {
            $this->recordFailedAttempt($username);
            return [
                'success' => false,
                'error' => 'Invalid username or password'
            ];
        }
        
        // Verify password
        if (!$user->verifyPassword($password)) {
            $this->recordFailedAttempt($username);
            return [
                'success' => false,
                'error' => 'Invalid username or password'
            ];
        }
        
        // Clear failed attempts
        $this->clearFailedAttempts($username);
        
        // Update last login timestamp
        $this->userRepo->updateLastLogin($user->id);
        
        // Create session
        $this->createSession($user);
        
        return [
            'success' => true,
            'user' => $user->toArray()
        ];
    }
    
    /**
     * Logout the current user
     * 
     * @return array Result array with 'success'
     */
    public function logout(): array {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Destroy session
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[self::SESSION_NAME])) {
            setcookie(self::SESSION_NAME, '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        return [
            'success' => true
        ];
    }
    
    /**
     * Check if the current session is valid
     * 
     * @return bool True if session is valid, false otherwise
     */
    public function checkSession(): bool {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            if ($elapsed > self::SESSION_TIMEOUT) {
                $this->logout();
                return false;
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Get the current logged-in user
     * 
     * @return User|null The current user, or null if not logged in
     */
    public function getCurrentUser(): ?User {
        if (!$this->checkSession()) {
            return null;
        }
        
        $user_id = $_SESSION['user_id'];
        return $this->userRepo->findById($user_id);
    }
    
    /**
     * Generate a CSRF token
     * 
     * @return string The CSRF token
     */
    public function generateCsrfToken(): string {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify a CSRF token
     * 
     * @param string $token The token to verify
     * @return bool True if valid, false otherwise
     */
    public function verifyCsrfToken(string $token): bool {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists in session
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Compare tokens using timing-safe comparison
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Check if a user has permission to perform an action
     * 
     * @param string $action The action to check (create, read, update, delete, publish)
     * @param string $resource The resource type (country, user, etc.)
     * @return bool True if the user has permission, false otherwise
     */
    public function hasPermission(string $action, string $resource): bool {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        return $user->hasPermission($action, $resource);
    }
    
    /**
     * Require authentication (redirect to login if not authenticated)
     * 
     * @param string $redirect_url Optional URL to redirect to after login
     * @return void
     */
    public function requireAuth(string $redirect_url = ''): void {
        if (!$this->checkSession()) {
            // Store redirect URL in session
            if (!empty($redirect_url)) {
                $_SESSION['redirect_after_login'] = $redirect_url;
            }
            
            // Redirect to login page
            header('Location: /admin/login.php');
            exit;
        }
    }
    
    /**
     * Require specific permission (return 403 if not authorized)
     * 
     * @param string $action The action to check
     * @param string $resource The resource type
     * @return void
     */
    public function requirePermission(string $action, string $resource): void {
        $this->requireAuth();
        
        if (!$this->hasPermission($action, $resource)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'You do not have permission to perform this action'
            ]);
            exit;
        }
    }
    
    /**
     * Create a session for a user
     * 
     * @param User $user The user to create a session for
     * @return void
     */
    private function createSession(User $user): void {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Store user information in session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Check if a username is locked out due to too many failed attempts
     * 
     * @param string $username The username to check
     * @return bool True if locked out, false otherwise
     */
    private function isLockedOut(string $username): bool {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'login_attempts_' . md5($username);
        
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        $attempts = $_SESSION[$key];
        
        // Check if locked out
        if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
            $elapsed = time() - $attempts['last_attempt'];
            if ($elapsed < self::LOCKOUT_DURATION) {
                return true;
            } else {
                // Lockout period expired, clear attempts
                unset($_SESSION[$key]);
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Record a failed login attempt
     * 
     * @param string $username The username that failed
     * @return void
     */
    private function recordFailedAttempt(string $username): void {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'login_attempts_' . md5($username);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'last_attempt' => 0
            ];
        }
        
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
    }
    
    /**
     * Clear failed login attempts for a username
     * 
     * @param string $username The username to clear attempts for
     * @return void
     */
    private function clearFailedAttempts(string $username): void {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'login_attempts_' . md5($username);
        unset($_SESSION[$key]);
    }
    
    /**
     * Get the number of remaining login attempts
     * 
     * @param string $username The username to check
     * @return int The number of remaining attempts
     */
    public function getRemainingAttempts(string $username): int {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'login_attempts_' . md5($username);
        
        if (!isset($_SESSION[$key])) {
            return self::MAX_LOGIN_ATTEMPTS;
        }
        
        $attempts = $_SESSION[$key];
        return max(0, self::MAX_LOGIN_ATTEMPTS - $attempts['count']);
    }
}
