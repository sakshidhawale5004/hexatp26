<?php
/**
 * Countries List Page
 * Country Content CMS
 * 
 * Requirements: 2.2, 8.2, 8.8, 8.9, 9.1, 9.6
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

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'country_name';
$sort_order = $_GET['order'] ?? 'ASC';

// Get countries
$contentService = new ContentService($conn);
$filters = [
    'status' => $status_filter,
    'sort' => $sort_by,
    'order' => $sort_order
];
$countries = $contentService->getAllCountries($filters);

// Get statistics
$stats = $contentService->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countries List | HexaTP CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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

        .filter-section {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-btn {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-slate);
            padding: 8px 20px;
            border-radius: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }

        .table-container {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
            color: var(--text-main);
        }

        .table thead {
            border-bottom: 2px solid var(--glass-border);
        }

        .table th {
            color: var(--accent);
            font-weight: 600;
            border: none;
            padding: 15px;
            background: transparent;
        }

        .table td {
            border: none;
            padding: 15px;
            border-bottom: 1px solid var(--glass-border);
            color: #444;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .badge-published {
            background: #4caf50;
            color: var(--text-main);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .badge-draft {
            background: #ff9800;
            color: #000;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .action-btn {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--accent);
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn:hover {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .action-btn-view {
            border-color: rgba(255, 255, 255, 0.15);
            color: var(--text-main);
        }

        .action-btn-view:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent);
            border-color: var(--accent);
        }

        .action-btn-live {
            border-color: rgba(76, 175, 80, 0.3);
            color: #81c784;
        }

        .action-btn-live:hover {
            background: #4caf50;
            color: var(--text-main);
            border-color: #4caf50;
        }

        .action-btn-danger {
            border-color: rgba(220, 53, 69, 0.5);
            color: #ff6b6b;
        }

        .action-btn-danger:hover {
            background: #dc3545;
            color: var(--text-main);
            border-color: #dc3545;
        }

        .btn-add-new {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-add-new:hover {
            background: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .stats-mini {
            display: inline-block;
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            padding: 8px 15px;
            border-radius: 8px;
            margin-right: 10px;
            font-size: 0.9rem;
        }

        .stats-mini strong {
            color: var(--accent);
        }

        /* DataTables styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: 6px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-slate);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-slate) !important;
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 6px;
            padding: 6px 12px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--accent) !important;
            color: #000 !important;
            border-color: var(--accent);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--accent) !important;
            color: #000 !important;
            border-color: var(--accent);
        }

        @media (max-width: 768px) {
            .table-container {
                padding: 10px;
            }

            .action-btn {
                margin-bottom: 5px;
            }

            .stats-mini {
                display: block;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-3">
                    <img src="../logo-hexatp.jpeg" alt="HexaTP Logo" style="height: 40px;">
                    <h1 class="mb-0">Countries List</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="dashboard.php" class="btn-add-new">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="country_edit.php?action=new" class="btn-add-new">
                        <i class="bi bi-plus-circle"></i> Add New Country
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Statistics -->
        <div class="mb-3">
            <span class="stats-mini">
                <i class="bi bi-globe"></i> Total: <strong><?php echo $stats['total']; ?></strong>
            </span>
            <span class="stats-mini">
                <i class="bi bi-check-circle"></i> Published: <strong><?php echo $stats['published']; ?></strong>
            </span>
            <span class="stats-mini">
                <i class="bi bi-pencil-square"></i> Drafts: <strong><?php echo $stats['draft']; ?></strong>
            </span>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="d-flex flex-wrap align-items-center">
                <span style="color: var(--text-slate); margin-right: 15px;">
                    <i class="bi bi-funnel"></i> Filter by Status:
                </span>
                <a href="?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                    All
                </a>
                <a href="?status=published" class="filter-btn <?php echo $status_filter === 'published' ? 'active' : ''; ?>">
                    Published
                </a>
                <a href="?status=draft" class="filter-btn <?php echo $status_filter === 'draft' ? 'active' : ''; ?>">
                    Drafts
                </a>
            </div>
        </div>

        <!-- Countries Table -->
        <div class="table-container">
            <table id="countriesTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Country Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($countries as $country): 
                        // Logic to find live HTML file
                        $html_file = strtolower(str_replace(' ', '', $country->country_name)) . '.html';
                        
                        // Special cases for filenames that don't match standard pattern
                        $special_cases = [
                            'united arab emirates' => 'unitedarab.html',
                            'saudi arabia' => 'saudiarabia.html',
                            'vietnam' => 'viethnam.html',
                            'united states' => 'us.html'
                        ];
                        
                        $lookup_name = strtolower(trim($country->country_name));
                        if (isset($special_cases[$lookup_name])) {
                            $html_file = $special_cases[$lookup_name];
                        }
                        
                        $html_file_exists = file_exists(__DIR__ . '/../' . $html_file);
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($country->country_name); ?></strong>
                        </td>
                        <td>
                            <code style="color: var(--accent);"><?php echo htmlspecialchars($country->country_code); ?></code>
                        </td>
                        <td>
                            <span class="badge-<?php echo $country->status; ?>">
                                <?php echo ucfirst($country->status); ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo $country->updated_at->format('M d, Y H:i'); ?></small>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="country_edit.php?id=<?php echo $country->id; ?>" class="action-btn" title="Edit Content">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                
                                <a href="../country.php?id=<?php echo $country->id; ?>" target="_blank" class="action-btn action-btn-view" title="Preview dynamic page">
                                    <i class="bi bi-eye"></i> View
                                </a>

                                <?php if ($html_file_exists): ?>
                                <a href="../<?php echo $html_file; ?>" target="_blank" class="action-btn action-btn-live" title="View published HTML">
                                    <i class="bi bi-globe"></i> Live
                                </a>
                                <?php endif; ?>

                                <?php if ($current_user->role === 'admin'): ?>
                                <button onclick="deleteCountry(<?php echo $country->id; ?>, '<?php echo htmlspecialchars($country->country_name); ?>')" class="action-btn action-btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#countriesTable').DataTable({
                pageLength: 25,
                order: [[0, 'asc']],
                language: {
                    search: "Search countries:",
                    lengthMenu: "Show _MENU_ countries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ countries",
                    infoEmpty: "No countries available",
                    infoFiltered: "(filtered from _MAX_ total countries)",
                    zeroRecords: "No matching countries found"
                }
            });
        });

        // Delete country function
        function deleteCountry(id, name) {
            if (!confirm(`Are you sure you want to delete "${name}"?\n\nThis will permanently delete the country and all associated content (overview, regulatory frameworks, documentation cards).\n\nThis action cannot be undone.`)) {
                return;
            }

            // Show loading
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Deleting...';
            btn.disabled = true;

            fetch(`../api/country.php?id=${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Country deleted successfully');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete country'));
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('Error: Failed to delete country');
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
