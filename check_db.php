<?php
require_once __DIR__ . '/db_config.php';
$conn = getDBConnection();
$result = $conn->query("SELECT id, country_name, status FROM countries");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Name: {$row['country_name']} | Status: {$row['status']}\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>
