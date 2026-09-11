<?php
/**
 * Admin Login Page
 * Country Content CMS
 * 
 * Requirements: 5.1, 5.2, 5.8, 9.1, 9.2
 * 
 * FIXED: Removed session_start() to prevent multiple session initialization
 * AuthService now handles all session management
 */

// Load database config and auth service BEFORE any session operations
require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../services/AuthService.php';

// Initialize AuthService (it will handle session_start internally)
$authService = new AuthService($conn);

// Redirect if already logged in
if ($authService->checkSession()) {
    header('Location: dashboard.php');
    exit;
}

// Handle login form submission
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $result = $authService->login($username, $password);
        
        if ($result['success']) {
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error_message = $result['error'] ?? 'Login failed';
            $remaining = $authService->getRemainingAttempts($username);
            if ($remaining > 0) {
                $error_message .= " ($remaining attempts remaining)";
            }
        }
    } catch (Exception $e) {
        error_log('Login error: ' . $e->getMessage());
        $error_message = 'Unable to connect to authentication service';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | HexaTP CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-subtle: #f9fafb;
            --accent: #f5c400;
            --accent-glow: rgba(245, 196, 0, 0.2);
            --card-bg: #ffffff;
            --glass-border: rgba(0, 0, 0, 0.08);
            --text-main: #0f172a;
            --text-slate: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-main);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(245, 196, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(245, 196, 0, 0.03) 0%, transparent 50%);
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 40px;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-section h1 {
            color: var(--accent);
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .logo-section p {
            color: var(--text-slate);
            font-size: 0.9rem;
        }

        .form-label {
            color: var(--text-slate);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-main);
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
            color: var(--text-main);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-right: none;
            color: var(--text-slate);
        }

        .input-group .form-control {
            border-left: none;
        }

        .btn-login {
            background: var(--accent);
            color: #000;
            font-weight: 600;
            padding: 12px;
            border: none;
            border-radius: 8px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            background: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .alert i {
            margin-right: 8px;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: var(--glass-border);
        }

        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .form-check-label {
            color: var(--text-slate);
            font-size: 0.9rem;
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: var(--text-slate);
            font-size: 0.85rem;
        }

        .footer-text a {
            color: var(--accent);
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-card {
                padding: 30px 20px;
            }

            .logo-section h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="../logo-hexatp.jpeg" alt="HexaTP Logo" style="height: 60px; margin-bottom: 20px;">
                <h1>HexaTP CMS</h1>
                <p>Country Content Management System</p>
            </div>

            <?php if ($error_message): ?>
            <div class="alert" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            placeholder="Enter your username"
                            required
                            autocomplete="username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                    <label class="form-check-label" for="rememberMe">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </form>

            <div class="footer-text">
                <p>&copy; 2024 HexaTP. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
                return false;
            }
        });

        // Auto-focus username field
        document.getElementById('username').focus();
    </script>
</body>
</html>
