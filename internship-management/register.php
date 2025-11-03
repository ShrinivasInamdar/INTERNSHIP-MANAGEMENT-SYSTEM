<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/resumes/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $skills = sanitize($_POST['skills']);
    $year = sanitize($_POST['year']);
    $department = sanitize($_POST['department']);
    $roll_number = sanitize($_POST['roll_number']);

    // Validation
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name) || 
        empty($skills) || empty($year) || empty($department) || empty($roll_number)) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (!isset($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "Please upload your resume!";
    } else {
        // Handle file upload
        $file = $_FILES['resume'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        
        // Validate file
        if ($file_error !== 0) {
            $error = "Error uploading file!";
        } elseif (!in_array($file_ext, $allowed_extensions)) {
            $error = "Only PDF, DOC, and DOCX files are allowed!";
        } elseif ($file_size > 5242880) { // 5MB limit
            $error = "File size must be less than 5MB!";
        } else {
            // Check if email already exists
            $check_query = "SELECT * FROM users WHERE email = '$email'";
            $result = $conn->query($check_query);
            
            if ($result->num_rows > 0) {
                $error = "Email already registered!";
            } else {
                // Generate unique filename
                $new_filename = uniqid('resume_') . '_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert into users table
                    $user_query = "INSERT INTO users (email, password, role) VALUES ('$email', '$hashed_password', 'student')";
                    
                    if ($conn->query($user_query)) {
                        $user_id = $conn->insert_id;
                        
                        // Insert into student table
                        $student_query = "INSERT INTO student (user_id, first_name, last_name, resume_link, skills, year, department, roll_number) 
                                         VALUES ('$user_id', '$first_name', '$last_name', '$upload_path', '$skills', '$year', '$department', '$roll_number')";
                        
                        if ($conn->query($student_query)) {
                            $success = "Registration successful! You can now login.";
                        } else {
                            $error = "Error creating student profile: " . $conn->error;
                            // Delete uploaded file if database insertion fails
                            unlink($upload_path);
                        }
                    } else {
                        $error = "Error creating user account: " . $conn->error;
                        // Delete uploaded file if database insertion fails
                        unlink($upload_path);
                    }
                } else {
                    $error = "Error uploading resume file!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            margin: 0 auto;
        }
        .register-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-control, .form-select {
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
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
            display: block;
            padding: 12px;
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-upload-label:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .file-upload-label.has-file {
            border-color: #4caf50;
            background: #e8f5e9;
        }
        .file-info {
            margin-top: 10px;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2 class="register-title"><i class="fas fa-user-plus me-2"></i>Student Registration</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select" required>
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
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" required>
                            <option value="">Select Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Roll Number</label>
                    <input type="number" name="roll_number" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Skills (comma separated)</label>
                    <textarea name="skills" class="form-control" rows="3" placeholder="e.g., Python, JavaScript, React" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Resume (PDF, DOC, DOCX - Max 5MB)</label>
                    <div class="file-upload-wrapper">
                        <label class="file-upload-label" id="fileLabel">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: #667eea;"></i><br>
                            <span id="fileText">Click to upload or drag and drop</span>
                        </label>
                        <input type="file" name="resume" id="resume" class="file-upload-input" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div class="file-info">
                        <i class="fas fa-info-circle me-1"></i>Accepted formats: PDF, DOC, DOCX (Max size: 5MB)
                    </div>
                </div>

                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user-plus me-2"></i>Register
                </button>

                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php" style="color: #667eea; font-weight: 600;">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });

        // File upload display
        document.getElementById('resume').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const fileLabel = document.getElementById('fileLabel');
            const fileText = document.getElementById('fileText');
            
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                // Validate file type
                if (!['pdf', 'doc', 'docx'].includes(fileExt)) {
                    alert('Please upload only PDF, DOC, or DOCX files!');
                    e.target.value = '';
                    return;
                }
                
                // Validate file size
                if (file.size > 5242880) { // 5MB
                    alert('File size must be less than 5MB!');
                    e.target.value = '';
                    return;
                }
                
                fileLabel.classList.add('has-file');
                fileText.innerHTML = `<i class="fas fa-file-${fileExt === 'pdf' ? 'pdf' : 'word'} me-2"></i>${fileName} (${fileSize} MB)`;
            } else {
                fileLabel.classList.remove('has-file');
                fileText.textContent = 'Click to upload or drag and drop';
            }
        });
    </script>
</body>
</html>