<?php
require_once '../config.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$success = '';
$error = '';

// Handle resume update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_resume'])) {
    $file = $_FILES['new_resume'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['pdf', 'doc', 'docx'];

    if ($file_error !== 0) {
        $error = "Error uploading file!";
    } elseif (!in_array($file_ext, $allowed_extensions)) {
        $error = "Only PDF, DOC, and DOCX files are allowed!";
    } elseif ($file_size > 5242880) {
        $error = "File size must be less than 5MB!";
    } else {
        $get_old_resume = "SELECT resume_link FROM student WHERE student_id = $student_id";
        $old_resume_result = $conn->query($get_old_resume);
        $old_resume_data = $old_resume_result->fetch_assoc();
        $old_resume_path = $old_resume_data['resume_link'];

        $new_filename = uniqid('resume_') . '_' . time() . '.' . $file_ext;
        $upload_path = 'uploads/resumes/' . $new_filename;
        $full_upload_path = '../' . $upload_path;

        if (move_uploaded_file($file_tmp, $full_upload_path)) {
            $update_query = "UPDATE student SET resume_link = '$upload_path' WHERE student_id = $student_id";
            if ($conn->query($update_query)) {
                if (!empty($old_resume_path) && file_exists('../' . $old_resume_path)) {
                    unlink('../' . $old_resume_path);
                }
                header("Location: profile.php?success=1");
                exit();
            } else {
                $error = "Error updating database: " . $conn->error;
                unlink($full_upload_path);
            }
        } else {
            $error = "Error uploading resume file!";
        }
    }
}

// Update skills block
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_skills'])) {
    $updated_skills = trim($_POST['updated_skills']);
    if (!empty($updated_skills)) {
        $update_skills_query = "UPDATE student SET skills = '$updated_skills' WHERE student_id = $student_id";
        if ($conn->query($update_skills_query)) {
            header("Location: profile.php?skills_updated=1");
            exit();
        } else {
            $error = "Error updating skills: " . $conn->error;
        }
    } else {
        $error = "Skills field cannot be empty!";
    }
}

// Update personal info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personal'])) {
    $first_name = $conn->real_escape_string(trim($_POST['first_name']));
    $last_name = $conn->real_escape_string(trim($_POST['last_name']));
    $roll_number = $conn->real_escape_string(trim($_POST['roll_number']));
    $department = $conn->real_escape_string(trim($_POST['department']));
    $year = $conn->real_escape_string(trim($_POST['year']));
    $email = $conn->real_escape_string(trim($_POST['email']));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $update_personal_query = "UPDATE student SET 
            first_name='$first_name', last_name='$last_name', roll_number='$roll_number', 
            department='$department', year='$year', email='$email'
            WHERE student_id=$student_id";

        if ($conn->query($update_personal_query)) {
            header("Location: profile.php?personal_updated=1");
            exit();
        } else {
            $error = "Error updating personal info: " . $conn->error;
        }
    }
}

// Handle success messages
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "Resume updated successfully!";
} elseif (isset($_GET['skills_updated']) && $_GET['skills_updated'] == 1) {
    $success = "Skills updated successfully!";
} elseif (isset($_GET['personal_updated']) && $_GET['personal_updated'] == 1) {
    $success = "Personal information updated successfully!";
}

