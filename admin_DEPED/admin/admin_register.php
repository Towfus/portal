<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "deped_schools");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Error message
$error_message = "";
$success_message = "";

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate email domain
    if (!preg_match('/@deped\.gov\.ph$/', $email)) {
        $error_message = "Only @deped.gov.ph emails are allowed";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        // Create users table if it doesn't exist
        $table_check = $conn->query("SHOW TABLES LIKE 'users'");
        if ($table_check->num_rows == 0) {
            $create_table = "CREATE TABLE users (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                is_admin TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if (!$conn->query($create_table)) {
                $error_message = "Error creating users table: " . $conn->error;
            }
        }
        
        if (empty($error_message)) {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "Email already registered";
            } else {
                // Hash password and insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO users (email, password, is_admin) VALUES (?, ?, 1)");
                $insert_stmt->bind_param("ss", $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $success_message = "Registration successful! Please login.";
                    // Clear form
                    $_POST = array();
                } else {
                    $error_message = "Registration failed: " . $insert_stmt->error;
                }
                $insert_stmt->close();
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
    <title>Admin Registration - DepEd General Trias City</title>
    <!-- Same styles as login page -->
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
            <h1 class="form-title">REGISTRATION</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email (@deped.gov.ph)" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="sign-in-btn">Register</button>
            </form>
            <div class="login-link">
                Already have an account? <a href="admin_login.php">Login here</a>
            </div>
        </div>
    </div>
</body>
</html>