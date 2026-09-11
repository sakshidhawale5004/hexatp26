<?php
/**
 * Complete System Status Check
 * Verifies all components are working
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status - HexaTP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .status-card {
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #ddd;
        }
        .status-card.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .status-card.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .status-card.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .status-card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .status-card p {
            font-size: 0.9rem;
            color: #666;
            margin: 5px 0;
        }
        .icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .success .icon { color: #28a745; }
        .error .icon { color: #dc3545; }
        .warning .icon { color: #ffc107; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .url-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .url-list a {
            display: block;
            padding: 10px;
            margin: 5px 0;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .url-list a:hover {
            background: #667eea;
            color: white;
            transform: translateX(5px);
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin-top: 30px;
        }
        .code {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 System Status Check</h1>
            <p>Complete Database & Website Connection Status</p>
        </div>

        <div class="content">
            <?php
            $allGood = true;
            $errors = [];
            $warnings = [];
            
            // Check 1: PHP Version
            echo '<div class="section">';
            echo '<h2>📊 System Information</h2>';
            echo '<div class="status-grid">';
            
            $phpVersion = phpversion();
            $phpOk = version_compare($phpVersion, '7.4.0', '>=');
            echo '<div class="status-card ' . ($phpOk ? 'success' : 'warning') . '">';
            echo '<div class="icon">' . ($phpOk ? '✓' : '⚠') . '</div>';
            echo '<h3>PHP Version</h3>';
            echo '<p>' . $phpVersion . '</p>';
            echo '<p>' . ($phpOk ? 'Compatible' : 'Recommended: 7.4+') . '</p>';
            echo '</div>';
            
            // Check 2: MySQLi Extension
            $mysqliLoaded = extension_loaded('mysqli');
            echo '<div class="status-card ' . ($mysqliLoaded ? 'success' : 'error') . '">';
            echo '<div class="icon">' . ($mysqliLoaded ? '✓' : '✗') . '</div>';
            echo '<h3>MySQLi Extension</h3>';
            echo '<p>' . ($mysqliLoaded ? 'Loaded' : 'Not Loaded') . '</p>';
            echo '</div>';
            
            if (!$mysqliLoaded) {
                $allGood = false;
                $errors[] = 'MySQLi extension not loaded';
            }
            
            echo '</div></div>';
            
            // Check 3: Database Connection
            echo '<div class="section">';
            echo '<h2>🗄️ Database Connection</h2>';
            echo '<div class="status-grid">';
            
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPass = '';
            $dbName = 'hexatp_db';
            
            $conn = @new mysqli($dbHost, $dbUser, $dbPass);
            
            if ($conn->connect_error) {
                echo '<div class="status-card error">';
                echo '<div class="icon">✗</div>';
                echo '<h3>MySQL Server</h3>';
                echo '<p>Connection Failed</p>';
                echo '<p><small>' . $conn->connect_error . '</small></p>';
                echo '</div>';
                $allGood = false;
                $errors[] = 'Cannot connect to MySQL server';
            } else {
                echo '<div class="status-card success">';
                echo '<div class="icon">✓</div>';
                echo '<h3>MySQL Server</h3>';
                echo '<p>Connected</p>';
                echo '<p>Host: ' . $dbHost . '</p>';
                echo '</div>';
                
                // Check Database
                $dbExists = $conn->select_db($dbName);
                echo '<div class="status-card ' . ($dbExists ? 'success' : 'warning') . '">';
                echo '<div class="icon">' . ($dbExists ? '✓' : '⚠') . '</div>';
                echo '<h3>Database</h3>';
                echo '<p>' . ($dbExists ? 'Exists' : 'Not Found') . '</p>';
                echo '<p>' . $dbName . '</p>';
                echo '</div>';
                
                if (!$dbExists) {
                    $warnings[] = 'Database does not exist - run create_database.php';
                }
                
                // Check Tables
                if ($dbExists) {
                    $tables = ['consultations', 'inquiries'];
                    $existingTables = [];
                    $missingTables = [];
                    
                    foreach ($tables as $table) {
                        $result = $conn->query("SHOW TABLES LIKE '$table'");
                        if ($result && $result->num_rows > 0) {
                            $existingTables[] = $table;
                        } else {
                            $missingTables[] = $table;
                        }
                    }
                    
                    echo '<div class="status-card ' . (empty($missingTables) ? 'success' : 'warning') . '">';
                    echo '<div class="icon">' . (empty($missingTables) ? '✓' : '⚠') . '</div>';
                    echo '<h3>Database Tables</h3>';
                    if (empty($missingTables)) {
                        echo '<p>All tables exist</p>';
                        echo '<p><small>' . implode(', ', $existingTables) . '</small></p>';
                    } else {
                        echo '<p>Missing tables</p>';
                        echo '<p><small>' . implode(', ', $missingTables) . '</small></p>';
                        $warnings[] = 'Some tables missing - run create_database.php';
                    }
                    echo '</div>';
                }
                
                $conn->close();
            }
            
            echo '</div></div>';
            
            // Check 4: Files
            echo '<div class="section">';
            echo '<h2>📁 Required Files</h2>';
            echo '<div class="status-grid">';
            
            $requiredFiles = [
                'db_config.php' => 'Database Config',
                'save_inquiry.php' => 'Form Handler',
                'contact.html' => 'Contact Form',
                'admin_consultations.php' => 'Admin Panel',
                'create_database.php' => 'Database Setup',
                'test_connection.php' => 'Connection Test'
            ];
            
            foreach ($requiredFiles as $file => $desc) {
                $exists = file_exists($file);
                echo '<div class="status-card ' . ($exists ? 'success' : 'error') . '">';
                echo '<div class="icon">' . ($exists ? '✓' : '✗') . '</div>';
                echo '<h3>' . $desc . '</h3>';
                echo '<p>' . $file . '</p>';
                echo '<p>' . ($exists ? 'Found' : 'Missing') . '</p>';
                echo '</div>';
                
                if (!$exists) {
                    $allGood = false;
                    $errors[] = $file . ' is missing';
                }
            }
            
            echo '</div></div>';
            ?>

            <!-- Quick Actions -->
            <div class="section">
                <h2>🚀 Quick Actions</h2>
                <div class="url-list">
                    <a href="create_database.php" target="_blank">🗄️ Create Database & Tables</a>
                    <a href="test_connection.php" target="_blank">🔍 Test Database Connection</a>
                    <a href="contact.html" target="_blank">📝 Test Contact Form</a>
                    <a href="admin_consultations.php" target="_blank">👨‍💼 View Admin Panel</a>
                    <a href="index.html" target="_blank">🏠 Go to Homepage</a>
                    <a href="http://localhost/phpmyadmin" target="_blank">🗄️ Open phpMyAdmin</a>
                </div>
            </div>

            <!-- Summary -->
            <div class="summary">
                <?php if ($allGood && empty($warnings)): ?>
                    <h2>🎉 Everything is Working!</h2>
                    <p style="font-size: 1.2rem; margin: 20px 0;">
                        All systems are operational. Your website is ready!
                    </p>
                    <a href="contact.html" class="btn">Test Contact Form</a>
                    <a href="admin_consultations.php" class="btn">View Admin Panel</a>
                <?php elseif (!empty($errors)): ?>
                    <h2>⚠️ Action Required</h2>
                    <p style="font-size: 1.1rem; margin: 20px 0;">
                        Please fix the errors below:
                    </p>
                    <div style="text-align: left; background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px; margin: 20px 0;">
                        <?php foreach ($errors as $error): ?>
                            <p>❌ <?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <h2>⚠️ Setup Needed</h2>
                    <p style="font-size: 1.1rem; margin: 20px 0;">
                        Some setup steps are required:
                    </p>
                    <div style="text-align: left; background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px; margin: 20px 0;">
                        <?php foreach ($warnings as $warning): ?>
                            <p>⚠️ <?php echo $warning; ?></p>
                        <?php endforeach; ?>
                    </div>
                    <a href="create_database.php" class="btn">Create Database Now</a>
                <?php endif; ?>
            </div>

            <!-- Configuration Info -->
            <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-left: 5px solid #2196F3; border-radius: 5px;">
                <h4 style="color: #2196F3; margin-bottom: 10px;">📋 Current Configuration</h4>
                <div class="code">
                    <strong>Database Host:</strong> <?php echo $dbHost; ?><br>
                    <strong>Database User:</strong> <?php echo $dbUser; ?><br>
                    <strong>Database Name:</strong> <?php echo $dbName; ?><br>
                    <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
                    <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
