<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard | PUP</title>
    <link rel="stylesheet" href="{{ asset('css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/student-style.css') }}" />
    <style>
        /* Enhanced Card Spacing */
        .container {
            padding: 40px !important; /* Extra whitespace around the whole dashboard */
            gap: 30px !important;     /* More space between sidebar and main content */
        }

        /* Provide internal breathing room for the sidebar */
        .details {
            padding: 30px !important;
            line-height: 1.8; /* Spread out the text lines */
        }

        .details h3 {
            margin-bottom: 20px;
        }

        /* Separate the main sections */
        .profile-header, 
        .attendance-insights, 
        .attendance-table {
            padding: 35px !important; /* whitespace inside the white card */
            margin-bottom: 30px !important; /* whitespace between cards */
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
        }

        /* Spacing for the attendance circle */
        .attendance-insights h3 {
            margin-bottom: 15px;
        }

        .attendance-insights p {
            margin-bottom: 25px;
            color: #666;
        }

        /* Table header spacing */
        .attendance-table h3 {
            margin-bottom: 20px;
        }

        /* Ensure table rows aren't cramped */
        th, td {
            padding: 15px !important;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <header>
            <div class="logo">
                <a href="{{ url('/student/student_dashboard') }}" style="display: flex; align-items: center; text-decoration: none; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 35px; height: 35px; margin-right: 8px;">
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
            <hr class="details-line" style="margin-bottom: 20px;">
            <p><strong>Email:</strong><br> {{ $user->email }}</p>
            <p><strong>No. Of Subjects:</strong> {{ $enrolled_count }}</p>
            <p><strong>Student ID:</strong> {{ $user->user_id }}</p>
        </aside>

        <main class="main">
            <section class="profile-header">
                <div class="info">
                    <h2 style="font-size: 1.8rem;">{{ $user->last_name }}, {{ $user->first_name }}</h2>
                    <p style="color: #888;">Active Student</p>
                </div>
            </section>

            <section class="attendance-insights">
                <h3>Attendance Rate (Present)</h3>
                <p>Based on {{ $present_count }} Present marks out of {{ $total_records }} Total Records.</p>
                <div class="metrics">
                    <div class="metric">
                        <div class="circle maroon" style="margin: 0 auto; width: 120px; height: 120px; font-size: 1.8rem;">
                            {{ number_format($overall_rate, 1) }}%
                        </div>
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
                                <td style="text-align: left; font-weight: 600;">{{ $subject->course_name }}</td>
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