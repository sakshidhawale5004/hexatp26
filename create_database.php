<?php
/**
 * Automatic Database Setup Script
 * Creates database and tables automatically
 */

header('Content-Type: text/html; charset=utf-8');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hexatp_db";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 700px;
            width: 100%;
            background: white;
            border-radius: 20px;
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
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        .step {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f8f9fa;
            border-left: 5px solid #ddd;
        }
        .step.success {
            border-left-color: #28a745;
            background: #d4edda;
            color: #155724;
        }
        .step.error {
            border-left-color: #dc3545;
            background: #f8d7da;
            color: #721c24;
        }
        .step.info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            margin-top: 30px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ Database Setup</h1>
            <p>Automatic Database Creation</p>
        </div>

        <div class="content">
            <?php
            $allSuccess = true;
            
            // Step 1: Connect to MySQL
            echo "<div class='step info'><strong>Step 1:</strong> Connecting to MySQL server...</div>";
            
            $conn = new mysqli($servername, $username, $password);
            
            if ($conn->connect_error) {
                echo "<div class='step error'><strong>✗ Error:</strong> Connection failed: " . $conn->connect_error . "</div>";
                echo "<div class='step info'><strong>Solution:</strong> Make sure XAMPP MySQL service is running</div>";
                $allSuccess = false;
            } else {
                echo "<div class='step success'><strong>✓ Success:</strong> Connected to MySQL server</div>";
                
                // Step 2: Create database
                echo "<div class='step info'><strong>Step 2:</strong> Creating database '$dbname'...</div>";
                
                $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                if ($conn->query($sql) === TRUE) {
                    echo "<div class='step success'><strong>✓ Success:</strong> Database '$dbname' created or already exists</div>";
                } else {
                    echo "<div class='step error'><strong>✗ Error:</strong> " . $conn->error . "</div>";
                    $allSuccess = false;
                }
                
                // Step 3: Select database
                if ($conn->select_db($dbname)) {
                    echo "<div class='step success'><strong>✓ Success:</strong> Database selected</div>";
                    
                    // Step 4: Create consultations table
                    echo "<div class='step info'><strong>Step 3:</strong> Creating 'consultations' table...</div>";
                    
                    $sql = "CREATE TABLE IF NOT EXISTS consultations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        phone VARCHAR(20) NOT NULL,
                        consultation_type VARCHAR(100) NOT NULL,
                        appointment_date DATE NOT NULL,
                        appointment_time VARCHAR(20) NOT NULL,
                        message LONGTEXT,
                        status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_email (email),
                        INDEX idx_date (appointment_date),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                    
                    if ($conn->query($sql) === TRUE) {
                        echo "<div class='step success'><strong>✓ Success:</strong> Table 'consultations' created or already exists</div>";
                    } else {
                        echo "<div class='step error'><strong>✗ Error:</strong> " . $conn->error . "</div>";
                        $allSuccess = false;
                    }
                    
                    // Step 5: Create inquiries table
                    echo "<div class='step info'><strong>Step 4:</strong> Creating 'inquiries' table...</div>";
                    
                    $sql = "CREATE TABLE IF NOT EXISTS inquiries (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        phone VARCHAR(20) NOT NULL,
                        message LONGTEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_email (email)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                    
                    if ($conn->query($sql) === TRUE) {
                        echo "<div class='step success'><strong>✓ Success:</strong> Table 'inquiries' created or already exists</div>";
                    } else {
                        echo "<div class='step error'><strong>✗ Error:</strong> " . $conn->error . "</div>";
                        $allSuccess = false;
                    }
                    
                    // Step 6: Verify tables
                    echo "<div class='step info'><strong>Step 5:</strong> Verifying tables...</div>";
                    
                    $result = $conn->query("SHOW TABLES");
                    $tables = [];
                    while ($row = $result->fetch_array()) {
                        $tables[] = $row[0];
                    }
                    
                    if (in_array('consultations', $tables) && in_array('inquiries', $tables)) {
                        echo "<div class='step success'><strong>✓ Success:</strong> All tables verified: " . implode(', ', $tables) . "</div>";
                    } else {
                        echo "<div class='step error'><strong>✗ Error:</strong> Some tables are missing</div>";
                        $allSuccess = false;
                    }
                    
                } else {
                    echo "<div class='step error'><strong>✗ Error:</strong> Could not select database</div>";
                    $allSuccess = false;
                }
                
                $conn->close();
            }
            ?>

            <div class="summary">
                <?php if ($allSuccess): ?>
                    <h2>🎉 Setup Complete!</h2>
                    <p style="font-size: 1.2rem; margin: 20px 0;">
                        Database and tables created successfully!
                    </p>
                    <a href="test_connection.php" class="btn">Test Connection</a>
                    <a href="index.html" class="btn">Go to Homepage</a>
                    <a href="contact.html" class="btn">Test Contact Form</a>
                <?php else: ?>
                    <h2>⚠️ Setup Issues</h2>
                    <p style="font-size: 1.1rem; margin: 20px 0;">
                        Please check the errors above and try again
                    </p>
                    <a href="create_database.php" class="btn">Try Again</a>
                <?php endif; ?>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 5px solid #2196F3; border-radius: 5px;">
                <h4 style="color: #2196F3; margin-bottom: 10px;">📋 Database Information</h4>
                <p style="color: #0c5460; line-height: 1.8;">
                    <strong>Database Name:</strong> <?php echo $dbname; ?><br>
                    <strong>Tables:</strong> consultations, inquiries<br>
                    <strong>Host:</strong> <?php echo $servername; ?><br>
                    <strong>User:</strong> <?php echo $username; ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
