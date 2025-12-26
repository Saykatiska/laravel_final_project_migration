<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IskoTrack | System Login</title>
  <link rel="stylesheet" href="{{ asset('css/loginstyle.css') }}">
</head>
<body>
  <div class="container">

    <div class="left-section">
      <div class="overlay"></div>

      <div class="text-content">
        <h3>A Web Development Case Study</h3>
        <p>Presented to <b>Prof. Marilou Fernandez Novida</b><br>
          <i>Polytechnic University of the Philippines – San Juan Campus</i>
        </p>

        <div class="welcome-text">
          <h1>Hello,<br> welcome!</h1>
        </div>
      </div>  

      <div class="developers">
        <p><b>DEVELOPED BY:</b></p>
        <div class="roles">
          <div>
            <p>Back-end Developer</p>
            <p>
              Tomas, Cydoel<br>
              Lapido, Jhanna Lou<br>
              Baniqued, Ysabella Nicole<br>
              Manuel, Alaiza Claine
            </p>
          </div>
          <div>
            <p>Front-end Developer</p>
            <p>
              Pinto, Kathleen<br>
              Tamalla, Trunkziszaq Mae
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="right-section">
      <div class="login-box">
        <h2>SYSTEM LOGIN</h2>
        <p class="desc">Enter your username and password to sign in!</p>

        {{-- Display Error Messages from the Controller --}}
        @if (session('error_message'))
            <div class="message_error">
                {!! session('error_message') !!}
            </div>
        @endif

        <form method="POST" action="/login">
          @csrf
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" placeholder="Enter username" required>

          <label for="password">Password:</label>
          <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
          
          <button type="submit">Sign In</button>
        </form>
        
        {{-- Display System Status Footer --}}
        @if ($is_maintenance || $is_limited)
            <p style="text-align: center; margin-top: 15px; color: #888; font-size: 0.9em;">
                System Status: <b>{{ $system_status }}</b>.<br>
                Only Admins can log in during Maintenance.
            </p>
        @endif
        
      </div>
    </div>

  </div>
</body>
</html>