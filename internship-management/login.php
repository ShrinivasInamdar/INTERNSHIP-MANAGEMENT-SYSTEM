<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password!";
    } else {
        // Check if user exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password']) || ($user['email'] === 'admin' && $password === 'admin123')) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Get additional info based on role
                if ($user['role'] === 'student') {
                    $student_query = "SELECT * FROM student WHERE user_id = " . $user['user_id'];
                    $student_result = $conn->query($student_query);
                    if ($student_result->num_rows > 0) {
                        $student = $student_result->fetch_assoc();
                        $_SESSION['student_id'] = $student['student_id'];
                        $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                    }
                }

                // Redirect based on role
                redirectToDashboard();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Email not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Internship Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
        }
        .login-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        .login-icon {
            font-size: 4rem;
            color: #667eea;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
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
        .admin-hint {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h2 class="login-title">Welcome Back</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>

                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>

                        <div class="text-center mt-3">
                            <p>Don't have an account? <a href="register.php" style="color: #667eea; font-weight: 600;">Register here</a></p>
                            <a href="index.php" style="color: #999;">← Back to Home</a>
                        </div>
                    </form>

                    <div class="admin-hint">
                        <strong><i class="fas fa-info-circle me-2"></i>Admin Login:</strong><br>
                        Email: admin<br>
                        Password: admin123
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>