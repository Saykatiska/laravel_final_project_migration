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

        DB::table('system_settings')->updateOrInsert(
            ['setting_key' => 'System_Status'],
            ['setting_value' => $newStatus]
        );

        return back()->with('success_message', "System setting updated to $newStatus!");
    }

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

    public function deleteEnrollment($id)
    {
        DB::table('enrollments')->where('enrollment_id', $id)->delete();

        return back()->with('success_message', 'Enrollment record deleted successfully.');
    }

    public function createUser()
    {
        return view('admin_add_user');
    }

    public function storeUser(Request $request)
    {
        // 1. Validate the input
        $validated = $request->validate([
            'username'   => 'required|string|unique:users,username|max:255',
            'password'   => 'required|string|min:4',
            'role'       => 'required|in:Faculty,Student',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
        ]);

        // 2. Insert into Database
        DB::table('users')->insert($validated);

        // 3. Redirect with success message
        return redirect('/admin/users')->with('success_message', "User {$validated['username']} created successfully!");
    }

    
    public function updateUser(Request $request, $id)
    {
        $validated = $request->validate([
            'username'   => 'required|string|max:255|unique:users,username,' . $id . ',user_id',
            'role'       => 'required|in:Faculty,Student',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $id . ',user_id',
        ]);

        $updateData = $validated;
        if ($request->filled('password')) {
            $updateData['password'] = $request->password; 
        }

        DB::table('users')->where('user_id', $id)->update($updateData);

        return redirect('/admin/users')->with('success_message', "User updated successfully!");
    }


    public function deleteUser($id)
    {
        // 1. Critical Safety Check: Prevent admin from deleting themselves
        if ($id == session('user_id')) {
            return back()->with('error_message', 'Error: You cannot delete your own active administrator account.');
        }

        try {
            DB::transaction(function () use ($id) {
                // 2. Delete related attendance records
                DB::table('attendance_records')
                    ->where('student_id', $id)
                    ->orWhere('recorded_by', $id)
                    ->delete();

                // 3. Delete related enrollment records
                DB::table('enrollments')
                    ->where('student_id', $id)
                    ->delete();

                // 4. Unassign faculty from courses
                DB::table('courses')
                    ->where('faculty_id', $id)
                    ->update(['faculty_id' => null]);

                // 5. Main User Delete
                DB::table('users')->where('user_id', $id)->delete();
            });

            return redirect('/admin/users')->with('success_message', "Success: User ID $id and all related data were deleted.");
        } catch (\Exception $e) {
            return redirect('/admin/users')->with('error_message', 'Error: Failed to delete user. ' . $e->getMessage());
        }
    }


    public function courseManagement()
    {
        // Fetch courses with faculty names
        $courses = DB::table('courses as c')
            ->leftJoin('users as u', 'c.faculty_id', '=', 'u.user_id')
            ->select('c.*', 'u.first_name', 'u.last_name')
            ->orderBy('c.course_code')
            ->get();

        // Fetch faculty members for the dropdowns
        $faculty = DB::table('users')->where('role', 'Faculty')->orderBy('last_name')->get();

        return view('admin_course_management', [
            'courses' => $courses,
            'faculty_members' => $faculty
        ]);
    }

    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'course_code' => 'required|string|unique:courses,course_code|max:50',
            'course_name' => 'required|string|max:255',
            'faculty_id'  => 'required|exists:users,user_id',
        ]);

        DB::table('courses')->insert($validated);

        return redirect('/admin/courses')->with('success_message', "Course {$validated['course_name']} added successfully!");
    }

    public function updateCourse(Request $request, $id)
    {
        $validated = $request->validate([
            'course_code' => 'required|string|max:50|unique:courses,course_code,'.$id.',course_id',
            'course_name' => 'required|string|max:255',
            'faculty_id'  => 'required|exists:users,user_id',
        ]);

        DB::table('courses')->where('course_id', $id)->update($validated);

        return redirect('/admin/courses')->with('success_message', "Course updated successfully!");
    }

    public function deleteCourse($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. Delete attendance records linked to this course
                DB::table('attendance_records')->where('course_id', $id)->delete();

                // 2. Delete enrollments to satisfy foreign key constraints
                DB::table('enrollments')->where('course_id', $id)->delete();

                // 3. Finally, delete the course itself
                DB::table('courses')->where('course_id', $id)->delete();
            });

            return redirect('/admin/courses')->with('success_message', "Course and all related data (attendance & enrollment) deleted.");
        } catch (\Exception $e) {
            return redirect('/admin/courses')->with('error_message', "Delete failed: " . $e->getMessage());
        }
    }

    public function enrollmentManagement()
    {
        // 1. Fetch current enrollments
        $enrollments = DB::table('enrollments as e')
            ->join('courses as c', 'e.course_id', '=', 'c.course_id')
            ->join('users as u', 'e.student_id', '=', 'u.user_id')
            ->select('e.enrollment_id', 'c.course_code', 'c.course_name', 'u.first_name', 'u.last_name', 'u.username')
            ->orderBy('c.course_code')
            ->orderBy('u.last_name')
            ->get();

        $grouped = $enrollments->groupBy('course_code');

        // 2. Fetch dropdown data for the Popup
        $students = DB::table('users')->where('role', 'Student')->orderBy('last_name')->get();
        $courses = DB::table('courses')->orderBy('course_code')->get();

        return view('admin_enrollment_management', [
            'grouped_enrollments' => $grouped,
            'total_count' => $enrollments->count(),
            'students' => $students,
            'courses' => $courses
        ]);
    }

    public function storeEnrollment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,user_id',
            'course_id'  => 'required|exists:courses,course_id',
        ]);

        // Check for duplicate
        $exists = DB::table('enrollments')
            ->where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($exists) {
            return back()->with('error_message', 'Error: Student is already enrolled in this course.');
        }

        DB::table('enrollments')->insert([
            'student_id' => $request->student_id,
            'course_id'  => $request->course_id,
            'enroll_date' => now()
        ]);

        return redirect('/admin/enrollment')->with('success_message', 'Student successfully enrolled!');
    }
}