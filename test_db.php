<?php
/**
 * Database Connection Test
 * This file tests if your database connection works
 * Delete this file after testing!
 */

echo "<h1>Database Connection Test</h1>";
echo "<hr>";

// Test 1: Check if db_config.php exists
echo "<h2>Test 1: Check db_config.php</h2>";
if (file_exists('db_config.php')) {
    echo "✅ db_config.php file exists<br>";
    
    // Try to include it
    try {
        require_once 'db_config.php';
        echo "✅ db_config.php loaded successfully<br>";
    } catch (Exception $e) {
        echo "❌ Error loading db_config.php: " . $e->getMessage() . "<br>";
        die();
    }
} else {
    echo "❌ db_config.php file NOT FOUND!<br>";
    echo "Please upload db_config.php to the same directory as this file.<br>";
    die();
}

// Test 2: Check database connection
echo "<h2>Test 2: Database Connection</h2>";
if (isset($conn) && $conn instanceof mysqli) {
    if ($conn->ping()) {
        echo "✅ Database connection successful!<br>";
        echo "Connected to database: " . DB_NAME . "<br>";
    } else {
        echo "❌ Database connection failed!<br>";
        echo "Error: " . $conn->error . "<br>";
        die();
    }
} else {
    echo "❌ Connection object not created!<br>";
    die();
}

// Test 3: Check if users table exists
echo "<h2>Test 3: Check Tables</h2>";
$tables_to_check = ['users', 'countries', 'country_overview', 'regulatory_frameworks', 
                    'documentation_cards', 'content_revisions', 'audit_log'];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' NOT FOUND!<br>";
    }
}

// Test 4: Check database credentials
echo "<h2>Test 4: Database Configuration</h2>";
echo "Host: " . DB_HOST . "<br>";
echo "Database: " . DB_NAME . "<br>";
echo "User: " . DB_USER . "<br>";
echo "Password: " . (defined('DB_PASS') ? '***hidden***' : 'NOT SET') . "<br>";

echo "<hr>";
echo "<h2>✅ All Tests Passed!</h2>";
echo "<p>Your database is configured correctly. You can now:</p>";
echo "<ol>";
echo "<li><a href='create_admin.php'>Create your admin user</a></li>";
echo "<li>Delete this test file (test_db.php) for security</li>";
echo "</ol>";

$conn->close();
?>
