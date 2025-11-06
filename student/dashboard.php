<?php
require_once '../config.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header("Location: ../login.php");
    exit();
}

// Get filter parameters
$department_filter = isset($_GET['department']) ? sanitize($_GET['department']) : '';
$stipend_filter = isset($_GET['stipend']) ? sanitize($_GET['stipend']) : '';
$location_filter = isset($_GET['location']) ? sanitize($_GET['location']) : '';

// Build query with filters
$query = "SELECT * FROM internship WHERE status = 'open'";
$conditions = [];

if (!empty($department_filter)) {
    $conditions[] = "department = '$department_filter'";
}

if (!empty($stipend_filter)) {
    if ($stipend_filter === 'paid') {
        $conditions[] = "stipend != 'Unpaid' AND stipend != ''";
    } elseif ($stipend_filter === 'unpaid') {
        $conditions[] = "(stipend = 'Unpaid' OR stipend = '')";
    }
}

if (!empty($location_filter)) {
    $conditions[] = "location LIKE '%$location_filter%'";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$query .= " ORDER BY posted_on DESC";
$result = $conn->query($query);

// Get departments for filter dropdown
$dept_query = "SELECT DISTINCT department FROM internship ORDER BY department";
$dept_result = $conn->query($dept_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        .internship-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .internship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .company-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .role-title {
            font-size: 1.1rem;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .info-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .badge-location {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge-stipend {
            background: #e8f5e9;
            color: #388e3c;
        }
        .badge-duration {
            background: #fff3e0;
            color: #f57c00;
        }
        .badge-department {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .btn-apply {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .deadline-text {
            color: #d32f2f;
            font-weight: 600;
        }
        .no-internships {
            text-align: center;
            padding: 60px;
            color: #999;
        }
        .navbar-custom .nav-link.active{
            color: yellow;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-briefcase me-2"></i>Internship Portal
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
                        <a class="nav-link" href="my_applications.php">
                            <i class="fas fa-file-alt me-1"></i>My Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-1"></i>Profile
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-search me-2"></i>Available Internships
                    <small class="text-muted" style="font-size: 1rem;">Welcome, <?php echo $_SESSION['student_name']; ?></small>
                </h2>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-building me-2"></i>Department</label>
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            <?php while ($dept = $dept_result->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department']; ?>" 
                                    <?php echo ($department_filter === $dept['department']) ? 'selected' : ''; ?>>
                                    <?php echo $dept['department']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-money-bill me-2"></i>Stipend</label>
                        <select name="stipend" class="form-select">
                            <option value="">All</option>
                            <option value="paid" <?php echo ($stipend_filter === 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="unpaid" <?php echo ($stipend_filter === 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Location</label>
                        <select name="location" class="form-select">
                            <option value="">All Locations</option>
                            <option value="Remote" <?php echo ($location_filter === 'Remote') ? 'selected' : ''; ?>>Remote</option>
                            <option value="On-site" <?php echo ($location_filter === 'On-site') ? 'selected' : ''; ?>>On-site</option>
                            <option value="Hybrid" <?php echo ($location_filter === 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-apply flex-grow-1">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Internships List -->
        <?php if ($result->num_rows > 0): ?>
            <?php while ($internship = $result->fetch_assoc()): ?>
                <div class="internship-card">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="company-name"><?php echo $internship['company_name']; ?></div>
                            <div class="role-title"><?php echo $internship['role']; ?></div>
                            
                            <div class="mb-2">
                                <?php if ($internship['department']): ?>
                                    <span class="info-badge badge-department">
                                        <i class="fas fa-building me-1"></i><?php echo $internship['department']; ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($internship['location']): ?>
                                    <span class="info-badge badge-location">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo $internship['location']; ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($internship['stipend']): ?>
                                    <span class="info-badge badge-stipend">
                                        <i class="fas fa-money-bill me-1"></i><?php echo $internship['stipend']; ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($internship['duration']): ?>
                                    <span class="info-badge badge-duration">
                                        <i class="fas fa-clock me-1"></i><?php echo $internship['duration']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="deadline-text">
                                <i class="fas fa-calendar-times me-2"></i>
                                Apply by: <?php echo date('M d, Y', strtotime($internship['deadline'])); ?>
                            </div>
                        </div>
                        <div class="col-md-3 text-end d-flex flex-column align-items-end justify-content-center gap-2">
                            <button class="btn btn-outline-primary btn-sm" 
                                    onclick="viewDetails(<?php echo htmlspecialchars(json_encode($internship)); ?>)"
                                    style="width: 150px;">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </button>
                            <a href="apply_internship.php?id=<?php echo $internship['internship_id']; ?>" 
                               class="btn btn-apply btn-sm"
                               style="width: 150px;">
                                <i class="fas fa-paper-plane me-2"></i>Apply Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-internships">
                <i class="fas fa-inbox" style="font-size: 4rem; color: #ddd;"></i>
                <h4 class="mt-3">No internships found</h4>
                <p>Try adjusting your filters or check back later</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
   <!-- View details button code -->
    <!-- Internship Details Modal -->
<div class="modal fade" id="internshipModal" tabindex="-1" aria-labelledby="internshipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="internshipModalLabel">Internship Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="internshipDetailsContent">
        <!-- Details will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast message for applied internship -->
<?php if (isset($_SESSION['toast_message'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <div id="liveToast" 
     class="toast align-items-center border-0 text-white" 
     role="alert" aria-live="assertive" aria-atomic="true"
     style="
        background: <?php 
            echo match($_SESSION['toast_type']) {
                'success' => 'linear-gradient(135deg, #4CAF50 0%, #81C784 100%)',
                'warning' => 'linear-gradient(135deg, #FFB74D 0%, #FF9800 100%)',
                'danger'  => 'linear-gradient(135deg, #e57373 0%, #ef5350 100%)',
                default   => 'linear-gradient(135deg, #e57373 0%, #ef5350 100%)' }; 
        ?>;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        border-radius: 12px;">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-info-circle me-2"></i><?php echo $_SESSION['toast_message']; ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toastEl = document.getElementById('liveToast');
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    });
</script>

<?php 
unset($_SESSION['toast_message']);
unset($_SESSION['toast_type']);
endif; 
?>

<script>
function viewDetails(internship) {
  let content = `
    <h4>${internship.title}</h4>
    <p><strong>Company:</strong> ${internship.company_name}</p>
    <p><strong>Role:</strong> ${internship.role}</p>
    <p><strong>Department:</strong> ${internship.department || 'N/A'}</p>
    <p><strong>Location:</strong> ${internship.location || 'N/A'}</p>
    <p><strong>Stipend:</strong> ${internship.stipend || 'N/A'}</p>
    <p><strong>Duration:</strong> ${internship.duration || 'N/A'}</p>
    <p><strong>Deadline:</strong> ${internship.deadline}</p>
    <hr>
    <p><strong>Description:</strong></p>
    <p>${internship.description ? internship.description : 'No description provided.'}</p>
  `;

  document.getElementById('internshipDetailsContent').innerHTML = content;

  // Show modal
  let modal = new bootstrap.Modal(document.getElementById('internshipModal'));
  modal.show();
}
</script>
</body>
</html>