<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "deped_schools";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Setup</h2>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($dbname);

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Check if we're adding a new user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_email'])) {
    $new_email = $_POST['new_email'];
    $new_password = $_POST['new_password'];
    
    // Validate email domain
    if (!preg_match('/@deped\.gov\.ph$/', $new_email)) {
        echo "<div style='color: red; margin: 10px 0;'>Only @deped.gov.ph emails are allowed</div>";
    } else {
        // Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $new_email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo "<div style='color: red; margin: 10px 0;'>Email already exists!</div>";
        } else {
            // Insert the new user
            $insert_stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $new_email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                echo "<div style='color: green; margin: 10px 0;'>New user created successfully!</div>";
            } else {
                echo "<div style='color: red; margin: 10px 0;'>Error creating user: " . $insert_stmt->error . "</div>";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// Get all users
$result = $conn->query("SELECT id, email, created_at FROM users ORDER BY id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h2 {
            color: #4a90e2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        form {
            margin-top: 20px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4a90e2;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #3a7bc8;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            color: #4a90e2;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Current Users</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Created At</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row["id"]; ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td><?php echo $row["created_at"]; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No users found</p>
    <?php endif; ?>
    
    <h2>Add New User</h2>
    <form method="post" action="">
        <div>
            <label for="new_email">Email (@deped.gov.ph):</label>
            <input type="email" id="new_email" name="new_email" required>
        </div>
        <div>
            <label for="new_password">Password:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div>
            <input type="submit" value="Add User">
        </div>
    </form>
    
    <div class="links">
        <a href="admin_login.php">Go to Login Page</a>
    </div>
</body>
</html>

<?php
// Close connection
$conn->close();
?>