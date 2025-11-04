<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

// Handle status update
if (isset($_GET['update_status'])) {
    $app_id = intval($_GET['update_status']);
    $new_status = sanitize($_GET['status']);
    
    if (in_array($new_status, ['applied', 'accepted', 'rejected'])) {
        $update_query = "UPDATE application SET status = '$new_status' WHERE application_id = $app_id";
        if ($conn->query($update_query)) {
            $success = "Application status updated successfully!";
        } else {
            $error = "Error updating status: " . $conn->error;
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['filter_status']) ? sanitize($_GET['filter_status']) : '';
$internship_filter = isset($_GET['filter_internship']) ? intval($_GET['filter_internship']) : 0;

// Build query with filters
$query = "SELECT a.*, s.first_name, s.last_name, s.email, s.department as student_dept, s.year, s.resume_link, s.skills,
          i.title, i.role, i.company_name, i.department as internship_dept
          FROM application a
          JOIN student s ON a.student_id = s.student_id
          JOIN internship i ON a.internship_id = i.internship_id";

$conditions = [];
if (!empty($status_filter)) {
    $conditions[] = "a.status = '$status_filter'";
}
if ($internship_filter > 0) {
    $conditions[] = "a.internship_id = $internship_filter";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY a.applied_on DESC";
$result = $conn->query($query);

// Get internships for filter
$internships_query = "SELECT internship_id, company_name, role FROM internship ORDER BY company_name";
$internships_result = $conn->query($internships_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications</title>
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
        }
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-applied {
            background: #fff3e0;
            color: #f57c00;
        }
        .badge-accepted {
            background: #e8f5e9;
            color: #388e3c;
        }
        .badge-rejected {
            background: #ffebee;
            color: #d32f2f;
        }
        .btn-action {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            margin: 2px;
        }
        .student-info {
            cursor: pointer;
            color: #667eea;
            text-decoration: underline;
        }
        .student-info:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt me-2"></i>Admin Portal
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_internships.php">
                            <i class="fas fa-briefcase me-1"></i>Manage Internships
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="view_applications.php">
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
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <h4 class="mb-4"><i class="fas fa-file-alt me-2"></i>All Applications</h4>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Status</label>
                            <select name="filter_status" class="form-select">
                                <option value="">All Status</option>
                                <option value="applied" <?php echo ($status_filter === 'applied') ? 'selected' : ''; ?>>Pending</option>
                                <option value="accepted" <?php echo ($status_filter === 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                                <option value="rejected" <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Filter by Internship</label>
                            <select name="filter_internship" class="form-select">
                                <option value="">All Internships</option>
                                <?php while ($intern = $internships_result->fetch_assoc()): ?>
                                    <option value="<?php echo $intern['internship_id']; ?>" 
                                        <?php echo ($internship_filter === $intern['internship_id']) ? 'selected' : ''; ?>>
                                        <?php echo $intern['company_name'] . ' - ' . $intern['role']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Department</th>
                            <th>Company</th>
                            <th>Role</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($app = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $app['application_id']; ?></td>
                                    <td>
                                        <span class="student-info" 
                                              data-bs-toggle="modal" 
                                              data-bs-target="#studentModal"
                                              onclick="showStudentInfo(<?php echo htmlspecialchars(json_encode($app)); ?>)">
                                            <?php echo $app['first_name'] . ' ' . $app['last_name']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $app['student_dept']; ?></td>
                                    <td><strong><?php echo $app['company_name']; ?></strong></td>
                                    <td><?php echo $app['role']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($app['applied_on'])); ?></td>
                                    <td>
                                        <span class="status-badge badge-<?php echo $app['status']; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'applied'): ?>
                                            <a href="?update_status=<?php echo $app['application_id']; ?>&status=accepted" 
                                               class="btn btn-sm btn-success btn-action"
                                               onclick="return confirm('Accept this application?')">
                                                <i class="fas fa-check"></i> Accept
                                            </a>
                                            <a href="?update_status=<?php echo $app['application_id']; ?>&status=rejected" 
                                               class="btn btn-sm btn-danger btn-action"
                                               onclick="return confirm('Reject this application?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php else: ?>
                                            <a href="?update_status=<?php echo $app['application_id']; ?>&status=applied" 
                                               class="btn btn-sm btn-warning btn-action"
                                               onclick="return confirm('Reset to pending?')">
                                                <i class="fas fa-undo"></i> Reset
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No applications found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student Info Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>Student Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="studentDetails">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showStudentInfo(app) {
            const details = `
                <div class="mb-3">
                    <strong>Name:</strong> ${app.first_name} ${app.last_name}
                </div>
                <div class="mb-3">
                    <strong>Email:</strong> ${app.email}
                </div>
                <div class="mb-3">
                    <strong>Department:</strong> ${app.student_dept}
                </div>
                <div class="mb-3">
                    <strong>Year:</strong> ${app.year}
                </div>
                                                <div class="mb-3">
                    <strong>Skills:</strong> ${app.skills}
                </div>
                                                <div class="mb-3">
                    <strong>Resume:</strong><br>
                    <a href="../download_resume.php?file=${encodeURIComponent(app.resume_link)}" target="_blank" class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-eye me-2"></i>View Resume
                    </a>
                    <a href="../download_resume.php?file=${encodeURIComponent(app.resume_link)}" download class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-2"></i>Download Resume
                    </a>
                </div>
            `;
            document.getElementById('studentDetails').innerHTML = details;
        }
    </script>
</body>
</html>