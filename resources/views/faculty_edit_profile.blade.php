<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty | Edit Profile</title>
    <link rel="stylesheet" href="{{ asset('css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <style>
        /* Enhanced Page Spacing and Whitespace */
        .container {
            padding: 40px !important; /* Prevents cards from hugging screen edges */
            gap: 30px !important;     /* Consistent gap between sidebar and main content */
        }

        /* Sidebar Breathing Room */
        .details {
            padding: 30px !important;
            line-height: 1.8; /* Adds vertical whitespace between info lines */
            border-radius: 12px;
        }

        .details h3 {
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 10px;
        }

        /* Standardized Main Card styling */
        .admin-page-container {
            margin: 0 !important; /* Overriding auto margin since it's inside a grid container */
            padding: 40px 50px !important;
            border-radius: 12px;
        }

        .attendance-table h3 {
            margin-bottom: 30px;
            font-size: 1.5rem;
            color: #333;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 10px;
        }

        /* Modern Form Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #4b5563; /* Subtle dark gray */
        }

        .form-group input {
            width: 100% !important;
            padding: 12px !important;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: border-color 0.2s;
        }

        /* Helpers for grid spanning */
        .full-width {
            grid-column: span 2;
        }

        .update-btn {
            width: 100%;
            padding: 15px;
            font-weight: bold;
            font-size: 1rem;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="nav-bar">
    <header>
        <div class="logo">
            <a href="{{ url('/faculty/faculty_dashboard') }}" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 35px; height: 35px; margin-right: 8px;">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
            </a>
        </div>
        <nav class="top-right-menu">
            <ul>
                <li><a href="{{ url('/faculty/faculty_dashboard') }}">Home</a></li>
                <li class="active-nav"><a href="{{ url('/faculty/profile') }}">Profile</a></li>
                <li><a href="{{ url('/logout') }}">Logout</a></li>
            </ul>
        </nav>
    </header>
</div>

<div class="container">
    <aside class="details">
        <h3>Your Information</h3>
        <p><strong>Faculty ID:</strong><br> {{ $faculty->user_id }}</p>
        <p><strong>Current Name:</strong><br> {{ $faculty->first_name }} {{ $faculty->last_name }}</p>
        <p><strong>Active Email:</strong><br> {{ $faculty->email }}</p>
    </aside>

    <main class="admin-page-container">
        <section class="attendance-table">
            <h3>Update Your Profile</h3>

            @if(session('success_message'))
                <div class="message success" style="margin-bottom: 20px;">
                    {{ session('success_message') }}
                </div>
            @endif
            
            @if ($errors->any())
                <div class="message error" style="margin-bottom: 20px;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('/faculty/profile') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username', $faculty->username) }}" required placeholder="Edit username">
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $faculty->first_name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $faculty->last_name) }}" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $faculty->email) }}" required>
                    </div>
                </div>

                <button class="update-btn" type="submit">Save Profile Changes</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>