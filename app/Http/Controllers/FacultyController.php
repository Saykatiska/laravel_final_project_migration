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

        // 2. Determine which course is selected (default to first one)
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
}