<?php
session_start();
// Include the database connection file
include '../db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$update_error = ''; // Initialize error variable

// --- Handle Profile Update POST Request ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_changes'])) {
    // Sanitize and retrieve data from the POST request
    $new_username = trim($_POST['username']);
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']);
    $new_email = trim($_POST['email']);
    
    // Simple validation
    if (empty($new_username) || empty($new_first_name) || empty($new_last_name) || empty($new_email)) {
        $update_error = "All fields are required.";
    } else {
        // Prepare the secure UPDATE SQL statement
        // Updating username, first_name, last_name, and email in the users table
        $update_sql = "
            UPDATE school_attendance.users 
            SET username = ?, first_name = ?, last_name = ?, email = ?
            WHERE user_id = ?
        ";
        
        $stmt_update = $conn->prepare($update_sql);
        
        if ($stmt_update) {
            // "ssssi" means: 4 strings (username, first_name, last_name, email), 1 integer (user_id)
            $stmt_update->bind_param("ssssi", $new_username, $new_first_name, $new_last_name, $new_email, $user_id);
            
            if ($stmt_update->execute()) {
                // Success: Redirect to the same page with a success parameter to show the message
                header("Location: student_edit_profile.php?update=success");
                exit();
            } else {
                $update_error = "Error updating record: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $update_error = "Database preparation error: " . $conn->error;
        }
    }
}


// --- Fetch Current User Details (for displaying the form and profile data) ---
$sql = "SELECT username, user_id, first_name, last_name, email, role FROM school_attendance.users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// --- Fetch Enrolled Subjects Count ---
$sql_enrollments = "
    SELECT 
        COUNT(DISTINCT course_id) AS enrolled_subjects
    FROM 
        school_attendance.enrollments
    WHERE 
        student_id = ?
";
$stmt_enrollments = $conn->prepare($sql_enrollments);
$stmt_enrollments->bind_param("i", $user_id);
$stmt_enrollments->execute();
$result_enrollments = $stmt_enrollments->get_result();
$enrolled_subjects = $result_enrollments->fetch_assoc()['enrolled_subjects'];
$stmt_enrollments->close();

// Close main connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student | Edit Profile</title>
    <link rel="stylesheet" href="../style2.css" />
    <link rel="stylesheet" href="student-style.css" />
</head>
<body>
    <div class="nav-bar">
        <header>
            <div class="logo">
                <a href="student_dashboard.php" style="display: flex; align-items: center; text-decoration: none; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6" viewBox="0 0 20 20" fill="currentColor" style="width: 24px; height: 24px; margin-right: 8px;">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                </a>
            </div>
            <nav class="top-right-menu">
                <ul>
                    <li><a href="student_dashboard.php" style="color: white; text-decoration: none;">Home</a></li>
                    <li class="active-nav"><a href="student_edit_profile.php" style="color: white; text-decoration: none;">Profile</a></li>
                    <li><a href="../logout.php" style="color: white; text-decoration: none;">Logout</a></li>
                </ul>
            </nav>
        </header>
    </div>

    <div class="container">
        <aside class="details">
            <h3>Student Details</h3>
            <hr class="details-line">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>No. Of Subject:</strong> <?php echo htmlspecialchars($enrolled_subjects); ?></p>
            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($user['user_id']); ?></p>
        </aside>

        <main class="main">
            <section class="profile-header">
                <div class="avatar"></div>
                <div class="info">
                    <h2><?php echo htmlspecialchars($user['last_name']);?>, 
                    <?php echo htmlspecialchars($user['first_name']);?></h2>
                </div>
            </section>
            
            <?php if (isset($_GET['update']) && $_GET['update'] == 'success'): ?>
                <p style="color: green; font-weight: bold; margin-bottom: 20px;">Profile updated successfully!</p>
            <?php endif; ?>

            <?php if (!empty($update_error)): ?>
                <p style="color: red; font-weight: bold; margin-bottom: 20px;">Update failed: <?php echo htmlspecialchars($update_error); ?></p>
            <?php endif; ?>


            <section class="update-profile">
                <h3>Update Profile</h3>
                <form id="updateForm" method="POST">
                    
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"><br>
                    
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>"><br>

                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>"><br>

                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br><br>

                    <button type="submit" name="save_changes" class="update-btn">Save Changes</button>
                </form>
            </section>
        </main>
    </div>

    </body>
</html>