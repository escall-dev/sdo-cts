<?php
/**
 * Admin Authentication Helper
 * Handles session management and access control
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/admin_config.php';
require_once __DIR__ . '/../../models/AdminUser.php';
require_once __DIR__ . '/../../models/ActivityLog.php';

class AdminAuth {
    private static $instance = null;
    private $user = null;
    private $adminUserModel;
    private $activityLog;

    private function __construct() {
        $this->startSession();
        $this->adminUserModel = new AdminUser();
        $this->activityLog = new ActivityLog();
        $this->loadUser();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(ADMIN_SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => ADMIN_SESSION_LIFETIME,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    private function loadUser() {
        if (isset($_SESSION['admin_user_id'])) {
            $this->user = $this->adminUserModel->getById($_SESSION['admin_user_id']);
            if (!$this->user || !$this->user['is_active']) {
                $this->logout();
            }
        }
    }

    /**
     * Login with email and password
     */
    public function login($email, $password) {
        $user = $this->adminUserModel->authenticate($email, $password);
        
        if ($user) {
            $this->setSession($user);
            $this->activityLog->log($user['id'], 'login', 'auth', null, 'User logged in via email/password');
            return true;
        }
        
        return false;
    }

    /**
     * Login with Google OAuth
     * @param bool $autoRegister If true, automatically register new users
     */
    public function loginWithGoogle($googleId, $email, $name, $avatar = null, $autoRegister = false) {
        $user = $this->adminUserModel->authenticateGoogle($googleId, $email, $name, $avatar, $autoRegister);
        
        if ($user) {
            $this->setSession($user);
            // Check if this is a new registration or existing login
            $isNewUser = empty($user['last_login']) || $user['last_login'] === null;
            $logMessage = $isNewUser ? 'New user registered and logged in via Google OAuth' : 'User logged in via Google OAuth';
            $this->activityLog->log($user['id'], 'login', 'auth', null, $logMessage);
            return true;
        }
        
        return false;
    }

    /**
     * Set session data after successful login
     */
    private function setSession($user) {
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_user_email'] = $user['email'];
        $_SESSION['admin_user_name'] = $user['full_name'];
        $_SESSION['admin_user_role'] = $user['role_name'];
        $_SESSION['admin_login_time'] = time();
        $this->user = $user;
    }

    /**
     * Logout user
     */
    public function logout() {
        if ($this->user) {
            $this->activityLog->log($this->user['id'], 'logout', 'auth', null, 'User logged out');
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        $this->user = null;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->user !== null;
    }

    /**
     * Get current user
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Get current user ID
     */
    public function getUserId() {
        return $this->user ? $this->user['id'] : null;
    }

    /**
     * Get current user name
     */
    public function getUserName() {
        return $this->user ? $this->user['full_name'] : null;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission) {
        if (!$this->user) {
            return false;
        }
        return $this->adminUserModel->hasPermission($this->user, $permission);
    }

    /**
     * Check if current user is Super Admin
     */
    public function isSuperAdmin() {
        if (!$this->user) {
            return false;
        }
        return strtolower($this->user['role_name']) === 'super admin';
    }

    /**
     * Require login - redirect to login page if not authenticated
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /SDO-cts/admin/login.php');
            exit;
        }
    }

    /**
     * Require specific permission
     */
    public function requirePermission($permission) {
        $this->requireLogin();
        
        if (!$this->hasPermission($permission)) {
            header('HTTP/1.1 403 Forbidden');
            include __DIR__ . '/../403.php';
            exit;
        }
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Log activity
     */
    public function logActivity($actionType, $entityType, $entityId = null, $description = null, $oldValue = null, $newValue = null) {
        return $this->activityLog->log(
            $this->getUserId(),
            $actionType,
            $entityType,
            $entityId,
            $description,
            $oldValue,
            $newValue
        );
    }
}

// Helper function to get auth instance
function auth() {
    return AdminAuth::getInstance();
}

