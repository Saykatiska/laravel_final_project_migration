<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | User Management</title>
    <link rel="stylesheet" href="{{ asset('css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Ensuring table headers are centered */
        th, td {
            text-align: center !important;
            vertical-align: middle;
        }
        
        /* Dropdown specific overrides to ensure the arrow appears */
        .custom-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 16px !important;
            padding-right: 40px !important;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <header>
            <div class="logo">
                <a href="{{ url('/admin_dashboard') }}" style="display: flex; align-items: center; text-decoration: none; color: white;">
                    <svg viewBox="0 0 24 24" fill="currentColor" style="color: white; margin-right: 8px; width:30px; height:30px;">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.8 6-3.8 1.99 0 5.97 1.81 6 3.8-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                </a>
            </div>
            <nav class="top-right-menu">
                <ul>
                    <li><a href="{{ url('/admin_dashboard') }}">Home</a></li>
                    <li class="active-nav"><a href="{{ url('/admin/users') }}">Users</a></li>
                    <li><a href="{{ url('/admin/courses') }}">Courses</a></li>
                    <li><a href="{{ url('/admin/enrollment') }}">Enrollment</a></li>
                    <li><a href="{{ url('/logout') }}">Logout</a></li>
                    <li><a href="{{ url('/admin/settings') }}" class="btn-secondary">Settings</a></li>
                </ul>
            </nav>
        </header>
    </div>
    
    @if(session('success_message'))
        <div class="message-box success">{{ session('success_message') }}</div>
    @endif

    @if(session('error_message'))
        <div class="message-box error">{{ session('error_message') }}</div>
    @endif
    
    <main class="main">
        <section class="attendance-table">
            <div class="table-header-controls">
                <h1 style="padding: 0 10px 10px 0px;">User Management</h1>
                <nav class="sub-menu-inline">
                    <a href="javascript:void(0)" class="btn-primary" onclick="openModal()">Add New User</a>
                </nav>
            </div>
            <hr><br>

            <div class="table-header-controls"><h2>Faculty Members</h2></div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($faculty_users as $user)
                        <tr>
                            <td>{{ $user->user_id }}</td>
                            <td>{{ $user->username }}</td>
                            <td><span class="role-faculty">{{ $user->role }}</span></td>
                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <a href="javascript:void(0)" class="btn-edit"
                                   onclick="openEditModal('{{ $user->user_id }}', '{{ $user->username }}', '{{ $user->first_name }}', '{{ $user->last_name }}', '{{ $user->email }}', '{{ $user->role }}')">Edit</a>
                                <a href="{{ url('/admin/users/delete/'.$user->user_id) }}" class="btn-delete"
                                   onclick="return confirm('Delete this user?');">Delete</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No Faculty users found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <hr style="margin: 40px 0; border-top: 1px solid #ccc;">

            <div class="table-header-controls"><h2>Students</h2></div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($student_users as $user)
                        <tr>
                            <td>{{ $user->user_id }}</td>
                            <td>{{ $user->username }}</td>
                            <td><span class="role-student">{{ $user->role }}</span></td>
                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <a href="javascript:void(0)" class="btn-edit"
                                   onclick="openEditModal('{{ $user->user_id }}', '{{ $user->username }}', '{{ $user->first_name }}', '{{ $user->last_name }}', '{{ $user->email }}', '{{ $user->role }}')">Edit</a>
                                <a href="{{ url('/admin/users/delete/'.$user->user_id) }}" class="btn-delete"
                                   onclick="return confirm('Delete this user?');">Delete</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No Student users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>

    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create New Account</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ url('/admin/users/add') }}">
                    @csrf
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" required placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" required placeholder="Enter temporary password">
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select name="role" required class="custom-select">
                            <option value="" disabled selected hidden>-- Select User Role --</option>
                            <option value="Faculty">Faculty Member</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" required placeholder="First Name">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" required placeholder="Last Name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" required placeholder="email@example.com">
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top: 10px; width: 100%;">Save User</button>
                </form>
            </div>
        </div>
    </div>

    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit User Details</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST" action="">
                    @csrf
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (Optional)</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="edit_role" required class="custom-select">
                            <option value="Faculty">Faculty Member</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top: 10px; width: 100%;">Update Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById("addUserModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("addUserModal").style.display = "none";
        }

        function openEditModal(id, username, firstName, lastName, email, role) {
            const form = document.getElementById('editUserForm');
            form.action = "{{ url('/admin/users/edit') }}/" + id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById("editUserModal").style.display = "block";
        }

        function closeEditModal() {
            document.getElementById("editUserModal").style.display = "none";
        }

        window.onclick = function(event) {
            let addModal = document.getElementById("addUserModal");
            let editModal = document.getElementById("editUserModal");
            if (event.target == addModal) closeModal();
            if (event.target == editModal) closeEditModal();
        }
    </script>
</body>
</html>