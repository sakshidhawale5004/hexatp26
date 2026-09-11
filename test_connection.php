<?php
/**
 * Database Connection Test Script
 * HexaTP - Test all database functionality
 * 
 * SECURITY WARNING: Delete this file after testing!
 */

// Prevent caching
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - HexaTP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content {
            padding: 30px;
        }
        
        .test-section {
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #ddd;
            background: #f8f9fa;
        }
        
        .test-section.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .test-section.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        .test-section.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        
        .test-title {
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .icon {
            font-size: 1.5em;
        }
        
        .test-details {
            margin-top: 10px;
            padding: 15px;
            background: white;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .test-details strong {
            color: #667eea;
        }
        
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: center;
        }
        
        .summary h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .stat {
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 8px;
            min-width: 120px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .warning-box h3 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        
        .warning-box p {
            color: #856404;
            line-height: 1.6;
        }
        
        .next-steps {
            background: #e7f3ff;
            border-left: 5px solid #2196F3;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .next-steps h3 {
            color: #1976D2;
            margin-bottom: 15px;
        }
        
        .next-steps ol {
            margin-left: 20px;
            line-height: 1.8;
        }
        
        .next-steps li {
            margin-bottom: 10px;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Database Connection Test</h1>
            <p>HexaTP Transfer Pricing Solutions</p>
        </div>
        
        <div class="content">
            <?php
            $tests_passed = 0;
            $tests_failed = 0;
            $tests_total = 0;
            
            // Test 1: Include database config
            $tests_total++;
            echo '<div class="test-section ';
            try {
                require_once 'db_config.php';
                echo 'success">';
                echo '<div class="test-title"><span class="icon">✅</span> Test 1: Database Configuration File</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> Successfully loaded<br>';
                echo '<strong>Host:</strong> ' . DB_HOST . '<br>';
                echo '<strong>Database:</strong> ' . DB_NAME . '<br>';
                echo '<strong>User:</strong> ' . DB_USER . '<br>';
                echo '</div>';
                $tests_passed++;
            } catch (Exception $e) {
                echo 'error">';
                echo '<div class="test-title"><span class="icon">❌</span> Test 1: Database Configuration File</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> Failed<br>';
                echo '<strong>Error:</strong> ' . $e->getMessage();
                echo '</div>';
                $tests_failed++;
            }
            echo '</div>';
            
            // Test 2: Database Connection
            $tests_total++;
            echo '<div class="test-section ';
            if ($conn->connect_error) {
                echo 'error">';
                echo '<div class="test-title"><span class="icon">❌</span> Test 2: Database Connection</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> Connection Failed<br>';
                echo '<strong>Error:</strong> ' . $conn->connect_error;
                echo '</div>';
                $tests_failed++;
            } else {
                echo 'success">';
                echo '<div class="test-title"><span class="icon">✅</span> Test 2: Database Connection</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> Connected Successfully<br>';
                echo '<strong>Server Info:</strong> ' . $conn->server_info . '<br>';
                echo '<strong>Protocol Version:</strong> ' . $conn->protocol_version . '<br>';
                echo '<strong>Character Set:</strong> ' . $conn->character_set_name();
                echo '</div>';
                $tests_passed++;
            }
            echo '</div>';
            
            // Test 3: Check if tables exist
            $tests_total++;
            echo '<div class="test-section ';
            $tables_exist = true;
            $missing_tables = [];
            
            $required_tables = ['consultations', 'inquiries'];
            foreach ($required_tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows == 0) {
                    $tables_exist = false;
                    $missing_tables[] = $table;
                }
            }
            
            if (!$tables_exist) {
                echo 'warning">';
                echo '<div class="test-title"><span class="icon">⚠️</span> Test 3: Database Tables</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> Tables Missing<br>';
                echo '<strong>Missing Tables:</strong> ' . implode(', ', $missing_tables) . '<br>';
                echo '<strong>Action Required:</strong> Run the SQL script in phpMyAdmin to create tables';
                echo '</div>';
                $tests_failed++;
            } else {
                echo 'success">';
                echo '<div class="test-title"><span class="icon">✅</span> Test 3: Database Tables</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> All Required Tables Exist<br>';
                echo '<strong>Tables Found:</strong> ' . implode(', ', $required_tables);
                
                // Show table structure
                foreach ($required_tables as $table) {
                    $result = $conn->query("DESCRIBE $table");
                    echo "<br><br><strong>$table structure:</strong><br>";
                    while ($row = $result->fetch_assoc()) {
                        echo "- {$row['Field']} ({$row['Type']})<br>";
                    }
                }
                echo '</div>';
                $tests_passed++;
            }
            echo '</div>';
            
            // Test 4: Test Write Permission
            $tests_total++;
            echo '<div class="test-section ';
            if ($tables_exist) {
                $test_query = "INSERT INTO inquiries (name, email, phone, message) VALUES ('Test User', 'test@example.com', '1234567890', 'Test message from connection script')";
                
                if ($conn->query($test_query)) {
                    $insert_id = $conn->insert_id;
                    // Clean up test data
                    $conn->query("DELETE FROM inquiries WHERE id = $insert_id");
                    
                    echo 'success">';
                    echo '<div class="test-title"><span class="icon">✅</span> Test 4: Write Permissions</div>';
                    echo '<div class="test-details">';
                    echo '<strong>Status:</strong> Write Test Successful<br>';
                    echo '<strong>Test Record ID:</strong> ' . $insert_id . ' (deleted after test)<br>';
                    echo '<strong>Result:</strong> Database is ready to accept form submissions';
                    echo '</div>';
                    $tests_passed++;
                } else {
                    echo 'error">';
                    echo '<div class="test-title"><span class="icon">❌</span> Test 4: Write Permissions</div>';
                    echo '<div class="test-details">';
                    echo '<strong>Status:</strong> Write Test Failed<br>';
                    echo '<strong>Error:</strong> ' . $conn->error;
                    echo '</div>';
                    $tests_failed++;
                }
            } else {
                echo 'warning">';
                echo '<div class="test-title"><span class="icon">⚠️</span> Test 4: Write Permissions</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> Skipped (tables not created yet)';
                echo '</div>';
            }
            echo '</div>';
            
            // Test 5: Check PHP Files
            $tests_total++;
            echo '<div class="test-section ';
            $required_files = [
                'save_inquiry.php' => 'Contact form handler',
                'admin_consultations.php' => 'Admin panel',
                'check_status.php' => 'Status checker'
            ];
            
            $missing_files = [];
            foreach ($required_files as $file => $description) {
                if (!file_exists($file)) {
                    $missing_files[] = "$file ($description)";
                }
            }
            
            if (count($missing_files) > 0) {
                echo 'warning">';
                echo '<div class="test-title"><span class="icon">⚠️</span> Test 5: Required PHP Files</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> Some files missing<br>';
                echo '<strong>Missing Files:</strong><br>';
                foreach ($missing_files as $file) {
                    echo "- $file<br>";
                }
                echo '</div>';
            } else {
                echo 'success">';
                echo '<div class="test-title"><span class="icon">✅</span> Test 5: Required PHP Files</div>';
                echo '<div class="test-details">';
                echo '<strong>Status:</strong> All required files present<br>';
                foreach ($required_files as $file => $description) {
                    echo "- $file ($description)<br>";
                }
                echo '</div>';
                $tests_passed++;
            }
            echo '</div>';
            
            // Close connection
            $conn->close();
            ?>
            
            <!-- Summary -->
            <div class="summary">
                <h2>Test Summary</h2>
                <div class="summary-stats">
                    <div class="stat">
                        <div class="stat-number"><?php echo $tests_total; ?></div>
                        <div class="stat-label">Total Tests</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $tests_passed; ?></div>
                        <div class="stat-label">Passed</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $tests_failed; ?></div>
                        <div class="stat-label">Failed</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo round(($tests_passed / $tests_total) * 100); ?>%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>
            </div>
            
            <?php if ($tests_passed == $tests_total): ?>
            <!-- All tests passed -->
            <div class="next-steps">
                <h3>🎉 All Tests Passed! Next Steps:</h3>
                <ol>
                    <li><strong>Delete this test file</strong> for security: <code>test_connection.php</code></li>
                    <li><strong>Delete setup files:</strong> <code>create_database.php</code>, <code>DATABASE_SETUP_GUIDE.md</code></li>
                    <li><strong>Test your contact form</strong> at: <a href="contact.html">contact.html</a></li>
                    <li><strong>Access admin panel</strong> at: <a href="admin_consultations.php">admin_consultations.php</a></li>
                    <li><strong>Secure db_config.php</strong> using .htaccess rules</li>
                </ol>
            </div>
            <?php else: ?>
            <!-- Some tests failed -->
            <div class="next-steps">
                <h3>⚠️ Action Required:</h3>
                <ol>
                    <?php if (!$tables_exist): ?>
                    <li><strong>Create database tables:</strong>
                        <ul>
                            <li>Go to Hostinger → Databases → Manage → phpMyAdmin</li>
                            <li>Click "SQL" tab</li>
                            <li>Copy and paste the SQL from <code>DATABASE_SETUP_GUIDE.md</code></li>
                            <li>Click "Go"</li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (count($missing_files) > 0): ?>
                    <li><strong>Upload missing PHP files</strong> to your public_html folder</li>
                    <?php endif; ?>
                    
                    <li><strong>Refresh this page</strong> after fixing issues</li>
                </ol>
            </div>
            <?php endif; ?>
            
            <!-- Security Warning -->
            <div class="warning-box">
                <h3>🔒 Security Warning</h3>
                <p><strong>IMPORTANT:</strong> Delete this test file (<code>test_connection.php</code>) after verifying your database connection. This file exposes sensitive database information and should not remain on your production server.</p>
            </div>
        </div>
    </div>
</body>
</html>
