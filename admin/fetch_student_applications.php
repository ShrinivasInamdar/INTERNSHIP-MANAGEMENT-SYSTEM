<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    exit("Unauthorized access");
}

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id === 0) {
    exit("Invalid student ID");
}

$query = "SELECT a.application_id, a.applied_on, a.status, 
                 i.company_name, i.role, i.department
          FROM application a
          JOIN internship i ON a.internship_id = i.internship_id
          WHERE a.student_id = $student_id
          ORDER BY a.applied_on DESC";

$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo "<p class='text-muted mb-0'>This student hasn't applied for any internships yet.</p>";
} else {
    $count = $result->num_rows;
    echo "<p><strong>Total Internships Applied:</strong> <span class='badge button-color'>$count</span></p>";
    echo "<ul class='list-group'>";
    while ($row = $result->fetch_assoc()) {
        $status_color = ($row['status'] === 'accepted') ? 'success' : (($row['status'] === 'rejected') ? 'danger' : 'warning');
        echo "
            <li class='list-group-item d-flex justify-content-between align-items-start flex-column'>
                <div class='w-100'>
                    <strong>{$row['company_name']}</strong> — {$row['role']}<br>
                    <small class='text-muted'>
                        Department: {$row['department']} | Applied on: " . date('M d, Y', strtotime($row['applied_on'])) . "
                    </small>
                </div>
                <span class='badge bg-$status_color align-self-end mt-2'>{$row['status']}</span>
            </li>
        ";
    }
    echo "</ul>";
}
?>
<style>
    .button-color{
        background-color:green;
    }
</style>