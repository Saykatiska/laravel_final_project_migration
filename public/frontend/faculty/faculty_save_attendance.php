<?php
session_start();
// Security Check: Ensure the user is logged in and is a Faculty member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "Faculty") {
    header("Location: ../login.php");
    exit;
}

require_once "../db_connect.php";

$faculty_id = $_SESSION['user_id'];
$selected_course_code = $_GET['course_code'] ?? 'N/A'; // Get the course code from URL for redirection

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: faculty_dashboard.php"); 
    exit;
}

// --- Get and Validate Initial Data ---
$course_id = (int) ($_POST['course_id'] ?? 0);
$attendance_date = date('Y-m-d'); // Use the current date for the attendance record

if ($course_id === 0) {
    $message = urlencode("Error: Cannot submit attendance without a valid course ID.");
    header("Location: faculty_dashboard.php?course_code={$selected_course_code}&error={$message}");
    exit;
}

// --- Prepare the Database Transaction and Statement ---

// The attendance_records table requires (student_id, course_id, attendance_date, status, recorded_by)
$sql_insert = "INSERT INTO school_attendance.attendance_records 
               (student_id, course_id, attendance_date, status, recorded_by) 
               VALUES (?, ?, ?, ?, ?)";

$stmt = null;
$conn->begin_transaction(); // Start transaction: ensures all inserts succeed or all fail

$success_count = 0;
$error_count = 0;

try {
    $stmt = $conn->prepare($sql_insert);
    
    // Bind parameters: 'iissi' -> integer(student_id), integer(course_id), string(date), string(status), integer(faculty_id)
    $stmt->bind_param("iissi", $student_id, $course_id_param, $attendance_date_param, $status, $recorded_by_param);

    // Set the static parameters once
    $course_id_param = $course_id;
    $attendance_date_param = $attendance_date;
    $recorded_by_param = $faculty_id;
    
    // --- Loop Through POST Data to Insert Records ---
    foreach ($_POST as $key => $status) {
        // We look for radio button names like 'student_123'
        if (strpos($key, 'student_') === 0) {
            $student_id = (int) str_replace('student_', '', $key);
            
            // Basic validation for the status value
            $valid_statuses = ['Present', 'Absent', 'Late', 'Excused'];
            $status = in_array($status, $valid_statuses) ? $status : 'Absent';
            
            // Execute the insert query for this student
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
                // Log the specific error for debugging
                error_log("DB Error inserting attendance for student $student_id: " . $stmt->error);
            }
        }
    }

    // --- Finalize Transaction and Redirect ---
    if ($error_count === 0 && $success_count > 0) {
        // Full success
        $conn->commit();
        $message = urlencode("Attendance recorded successfully for $success_count students.");
        header("Location: faculty_dashboard.php?course_code={$selected_course_code}&success={$message}");
    } else {
        // Total failure or partial failure
        $conn->rollback();
        $message = urlencode("Attendance submission failed. $success_count succeeded, $error_count failed. No changes were saved to the database.");
        header("Location: faculty_dashboard.php?course_code={$selected_course_code}&error={$message}");
    }

} catch (Exception $e) {
    if ($conn) $conn->rollback();
    error_log("Transaction failed: " . $e->getMessage());
    $message = urlencode("An unexpected error occurred: " . $e->getMessage());
    header("Location: faculty_dashboard.php?course_code={$selected_course_code}&error={$message}");

} finally {
    if ($stmt) $stmt->close();
    if ($conn) $conn->close();
    exit;
}
?>