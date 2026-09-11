<?php
/**
 * Web-Based Admin User Creation
 * 
 * SECURITY WARNING: Delete this file after creating your admin user!
 * 
 * This script creates the first admin user for the CMS.
 * Visit this page in your browser, fill in the form, and submit.
 */

// Check if admin user already exists
require_once 'db_config.php';

$admin_exists = false;
$error = '';
$success = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    
    // Validation
    if (empty($username) || strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif (empty($password) || strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Create admin user using User model
            require_once __DIR__ . '/models/User.php';
            require_once __DIR__ . '/repositories/UserRepository.php';
            
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->role = User::ROLE_ADMIN;
            $user->setPassword($password); // This will hash the password correctly
            
            $userRepo = new UserRepository($conn);
            $user_id = $userRepo->create($user);
            
            if ($user_id) {
                $success = 'Admin user created successfully! You can now <a href="admin/login.php">login here</a>.';
                $admin_exists = true;
            } else {
                $error = 'Failed to create user: ' . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Check if any admin users exist
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    $admin_exists = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - HexaTP CMS</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        
        .warning strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #155724;
        }
        
        .success a {
            color: #155724;
            font-weight: bold;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--text-main);
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .info {
            background: #e7f3ff;
            border: 1px solid #2196F3;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            color: #0c5460;
            font-size: 14px;
        }
        
        .info strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Create Admin User</h1>
        <p class="subtitle">HexaTP Country Content CMS</p>
        
        <?php if ($admin_exists && !$success): ?>
            <div class="warning">
                <strong>⚠️ Admin User Already Exists</strong>
                An admin user has already been created. If you need to create another admin user, please use the admin panel.
            </div>
            <div class="info">
                <strong>Next Steps:</strong>
                1. Delete this file (create_admin.php) for security<br>
                2. <a href="admin/login.php">Login to the admin panel</a>
            </div>
        <?php else: ?>
            
            <?php if ($success): ?>
                <div class="success">
                    <?php echo $success; ?>
                </div>
                <div class="warning">
                    <strong>⚠️ IMPORTANT - Delete This File!</strong>
                    For security reasons, please delete <code>create_admin.php</code> from your server immediately.
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <div class="warning">
                    <strong>⚠️ Security Notice</strong>
                    Delete this file after creating your admin user!
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required minlength="3" placeholder="admin">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="admin@hexatp.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password * (min 8 characters)</label>
                        <input type="password" id="password" name="password" required minlength="8" placeholder="••••••••">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password *</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8" placeholder="••••••••">
                    </div>
                    
                    <button type="submit" class="btn">Create Admin User</button>
                </form>
                
                <div class="info">
                    <strong>After creating the admin user:</strong>
                    1. Delete this file (create_admin.php)<br>
                    2. Login at <a href="admin/login.php">/admin/login.php</a><br>
                    3. Start managing your country content!
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>
