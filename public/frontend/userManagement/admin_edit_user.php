<?php
use Illuminate\Support\Facades\DB;
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}





$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$user = null;

// Check if user_id is valid
if ($user_id <= 0) {
    header("Location: ../admin_dashboard.php");
    exit();
}

// --- DISPLAY LOGIC (FETCH EXISTING DATA) ---
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Fetch the user's current data
    $user = DB::table('users')->where('user_id', $user_id)->first();
    
    if ($user) {
        $user = (array) $user;
        // Prevent Admin from editing another Admin's role (optional security measure)
        if ($user['role'] === 'Admin') {
            $message = "Error: You cannot edit the role of the main administrator.";
            $user = null;
        }
    } else {
        $message = "Error: User not found.";
        // Redirect if user ID is invalid
        header("Location: admin_dashboard.php?error=notfound");
        exit();
    }
}

// --- PROCESSING LOGIC (HANDLE FORM SUBMISSION) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_id > 0) {
    
    // Get and Sanitize Input
    $username = trim($_POST['username']);
    $password = $_POST['password']; 
    $role = $_POST['role'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    // Basic Validation
    if (!in_array($role, ['Faculty', 'Student']) || empty($username) || empty($first_name)) {
        $message = "Error: Invalid role or missing required fields.";
    } else {
        
        $update_data = [
            'username' => $username,
            'role' => $role,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
        ];
        
        // Checks if password needs updating (optional)
        if (!empty($password)) {
            $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            DB::table('users')->where('user_id', $user_id)->update($update_data);
            $message = "Success: User " . htmlspecialchars($username) . " updated successfully!";
            // Re-fetch data to show updated values in the form after success
            $user = (array) DB::table('users')->where('user_id', $user_id)->first();
        } catch (\Illuminate\Database\QueryException $e) {
             // Handle duplicate entry error
            if ($e->errorInfo[1] == 1062) {
                $message = "Error: Username or Email already exists for another user.";
            } else {
                $message = "Error: Could not update user. " . $e->getMessage();
            }
        }
    }
}

close_db_connection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Edit User</title>
    <link rel="stylesheet" href="../style.css"> 
    <link rel="stylesheet" href="../style2.css">
    <link rel="stylesheet" href="admin-user-style.css">