// Get student details
$query = "SELECT * FROM student WHERE student_id = $student_id";
$result = $conn->query($query);
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
            transition: 0.3s;
        }

        .navbar-custom .nav-link:hover {
            color: white;
            transform: translateY(-2px);
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .profile-header {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            color: #667eea;
        }

        .info-row {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 1.1rem;
        }

        .resume-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .btn-resume {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-resume:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload-input {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
        }

        .file-upload-label {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-label:hover {
            background: #764ba2;
        }
        .navbar-custom .nav-link.active{
            color: yellow;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-briefcase me-2"></i>Internship Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i
                                class="fas fa-home me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_applications.php"><i
                                class="fas fa-file-alt me-1"></i>My Applications</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php"><i
                                class="fas fa-user me-1"></i>Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php"><i
                                class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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

        <div class="profile-header">
            <div class="profile-avatar"><i class="fas fa-user"></i></div>
            <h2><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></h2>
            <p class="mb-0"><i class="fas fa-envelope me-2"></i><?php echo $student['email']; ?></p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="profile-card mt-4">
    <h5 class="mb-4 d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user-cog me-2" style="color: #667eea;"></i>Personal Information</span>
        <button class="btn btn-sm btn-outline-primary" id="editPersonalBtn">
            <i class="fas fa-edit me-1"></i>Edit
        </button>
    </h5>

    <div id="personalDisplay">
        <div class="info-row">
            <div class="info-label">First Name</div>
            <div class="info-value"><?php echo $student['first_name']; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Last Name</div>
            <div class="info-value"><?php echo $student['last_name']; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Roll Number</div>
            <div class="info-value"><?php echo $student['roll_number']; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Department</div>
            <div class="info-value"><?php echo $student['department']; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Year</div>
            <div class="info-value"><?php echo $student['year']; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Email</div>
            <div class="info-value"><?php echo $student['email']; ?></div>
        </div>
    </div>
    
    <form method="POST" id="editPersonalForm" style="display: none;">
        <div class="mb-3">
            <label for="first_name" class="form-label"><strong>First Name</strong></label>
            <input type="text" class="form-control" name="first_name" id="first_name"
                value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label"><strong>Last Name</strong></label>
            <input type="text" class="form-control" name="last_name" id="last_name"
                value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="roll_number" class="form-label"><strong>Roll Number</strong></label>
            <input type="text" class="form-control" name="roll_number" id="roll_number"
                value="<?php echo htmlspecialchars($student['roll_number']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="department" class="form-label"><strong>Department</strong></label>
            <select class="form-select" name="department" id="department" required>
                <?php
                $departments = ['Computer Science', 'Electronics & Computer Science', 'Mechanical', 'Civil'];
                foreach ($departments as $dept) {
                    $selected = ($student['department'] == $dept) ? 'selected' : '';
                    echo "<option value=\"$dept\" $selected>$dept</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="year" class="form-label"><strong>Year</strong></label>
            <select class="form-select" name="year" id="year" required>
                <?php
                $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
                foreach ($years as $yr) {
                    $selected = ($student['year'] == $yr) ? 'selected' : '';
                    echo "<option value=\"$yr\" $selected>$yr</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label"><strong>Email</strong></label>
            <input type="email" class="form-control" name="email" id="email"
                value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" id="cancelPersonalBtn">Cancel</button>
            <button type="submit" name="update_personal" class="btn btn-resume">Save Changes</button>
        </div>
    </form>
    </div>
            </div>

            <div class="col-md-6">
                <!-- Skills Card -->
                <div class="profile-card">
                    <h5 class="mb-4 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-cogs me-2" style="color:#667eea;"></i>Skills & Expertise</span>
                        <button class="btn btn-sm btn-outline-primary" id="editSkillsBtn"><i
                                class="fas fa-edit me-1"></i>Edit</button>
                    </h5>
                    <div id="skillsDisplay">
                        <div class="info-row">
                            <div class="info-label">Skills</div>
                            <div class="info-value">
                                <?php
                                $skills = explode(',', $student['skills']);
                                foreach ($skills as $skill)
                                    echo '<span class="badge me-2 mb-2" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%) !important;">' . trim($skill) . '</span>';
                                ?>
                            </div>
                        </div>
                    </div>
                    <form method="POST" id="editSkillsForm" style="display:none;">
                        <div class="mb-3"><label class="form-label"><strong>Edit Your Skills</strong></label>
                            <textarea name="updated_skills" class="form-control"
                                rows="3"><?php echo htmlspecialchars($student['skills']); ?></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                            <button type="submit" name="update_skills" class="btn btn-resume">Save Changes</button>
                        </div>
                    </form>
                </div>

                <!-- Resume Card -->
                <div class="profile-card mt-4">
                    <h5 class="mb-4"><i class="fas fa-file-alt me-2" style="color:#667eea;"></i>Resume</h5>
                    <div class="resume-section">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <?php
                            $file_ext = strtolower(pathinfo($student['resume_link'], PATHINFO_EXTENSION));
                            $icon_class = 'fa-file-pdf';
                            $icon_color = '#d32f2f';
                            if ($file_ext === 'doc' || $file_ext === 'docx') {
                                $icon_class = 'fa-file-word';
                                $icon_color = '#2b# Continue Resume Card
579a';
                            }
                            ?>
                            <i class="fas <?php echo $icon_class; ?> fa-2x"
                                style="color: <?php echo $icon_color; ?>;"></i>
                            <span class="ms-3">
                                <strong>Current Resume</strong><br>
                                <small class="text-muted"><?php echo basename($student['resume_link']); ?></small>
                            </span>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="../download_resume.php?file=<?php echo urlencode($student['resume_link']); ?>&download=1"
                                class="btn btn-outline-primary flex-grow-1">
                                <i class="fas fa-download me-2"></i>Download
                            </a>
                        </div>

                        <hr class="my-4">

                        <form method="POST" enctype="multipart/form-data" id="resumeForm">
                            <label class="form-label"><strong>Update Resume</strong></label>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="file-upload-wrapper flex-grow-1">
                                    <label class="file-upload-label w-100 text-center mb-0">
                                        <i class="fas fa-upload me-2"></i><span id="fileText">Choose New File</span>
                                    </label>
                                    <input type="file" name="new_resume" id="new_resume" class="file-upload-input"
                                        accept=".pdf,.doc,.docx">
                                </div>
                                <button type="submit" class="btn btn-resume" id="uploadBtn" disabled>
                                    <i class="fas fa-check me-2"></i>Update
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>Accepted: PDF, DOC, DOCX (Max 5MB)
                            </small>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // Resume file upload preview & validation
            document.getElementById('new_resume').addEventListener('change', function (e) {
                const file = e.target.files[0];
                const fileText = document.getElementById('fileText');
                const uploadBtn = document.getElementById('uploadBtn');

                if (file) {
                    const fileName = file.name;
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    const fileExt = fileName.split('.').pop().toLowerCase();

                    if (!['pdf', 'doc', 'docx'].includes(fileExt)) {
                        alert('Please upload only PDF, DOC, or DOCX files!');
                        e.target.value = '';
                        uploadBtn.disabled = true;
                        fileText.textContent = 'Choose New File';
                        return;
                    }

                    if (file.size > 5242880) {
                        alert('File size must be less than 5MB!');
                        e.target.value = '';
                        uploadBtn.disabled = true;
                        fileText.textContent = 'Choose New File';
                        return;
                    }

                    fileText.innerHTML = `${fileName} (${fileSize} MB)`;
                    uploadBtn.disabled = false;
                } else {
                    fileText.textContent = 'Choose New File';
                    uploadBtn.disabled = true;
                }
            });

            // Skills edit toggle
            document.getElementById('editSkillsBtn').addEventListener('click', function () {
                document.getElementById('skillsDisplay').style.display = 'none';
                document.getElementById('editSkillsForm').style.display = 'block';
            });

            document.getElementById('cancelEditBtn').addEventListener('click', function () {
                document.getElementById('skillsDisplay').style.display = 'block';
                document.getElementById('editSkillsForm').style.display = 'none';
            });

            // Personal info edit toggle
            document.getElementById('editPersonalBtn').addEventListener('click', function () {
                document.getElementById('personalDisplay').style.display = 'none';
                document.getElementById('editPersonalForm').style.display = 'block';
            });

            document.getElementById('cancelPersonalBtn').addEventListener('click', function () {
                document.getElementById('personalDisplay').style.display = 'block';
                document.getElementById('editPersonalForm').style.display = 'none';
            });
        </script>
</body>

</html>