<?php
session_start();
// Check user session and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "Faculty") {
    header("Location: ../login.php");
    exit;
}
require_once "../db_connect.php";

$faculty_id = $_SESSION['user_id'];
$update_error = null; // Initialize error variable

// --- Handle POST Request for Profile Update (Security: Uses Prepared Statements) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve and Sanitize data from the POST request
    $username = trim($_POST['username']);
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    // Simple validation
    if (empty($username) || empty($first) || empty($last) || empty($email)) {
        $update_error = "All fields are required.";
    } else {

        // Prepare the UPDATE statement to include the 'username' field
        $stmt_update = $conn->prepare("
            UPDATE school_attendance.users 
            SET username=?, first_name=?, last_name=?, email=?
            WHERE user_id=?
        ");
        
        // 'ssssi': four string parameters and one integer
        $stmt_update->bind_param("ssssi", $username, $first, $last, $email, $faculty_id);

        if ($stmt_update->execute()) {
            // Successful update: Update session variables and redirect
            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $first;
            $_SESSION['last_name'] = $last;
            $_SESSION['email'] = $email;
            
            // Redirect to refresh the page and show success message
            header("Location: faculty_edit_profile.php?updated=1");
            exit;
        } else {
            // Handle error (e.g., duplicate email/username constraint)
            $update_error = "Error updating profile. Check if the username or email is already taken. Details: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}

// --- Initial or Refetch Faculty Details ---
// This runs on initial page load AND after a failed POST (to preserve error)
// AND after a successful redirect (to display new data).
$stmt_faculty = $conn->prepare("SELECT * FROM school_attendance.users WHERE user_id=?");
$stmt_faculty->bind_param("i", $faculty_id);
$stmt_faculty->execute();
$faculty = $stmt_faculty->get_result()->fetch_assoc();
$stmt_faculty->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty | Edit Profile</title>
    <link rel="stylesheet" href="../../css/core.css" />
    <link rel="stylesheet" href="../../css/app.css" />

</head>
<body>

<div class="nav-bar">
    <header>
        <div class="logo">
            <a href="faculty_dashboard.php" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6" viewBox="0 0 20 20" fill="currentColor" style="width: 24px; height: 24px; margin-right: 8px;">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            </a>
        </div>
        <nav class="top-right-menu">
            <ul>
                <li><a href="faculty_dashboard.php" style="color: white; text-decoration: none;">Home</a></li>
                <li class="active-nav"><a href="faculty_edit_profile.php" style="color: white; text-decoration: none;">Profile</a></li>
                <li><a href="../logout.php" style="color: white; text-decoration: none;">Logout</a></li>
            </ul>
        </nav>
    </header>
    </div>

<div class="container">

<aside class="details">
    <h3>Your Info</h3>
    <p><strong>Faculty ID:</strong> <?= htmlspecialchars($faculty_id); ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($faculty['first_name'] . " " . $faculty['last_name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($faculty['email']); ?></p>
</aside>

<main class="main">
<section class="attendance-table">
    <h3>Update Profile</h3>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div class="success-message" style="color: green; font-weight: bold;">Profile successfully updated!</div>
    <?php endif; ?>
    
    <?php if ($update_error !== null): // Check for error during POST attempt ?>
        <div class="error-message" style="color: red; font-weight: bold;"><?php echo htmlspecialchars($update_error); ?></div>
    <?php endif; ?>

    <form method="POST" action="faculty_edit_profile.php">

    <label for="username">Username:</label>
    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($faculty['username']); ?>" required><br>

    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($faculty['first_name']); ?>" required>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($faculty['last_name']); ?>" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($faculty['email']); ?>" required>

    <button class="update-btn" type="submit">Save Changes</button>
    </form>

</section>
</main>

</div>
</body>
</html>