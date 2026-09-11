<?php

/**
 * User Model
 * 
 * Represents a user account with authentication and authorization capabilities.
 * Includes password hashing, verification, and permission checking methods.
 */
class User {
    public int $id;
    public string $username;
    private string $password_hash;
    public string $email;
    public string $role; // 'admin' or 'editor'
    public ?DateTime $last_login;
    public DateTime $created_at;
    
    // Valid roles
    const ROLE_ADMIN = 'admin';
    const ROLE_EDITOR = 'editor';
    const VALID_ROLES = [self::ROLE_ADMIN, self::ROLE_EDITOR];
    
    // Password hashing options
    const PASSWORD_HASH_ALGO = PASSWORD_BCRYPT;
    const PASSWORD_HASH_COST = 12;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->role = self::ROLE_EDITOR;
        $this->created_at = new DateTime();
        $this->last_login = null;
    }
    
    /**
     * Verify a password against the stored hash
     * 
     * @param string $password The plain text password to verify
     * @return bool True if the password matches, false otherwise
     */
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password_hash);
    }
    
    /**
     * Set a new password (hashes it automatically)
     * 
     * Uses bcrypt hashing algorithm with cost factor 12 for security.
     * 
     * @param string $password The plain text password to hash and store
     * @return void
     */
    public function setPassword(string $password): void {
        $this->password_hash = password_hash(
            $password, 
            self::PASSWORD_HASH_ALGO, 
            ['cost' => self::PASSWORD_HASH_COST]
        );
    }
    
    /**
     * Get the password hash (for internal use only)
     * 
     * @return string The password hash
     */
    public function getPasswordHash(): string {
        return $this->password_hash;
    }
    
    /**
     * Set the password hash directly (for loading from database)
     * 
     * @param string $hash The password hash
     * @return void
     */
    public function setPasswordHash(string $hash): void {
        $this->password_hash = $hash;
    }
    
    /**
     * Check if the user has permission to perform an action on a resource
     * 
     * Permission rules:
     * - Admin: Can perform all actions on all resources
     * - Editor: Can create, read, update (but not delete or publish)
     * 
     * @param string $action The action to check (create, read, update, delete, publish)
     * @param string $resource The resource type (country, user, etc.)
     * @return bool True if the user has permission, false otherwise
     */
    public function hasPermission(string $action, string $resource): bool {
        // Admin has all permissions
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }
        
        // Editor permissions
        if ($this->role === self::ROLE_EDITOR) {
            // Editors can read all resources
            if ($action === 'read') {
                return true;
            }
            
            // Editors can create and update country content
            if (in_array($resource, ['country', 'overview', 'regulatory_framework', 'documentation_card'])) {
                if (in_array($action, ['create', 'update'])) {
                    return true;
                }
            }
            
            // Editors cannot delete, publish, or manage users
            return false;
        }
        
        // Unknown role has no permissions
        return false;
    }
    
    /**
     * Convert the User model to an associative array
     * 
     * Note: Password hash is excluded from the array for security.
     * 
     * @return array The user data as an array
     */
    public function toArray(): array {
        $data = [
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'last_login' => $this->last_login ? $this->last_login->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
        
        // Include id if it's set
        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        
        return $data;
    }
    
    /**
     * Create a User model from an associative array
     * 
     * @param array $data The user data as an array
     * @return User The User model instance
     */
    public static function fromArray(array $data): User {
        $user = new User();
        
        // Set id if provided
        if (isset($data['id'])) {
            $user->id = (int)$data['id'];
        }
        
        // Set required fields
        $user->username = $data['username'] ?? '';
        $user->email = $data['email'] ?? '';
        $user->role = $data['role'] ?? self::ROLE_EDITOR;
        
        // Set password hash if provided (for loading from database)
        if (isset($data['password_hash'])) {
            $user->setPasswordHash($data['password_hash']);
        }
        
        // Set timestamps
        if (isset($data['last_login']) && $data['last_login'] !== null) {
            $user->last_login = is_string($data['last_login']) 
                ? new DateTime($data['last_login']) 
                : $data['last_login'];
        }
        
        if (isset($data['created_at'])) {
            $user->created_at = is_string($data['created_at']) 
                ? new DateTime($data['created_at']) 
                : $data['created_at'];
        }
        
        return $user;
    }
}
