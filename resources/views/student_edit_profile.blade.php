<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student | Edit Profile</title>
    <link rel="stylesheet" href="{{ asset('css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <style>
        /* Consistency with Dashboard whitespace */
        .container {
            padding: 40px !important;
            gap: 30px !important;
        }

        .details {
            padding: 30px !important;
            line-height: 1.8;
        }

        /* Card Improvements */
        .profile-header, 
        .update-profile {
            padding: 35px !important;
            margin-bottom: 30px !important;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
        }

        /* Form Layout Improvements */
        .update-profile h3 {
            margin-bottom: 25px;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group input {
            width: 100% !important; /* Forces full width within the grid cell */
            padding: 12px !important;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .full-width {
            grid-column: span 2;
        }

        .update-btn {
            width: 100%;
            padding: 12px;
            font-weight: bold;
            letter-spacing: 0.5px;
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
            <hr class="details-line" style="margin-bottom: 20px;">
            <p><strong>Email:</strong><br> {{ $user->email }}</p>
            <p><strong>No. Of Subject:</strong> {{ $enrolled_count }}</p>
            <p><strong>Student ID:</strong> {{ $user->user_id }}</p>
        </aside>

        <main class="main">
            <section class="profile-header">
                <div style="display: flex; align-items: center;">
                    <div class="info">
                        <h2 style="font-size: 1.6rem;">{{ $user->last_name }}, {{ $user->first_name }}</h2>
                        <p style="color: #666; margin: 0;">Student Profile Account</p>
                    </div>
                </div>
            </section>
            
            @if(session('success_message'))
                <div class="message success" style="margin-bottom: 20px;">
                    {{ session('success_message') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="message error" style="margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="update-profile">
                <h3>Update Information</h3>
                <form id="updateForm" method="POST" action="{{ url('/student/profile') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" placeholder="Your unique username">
                        </div>
                        
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}">
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}">
                        </div>

                        <div class="form-group full-width">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}">
                        </div>
                    </div>

                    <button type="submit" class="update-btn" style="margin-top: 20px;">Save Changes</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>