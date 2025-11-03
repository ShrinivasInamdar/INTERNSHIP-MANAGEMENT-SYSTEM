<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get file path from parameter
$file_path = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file_path)) {
    die("File not specified!");
}

// Security: Only allow files from uploads/resumes directory
if (strpos($file_path, 'uploads/resumes/') !== 0) {
    die("Invalid file path!");
}

// Check if file exists
if (!file_exists($file_path)) {
    die("File not found!");
}

// Additional security checks
// If user is a student, they can only download their own resume
if (isStudent()) {
    $student_id = $_SESSION['student_id'];
    $query = "SELECT resume_link FROM student WHERE student_id = $student_id";
    $result = $conn->query($query);
    $student = $result->fetch_assoc();
    
    if ($student['resume_link'] !== $file_path) {
        die("Access denied! You can only download your own resume.");
    }
}

// Admins can download any resume (no additional check needed)

// Get file information
$file_name = basename($file_path);
$file_size = filesize($file_path);
$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Set appropriate content type
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$content_type = isset($content_types[$file_ext]) ? $content_types[$file_ext] : 'application/octet-stream';

// Set headers for file download
header('Content-Type: ' . $content_type);
header('Content-Disposition: inline; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($file_path);
exit();
?>