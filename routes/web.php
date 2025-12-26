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

// Enrollment Management
Route::get('/admin/enrollment', [AdminController::class, 'enrollmentManagement']);
Route::get('/admin/enrollment/delete/{id}', [AdminController::class, 'deleteEnrollment']);

// Course Management Route
Route::get('/admin/courses', [AdminController::class, 'courseManagement']);

Route::get('/student/student_dashboard', [StudentController::class, 'dashboard']);

Route::get('/faculty/faculty_dashboard', [FacultyController::class, 'dashboard']);