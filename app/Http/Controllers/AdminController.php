<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Fetch Counts using Laravel Query Builder
        $coursesCount = DB::table('courses')->count();
        $uniqueEnrolled = DB::table('enrollments')->distinct('student_id')->count('student_id');
        $totalEnrollmentRecords = DB::table('enrollments')->count();
        
        $facultyCount = DB::table('users')->where('role', 'Faculty')->count();
        $studentCount = DB::table('users')->where('role', 'Student')->count();

        // 2. Calculate Percentages
        $totalUsers = $facultyCount + $studentCount;
        $facultyPercent = $totalUsers > 0 ? round(($facultyCount / $totalUsers) * 100) : 0;
        $studentPercent = $totalUsers > 0 ? 100 - $facultyPercent : 0;

        // 3. Pass data to the view
        return view('admin_dashboard', [
            'first_name' => Session::get('first_name', 'Admin'),
            'stats' => [
                'courses' => $coursesCount,
                'enrolled' => $uniqueEnrolled,
                'total_enrollment_records' => $totalEnrollmentRecords,
                'users' => $totalUsers,
                'faculty' => $facultyCount,
                'students' => $studentCount,
                'faculty_percent' => $facultyPercent,
                'students_percent' => $studentPercent,
            ]
        ]);
    }

    public function settings()
    {
        // Fetch current status or default to 'Online'
        $setting = DB::table('system_settings')->where('setting_key', 'System_Status')->first();
        $currentStatus = $setting ? $setting->setting_value : 'Online';

        return view('admin_settings', ['current_status' => $currentStatus]);
    }

    public function updateSettings(Request $request)
    {
        $newStatus = $request->input('system_status');

        // Laravel's updateOrInsert handles the logic of checking if it exists first
        DB::table('system_settings')->updateOrInsert(
            ['setting_key' => 'System_Status'],
            ['setting_value' => $newStatus]
        );

        return back()->with('success_message', "System setting updated to $newStatus!");
    }

    // Add to AdminController.php

    public function userManagement()
    {
        // Fetch all Faculty and Students in one query, ordered by name
        $allUsers = DB::table('users')
            ->whereIn('role', ['Faculty', 'Student'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Organize them using Laravel Collections
        $faculty = $allUsers->where('role', 'Faculty');
        $students = $allUsers->where('role', 'Student');

        return view('admin_user_management', [
            'faculty_users' => $faculty,
            'student_users' => $students
        ]);
    }

    public function enrollmentManagement()
    {
        // Fetch all enrollments with course and student details
        $enrollments = DB::table('enrollments as e')
            ->join('courses as c', 'e.course_id', '=', 'c.course_id')
            ->join('users as u', 'e.student_id', '=', 'u.user_id')
            ->select('e.enrollment_id', 'c.course_code', 'c.course_name', 'u.first_name', 'u.last_name', 'u.username')
            ->orderBy('c.course_code')
            ->orderBy('u.last_name')
            ->get();

        // Group the results by course_code automatically!
        $grouped = $enrollments->groupBy('course_code');

        return view('admin_enrollment_management', [
            'grouped_enrollments' => $grouped,
            'total_count' => $enrollments->count()
        ]);
    }

    public function deleteEnrollment($id)
    {
        DB::table('enrollments')->where('enrollment_id', $id)->delete();

        return back()->with('success_message', 'Enrollment record deleted successfully.');
    }

    public function courseManagement()
    {
        // Fetch all courses and join with users to get faculty names
        $courses = DB::table('courses as c')
            ->leftJoin('users as u', 'c.faculty_id', '=', 'u.user_id')
            ->select('c.course_id', 'c.course_code', 'c.course_name', 'u.first_name', 'u.last_name')
            ->orderBy('c.course_code')
            ->get();

        return view('admin_course_management', [
            'courses' => $courses
        ]);
    }
}