<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty | Home</title>
    <link rel="stylesheet" href="{{ asset('css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
</head>
<body>
    <div class="nav-bar">
        <header>
            <div class="logo">
                <a href="{{ url('/faculty/faculty_dashboard') }}" style="display: flex; align-items: center; text-decoration: none; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6" viewBox="0 0 20 20" fill="currentColor" style="width: 24px; height: 24px; margin-right: 8px;">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                </a>
            </div>
            <nav class="top-right-menu">
                <ul>
                    <li class="active-nav"><a href="{{ url('/faculty/faculty_dashboard') }}">Home</a></li>
                    <li><a href="{{ url('/faculty/profile') }}">Profile</a></li>
                    <li><a href="{{ url('/logout') }}">Logout</a></li>
                </ul>
            </nav>
        </header>
    </div>

    <div class="container">
        <aside class="details">
            <h3>Classes</h3>
            <div id="classListSidebar">
                @forelse ($courses as $course)
                    <a href="{{ url('/faculty/faculty_dashboard?course_code=' . $course->course_code) }}" 
                       class="class-link {{ $selectedCourseCode == $course->course_code ? 'active' : '' }}">
                        <div class="class-item">{{ $course->course_code }}</div>
                    </a>
                @empty
                    <p>No courses assigned.</p>
                @endforelse
            </div>
            <br>
            <h3>Class Info</h3>
            <p id="classInfo">
                @if ($displayCourse)
                    Class: {{ $displayCourse->course_code }}<br>
                    Subject: {{ $displayCourse->course_name }}<br>
                    Total Students: {{ count($students) }}
                @else
                    No classes available.
                @endif
            </p>
        </aside>

        <main class="main">
            @if(session('success_message'))
                <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    {{ session('success_message') }}
                </div>
            @endif

            @if(session('error_message'))
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    {{ session('error_message') }}
                </div>
            @endif

            <section class="attendance-table">
                <h3>Take Attendance ({{ $displayCourse->course_code ?? 'N/A' }})</h3>
                <form id="attendanceForm" action="{{ url('/faculty/save-attendance?course_code=' . ($displayCourse->course_code ?? '')) }}" method="POST">
                    @csrf 
                    <input type="hidden" name="course_id" value="{{ $displayCourse->course_id ?? '' }}">
                    <table id="attendanceTable">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Present</th>
                                <th>Absent</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceBody">
                            @forelse ($students as $student)
                                <tr>
                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td><input type="radio" name="student_{{ $student->user_id }}" value="Present" required></td>
                                    <td><input type="radio" name="student_{{ $student->user_id }}" value="Absent"></td>
                                </tr>
                            @empty
                                <tr><td colspan="3">No students found for this class.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($displayCourse)
                        <button type="submit" class="update-btn">Submit Attendance</button>
                    @endif
                </form>
            </section>
            
            <hr>

            <section class="attendance-table">
                <h3>Class List ({{ $displayCourse->course_code ?? 'N/A' }})</h3>
                <table id="classListTable">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody id="classListBody">
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                <td>{{ $student->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>