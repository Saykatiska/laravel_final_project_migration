<?php
session_start();

// Ensure only the Admin can access this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

$user_id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// Check if a valid ID was provided
if ($user_id_to_delete <= 0) {
    header("Location: admin_user_management.php?error=invalidid");
    exit();
}

// CRITICAL SAFETY CHECK: Prevent the admin from deleting their own account (User ID 1 in sample data)
if ($user_id_to_delete === $_SESSION['user_id']) {
    $message = "Error: You cannot delete your own active administrator account.";
    header("Location: admin_user_management.php?msg=" . urlencode($message));
    exit();
}

// Delete related attendance records (if any)
$sql_delete_attendance = "DELETE FROM attendance_records WHERE student_id = ? OR recorded_by = ?";
$stmt_attendance = $conn->prepare($sql_delete_attendance);
$stmt_attendance->bind_param("ii", $user_id_to_delete, $user_id_to_delete);
$stmt_attendance->execute();
$stmt_attendance->close();

// Delete related enrollment records (if the user is a student)
$sql_delete_enrollments = "DELETE FROM enrollments WHERE student_id = ?";
$stmt_enrollment = $conn->prepare($sql_delete_enrollments);
$stmt_enrollment->bind_param("i", $user_id_to_delete);
$stmt_enrollment->execute();
$stmt_enrollment->close();

// Unassign faculty from courses (if the user is a faculty member)
// UPDATE the faculty_id in courses to NULL or reassign them
$sql_unassign_courses = "UPDATE courses SET faculty_id = NULL WHERE faculty_id = ?";
$stmt_unassign = $conn->prepare($sql_unassign_courses);
$stmt_unassign->bind_param("i", $user_id_to_delete);
$stmt_unassign->execute();
$stmt_unassign->close();


// MAIN DELETE QUERY
$sql_delete_user = "DELETE FROM users WHERE user_id = ?";
$stmt_delete = $conn->prepare($sql_delete_user);
$stmt_delete->bind_param("i", $user_id_to_delete);

if ($stmt_delete->execute()) {
    // Check if any row was actually deleted
    if ($stmt_delete->affected_rows > 0) {
        $message = "Success: User ID " . $user_id_to_delete . " and all related data were successfully deleted.";
    } else {
        $message = "Error: User ID " . $user_id_to_delete . " was not found.";
    }
} else {
    $message = "Error: Failed to delete user. " . $stmt_delete->error;
}

$stmt_delete->close();
close_db_connection($conn);

// REDIRECT BACK TO DASHBOARD
header("Location: admin_user_management.php?msg=" . urlencode($message));
exit();
?>