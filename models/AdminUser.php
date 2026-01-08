<?php
/**
 * AdminUser Model
 * Handles admin user authentication and management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class AdminUser {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Authenticate user with email and password
     */
    public function authenticate($email, $password) {
        $sql = "SELECT au.*, ar.name as role_name, ar.permissions as role_permissions
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.email = ? AND au.is_active = 1";
        
        $user = $this->db->query($sql, [$email])->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }

    /**
     * Authenticate or create user via Google OAuth
     * @param bool $autoRegister If true, automatically create new users
     */
    public function authenticateGoogle($googleId, $email, $name, $avatar = null, $autoRegister = false) {
        // Check if user exists by Google ID
        $sql = "SELECT au.*, ar.name as role_name, ar.permissions as role_permissions
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.google_id = ? AND au.is_active = 1";
        
        $user = $this->db->query($sql, [$googleId])->fetch();
        
        if ($user) {
            $this->updateLastLogin($user['id']);
            return $user;
        }

        // Check if user exists by email
        $sql = "SELECT au.*, ar.name as role_name, ar.permissions as role_permissions
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.email = ? AND au.is_active = 1";
        
        $user = $this->db->query($sql, [$email])->fetch();
        
        if ($user) {
            // Link Google account to existing user
            $this->db->query("UPDATE admin_users SET google_id = ?, avatar_url = ? WHERE id = ?", 
                [$googleId, $avatar, $user['id']]);
            $this->updateLastLogin($user['id']);
            $user['google_id'] = $googleId;
            $user['avatar_url'] = $avatar;
            return $user;
        }

        // User not found - auto-register if enabled
        if ($autoRegister) {
            return $this->registerGoogleUser($googleId, $email, $name, $avatar);
        }

        return false;
    }

    /**
     * Register a new user via Google OAuth
     */
    private function registerGoogleUser($googleId, $email, $name, $avatar) {
        // Default role for new Google users (Staff role = id 3, or you can change to 4 for Viewer)
        $defaultRoleId = 3; // Staff role
        
        // Insert new user
        $sql = "INSERT INTO admin_users (email, full_name, role_id, google_id, avatar_url, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW())";
        
        $this->db->query($sql, [$email, $name, $defaultRoleId, $googleId, $avatar]);
        $userId = $this->db->lastInsertId();
        
        if ($userId) {
            $this->updateLastLogin($userId);
            
            // Fetch and return the complete user record
            return $this->getById($userId);
        }
        
        return false;
    }

    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $sql = "SELECT au.*, ar.name as role_name, ar.permissions as role_permissions
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Get all admin users
     */
    public function getAll($filters = []) {
        $sql = "SELECT au.*, ar.name as role_name,
                       creator.full_name as created_by_name
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                LEFT JOIN admin_users creator ON au.created_by = creator.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['role_id'])) {
            $sql .= " AND au.role_id = ?";
            $params[] = $filters['role_id'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND au.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND au.unit = ?";
            $params[] = $filters['unit'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (au.full_name LIKE ? OR au.email LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY au.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Create new admin user
     */
    public function create($data, $createdBy = null) {
        $passwordHash = !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null;
        
        $sql = "INSERT INTO admin_users (email, password_hash, full_name, role_id, unit, is_active, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['email'],
            $passwordHash,
            $data['full_name'],
            $data['role_id'],
            $data['unit'] ?? null,
            $data['is_active'] ?? 1,
            $createdBy
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update admin user
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }

        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = $data['full_name'];
        }

        if (isset($data['role_id'])) {
            $fields[] = "role_id = ?";
            $params[] = $data['role_id'];
        }

        if (isset($data['unit'])) {
            $fields[] = "unit = ?";
            $params[] = $data['unit'];
        }

        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $params[] = $data['is_active'];
        }

        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (array_key_exists('avatar_url', $data)) {
            $fields[] = "avatar_url = ?";
            $params[] = $data['avatar_url'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE admin_users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }

    /**
     * Deactivate admin user
     */
    public function deactivate($id) {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Activate admin user
     */
    public function activate($id) {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Delete admin user
     */
    public function delete($id) {
        $sql = "DELETE FROM admin_users WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM admin_users WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return $this->db->query($sql, $params)->fetch() !== false;
    }

    /**
     * Get all roles
     */
    public function getRoles() {
        $sql = "SELECT * FROM admin_roles WHERE is_active = 1 ORDER BY id";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get role by ID
     */
    public function getRoleById($id) {
        $sql = "SELECT * FROM admin_roles WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Get distinct units from registered users
     * Returns only units that are actually assigned to users in the system
     */
    public function getRegisteredUnits() {
        $sql = "SELECT DISTINCT unit 
                FROM admin_users 
                WHERE unit IS NOT NULL AND unit != '' 
                ORDER BY unit ASC";
        $results = $this->db->query($sql)->fetchAll();
        
        $units = [];
        foreach ($results as $row) {
            $units[] = $row['unit'];
        }
        
        return $units;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($user, $permission) {
        $permissions = json_decode($user['role_permissions'], true);
        
        // Super admin has all permissions
        if (isset($permissions['all']) && $permissions['all'] === true) {
            return true;
        }

        // Check specific permission
        $parts = explode('.', $permission);
        if (count($parts) === 2) {
            $category = $parts[0];
            // Check if has full category access
            if (isset($permissions[$category]) && $permissions[$category] === true) {
                return true;
            }
        }

        return isset($permissions[$permission]) && $permissions[$permission] === true;
    }
}

