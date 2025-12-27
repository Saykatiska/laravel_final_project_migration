<?php
session_start();

// Authorization Check: Must be Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

include '../db_connect.php'; 
$message = '';
$status_class = '';

// Define the close function if it's not in db_connect.php
if (!function_exists('close_db_connection')) {
    function close_db_connection($conn) {
        if ($conn) {
            $conn->close();
        }
    }
}


// --- HANDLE FORM SUBMISSION (NEW) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = (int)$_POST['student_id'];
    $course_id = (int)$_POST['course_id'];

    if ($student_id > 0 && $course_id > 0) {
        // Check for duplicate enrollment first (using prepared statements)
        $check_sql = "SELECT enrollment_id FROM enrollments WHERE student_id = ? AND course_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $student_id, $course_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            // INSERT NEW ENROLLMENT (using prepared statements)
            $sql = "INSERT INTO enrollments (student_id, course_id, enroll_date) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $student_id, $course_id);
            
            if ($stmt->execute()) {
                $message = "Success: Student successfully enrolled in the course!";
                $status_class = "success";
            } else {
                $message = "Error: Could not enroll student. " . $stmt->error;
                $status_class = "error";
            }
            $stmt->close();
        } else {
            $message = "Error: Student is already enrolled in this course.";
            $status_class = "error";
        }
        $check_stmt->close();
    } else {
        $message = "Error: Invalid Student or Course selected.";
        $status_class = "error";
    }
    
    // REDIRECT BACK TO MAIN ENROLLMENT PAGE with status message
    $redirect_url = "admin_enrollment_management.php?message=" . urlencode($message) . "&status=" . $status_class;
    close_db_connection($conn);
    header("Location: " . $redirect_url);
    exit();
}


// --- FETCH DROPDOWN DATA (Students and Courses) ---
// Fetch Students
$student_sql = "SELECT user_id, first_name, last_name, username FROM users WHERE role = 'Student' ORDER BY last_name";
$student_result = $conn->query($student_sql);
$students = [];
if ($student_result) {
    while($row = $student_result->fetch_assoc()) { $students[] = $row; }
}

// Fetch Courses
$course_sql = "SELECT course_id, course_code, course_name FROM courses ORDER BY course_code";
$course_result = $conn->query($course_sql);
$courses = [];
if ($course_result) {
    while($row = $course_result->fetch_assoc()) { $courses[] = $row; }
}

close_db_connection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Enroll New Student</title>
    <link rel="stylesheet" href="../../css/core.css">
    <link rel="stylesheet" href="../../css/app.css">
</head>
<body>
    <div class="nav-bar">
    <header>
        <div class="logo">
                    <a href="admin_enrollment_management.php" style="display: flex; align-items: center; text-decoration: none; color: white;">
                        <svg 
                            version="1.1" 
                            id="Layer_1" 
                            xmlns="http://www.w3.org/2000/svg" 
                            xmlns:xlink="http://www.w3.org/1999/xlink" 
                            x="0px" 
                            y="0px"
                            width="50px" 
                            height="50px"
                            viewBox="0 0 300 300" 
                            fill="currentColor"
                            xml:space="preserve"
                        >
                            <path d="M194.839,115.958l1.576,0.678c0.668,0.287,1.361,0.423,2.044,0.423c2.006,0,3.915-1.174,4.76-3.134c1.127-2.627-0.085-5.672-2.712-6.802l-1.578-0.678c-2.627-1.13-5.67,0.085-6.802,2.711C191,111.784,192.212,114.828,194.839,115.958z M240.331,157.468v-29.814c0-2.858-2.319-5.177-5.178-5.177h-88.727c-2.86,0-5.178,2.318-5.178,5.177c0,2.859,2.317,5.179,5.178,5.179h63.576l-63.576,27.333l-75.619-32.512l75.619-32.509l33.941,14.592c2.627,1.129,5.674-0.085,6.802-2.712c1.128-2.627-0.085-5.672-2.712-6.801L148.47,84.751c-1.305-0.561-2.784-0.561-4.09,0l-88.729,38.146c-1.9,0.817-3.133,2.688-3.133,4.756c0,2.07,1.232,3.941,3.133,4.758l88.729,38.146c0.652,0.279,1.349,0.42,2.045,0.42c0.695,0,1.392-0.141,2.045-0.42l48.833-20.996v35.826c-28.496,26.367-73.263,26.367-101.761,0l0.002-9.137c0-2.86-2.317-5.178-5.178-5.178l0,0c-2.857,0-5.176,2.317-5.176,5.176l-0.002,11.357c0,1.393,0.561,2.725,1.555,3.699c16.03,15.711,37.225,24.363,59.682,24.363c22.455,0,43.65-8.652,59.681-24.363c0.994-0.974,1.554-2.307,1.554-3.698l-0.002-42.497l22.318-9.595v21.952c-4.219,1.961-7.15,6.236-7.15,11.186c0,5.078,3.79,18.441,12.327,18.441c8.538,0,12.328-13.363,12.328-18.441C247.481,163.704,244.549,159.429,240.331,157.468z M235.153,175.34c-1.007-1.976-1.973-5.029-1.973-6.687c0-1.088,0.884-1.974,1.973-1.974c1.088,0,1.972,0.886,1.972,1.974C237.125,170.311,236.159,173.365,235.153,175.34zM90.369,165.68c2.858,0,5.176-2.317,5.176-5.177v-1.724c0-2.86-2.317-5.178-5.176-5.178c-2.859,0-5.178,2.317-5.178,5.178v1.724C85.191,163.363,87.509,165.68,90.369,165.68z"/>
                        </svg>
                    </a>
                </div>
        <nav class="top-right-menu">
            <ul>
                <li><a href="../admin_dashboard.php" style="color: white; text-decoration: none;">Home</a></li>
                <li><a href="../userManagement/admin_user_management.php" style="color: white; text-decoration: none;">Users</a></li>
                <li><a href="../courseManagement/admin_course_management.php" style="color: white; text-decoration: none;">Courses</a></li>
                <li class="active-nav"><a href="admin_enrollment_management.php" style="color: white; text-decoration: none;">Enrollment</a></li>
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
        <section class="form-container-box">
            
            <h2>Enroll a New Student</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $status_class; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin_add_enrollment.php">
                
                <label for="student_id">Select Student:</label>
                <select id="student_id" name="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['user_id']; ?>">
                            <?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' (' . $student['username'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="course_id">Select Course:</label>
                <select id="course_id" name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>">
                            <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <button type="submit"><strong>Enroll Student</strong></button>
            </form>
            
            <p><br><a href="admin_enrollment_management.php">
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