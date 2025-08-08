<?php
// view_forms.php - Handle file viewing for users
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "deped_schools";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("File ID not provided.");
}

$fileId = intval($_GET['id']);

// Get file information from database
$stmt = $conn->prepare("SELECT * FROM `documents` WHERE id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("File not found in database.");
}

$fileData = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Construct the full file path
$filePath = __DIR__ . '/../../' . $fileData['filepath'];

// Check if file exists
if (!file_exists($filePath)) {
    die("File not found on server: " . htmlspecialchars($fileData['filename']));
}

// Get file information
$fileName = $fileData['filename'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Set appropriate headers for viewing
switch ($fileExt) {
    case 'pdf':
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
        break;
    case 'doc':
        header('Content-Type: application/msword');
        header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
        break;
    case 'docx':
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
        break;
    default:
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
}

header('Content-Length: ' . filesize($filePath));
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour

// Clear any output buffers
ob_clean();
flush();

// Read and output the file
readfile($filePath);
exit;
?>