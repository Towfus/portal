<?php
// download_forms.php - Handle file downloads and viewing
session_start();

// Database connection (same as admin_forms.php)
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
// The filepath in database is like: 'shared/forms/renewal_recognition/filename.pdf'
// We need to go up from the current directory to find the shared folder
$filePath = __DIR__ . '/../../' . $fileData['filepath'];

// Alternative path construction if the above doesn't work
// $filePath = dirname(__DIR__, 2) . '/' . $fileData['filepath'];

// Check if file exists
if (!file_exists($filePath)) {
    die("File not found on server: " . htmlspecialchars($fileData['filename']));
}

// Get file information
$fileName = $fileData['filename'];
$fileSize = filesize($filePath);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Set appropriate headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $fileSize);

// Clear any output buffers
ob_clean();
flush();

// Read and output the file
readfile($filePath);
exit;
?>