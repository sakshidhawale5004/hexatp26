<?php
/**
 * Database Status Check Utility
 * Verifies if all necessary tables and columns exist for the enhanced CMS.
 */
require_once 'db_config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>HexaTP DB Status Check</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background: #f4f4f9; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .success { color: #2ecc71; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        h2 { color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: var(--text-main); text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
<div class='card'>";

echo "<h2>HexaTP CMS Status Check</h2>";

// 1. Check Tables
$required_tables = [
    'countries', 
    'country_services', 
    'country_overview', 
    'regulatory_frameworks', 
    'documentation_cards'
];

$all_tables_ok = true;

foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p><span class='success'>✅ Table '$table' exists.</span></p>";
        
        // 2. Check Specific Columns for countries
        if ($table === 'countries') {
            $columns = $conn->query("SHOW COLUMNS FROM countries");
            $col_names = [];
            while($col = $columns->fetch_assoc()) {
                $col_names[] = $col['Field'];
            }
            
            $new_fields = ['cta_title', 'cta_button_text', 'footer_title', 'footer_email'];
            foreach ($new_fields as $field) {
                if (in_array($field, $col_names)) {
                    echo "<p style='margin-left:20px;'><span class='success'>✅ Column '$field' found.</span></p>";
                } else {
                    echo "<p style='margin-left:20px;'><span class='error'>❌ Column '$field' is MISSING!</span></p>";
                    $all_tables_ok = false;
                }
            }
        }
    } else {
        echo "<p><span class='error'>❌ Table '$table' is MISSING!</span></p>";
        $all_tables_ok = false;
    }
}

if ($all_tables_ok) {
    echo "<div style='background:#e8f8f5; padding:15px; border-radius:5px; border:1px solid #2ecc71; margin-top:20px;'>
            <p><strong>Status: READY!</strong> All database structures are correctly configured.</p>
            <p>You can now run the migration script to sync all content:</p>
            <a href='migrate_countries.php' class='btn'>Run Migration Now</a>
          </div>";
} else {
    echo "<div style='background:#fdedec; padding:15px; border-radius:5px; border:1px solid #e74c3c; margin-top:20px;'>
            <p><strong>Status: INCOMPLETE!</strong> Please run the SQL command in phpMyAdmin to add the missing tables/columns.</p>
          </div>";
}

echo "</div>
</body>
</html>";
?>
