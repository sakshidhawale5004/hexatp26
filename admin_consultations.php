<?php
/**
 * Admin Panel - View Consultations
 * HexaTP Consultation System
 */

require_once 'db_config.php';

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'created_at';
$sort_order = $_GET['order'] ?? 'DESC';

// Build query
$query = "SELECT * FROM consultations WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " ORDER BY " . $sort_by . " " . $sort_order;

$result = $conn->query($query);
$consultations = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $consultations[] = $row;
    }
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed
FROM consultations";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Consultations | HexaTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        }

        .stats-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .stats-card h3 {
            color: var(--accent);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stats-card p {
            color: var(--text-slate);
            margin: 0;
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
        }

        .table thead {
            border-bottom: 2px solid var(--glass-border);
        }

        .table th {
            color: var(--accent);
            font-weight: 600;
            border: none;
            padding: 15px;
        }

        .table td {
            border: none;
            padding: 15px;
            border-bottom: 1px solid var(--glass-border);
            color: #444;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .badge-pending {
            background: #ff9800;
            color: #000;
        }

        .badge-confirmed {
            background: #4caf50;
            color: var(--text-main);
        }

        .badge-completed {
            background: #2196f3;
            color: var(--text-main);
        }

        .badge-cancelled {
            background: #f44336;
            color: var(--text-main);
        }

        .filter-section {
            margin-bottom: 30px;
        }

        .filter-btn {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            color: #444;
            padding: 10px 20px;
            border-radius: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--accent);
            color: #000;
            border-color: var(--accent);
        }

        .action-btn {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--accent);
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            transition: 0.3s;
        }

        .action-btn:hover {
            background: var(--accent);
            color: #000;
        }

        .modal-content {
            background: var(--bg-light);
            border: 1px solid var(--glass-border);
        }

        .modal-header {
            border-bottom: 1px solid var(--glass-border);
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            color: var(--text-main);
            box-shadow: none;
        }

        .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            color: var(--text-main);
            box-shadow: none;
        }

        .form-select option {
            background: #050a14;
            color: var(--text-main);
        }

        @media (max-width: 768px) {
            .table-container {
                padding: 10px;
            }

            .table th,
            .table td {
                padding: 8px;
                font-size: 12px;
            }

            .stats-card {
                margin-bottom: 15px;
            }

            .stats-card h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1><i class="bi bi-calendar-check"></i> Consultations Dashboard</h1>
            <p class="text-muted mb-0">Manage all consultation requests</p>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['total'] ?? 0; ?></h3>
                    <p>Total Consultations</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['pending'] ?? 0; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['confirmed'] ?? 0; ?></h3>
                    <p>Confirmed</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['completed'] ?? 0; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <h5 class="mb-3">Filter by Status:</h5>
            <a href="?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="?status=confirmed" class="filter-btn <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
            <a href="?status=completed" class="filter-btn <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">Completed</a>
            <a href="?status=cancelled" class="filter-btn <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
        </div>

        <!-- Consultations Table -->
        <div class="table-container">
            <?php if (!empty($consultations)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultations as $consultation): ?>
                            <tr>
                                <td>#<?php echo $consultation['id']; ?></td>
                                <td><?php echo htmlspecialchars($consultation['name']); ?></td>
                                <td><?php echo htmlspecialchars($consultation['email']); ?></td>
                                <td><?php echo htmlspecialchars($consultation['phone']); ?></td>
                                <td><?php echo htmlspecialchars($consultation['consultation_type']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($consultation['appointment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($consultation['appointment_time']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $consultation['status']; ?>">
                                        <?php echo ucfirst($consultation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $consultation['id']; ?>">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal<?php echo $consultation['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Consultation Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($consultation['name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($consultation['email']); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($consultation['phone']); ?></p>
                                            <p><strong>Type:</strong> <?php echo htmlspecialchars($consultation['consultation_type']); ?></p>
                                            <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($consultation['appointment_date'])); ?></p>
                                            <p><strong>Time:</strong> <?php echo htmlspecialchars($consultation['appointment_time']); ?></p>
                                            <p><strong>Status:</strong> <span class="badge badge-<?php echo $consultation['status']; ?>"><?php echo ucfirst($consultation['status']); ?></span></p>
                                            <p><strong>Message:</strong></p>
                                            <p><?php echo nl2br(htmlspecialchars($consultation['message'])); ?></p>
                                            <p><small class="text-muted">Submitted: <?php echo date('M d, Y H:i', strtotime($consultation['created_at'])); ?></small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">No consultations found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
