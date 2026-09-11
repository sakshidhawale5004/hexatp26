<?php
/**
 * Admin Dashboard
 * Country Content CMS
 * 
 * Requirements: 2.1, 5.2, 5.4, 8.10
 */

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/ContentService.php';

// Start session and check authentication
$conn = getDBConnection();
$authService = new AuthService($conn);

if (!$authService->checkSession()) {
    header('Location: login.php');
    exit;
}

// Get current user
$current_user = $authService->getCurrentUser();

// Get content statistics
$contentService = new ContentService($conn);
$stats = $contentService->getStatistics();

// Generate CSRF token
$csrf_token = $authService->generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | HexaTP CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
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

        body {
            background-color: var(--bg-light);
            color: var(--text-main);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.85); box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            color: var(--accent);
            font-weight: 800;
            margin: 0;
        }

        .user-info {
            color: var(--text-slate);
            font-size: 0.9rem;
        }

        .user-info strong {
            color: var(--text-main);
        }

        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            border-color: var(--accent);
        }

        .stats-card .icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 15px;
        }

        .stats-card h3 {
            color: var(--accent);
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stats-card p {
            color: var(--text-slate);
            margin: 0;
            font-size: 0.95rem;
        }

        .quick-actions {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .quick-actions h2 {
            color: var(--accent);
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .action-btn {
            background: rgba(245, 196, 0, 0.1);
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 15px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-right: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .action-btn:hover {
            background: var(--accent);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .action-btn i {
            font-size: 1.2rem;
        }

        .nav-menu {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .nav-menu a {
            color: var(--text-slate);
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 5px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(245, 196, 0, 0.1);
            color: var(--accent);
        }

        .nav-menu a i {
            margin-right: 10px;
            width: 20px;
        }

        .logout-btn {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }

        .logout-btn:hover {
            background: #dc3545;
            color: var(--text-main);
            border-color: #dc3545;
        }

        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 15px;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-3">
                    <img src="../logo-hexatp.jpeg" alt="HexaTP Logo" style="height: 40px;">
                    <h1 class="mb-0">Dashboard</h1>
                </div>
                <div class="col-md-6 text-end">
                    <div class="user-info">
                        <i class="bi bi-person-circle"></i>
                        <strong><?php echo htmlspecialchars($current_user->username); ?></strong>
                        <span class="badge bg-warning text-dark ms-2"><?php echo ucfirst($current_user->role); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
                <div class="nav-menu">
                    <a href="dashboard.php" class="active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="countries_list.php">
                        <i class="bi bi-globe"></i> Countries List
                    </a>
                    <a href="country_edit.php?action=new">
                        <i class="bi bi-plus-circle"></i> Add New Country
                    </a>
                    <hr style="border-color: var(--glass-border);">
                    <a href="#" onclick="logout()" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="icon">
                                <i class="bi bi-globe"></i>
                            </div>
                            <h3><?php echo $stats['total']; ?></h3>
                            <p>Total Countries</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <h3><?php echo $stats['published']; ?></h3>
                            <p>Published</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <h3><?php echo $stats['draft']; ?></h3>
                            <p>Drafts</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2><i class="bi bi-lightning-charge"></i> Quick Actions</h2>
                    <a href="countries_list.php" class="action-btn">
                        <i class="bi bi-list-ul"></i>
                        View All Countries
                    </a>
                    <a href="country_edit.php?action=new" class="action-btn">
                        <i class="bi bi-plus-circle"></i>
                        Add New Country
                    </a>
                    <?php if ($current_user->role === 'admin'): ?>
                    <a href="migrate_content.php" class="action-btn">
                        <i class="bi bi-arrow-repeat"></i>
                        Migrate Content
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Welcome Message -->
                <div class="quick-actions">
                    <h2><i class="bi bi-info-circle"></i> Welcome to HexaTP CMS</h2>
                    <p style="color: var(--text-slate); line-height: 1.8;">
                        This Content Management System allows you to manage country-specific transfer pricing law content. 
                        You can create, edit, and publish country pages with overview sections, regulatory frameworks, 
                        and documentation cards.
                    </p>
                    <p style="color: var(--text-slate); line-height: 1.8; margin-top: 15px;">
                        <strong style="color: var(--accent);">Your Role:</strong> 
                        <?php if ($current_user->role === 'admin'): ?>
                            As an administrator, you have full access to all features including creating, editing, publishing, and deleting countries.
                        <?php else: ?>
                            As an editor, you can create and edit countries, but only administrators can publish or delete content.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('../api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'logout'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = 'login.php';
                });
            }
        }
    </script>
</body>
</html>
