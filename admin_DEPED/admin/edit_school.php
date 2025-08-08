<?php
// edit_school.php - Form to edit an existing school
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "deped_schools";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = '';
$errorMessage = '';
$school = null;

// Check if ID parameter is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = (int)$_GET['id'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_id = trim($_POST['school_id']);
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $barangay = trim($_POST['barangay']);
    $city = trim($_POST['city'] ?? 'General Trias');
    $province = trim($_POST['province'] ?? 'Cavite');
    $telephone_number = trim($_POST['telephone_number'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validate input
    if (empty($school_id) || empty($name) || empty($address) || empty($barangay)) {
        $errorMessage = "Required fields are missing";
    } else {
        // Update school information with all fields
        $sql = "UPDATE schools SET 
                school_id = ?, 
                name = ?, 
                address = ?, 
                barangay = ?, 
                city = ?, 
                province = ?, 
                telephone_number = ?, 
                mobile_number = ?, 
                email = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", 
            $school_id, 
            $name, 
            $address, 
            $barangay, 
            $city, 
            $province, 
            $telephone_number, 
            $mobile_number, 
            $email, 
            $id);
        
        if ($stmt->execute()) {
            $successMessage = "School updated successfully!";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Fetch school information
$sql = "SELECT * FROM schools WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $school = $result->fetch_assoc();
} else {
    header("Location: admin_dashboard.php");
    exit;
}

$stmt->close();

// Include header
$pageTitle = 'Edit School';
include 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit School - DepEd Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
        }
        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-card {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4b5563;
        }
        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .submit-button {
            background-color: #1e40af;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }
        .submit-button:hover {
            background-color: #1e3a8a;
        }
        .cancel-button {
            background-color: #9ca3af;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            margin-right: 10px;
        }
        .cancel-button:hover {
            background-color: #6b7280;
        }
        .success-message {
            background-color: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="form-card">
            <h1 class="form-title">Edit School</h1>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-message">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name" class="form-label">School Name *</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           value="<?php echo htmlspecialchars($school['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">Address *</label>
                    <input type="text" id="address" name="address" class="form-input" 
                           value="<?php echo htmlspecialchars($school['address']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="barangay" class="form-label">Barangay *</label>
                    <input type="text" id="barangay" name="barangay" class="form-input" 
                           value="<?php echo htmlspecialchars($school['barangay']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="city" class="form-label">City/Municipality</label>
                    <input type="text" id="city" name="city" class="form-input" 
                           value="<?php echo htmlspecialchars($school['city'] ?? 'General Trias City'); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="province" class="form-label">Province</label>
                    <input type="text" id="province" name="province" class="form-input" 
                           value="<?php echo htmlspecialchars($school['province'] ?? 'Cavite'); ?>" readonly>
                </div>
                
                <div class="flex justify-end">
                    <a href="admin_dashboard.php" class="cancel-button">Cancel</a>
                    <button type="submit" class="submit-button">Update School</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>