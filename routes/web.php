<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\StudentController;

// 1. This displays the login page
Route::get('/', [LoginController::class, 'index']);

// 2. This handles the form submission when someone clicks "Sign In"
Route::post('/login', [LoginController::class, 'login']);

Route::get('/logout', [LoginController::class, 'logout']);

Route::get('/admin_dashboard', [AdminController::class, 'dashboard']);

// Settings Management
Route::get('/admin/settings', [AdminController::class, 'settings']);
Route::post('/admin/settings', [AdminController::class, 'updateSettings']);

// User Management
Route::get('/admin/users', [AdminController::class, 'userManagement']);
// Add User Routes
Route::get('/admin/users/add', [AdminController::class, 'createUser']);
Route::post('/admin/users/add', [AdminController::class, 'storeUser']);
// Edit User Routes
Route::get('/admin/users/edit/{id}', [AdminController::class, 'editUser']);
Route::post('/admin/users/edit/{id}', [AdminController::class, 'updateUser']);
// User Deletion Route
Route::get('/admin/users/delete/{id}', [AdminController::class, 'deleteUser']);

// Enrollment Management
Route::get('/admin/enrollment', [AdminController::class, 'enrollmentManagement']);
Route::get('/admin/enrollment/delete/{id}', [AdminController::class, 'deleteEnrollment']);
Route::post('/admin/enrollment/add', [AdminController::class, 'storeEnrollment']);

// Course Management Route
Route::get('/admin/courses', [AdminController::class, 'courseManagement']);
Route::post('/admin/courses/add', [AdminController::class, 'storeCourse']);
Route::post('/admin/courses/edit/{id}', [AdminController::class, 'updateCourse']);
Route::get('/admin/courses/delete/{id}', [AdminController::class, 'deleteCourse']);

Route::get('/student/student_dashboard', [StudentController::class, 'dashboard']);
// Student Profile Routes
Route::get('/student/profile', [StudentController::class, 'editProfile']);
Route::post('/student/profile', [StudentController::class, 'updateProfile']);

Route::get('/faculty/faculty_dashboard', [FacultyController::class, 'dashboard']);

// Faculty Profile Routes
Route::get('/faculty/profile', [FacultyController::class, 'editProfile']);
Route::post('/faculty/profile', [FacultyController::class, 'updateProfile']);
Route::post('/faculty/save-attendance', [FacultyController::class, 'saveAttendance']);