<?php
/**
 * Check User in Database
 * This will show us what users exist in the database
 * DELETE THIS FILE AFTER CHECKING!
 */

require_once 'db_config.php';

echo "<h1>User Database Check</h1>";
echo "<hr>";

echo "<h2>Checking users table:</h2>";

$query = "SELECT id, username, email, role, created_at FROM users";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<p>Found " . $result->num_rows . " user(s) in the database:</p>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created At</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>What to do:</h2>";
    echo "<p>Use the <strong>exact username</strong> shown above to login.</p>";
    echo "<p>If the username doesn't match what you expect, you may need to create a new admin user.</p>";
    
} else {
    echo "<p style='color: red;'>❌ No users found in the database!</p>";
    echo "<p>You need to create an admin user first.</p>";
    echo "<p><a href='create_admin.php'>Create Admin User</a></p>";
}

echo "<hr>";
echo "<p><strong>IMPORTANT:</strong> Delete this file (check_user.php) after checking!</p>";

$conn->close();
?>
