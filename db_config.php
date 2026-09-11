<?php
/**
 * Database Configuration File
 * HexaTP Country Content Management System
 * 
 * IMPORTANT: This file contains your Hostinger database credentials
 */

// Hostinger Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'u852823366_hexatp_db');       // Your Hostinger database name (FIXED: single underscore)
define('DB_USER', 'u852823366_hexatp_user');     // Your Hostinger database username (FIXED: single underscore)
define('DB_PASS', 'Hexatp_2026');                // Your Hostinger database password
define('DB_CHARSET', 'utf8mb4');
// Set default timezone for India
date_default_timezone_set('Asia/Kolkata');

/**
 * Get Database Connection
 * Returns a mysqli connection object
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        // Log error (don't expose details to users)
        error_log("Database connection failed: " . $conn->connect_error);
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please contact support.'
        ]));
    }
    
    // Set charset
    if (!$conn->set_charset(DB_CHARSET)) {
        error_log("Error setting charset: " . $conn->error);
    }
    
    return $conn;
}

// Create global connection for backward compatibility
$conn = getDBConnection();

?>
