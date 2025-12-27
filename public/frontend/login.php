<?php
session_start();

if (file_exists('db_connect.php')) {
    include 'db_connect.php'; 
} else {
    die("Error: Required file 'db_connect.php' not found.");
}


$error_message = '';

// --- MAINTENANCE MODE CHECK ---
$system_status = 'Online'; 
$settings_sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'System_Status'";
$settings_result = $conn->query($settings_sql);

if ($settings_result && $settings_result->num_rows > 0) {
    $row = $settings_result->fetch_assoc();
    $system_status = $row['setting_value'];
}

 // --- Variables for mode display ---
$is_maintenance = ($system_status === 'Maintenance');
$is_limited = ($system_status === 'Limited');
$maintenance_message = "The system is currently undergoing scheduled <b>maintenance</b>. Please check back later.";
$limited_message = "The system is currently operating in limited mode. Student accounts are temporarily blocked.";

// --- REDIRECT ALREADY LOGGED-IN USERS ---
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin_dashboard.php");
        exit();
    }
    
    // Automated Logout for non-admins during Maintenance or Limited modes
    if ($is_maintenance || ($is_limited && $_SESSION['role'] === 'Student')) {
        session_destroy();
        $error_message = $is_maintenance ? $maintenance_message : $limited_message;
        // Proceed to display the login form with the inline error message
    } else {
        // Normal redirection for non-admin users
        if ($_SESSION['role'] === 'Faculty') {
            header("Location: faculty/faculty_dashboard.php"); 
        } else if ($_SESSION['role'] === 'Student') {
            header("Location: student/student_dashboard.php"); 
        }
        exit();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $conn->real_escape_string($_POST['username']); 
    $password = $_POST['password'];

    // Prepare and execute the query to fetch the user by username
    $sql = "SELECT user_id, username, password, role, first_name FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username); //Prevents SQL Injection
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc(); // Fetch user data
        
        if ($password === $user['password']) { 
            
            // --- ENFORCEMENT CHECK FOR NEW LOGINS ---
            $is_admin = ($user['role'] === 'Admin');
            
            if ($is_maintenance && !$is_admin) { // If System is down, and user is NOT an Admin
                $error_message = $maintenance_message;
                $stmt->close();
                goto end_of_post;
                // Skip login process and display error message
            }
            if ($is_limited && $user['role'] === 'Student' && !$is_admin) { // If System is limited, and user is a Student
                $error_message = $limited_message;
                $stmt->close();
                goto end_of_post;
                // Skip login process and display error message
            }

            // Login successful: Start session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];

            // Redirect based on role
            if ($user['role'] === 'Admin') {
                header("Location: admin_dashboard.php");
            } else if ($user['role'] === 'Faculty') {
                header("Location: faculty/faculty_dashboard.php");
            } else if ($user['role'] === 'Student') {
                header("Location: student/student_dashboard.php");
            }
            exit();

        } else {
            // Password incorrect
            $error_message = "Invalid username or password.";
        }
    } else {
        // Username not found
        $error_message = "Invalid username or password.";
    }

    $stmt->close();
}

end_of_post:

close_db_connection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IskoTrack | System Login</title>
  <link rel="stylesheet" href="../css/core.css">
  <link rel="stylesheet" href="../css/app.css"> 
</head>
<body>
  <div class="container">

    <div class="left-section">
      <div class="overlay"></div>

      <div class="text-content">
        <h3>A Web Development Case Study</h3>
        <p>Presented to <b>Prof. Marilou Fernandez Novida</b><br>
          <i>Polytechnic University of the Philippines – San Juan Campus</i>
        </p>

        <div class="welcome-text">
          <h1>Hello,<br> welcome!</h1>
        </div>
      </div>

      <div class="developers">
        <p><b>DEVELOPED BY:</b></p>
        <div class="roles">
          <div>
            <p>Back-end Developer</p>
            <p>
              Tomas, Cydoel<br>
              Lapido, Jhanna Lou<br>
              Baniqued, Ysabella Nicole<br>
              Manuel, Alaiza Claine
            </p>
          </div>
          <div>
            <p>Front-end Developer</p>
            <p>
              Pinto, Kathleen<br>
              Tamalla, Trunkziszaq Mae
            </p>
          </p>
          </div>
        </div>
      </div>
    </div>

    <div class="right-section">
      <div class="login-box">
        <h2>SYSTEM LOGIN</h2>
        <p class="desc">Enter your username and password to sign in!</p>

        <?php 
        // Display the specific maintenance message if status is set
        if ($is_maintenance) {
            $error_message = $maintenance_message;
        } elseif ($is_limited) {
            $error_message = $limited_message;
        }

        // Display the error/status message inline
        if (!empty($error_message)): ?>
            <div class="message_error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" placeholder="Enter username" required>

          <label for="password">Password:</label>
          <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
          
          <button type="submit">Sign In</button>
        </form>
        
        <?php if ($is_maintenance || $is_limited): ?>
            <p style="text-align: center; margin-top: 15px; color: #888; font-size: 0.9em;">
                System Status: <b><?php echo htmlspecialchars($system_status); ?></b>.<br>Only Admins can log in during Maintenance.
            </p>
        <?php endif; ?>
        
      </div>
    </div>

  </div>
</body>
</html>