<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard | PUP</title>
    <link rel="stylesheet" href="{{ asset('css/style2.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/student-style.css') }}" />
</head>
<body>
    <div class="nav-bar">
        <header>
            <div class="logo">
                <a href="{{ url('/student/student_dashboard') }}" style="display: flex; align-items: center; text-decoration: none; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6" viewBox="0 0 20 20" fill="currentColor" style="width: 24px; height: 24px; margin-right: 8px;">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                </a>
            </div>
            <nav class="top-right-menu">
                <ul>
                    <li class="active-nav"><a href="{{ url('/student/student_dashboard') }}">Home</a></li>
                    <li><a href="{{ url('/student/profile') }}">Profile</a></li>
                    <li><a href="{{ url('/logout') }}">Logout</a></li>
                </ul>
            </nav>
        </header>
    </div>

    <div class="container">
        <aside class="details">
            <h3>Student | Home</h3>
            <hr class="details-line">
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>No. Of Subjects:</strong> {{ $enrolled_count }}</p>
            <p><strong>Student ID:</strong> {{ $user->user_id }}</p>
        </aside>

        <main class="main">
            <section class="profile-header">
                <div class="avatar"></div>
                <div class="info">
                    <h2>{{ $user->last_name }}, {{ $user->first_name }}</h2>
                </div>
            </section>

            <section class="attendance-insights">
                <h3>Attendance Rate (Present)</h3>
                <p>Based on {{ $present_count }} Present marks out of {{ $total_records }} Total Records.</p>
                <div class="metrics">
                    <div class="metric">
                        <div class="circle maroon">{{ number_format($overall_rate, 1) }}%</div>
                    </div>
                </div>
            </section>

            <section class="attendance-table">
                <h3>Attendance by Subject</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Attendance Rate</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Total Classes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subject_data as $subject)
                            @php
                                $rate = $subject->total_classes > 0 
                                    ? number_format(($subject->present_count / $subject->total_classes) * 100, 1) . '%' 
                                    : '0.0%';
                            @endphp
                            <tr>
                                <td>{{ $subject->course_name }}</td>
                                <td>{{ $rate }}</td>
                                <td>{{ $subject->present_count }}</td>
                                <td>{{ $subject->absent_count }}</td>
                                <td>{{ $subject->total_classes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No attendance records found for your enrolled subjects.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>