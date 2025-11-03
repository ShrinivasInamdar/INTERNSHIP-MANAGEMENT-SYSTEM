<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Management System</title>
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
        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 60px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 20px;
        }
        .hero-subtitle {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 40px;
        }
        .btn-custom {
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            margin: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .btn-outline-custom {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
        }
        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .features {
            margin-top: 60px;
        }
        .feature-card {
            text-align: center;
            padding: 30px;
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="hero-section text-center">
                    <i class="fas fa-briefcase feature-icon"></i>
                    <h1 class="hero-title">Internship Management System</h1>
                    <p class="hero-subtitle">Connect students with amazing internship opportunities</p>
                    
                    <div class="d-flex justify-content-center flex-wrap">
                        <a href="login.php" class="btn btn-primary-custom btn-custom">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="register.php" class="btn btn-outline-custom btn-custom">
                            <i class="fas fa-user-plus me-2"></i>Register as Student
                        </a>
                    </div>

                    <div class="features">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="feature-card">
                                    <i class="fas fa-search feature-icon"></i>
                                    <h4>Browse Internships</h4>
                                    <p>Explore diverse internship opportunities across departments</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="feature-card">
                                    <i class="fas fa-filter feature-icon"></i>
                                    <h4>Smart Filters</h4>
                                    <p>Filter by department, location, and stipend</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="feature-card">
                                    <i class="fas fa-chart-line feature-icon"></i>
                                    <h4>Track Applications</h4>
                                    <p>Monitor your application status in real-time</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>