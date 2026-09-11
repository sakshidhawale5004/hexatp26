<?php
/**
 * Simple Database Connection Test
 */

// Database credentials
$host = 'localhost';
$user = 'u852823366_hexatp_user';
$pass = 'Hexatp_2026';
$dbname = 'u852823366_hexatp_db';

echo "<h1>Simple Connection Test</h1>";
echo "<p><strong>Attempting connection with:</strong></p>";
echo "<ul>";
echo "<li>Host: $host</li>";
echo "<li>User: $user</li>";
echo "<li>Database: $dbname</li>";
echo "<li>Password: " . str_repeat('*', strlen($pass)) . "</li>";
echo "</ul>";

// Try to connect
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo "<p style='color: red;'><strong>❌ Connection FAILED:</strong> " . $conn->connect_error . "</p>";
    echo "<p><strong>Possible issues:</strong></p>";
    echo "<ul>";
    echo "<li>Password is incorrect</li>";
    echo "<li>Database user doesn't have permissions</li>";
    echo "<li>Database doesn't exist</li>";
    echo "</ul>";
} else {
    echo "<p style='color: green;'><strong>✅ Connection SUCCESSFUL!</strong></p>";
    echo "<p>Server info: " . $conn->server_info . "</p>";
    
    // Check tables
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<p><strong>Tables in database:</strong></p>";
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
    $conn->close();
}
?>
