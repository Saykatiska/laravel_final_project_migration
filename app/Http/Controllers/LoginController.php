<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    // Define the messages as properties to keep the code tidy
    protected $maintenance_message = "The system is currently undergoing scheduled <b>maintenance</b>. Please check back later.";
    protected $limited_message = "The system is currently operating in limited mode. Student accounts are temporarily blocked.";

    public function index()
    {
        // 1. Check if user is already logged in (Replacement for isset($_SESSION['user_id']))
        if (Session::has('user_id')) {
            return $this->redirectBasedOnRole(Session::get('role'));
        }

        // 2. Fetch System Status
        $settings = DB::table('system_settings')->where('setting_key', 'System_Status')->first();
        $system_status = $settings ? $settings->setting_value : 'Online';

        return view('index', [
            'system_status' => $system_status,
            'is_maintenance' => ($system_status === 'Maintenance'),
            'is_limited' => ($system_status === 'Limited'),
            'maintenance_message' => $this->maintenance_message,
            'limited_message' => $this->limited_message
        ]);
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // 1. Fetch User
        $user = DB::table('users')->where('username', $username)->first();

        // 2. Check if user exists
        if (!$user) {
            return back()->with('error_message', "Username not found.");
        }

        // 3. Verify Password (using your current plain-text match)
        if ($password === $user->password) {
            
            // --- START SYSTEM STATUS CHECK ---
            
            // 4. Fetch the current System Status from your settings table
            $setting = DB::table('system_settings')->where('setting_key', 'System_Status')->first();
            $status = $setting ? $setting->setting_value : 'Online';
            $role = $user->role;

            // 5. Apply Blocking Logic
            if ($status === 'Maintenance' && $role !== 'Admin') {
                return back()->with('error_message', "The system is currently under Maintenance. Only Admins can log in.");
            }
            
            if ($status === 'Limited' && $role === 'Student') {
                return back()->with('error_message', "Student access is temporarily disabled (Limited Mode).");
            }
            
            // --- END SYSTEM STATUS CHECK ---

            // 6. Success: Set Sessions
            Session::put([
                'user_id' => $user->user_id,
                'username' => $user->username,
                'role' => $user->role,
                'first_name' => $user->first_name,
            ]);

            return $this->redirectBasedOnRole($user->role);
        }

        return back()->with('error_message', "Invalid password.");
    }

    // Helper function to handle your various dashboard redirects
    private function redirectBasedOnRole($role)
    {
        if ($role === 'Admin') return redirect('/admin_dashboard');
        if ($role === 'Faculty') return redirect('/faculty/faculty_dashboard');
        if ($role === 'Student') return redirect('/student/student_dashboard');
        
        return redirect('/');
    }

    public function logout(Request $request)
{
    // 1. Clear all session data
    Session::flush();

    // 2. Invalidate the current session and regenerate the CSRF token for security
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // 3. Redirect back to the login/landing page
    return redirect('/')->with('success_message', 'You have been logged out.');
}
}