<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "deped_schools");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Error message
$error_message = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate email domain
    if (!preg_match('/@deped\.gov\.ph$/', $email)) {
        $error_message = "Only @deped.gov.ph emails are allowed";
    } else {
        // First ensure users table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'users'");
        if ($table_check->num_rows == 0) {
            $error_message = "No users exist. Please register first.";
        } else {
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = $user['is_admin']; // Make sure this field exists
                
                // Redirect based on role
                if ($user['is_admin'] == 1) {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: /php_projects/user/user/user_dashboard.php");
                }
                exit();
            } else {
                    $error_message = "Invalid password";
                }
            } else {
                $error_message = "Email not found. Please register first.";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <img src="images/deped_logo.png" alt="Schools Division Office Logo">
            <div class="logo-text">Schools Division Office</div>
            <div class="logo-text">General Trias City</div>
        </div>
        <div class="form-section">
            <h1 class="form-title">LOG IN</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email (@deped.gov.ph)" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-footer">
                    <div class="remember-me">
                        <input type="checkbox" id="remember_me" name="remember_me">
                        <label for="remember_me">Remember Me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="sign-in-btn">Log In</button>
            </form>
            <div class="register-link">
                Don't have an account? <a href="admin_register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>