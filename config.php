<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'internship_management');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['role']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to check if user is student
function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Function to redirect based on role
function redirectToDashboard() {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } elseif (isStudent()) {
        header("Location: student/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

// Function to sanitize input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Function to login admin (plain-text)
function loginAdmin($email, $password) {
    global $conn;
    $email = sanitize($email);
    $password = sanitize($password);

    $query = "SELECT * FROM admin WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if ($password === $admin['password']) {
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['email'] = $admin['email'];
            redirectToDashboard();
        } else {
            return "Invalid password!";
        }
    } else {
        return "Admin account not found!";
    }
}

// Function to login student (hashed password)
function loginStudent($email, $password) {
    global $conn;
    $email = sanitize($email);

    $query = "SELECT * FROM student WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
        if (password_verify($password, $student['password'])) {
            $_SESSION['role'] = 'student';
            $_SESSION['user_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            redirectToDashboard();
        } else {
            return "Invalid password!";
        }
    } else {
        return "Student account not found!";
    }
}
?>