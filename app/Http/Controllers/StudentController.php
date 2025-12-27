<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StudentController extends Controller
{
    public function dashboard()
    {
        $userId = Session::get('user_id');

        // 1. Fetch User Details
        $user = DB::table('users')->where('user_id', $userId)->first();

        // 2. Overall Attendance Stats
        $attendanceStats = DB::table('attendance_records')
            ->where('student_id', $userId)
            ->selectRaw('COUNT(attendance_id) as total_records')
            ->selectRaw('SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present_count')
            ->first();

        $totalRecords = $attendanceStats->total_records ?? 0;
        $presentCount = $attendanceStats->present_count ?? 0;
        $overallRate = $totalRecords > 0 ? ($presentCount / $totalRecords) * 100 : 0;

        // 3. Subject-wise Attendance Data
        $subjectData = DB::table('attendance_records as ar')
            ->join('courses as c', 'ar.course_id', '=', 'c.course_id')
            ->where('ar.student_id', $userId)
            ->select('c.course_name')
            ->selectRaw('COUNT(ar.attendance_id) as total_classes')
            ->selectRaw('SUM(CASE WHEN ar.status = "Present" THEN 1 ELSE 0 END) as present_count')
            ->selectRaw('SUM(CASE WHEN ar.status = "Absent" THEN 1 ELSE 0 END) as absent_count')
            ->groupBy('c.course_name')
            ->orderBy('c.course_name')
            ->get();

        // 4. Number of Enrolled Subjects
        $enrolledCount = DB::table('enrollments')
            ->where('student_id', $userId)
            ->distinct('course_id')
            ->count('course_id');

        return view('student_dashboard', [
            'user' => $user,
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'overall_rate' => $overallRate,
            'subject_data' => $subjectData,
            'enrolled_count' => $enrolledCount
        ]);
    }

    public function editProfile()
    {
        $userId = session('user_id');

        // 1. Fetch User Details
        $user = DB::table('users')->where('user_id', $userId)->first();

        // 2. Fetch Enrolled Subjects Count
        $enrolledCount = DB::table('enrollments')
            ->where('student_id', $userId)
            ->distinct('course_id')
            ->count('course_id');

        return view('student_edit_profile', [
            'user' => $user,
            'enrolled_count' => $enrolledCount
        ]);
    }

    public function updateProfile(Request $request)
    {
        $userId = session('user_id');

        // 1. Validate the input
        $validated = $request->validate([
            'username'   => 'required|string|max:255|unique:users,username,' . $userId . ',user_id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $userId . ',user_id',
        ]);

        // 2. Update the Database
        DB::table('users')->where('user_id', $userId)->update($validated);

        // 3. Redirect back with success message
        return redirect('/student/profile')->with('success_message', 'Profile updated successfully!');
    }
}