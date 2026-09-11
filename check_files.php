<?php
/**
 * File Existence Checker
 * This checks if all required files exist on the server
 * DELETE THIS FILE AFTER CHECKING!
 */

echo "<h1>File Existence Check</h1>";
echo "<hr>";

$files_to_check = [
    'db_config.php',
    'services/AuthService.php',
    'repositories/UserRepository.php',
    'models/User.php',
    'models/ValidationResult.php',
    'admin/login.php',
];

echo "<h2>Checking Required Files:</h2>";
echo "<ul>";

$all_exist = true;
foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $all_exist = $all_exist && $exists;
    
    if ($exists) {
        echo "<li style='color: green;'>✅ $file - EXISTS</li>";
    } else {
        echo "<li style='color: red;'>❌ $file - NOT FOUND!</li>";
    }
}

echo "</ul>";

if ($all_exist) {
    echo "<h2 style='color: green;'>✅ All files exist!</h2>";
    echo "<p>Now let's test if they can be loaded...</p>";
    
    echo "<h2>Testing File Loading:</h2>";
    echo "<ul>";
    
    // Test db_config.php
    try {
        require_once 'db_config.php';
        echo "<li style='color: green;'>✅ db_config.php loaded successfully</li>";
        
        if (isset($conn) && $conn instanceof mysqli) {
            echo "<li style='color: green;'>✅ Database connection object created</li>";
        } else {
            echo "<li style='color: red;'>❌ Database connection object NOT created</li>";
        }
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Error loading db_config.php: " . $e->getMessage() . "</li>";
    }
    
    // Test UserRepository
    try {
        require_once 'repositories/UserRepository.php';
        echo "<li style='color: green;'>✅ UserRepository.php loaded successfully</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Error loading UserRepository.php: " . $e->getMessage() . "</li>";
    }
    
    // Test User model
    try {
        require_once 'models/User.php';
        echo "<li style='color: green;'>✅ User.php loaded successfully</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Error loading User.php: " . $e->getMessage() . "</li>";
    }
    
    // Test AuthService
    try {
        require_once 'services/AuthService.php';
        echo "<li style='color: green;'>✅ AuthService.php loaded successfully</li>";
        
        // Try to create AuthService instance
        if (isset($conn)) {
            $authService = new AuthService($conn);
            echo "<li style='color: green;'>✅ AuthService instance created successfully</li>";
        }
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Error loading AuthService.php: " . $e->getMessage() . "</li>";
    }
    
    echo "</ul>";
    
    echo "<hr>";
    echo "<h2>✅ Diagnosis Complete!</h2>";
    echo "<p>If all tests passed, the login page should work now.</p>";
    echo "<p><a href='admin/login.php'>Try the login page</a></p>";
    
} else {
    echo "<h2 style='color: red;'>❌ Some files are missing!</h2>";
    echo "<p>Please upload the missing files to Hostinger.</p>";
}

echo "<hr>";
echo "<p><strong>IMPORTANT:</strong> Delete this file (check_files.php) after checking!</p>";
?>
