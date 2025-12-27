<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student | Edit Profile</title>
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
                    <li><a href="{{ url('/student/student_dashboard') }}">Home</a></li>
                    <li class="active-nav"><a href="{{ url('/student/profile') }}">Profile</a></li>
                    <li><a href="{{ url('/logout') }}">Logout</a></li>
                </ul>
            </nav>
        </header>
    </div>

    <div class="container">
        <aside class="details">
            <h3>Student Details</h3>
            <hr class="details-line">
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>No. Of Subject:</strong> {{ $enrolled_count }}</p>
            <p><strong>Student ID:</strong> {{ $user->user_id }}</p>
        </aside>

        <main class="main">
            <section class="profile-header">
                <div class="avatar"></div>
                <div class="info">
                    <h2>{{ $user->last_name }}, {{ $user->first_name }}</h2>
                </div>
            </section>
            
            @if(session('success_message'))
                <p style="color: green; font-weight: bold; margin-bottom: 20px;">{{ session('success_message') }}</p>
            @endif

            @if ($errors->any())
                <div style="color: red; margin-bottom: 20px;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="update-profile">
                <h3>Update Profile</h3>
                <form id="updateForm" method="POST" action="{{ url('/student/profile') }}">
                    @csrf
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"><br>
                    
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}"><br>

                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}"><br>

                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" value="{{ old('email', $user->email) }}"><br><br>

                    <button type="submit" class="update-btn">Save Changes</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>