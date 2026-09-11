<?php

require_once __DIR__ . '/../models/User.php';

/**
 * UserRepository
 * 
 * Data access layer for User entities.
 * Implements CRUD operations using prepared statements for SQL injection prevention.
 * Provides authentication and user management functionality.
 * 
 * Requirements: 5.1, 5.2, 7.1, 7.2, 7.9
 */
class UserRepository {
    private mysqli $conn;
    
    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct(mysqli $connection) {
        $this->conn = $connection;
    }
    
    /**
     * Create a new user
     * 
     * @param User $user The user to create
     * @return int|false The ID of the created user, or false on failure
     */
    public function create(User $user): int|false {
        $sql = "INSERT INTO users (
            username, password_hash, email, role, created_at
        ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $created_at = $user->created_at->format('Y-m-d H:i:s');
        $password_hash = $user->getPasswordHash();
        
        $stmt->bind_param(
            'sssss',
            $user->username,
            $password_hash,
            $user->email,
            $user->role,
            $created_at
        );
        
        $result = $stmt->execute();
        if (!$result) {
            $stmt->close();
            return false;
        }
        
        $insert_id = $this->conn->insert_id;
        $stmt->close();
        
        return $insert_id;
    }
    
    /**
     * Find a user by ID
     * 
     * @param int $id The user ID
     * @return User|null The user object, or null if not found
     */
    public function findById(int $id): ?User {
        $sql = "SELECT * FROM users WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) {
            return null;
        }
        
        return User::fromArray($row);
    }
    
    /**
     * Find a user by username
     * 
     * @param string $username The username to search for
     * @return User|null The user object, or null if not found
     */
    public function findByUsername(string $username): ?User {
        $sql = "SELECT * FROM users WHERE username = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param('s', $username);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) {
            return null;
        }
        
        return User::fromArray($row);
    }
    
    /**
     * Find all users
     * 
     * @param array $filters Optional filters (role, sort, order)
     * @return array Array of User objects
     */
    public function findAll(array $filters = []): array {
        $sql = "SELECT * FROM users";
        $conditions = [];
        $params = [];
        $types = '';
        
        // Apply role filter if provided
        if (isset($filters['role'])) {
            $conditions[] = "role = ?";
            $params[] = $filters['role'];
            $types .= 's';
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        // Apply sorting
        $sort = $filters['sort'] ?? 'username';
        $order = $filters['order'] ?? 'ASC';
        
        // Validate sort field to prevent SQL injection
        $allowed_sort_fields = ['username', 'email', 'role', 'created_at', 'last_login'];
        if (!in_array($sort, $allowed_sort_fields)) {
            $sort = 'username';
        }
        
        // Validate order
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql .= " ORDER BY {$sort} {$order}";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = User::fromArray($row);
        }
        
        $stmt->close();
        
        return $users;
    }
    
    /**
     * Update an existing user
     * 
     * @param int $id The user ID
     * @param User $user The updated user data
     * @return bool True on success, false on failure
     */
    public function update(int $id, User $user): bool {
        $sql = "UPDATE users SET
            username = ?,
            email = ?,
            role = ?
        WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param(
            'sssi',
            $user->username,
            $user->email,
            $user->role,
            $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Update user password
     * 
     * @param int $id The user ID
     * @param string $password_hash The new password hash
     * @return bool True on success, false on failure
     */
    public function updatePassword(int $id, string $password_hash): bool {
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param('si', $password_hash, $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Update user's last login timestamp
     * 
     * @param int $id The user ID
     * @return bool True on success, false on failure
     */
    public function updateLastLogin(int $id): bool {
        $sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Delete a user
     * 
     * @param int $id The user ID
     * @return bool True on success, false on failure
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM users WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Check if a user exists by ID
     * 
     * @param int $id The user ID
     * @return bool True if exists, false otherwise
     */
    public function exists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM users WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }
    
    /**
     * Check if a username already exists (for uniqueness validation)
     * 
     * @param string $username The username to check
     * @param int|null $exclude_id Optional ID to exclude from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function usernameExists(string $username, ?int $exclude_id = null): bool {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        $types = 's';
        
        if ($exclude_id !== null) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }
    
    /**
     * Get count of users by role
     * 
     * @param string|null $role Optional role filter ('admin', 'editor', or null for all)
     * @return int The count of users
     */
    public function getCount(?string $role = null): int {
        $sql = "SELECT COUNT(*) as count FROM users";
        
        if ($role !== null) {
            $sql .= " WHERE role = ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        
        if ($role !== null) {
            $stmt->bind_param('s', $role);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }
    
    /**
     * Check if user has a specific permission
     * 
     * This is a convenience method that loads the user and checks permissions.
     * 
     * @param int $user_id The user ID
     * @param string $action The action to check (create, read, update, delete, publish)
     * @param string $resource The resource type (country, user, etc.)
     * @return bool True if the user has permission, false otherwise
     */
    public function hasPermission(int $user_id, string $action, string $resource): bool {
        $user = $this->findById($user_id);
        if (!$user) {
            return false;
        }
        
        return $user->hasPermission($action, $resource);
    }
}
