<?php
require_once '../config.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student applications
$query = "SELECT a.*, i.company_name, i.role, i.department as internship_dept, i.stipend, i.duration, i.location 
          FROM application a
          JOIN internship i ON a.internship_id = i.internship_id
          WHERE a.student_id = $student_id
          ORDER BY a.applied_on DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 0; }
        .navbar-custom .navbar-brand { color: white; font-weight: 700; font-size: 1.5rem; }
        .navbar-custom .nav-link { color: rgba(255,255,255,0.9); margin: 0 10px; }
        .internship-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); border-left: 4px solid #667eea; }
        .company-name { font-size: 1.2rem; font-weight: 700; color: #333; }
        .role-title { font-size: 1rem; color: #667eea; font-weight: 600; margin-bottom: 10px; }
        .info-badge { display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; margin-right: 5px; margin-bottom: 5px; }
        .badge-location { background: #e3f2fd; color: #1976d2; }
        .badge-stipend { background: #e8f5e9; color: #388e3c; }
        .badge-duration { background: #fff3e0; color: #f57c00; }
        .badge-department { background: #f3e5f5; color: #7b1fa2; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-size: 0.85rem; font-weight: 600; display: inline-block; }
        .badge-applied { background: #fff3e0; color: #f57c00; }
        .badge-accepted { background: #e8f5e9; color: #388e3c; }
        .badge-rejected { background: #ffebee; color: #d32f2f; }
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
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link active" href="my_applications.php"><i class="fas fa-file-alt me-1"></i>My Applications</a></li>
            <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user me-1"></i>Profile</a></li>
            <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">My Internship Applications</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($app = $result->fetch_assoc()): ?>
            <div class="internship-card">
                <div class="company-name"><?php echo $app['company_name']; ?></div>
                <div class="role-title"><?php echo $app['role']; ?></div>
                <div class="mb-2">
                    <span class="info-badge badge-department"><?php echo $app['internship_dept']; ?></span>
                    <span class="info-badge badge-location"><?php echo $app['location']; ?></span>
                    <span class="info-badge badge-stipend"><?php echo $app['stipend']; ?></span>
                    <span class="info-badge badge-duration"><?php echo $app['duration']; ?></span>
                </div>
                <div class="status-badge badge-<?php echo $app['status']; ?>">
                    <?php echo ucfirst($app['status']); ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center text-muted mt-5">
            <i class="fas fa-inbox" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No applications found</h4>
            <p>Apply to internships from the dashboard</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>