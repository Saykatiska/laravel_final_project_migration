<?php
session_start();

// Authorization Check: Must be Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Get the course ID from the URL
$course_id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Message placeholders
$message = "";
$status = "";

if ($course_id_to_delete > 0) {
    // Start a transaction (so both deletions happen together)
    $conn->begin_transaction();

    try {
        // Delete related enrollments first (avoid foreign key violation)
        $deleteEnrollments = $conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
        $deleteEnrollments->bind_param("i", $course_id_to_delete);
        $deleteEnrollments->execute();
        $deleteEnrollments->close();

        // Now safely delete the course
        $deleteCourse = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
        $deleteCourse->bind_param("i", $course_id_to_delete);
        $deleteCourse->execute();
        $deleteCourse->close();

        // Commit the transaction if all is good
        $conn->commit();

        $message = "Success: Course ID {$course_id_to_delete} and related enrollments were successfully deleted.";
        $status = "success";
    } catch (Exception $e) {
        // Rollback if anything fails
        $conn->rollback();
        $message = "Error: Could not delete course. " . $e->getMessage();
        $status = "error";
    }
} else {
    $message = "Error: Invalid course ID provided for deletion.";
    $status = "error";
}

close_db_connection($conn);

// Redirect back with feedback
$redirect_url = "admin_course_management.php?message=" . urlencode($message) . "&status=" . $status;
header("Location: " . $redirect_url);
exit();
?>
