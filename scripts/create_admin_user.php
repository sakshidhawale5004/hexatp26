<?php
/**
 * Create Admin User Script
 * 
 * This script creates an admin user for the Country Content CMS.
 * Run this script once after deploying to create your first admin account.
 * 
 * Usage: php scripts/create_admin_user.php
 * 
 * Requirements: 5.1, 10.2
 */

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

echo "===========================================\n";
echo "  HexaTP CMS - Create Admin User\n";
echo "===========================================\n\n";

// Get database connection
try {
    $conn = getDBConnection();
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Create UserRepository
$userRepo = new UserRepository($conn);

// Get user input
echo "Enter admin user details:\n";
echo "-------------------------\n";

// Username
echo "Username: ";
$username = trim(fgets(STDIN));

if (empty($username) || strlen($username) < 3) {
    echo "✗ Username must be at least 3 characters long\n";
    exit(1);
}

// Check if username already exists
if ($userRepo->usernameExists($username)) {
    echo "✗ Username already exists. Please choose a different username.\n";
    exit(1);
}

// Email
echo "Email: ";
$email = trim(fgets(STDIN));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "✗ Invalid email address\n";
    exit(1);
}

// Password
echo "Password (min 8 characters): ";
$password = trim(fgets(STDIN));

if (empty($password) || strlen($password) < 8) {
    echo "✗ Password must be at least 8 characters long\n";
    exit(1);
}

// Confirm password
echo "Confirm Password: ";
$password_confirm = trim(fgets(STDIN));

if ($password !== $password_confirm) {
    echo "✗ Passwords do not match\n";
    exit(1);
}

// Role
echo "Role (admin/editor) [admin]: ";
$role = trim(fgets(STDIN));
if (empty($role)) {
    $role = 'admin';
}

if (!in_array($role, ['admin', 'editor'])) {
    echo "✗ Invalid role. Must be 'admin' or 'editor'\n";
    exit(1);
}

echo "\n";

// Create user
try {
    $user = new User();
    $user->username = $username;
    $user->email = $email;
    $user->role = $role;
    $user->setPassword($password);
    
    $user_id = $userRepo->create($user);
    
    if ($user_id) {
        echo "===========================================\n";
        echo "✓ User created successfully!\n";
        echo "===========================================\n\n";
        echo "User Details:\n";
        echo "  ID:       $user_id\n";
        echo "  Username: $username\n";
        echo "  Email:    $email\n";
        echo "  Role:     $role\n";
        echo "\n";
        echo "You can now login at: /admin/login.php\n";
        echo "===========================================\n";
    } else {
        echo "✗ Failed to create user\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Error creating user: " . $e->getMessage() . "\n";
    exit(1);
}
