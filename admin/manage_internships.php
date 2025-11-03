<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delete_query = "DELETE FROM internship WHERE internship_id = $id";
    if ($conn->query($delete_query)) {
        $success = "Internship deleted successfully!";
    } else {
        $error = "Error deleting internship: " . $conn->error;
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $role = sanitize($_POST['role']);
    $company_name = sanitize($_POST['company_name']);
    $department = sanitize($_POST['department']);
    $location = sanitize($_POST['location']);
    $stipend = sanitize($_POST['stipend']);
    $duration = sanitize($_POST['duration']);
    $application_link = sanitize($_POST['application_link']);
    $deadline = sanitize($_POST['deadline']);
    $status = sanitize($_POST['status']);
    $internship_id = isset($_POST['internship_id']) ? intval($_POST['internship_id']) : 0;

    if (empty($title) || empty($role) || empty($company_name) || empty($department) || empty($deadline)) {
        $error = "Please fill all required fields!";
    } else {
        if ($internship_id > 0) {
            // Update existing internship
            $query = "UPDATE internship SET 
                     title='$title', role='$role', company_name='$company_name', 
                     department='$department', location='$location', stipend='$stipend', 
                     duration='$duration', application_link='$application_link', 
                     deadline='$deadline', status='$status' 
                     WHERE internship_id=$internship_id";
        } else {
            // Add new internship
            $posted_on = date('Y-m-d');
            $query = "INSERT INTO internship (title, role, company_name, posted_on, application_link, deadline, status, location, stipend, duration, department) 
                     VALUES ('$title', '$role', '$company_name', '$posted_on', '$application_link', '$deadline', '$status', '$location', '$stipend', '$duration', '$department')";
        }

        if ($conn->query($query)) {
            $success = $internship_id > 0 ? "Internship updated successfully!" : "Internship added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Get all internships
$internships = $conn->query("SELECT * FROM internship ORDER BY posted_on DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Internships</title>
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
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
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
        .badge-filled {
            background: #e3f2fd;
            color: #1976d2;
        }
        .btn-action {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            margin: 0 2px;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
                        <a class="nav-link active" href="manage_internships.php">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-briefcase me-2"></i>Manage Internships</h4>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i>Add New Internship
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Stipend</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($internship = $internships->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $internship['internship_id']; ?></td>
                                <td><strong><?php echo $internship['company_name']; ?></strong></td>
                                <td><?php echo $internship['role']; ?></td>
                                <td><?php echo $internship['department']; ?></td>
                                <td><?php echo $internship['location']; ?></td>
                                <td><?php echo $internship['stipend']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($internship['deadline'])); ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo $internship['status']; ?>">
                                        <?php echo ucfirst($internship['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-action" onclick="editInternship(<?php echo htmlspecialchars(json_encode($internship)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $internship['internship_id']; ?>" 
                                       class="btn btn-sm btn-danger btn-action" 
                                       onclick="return confirm('Are you sure you want to delete this internship?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus me-2"></i>Add New Internship
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="internship_id" id="internship_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <input type="text" name="role" id="role" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name *</label>
                                <input type="text" name="company_name" id="company_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department *</label>
                                <select name="department" id="department" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Electronics">Electronics</option>
                                    <option value="Mechanical">Mechanical</option>
                                    <option value="Civil">Civil</option>
                                    <option value="Electrical">Electrical</option>
                                    <option value="IT">Information Technology</option>
                                    <option value="Business">Business</option>
                                    <option value="Marketing">Marketing</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Location</label>
                                <select name="location" id="location" class="form-select">
                                    <option value="">Select Location</option>
                                    <option value="Remote">Remote</option>
                                    <option value="On-site">On-site</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stipend</label>
                                <input type="text" name="stipend" id="stipend" class="form-control" placeholder="e.g., $500/month or Unpaid">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" name="duration" id="duration" class="form-control" placeholder="e.g., 3 months">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Application Link</label>
                                <input type="url" name="application_link" id="application_link" class="form-control" placeholder="https://">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deadline *</label>
                                <input type="date" name="deadline" id="deadline" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="filled">Filled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-add">
                            <i class="fas fa-save me-2"></i>Save Internship
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editInternship(internship) {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Internship';
            document.getElementById('internship_id').value = internship.internship_id;
            document.getElementById('title').value = internship.title;
            document.getElementById('role').value = internship.role;
            document.getElementById('company_name').value = internship.company_name;
            document.getElementById('department').value = internship.department;
            document.getElementById('location').value = internship.location;
            document.getElementById('stipend').value = internship.stipend;
            document.getElementById('duration').value = internship.duration;
            document.getElementById('application_link').value = internship.application_link;
            document.getElementById('deadline').value = internship.deadline;
            document.getElementById('status').value = internship.status;
            
            var modal = new bootstrap.Modal(document.getElementById('addModal'));
            modal.show();
        }

        // Reset form when modal is closed
        document.getElementById('addModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Add New Internship';
            document.querySelector('form').reset();
            document.getElementById('internship_id').value = '';
        });
    </script>
</body>
</html>