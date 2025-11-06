<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$total_internships = $conn->query("SELECT COUNT(*) as count FROM internship")->fetch_assoc()['count'];
$open_internships = $conn->query("SELECT COUNT(*) as count FROM internship WHERE status = 'open'")->fetch_assoc()['count'];
$total_students = $conn->query("SELECT COUNT(*) as count FROM student")->fetch_assoc()['count'];
$total_applications = $conn->query("SELECT COUNT(*) as count FROM application")->fetch_assoc()['count'];
$pending_applications = $conn->query("SELECT COUNT(*) as count FROM application WHERE status = 'applied'")->fetch_assoc()['count'];

// Get recent applications
$recent_apps = "SELECT a.*, s.first_name, s.last_name, i.title, i.role, i.company_name 
                FROM application a 
                JOIN student s ON a.student_id = s.student_id 
                JOIN internship i ON a.internship_id = i.internship_id 
                ORDER BY a.applied_on DESC 
                LIMIT 5";
$recent_result = $conn->query($recent_apps);

// Get internship summary
$internship_summary = "SELECT internship_id, title, role, company_name, status, deadline 
                       FROM internship 
                       ORDER BY posted_on DESC 
                       LIMIT 5";
$internship_result = $conn->query($internship_summary);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-custom .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.9);
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        .navbar-custom .nav-link:hover {
            color: white;
            transform: translateY(-2px);
        }
        .navbar-custom .nav-link.active{
            color: yellow;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .stats-label {
            color: #666;
            font-size: 1rem;
        }
        .stats-icon {
            font-size: 3rem;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        .card-purple { color: #667eea; }
        .card-blue { color: #2196f3; }
        .card-green { color: #4caf50; }
        .card-orange { color: #ff9800; }
        .card-red { color: #f44336; }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        .content-card h5 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .table-responsive {
            border-radius: 10px;
        }
        .table th {
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            border: none;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-open {
            background: #e8f5e9;
            color: #388e3c;
        }
        .badge-closed {
            background: #ffebee;
            color: #d32f2f;
        }
        .badge-applied {
            background: #fff3e0;
            color: #f57c00;
        }
        .badge-accepted {
            background: #e8f5e9;
            color: #388e3c;
        }
        .badge-rejected{
            background: #ff89892d;
            color: #d32f2f;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt me-2"></i>Admin Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_internships.php">
                            <i class="fas fa-briefcase me-1"></i>Manage Internships
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_applications.php">
                            <i class="fas fa-file-alt me-1"></i>Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <h2 class="mb-4"><i class="fas fa-chart-line me-2"></i>Dashboard Overview</h2>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card position-relative">
                    <i class="fas fa-briefcase stats-icon card-purple"></i>
                    <div class="stats-number card-purple"><?php echo $total_internships; ?></div>
                    <div class="stats-label">Total Internships</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card position-relative">
                    <i class="fas fa-door-open stats-icon card-green"></i>
                    <div class="stats-number card-green"><?php echo $open_internships; ?></div>
                    <div class="stats-label">Open Positions</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card position-relative">
                    <i class="fas fa-users stats-icon card-blue"></i>
                    <div class="stats-number card-blue"><?php echo $total_students; ?></div>
                    <div class="stats-label">Registered Students</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card position-relative">
                    <i class="fas fa-file-alt stats-icon card-orange"></i>
                    <div class="stats-number card-orange"><?php echo $total_applications; ?></div>
                    <div class="stats-label">Total Applications</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card position-relative">
                    <i class="fas fa-clock stats-icon card-red"></i>
                    <div class="stats-number card-red"><?php echo $pending_applications; ?></div>
                    <div class="stats-label">Pending Review</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Applications -->
            <div class="col-md-6">
                <div class="content-card">
                    <h5><i class="fas fa-clock me-2"></i>Recent Applications</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Internship</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_result->num_rows > 0): ?>
                                    <?php while ($app = $recent_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $app['first_name'] . ' ' . $app['last_name']; ?></td>
                                            <td>
                                                <small><?php echo $app['company_name']; ?></small><br>
                                                <strong><?php echo $app['role']; ?></strong>
                                            </td>
                                            <td><?php echo date('M d', strtotime($app['applied_on'])); ?></td>
                                            <td>
                                                <span class="status-badge badge-<?php echo $app['status']; ?>">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No applications yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="view_applications.php" class="btn btn-sm btn-outline-primary">View All →</a>
                    </div>
                </div>
            </div>

            <!-- Recent Internships -->
            <div class="col-md-6">
                <div class="content-card">
                    <h5><i class="fas fa-briefcase me-2"></i>Recent Internships</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Role</th>
                                    <th>Deadline</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($internship_result->num_rows > 0): ?>
                                    <?php while ($intern = $internship_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo $intern['company_name']; ?></strong></td>
                                            <td><?php echo $intern['role']; ?></td>
                                            <td><?php echo date('M d', strtotime($intern['deadline'])); ?></td>
                                            <td>
                                                <span class="status-badge badge-<?php echo $intern['status']; ?>">
                                                    <?php echo ucfirst($intern['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No internships posted</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="manage_internships.php" class="btn btn-sm btn-outline-primary">Manage All →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>