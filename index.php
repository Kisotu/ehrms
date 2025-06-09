<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-HR System Login</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>

    <!-- Main Container to center the login form -->
    <main class="login-container">
        <div class="login-card">
            
            <!-- Header -->
            <header class="login-header">
                <h1>E-HR System</h1>
                <p>Sign in to your account</p>
            </header>

            <!-- Login Form -->
            <form action="#" method="POST">
                <!-- Role Selector -->
                <div class="form-group">
                    <label>Sign in as:</label>
                    <div class="role-selector">
                        <button type="button" id="employee-btn" class="role-btn active" onclick="selectRole('employee')">
                            Employee
                        </button>
                        <button type="button" id="admin-btn" class="role-btn" onclick="selectRole('admin')">
                            Admin
                        </button>
                    </div>
                    <input type="hidden" id="role" name="role" value="employee">
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        <input type="text" name="username" id="username" class="form-input" placeholder="your.username" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                         <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <!-- Forgot Password -->
                <div class="forgot-password">
                    <a href="#">Forgot Password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    Sign In
                </button>
            </form>
        </div>
    </main>
    
    <!-- Page Footer -->
    <footer class="page-footer">
        <p>© 2024 E-HR Systems Inc. All rights reserved.</p>
    </footer>

    <script>
        // JavaScript to handle the role selection toggle
        function selectRole(selectedRole) {
            const employeeBtn = document.getElementById('employee-btn');
            const adminBtn = document.getElementById('admin-btn');
            const roleInput = document.getElementById('role');
            
            // Remove 'active' from both buttons first
            employeeBtn.classList.remove('active');
            adminBtn.classList.remove('active');

            // Add 'active' to the clicked button and set the hidden input's value
            if (selectedRole === 'admin') {
                adminBtn.classList.add('active');
                roleInput.value = 'admin';
            } else { // 'employee'
                employeeBtn.classList.add('active');
                roleInput.value = 'employee';
            }
        }

        // Prevent form submission for this demo
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const role = document.getElementById('role').value;
            const username = document.getElementById('username').value;
            // In a real application, you would send this data to the server.
            console.log(`Signing in as "${role}" with username "${username}"`);
        });
    </script>

</body>
</html>
