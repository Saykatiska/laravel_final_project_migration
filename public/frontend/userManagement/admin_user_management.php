<?php
session_start();

// Check if user logged in is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

function handle_status_message() {
    $display_message = '';
    $message_status = '';

    if (isset($_GET['msg'])) {
        $display_message = htmlspecialchars(urldecode($_GET['msg']));
        
        // Determine status based on message content
        if (strpos($display_message, 'Success') !== false) {
            $message_status = 'success';
        } else {
            // Includes 'Error:' or other failure messages
            $message_status = 'error';
        }
    }
    
    return ['message' => $display_message, 'status' => $message_status];
}

// --- Fetch Users from Database ---

function fetch_users_by_role($conn, $role) {
    if (!$conn || $conn->connect_error) {
        // Log error and return empty array if connection is bad
        error_log("DB Connection failed in fetch_users_by_role.");
        return [];
    }
    
    $sql = "SELECT user_id, username, role, first_name, last_name, email 
            FROM users 
            WHERE role = '{$role}'
            ORDER BY last_name, first_name";
            
    $users = [];
    
    try {
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Database error fetching {$role} users: " . $e->getMessage());
        return [];
    }
    
    return $users;
}

// Process messages
$message_data = handle_status_message();
$display_message = $message_data['message'];
$message_status = $message_data['status'];

// Fetch data
$faculty_users = fetch_users_by_role($conn, 'Faculty');
$student_users = fetch_users_by_role($conn, 'Student');

// Clean up
if (function_exists('close_db_connection')) {
    // close_db_connection($conn); 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | User Management</title>
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
    
    <?php if (!empty($display_message)): ?>
        <div class="message-box <?php echo $message_status; ?>">
            <?php echo $display_message; ?>
        </div>
    <?php endif; ?>
    
    <main class="main">
        <section class="attendance-table">
            <div class="table-header-controls" style="margin-bottom: 0;">
                <h1 style=" padding: 0 10px 10px 10px;">User Management</h1>
                <nav class="sub-menu-inline">
                        <a href="admin_add_user.php" class="btn-primary" style="margin-bottom: 0px;">Add New User</a>
                </nav>
            </div>
            <hr>
            <div class="table-header-controls">
                <h2>Faculty</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($faculty_users) > 0): ?>
                        <?php foreach ($faculty_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><span class="role-<?php echo strtolower($user['role']); ?>"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <a href="admin_edit_user.php?id=<?php echo $user['user_id']; ?>">Edit</a> | 
                                    <a href="admin_delete_user.php?id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No Faculty users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <hr style="border: 0; border-top: 1px solid #ccc; margin: 30px 0;">

            <div class="table-header-controls">
                <h2>Student</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($student_users) > 0): ?>
                        <?php foreach ($student_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><span class="role-<?php echo strtolower($user['role']); ?>"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <a href="admin_edit_user.php?id=<?php echo $user['user_id']; ?>">Edit</a> | 
                                    <a href="admin_delete_user.php?id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No Student users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </section>
        <br>
    </main>
</body>
</html>