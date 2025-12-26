<?php
session_start();

// Authorization Check: Must be Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

include '../db_connect.php';

// Define fallback close function
if (!function_exists('close_db_connection')) {
    function close_db_connection($conn) {
        if ($conn) {
            $conn->close();
        }
    }
}


$message = '';
$status = '';

// --- HANDLE FORM SUBMISSION (ADD COURSE) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $faculty_id = !empty($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : null;

    if (!empty($course_code) && !empty($course_name) && !empty($faculty_id)) {

        // Check for duplicate course code
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
        $checkStmt->bind_param("s", $course_code);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            // Duplicate found
            $message = "Error: Course code '{$course_code}' already exists. Please use a unique course code.";
            $status = "error";
        } else {
            // Insert the new course
            $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, faculty_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $course_code, $course_name, $faculty_id);

            if ($stmt->execute()) {
                $message = "Success: New course " . htmlspecialchars($course_name) . " (Code: " . htmlspecialchars($course_code) . ") has been added successfully.";
                $status = "success";
            } else {
                $message = "Error: Could not create course. " . $stmt->error;
                $status = "error";
            }
            $stmt->close();
        }
    } else {
        $message = "Error: Please fill in all required fields (Course Code, Course Name, and Faculty).";
        $status = "error";
    }

    close_db_connection($conn);

    // Redirect with feedback
    $redirect_url = "admin_course_management.php?message=" . urlencode($message) . "&status=" . $status;
    header("Location: " . $redirect_url);
    exit();
}

// --- FETCH FACULTY LIST (for dropdown) ---
$faculty_sql = "SELECT user_id, first_name, last_name FROM users WHERE role = 'Faculty' ORDER BY last_name";
$faculty_result = $conn->query($faculty_sql);
$faculty_members = [];
if ($faculty_result && $faculty_result->num_rows > 0) {
    while ($row = $faculty_result->fetch_assoc()) {
        $faculty_members[] = $row;
    }
}

close_db_connection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Add New Course</title>
    <link rel="stylesheet" href="../style2.css">
    <link rel="stylesheet" href="admin-course-style.css">
    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
    <header>
        <div class="logo">
                    <a href="admin_course_management.php" style="display: flex; align-items: center; text-decoration: none; color: white;">
                        <svg 
                            version="1.1" 
                            id="FileCabinet_Simplified" 
                            xmlns="http://www.w3.org/2000/svg" 
                            xmlns:xlink="http://www.w3.org/1999/xlink" 
                            x="0px" 
                            y="0px"
                            width="30px" height="30px" viewBox="0 0 128 128" 
                            fill="currentColor"
                            xml:space="preserve"
                        >
                            <path d="M119.779,112.895H9.038c-2.952,0-5.85-2.966-5.85-5.988V95.067H124.82v12 C124.82,109.932,122.934,112.895,119.779,112.895z M7.194,99.072v7.834c0,0.815,1.086,1.983,1.844,1.983h110.741c0.594,0,1.037-0.962,1.037-1.822v-7.995H7.194zM46.271,127.985l-1.576-3.683c1.248-0.534,2.285-1.015,2.847-1.32l0.171-0.094c0.222-0.121,0.499-0.272,0.566-0.333c0.019-0.034,0.156-0.299,0.383-1.437c0.281-1.407,0.247-3.959,0.217-6.21c-0.021-1.567-0.041-3.049,0.046-4.105l3.992,0.333c-0.072,0.863-0.053,2.25-0.033,3.719c0.035,2.569,0.07,5.227-0.294,7.049c-0.556,2.789-1.193,3.536-2.955,4.499l-0.17,0.094C48.541,126.998,47.068,127.645,46.271,127.985zM81.605,128c-1-0.432-2.369-1.041-3.209-1.496l-0.119-0.065c-1.895-1.044-2.389-1.808-2.932-4.536c-0.365-1.824-0.33-4.442-0.295-6.974c0.02-1.44,0.039-2.801-0.033-3.661l3.992-0.326c0.086,1.05,0.066,2.503,0.047,4.041c-0.031,2.216-0.064,4.727,0.217,6.137c0.223,1.115,0.346,1.428,0.4,1.514c0.035,0.021,0.313,0.175,0.537,0.299l0.107,0.059c0.537,0.291,1.564,0.768,2.875,1.333L81.605,128zM42.255,123.945h43.509v4.005h-43.509zM124.783,99.143H3.179V28.936c0-2.865,2.343-5.196,5.223-5.196h11.562v4.005H8.402c-0.672,0-1.218,0.534-1.218,1.19v66.202h113.593V28.936c0-0.657-0.547-1.19-1.219-1.19h-11.523V23.74h11.523c2.881,0,5.225,2.331,5.225,5.196V99.143z"/>

                            <path d="M30.158,89.805H12.375V33.005h7.589v4.005h-3.584V85.8h13.778zM115.465,89.805H98.035V85.8h13.424V37.01h-3.424V33.005h7.43zM98.477,27.392v60.41h4.006V27.392zM29.67,87.802H25.665V0.322h50.784v4.005H29.67zM102.824,21.058H81.607V0h4.004V17.053h17.213zM39.889,23.749h27.309v4.005H39.889zM39.889,34.518h46.803v4.005H39.889zM39.889,45.287h46.803v4.005H39.889zM39.889,56.054h46.803v4.005H39.889zM39.889,66.825h46.803v4.005H39.889zM39.889,77.592h46.803v4.005H39.889z"/>
                        </svg>
                    </a>
                </div>
        <nav class="top-right-menu">
            <ul>
                <li><a href="../admin_dashboard.php" style="color: white; text-decoration: none;">Home</a></li>
                <li><a href="../userManagement/admin_user_management.php" style="color: white; text-decoration: none;">Users</a></li>
                <li class="active-nav"><a href="admin_course_management.php" style="color: white; text-decoration: none;">Courses</a></li>
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
        <section class="form-container-box">
            <h1>Add New Course</h1>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $status; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin_add_course.php">
                <label for="course_code">Course Code:</label>
                <input type="text" id="course_code" name="course_code" 
                        value="<?php echo isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : ''; ?>" required><br>

                <label for="course_name">Course Name:</label>
                <input type="text" id="course_name" name="course_name" 
                        value="<?php echo isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : ''; ?>" required><br>

                <label for="faculty_id">Assigned Faculty:</label>
                <select id="faculty_id" name="faculty_id" required>
                    <option value="">-- Select Faculty --</option>
                    <?php foreach ($faculty_members as $faculty): ?>
                        <option value="<?php echo $faculty['user_id']; ?>"
                            <?php 
                                if (isset($_POST['faculty_id']) && $_POST['faculty_id'] == $faculty['user_id']) {
                                    echo 'selected';
                                }
                            ?>>
                            <?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <button type="submit"><strong>Add Course</strong></button>
            </form>
            <br>
            <p><br><a href="admin_course_management.php">
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