<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class FacultyController extends Controller
{
    public function dashboard(Request $request)
    {
        $facultyId = Session::get('user_id');

        // 1. Get all courses assigned to this faculty
        $courses = DB::table('courses')
            ->where('faculty_id', $facultyId)
            ->get();

        // 2. Determine which course is selected
        $selectedCourseCode = $request->query('course_code', optional($courses->first())->course_code);
        
        $displayCourse = null;
        $students = [];

        if ($selectedCourseCode) {
            // Find the full course details
            $displayCourse = DB::table('courses')
                ->where('course_code', $selectedCourseCode)
                ->where('faculty_id', $facultyId)
                ->first();

            if ($displayCourse) {
                // Get students enrolled in this specific course
                $students = DB::table('enrollments')
                    ->join('users', 'enrollments.student_id', '=', 'users.user_id')
                    ->where('enrollments.course_id', $displayCourse->course_id)
                    ->where('users.role', 'Student')
                    ->select('users.user_id', 'users.first_name', 'users.last_name', 'users.email')
                    ->orderBy('users.last_name')
                    ->get();
            }
        }

        return view('faculty_dashboard', [
            'courses' => $courses,
            'displayCourse' => $displayCourse,
            'students' => $students,
            'selectedCourseCode' => $selectedCourseCode
        ]);
    }

    public function editProfile()
    {
        $facultyId = session('user_id');
        
        // Fetch faculty details
        $faculty = DB::table('users')->where('user_id', $facultyId)->first();

        return view('faculty_edit_profile', compact('faculty'));
    }

    public function updateProfile(Request $request)
    {
        $facultyId = session('user_id');

        // 1. Validation (Ensures uniqueness but ignores the current user's ID)
        $validated = $request->validate([
            'username'   => 'required|string|max:255|unique:users,username,' . $facultyId . ',user_id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $facultyId . ',user_id',
        ]);

        // 2. Update Database
        DB::table('users')->where('user_id', $facultyId)->update($validated);

        // 3. Update Session data
        session([
            'username'   => $validated['username'],
            'first_name' => $validated['first_name'],
        ]);

        return redirect('/faculty/profile')->with('success_message', 'Profile successfully updated!');
    }

    public function saveAttendance(Request $request)
    {
        $facultyId = session('user_id');
        $courseId = $request->input('course_id');
        $courseCode = $request->query('course_code', 'N/A');

        if (!$courseId) {
            return back()->with('error_message', 'Error: Valid course ID is required.');
        }

        try {
            DB::transaction(function () use ($request, $courseId, $facultyId) {
                $attendanceDate = now()->toDateString(); // Gets '2026-01-09'
                $data = $request->all();

                foreach ($data as $key => $status) {
                    // Check if the input key matches the 'student_ID' pattern
                    if (strpos($key, 'student_') === 0) {
                        $studentId = str_replace('student_', '', $key);

                        DB::table('attendance_records')->updateOrInsert(
                            // 1. The Conditions (The "Unique Key")
                            [
                                'student_id'      => $studentId,
                                'course_id'       => $courseId,
                                'attendance_date' => $attendanceDate,
                            ],
                            // 2. The Values to Update/Insert
                            [
                                'status'      => $status, // Updates Present/Absent
                                'recorded_by' => $facultyId,
                            ]
                        );
                    }
                }
            });

            return redirect('/faculty/faculty_dashboard?course_code=' . $courseCode)
                ->with('success_message', 'Attendance recorded successfully!');

        } catch (\Exception $e) {
            return back()->with('error_message', 'Failed to record attendance: ' . $e->getMessage());
        }
    }
}