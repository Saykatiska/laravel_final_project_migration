<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty | Edit Profile</title>
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
                <li><a href="{{ url('/faculty/faculty_dashboard') }}">Home</a></li>
                <li class="active-nav"><a href="{{ url('/faculty/profile') }}">Profile</a></li>
                <li><a href="{{ url('/logout') }}">Logout</a></li>
            </ul>
        </nav>
    </header>
</div>

<div class="container">

<aside class="details">
    <h3>Your Info</h3>
    <p><strong>Faculty ID:</strong> {{ $faculty->user_id }}</p>
    <p><strong>Name:</strong> {{ $faculty->first_name }} {{ $faculty->last_name }}</p>
    <p><strong>Email:</strong> {{ $faculty->email }}</p>
</aside>

<main class="main">
<section class="attendance-table">
    <h3>Update Profile</h3>

    @if(session('success_message'))
        <div class="success-message" style="color: green; font-weight: bold; margin-bottom: 10px;">
            {{ session('success_message') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="error-message" style="color: red; font-weight: bold; margin-bottom: 10px;">
            <ul style="list-style: none; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/faculty/profile') }}">
        @csrf

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="{{ old('username', $faculty->username) }}" required><br>

        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $faculty->first_name) }}" required>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $faculty->last_name) }}" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="{{ old('email', $faculty->email) }}" required>

        <button class="update-btn" type="submit">Save Changes</button>
    </form>

</section>
</main>

</div>
</body>
</html>