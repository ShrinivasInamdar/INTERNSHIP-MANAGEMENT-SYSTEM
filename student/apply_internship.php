<?php
require_once '../config.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header("Location: ../login.php");
    exit();
}

$internship_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($internship_id === 0) {
    header("Location: dashboard.php");
    exit();
}

// Get internship details
$query = "SELECT * FROM internship WHERE internship_id = $internship_id AND status = 'open'";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Internship not found or no longer available!";
    header("Location: dashboard.php");
    exit();
}

$internship = $result->fetch_assoc();

// Check if already applied
$check_query = "SELECT * FROM application WHERE student_id = " . $_SESSION['student_id'] . " AND internship_id = $internship_id";
$check_result = $conn->query($check_query);

if ($check_result->num_rows > 0) {
    $_SESSION['error'] = "You have already applied for this internship!";
    header("Location: dashboard.php");
    exit();
}

// Handle application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $applied_on = date('Y-m-d');
    
    $insert_query = "INSERT INTO application (student_id, internship_id, applied_on, status) 
                     VALUES ($student_id, $internship_id, '$applied_on', 'applied')";
    
    if ($conn->query($insert_query)) {
        $_SESSION['success'] = "Application submitted successfully!";
        header("Location: view_applications.php");
        exit();
    } else {
        $error = "Error submitting application: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Internship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .apply-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            margin: 0 auto;
        }
        .apply-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .internship-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .detail-row {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
            min-width: 120px;
        }
        .detail-value {
            color: #333;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .info-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        .badge-location {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge-stipend {
            background: #e8f5e9;
            color: #388e3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="apply-container">
            <h2 class="apply-title">
                <i class="fas fa-paper-plane me-2"></i>Apply for Internship
            </h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="internship-details">
                <h4 style="color: #667eea; font-weight: 700; margin-bottom: 20px;">
                    <?php echo $internship['company_name']; ?>
                </h4>
                
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-briefcase me-2"></i>Role:</span>
                    <span class="detail-value"><?php echo $internship['role']; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-building me-2"></i>Department:</span>
                    <span class="detail-value"><?php echo $internship['department']; ?></span>
                </div>
                
                <?php if ($internship['location']): ?>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-map-marker-alt me-2"></i>Location:</span>
                    <span class="detail-value"><?php echo $internship['location']; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($internship['stipend']): ?>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-money-bill me-2"></i>Stipend:</span>
                    <span class="detail-value"><?php echo $internship['stipend']; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($internship['duration']): ?>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-clock me-2"></i>Duration:</span>
                    <span class="detail-value"><?php echo $internship['duration']; ?></span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-calendar-times me-2"></i>Deadline:</span>
                    <span class="detail-value" style="color: #d32f2f; font-weight: 600;">
                        <?php echo date('M d, Y', strtotime($internship['deadline'])); ?>
                    </span>
                </div>

                <?php if ($internship['application_link']): ?>
                <div class="detail-row">
                    <span class="detail-label"><i class="fas fa-link me-2"></i>Apply Link:</span>
                    <span class="detail-value">
                        <a href="<?php echo $internship['application_link']; ?>" target="_blank" style="color: #667eea;">
                            View Application Portal <i class="fas fa-external-link-alt ms-1"></i>
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                By clicking "Confirm Application", you confirm that your profile information is up-to-date and you meet the requirements for this internship.
            </div>

            <form method="POST" action="">
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-check me-2"></i>Confirm Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>