</head>
<body>
    <div class="nav-bar">
    <header>
        <div class="logo">
            <a href="admin_user_management.php" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <svg
                    viewBox="0 0 24 24" 
                    fill="currentColor" 
                    aria-hidden="true" 
                    style="color: white; margin-right: 8px; fill: white; width:30px; height:30px;"
                ><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.8 6-3.8 1.99 0 5.97 1.81 6 3.8-1.29 1.94-3.5 3.22-6 3.22z"
                /></svg>
            </a>
        </div>
        <nav class="top-right-menu">
            <ul>
                <li><a href="../admin_dashboard.php" style="color: white; text-decoration: none;">Home</a></li>
                <li class="active-nav"><a href="admin_user_management.php" style="color: white; text-decoration: none;">Users</a></li>
                <li><a href="../courseManagement/admin_course_management.php" style="color: white; text-decoration: none;">Courses</a></li>
                <li><a href="../enrollmentManagement/admin_enrollment_management.php" style="color: white; text-decoration: none;">Enrollment</a></li>
                <li><a href="../logout.php" style="color: white; text-decoration: none;">Logout</a></li>
                <li>
                    <a href="../admin_settings.php" class="btn-secondary">
                    <svg width="15px" height="15px" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="3" stroke="#ffffff" stroke-width="2"/>
                    <path 
                        d="M3.66122 10.6392C4.13377 10.9361 4.43782 11.4419 4.43782 11.9999C4.43781 12.558 4.13376 13.0638 3.66122 13.3607C3.33966 13.5627 3.13248 13.7242 2.98508 13.9163C2.66217 14.3372 2.51966 14.869 2.5889 15.3949C2.64082 15.7893 2.87379 16.1928 3.33973 16.9999C3.80568 17.8069 4.03865 18.2104 4.35426 18.4526C4.77508 18.7755 5.30694 18.918 5.83284 18.8488C6.07287 18.8172 6.31628 18.7185 6.65196 18.5411C7.14544 18.2803 7.73558 18.2699 8.21895 18.549C8.70227 18.8281 8.98827 19.3443 9.00912 19.902C9.02332 20.2815 9.05958 20.5417 9.15224 20.7654C9.35523 21.2554 9.74458 21.6448 10.2346 21.8478C10.6022 22 11.0681 22 12 22C12.9319 22 13.3978 22 13.7654 21.8478C14.2554 21.6448 14.6448 21.2554 14.8478 20.7654C14.9404 20.5417 14.9767 20.2815 14.9909 19.9021C15.0117 19.3443 15.2977 18.8281 15.7811 18.549C16.2644 18.27 16.8545 18.2804 17.3479 18.5412C17.6837 18.7186 17.9271 18.8173 18.1671 18.8489C18.693 18.9182 19.2249 18.7756 19.6457 18.4527C19.9613 18.2106 20.1943 17.807 20.6603 17C20.8677 16.6407 21.029 16.3614 21.1486 16.1272M20.3387 13.3608C19.8662 13.0639 19.5622 12.5581 19.5621 12.0001C19.5621 11.442 19.8662 10.9361 20.3387 10.6392C20.6603 10.4372 20.8674 10.2757 21.0148 10.0836C21.3377 9.66278 21.4802 9.13092 21.411 8.60502C21.3591 8.2106 21.1261 7.80708 20.6601 7.00005C20.1942 6.19301 19.9612 5.7895 19.6456 5.54732C19.2248 5.22441 18.6929 5.0819 18.167 5.15113C17.927 5.18274 17.6836 5.2814 17.3479 5.45883C16.8544 5.71964 16.2643 5.73004 15.781 5.45096C15.2977 5.1719 15.0117 4.6557 14.9909 4.09803C14.9767 3.71852 14.9404 3.45835 14.8478 3.23463C14.6448 2.74458 14.2554 2.35523 13.7654 2.15224C13.3978 2 12.9319 2 12 2C11.0681 2 10.6022 2 10.2346 2.15224C9.74458 2.35523 9.35523 2.74458 9.15224 3.23463C9.05958 3.45833 9.02332 3.71848 9.00912 4.09794C8.98826 4.65566 8.70225 5.17191 8.21891 5.45096C7.73557 5.73002 7.14548 5.71959 6.65205 5.4588C6.31633 5.28136 6.0729 5.18269 5.83285 5.15108C5.30695 5.08185 4.77509 5.22436 4.35427 5.54727C4.03866 5.78945 3.80569 6.19297 3.33974 7" 
                        stroke="#ffffff" 
                        stroke-width="1.5" 
                        stroke-linecap="round"/>
                    </svg>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    </div>
    
    <main class="main">
        <section class="edit-container-box"> 
            <h1>Edit User ID: <?php echo $user_id; ?></h1>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'Success') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($user): // Only display form if user data was fetched ?>
            <form method="POST" action="admin_edit_user.php?id=<?php echo $user_id; ?>">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>

                <label for="password">New Password (Leave blank to keep current):</label>
                <input type="password" id="password" name="password"><br>

                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="Faculty" <?php if ($user['role'] === 'Faculty') echo 'selected'; ?>>Faculty</option>
                    <option value="Student" <?php if ($user['role'] === 'Student') echo 'selected'; ?>>Student</option>
                </select><br>

                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required><br>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

                <button type="submit" name="update_user">Update User</button>
            </form>
            <?php endif; ?>
            <p><br><a href="admin_user_management.php">
                <svg class="back-button" style="
                    width:40px; 
                    height:40px; 
                    viewBox:0 0 24 24; 
                    fill:none; 
                    stroke:#800000; 
                    stroke-width:4; 
                    stroke-linecap:round; 
                    stroke-linejoin:round
                    ">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg></a></p>
        </section>
    </main>
</body>
</html>