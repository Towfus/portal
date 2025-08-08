<?php
// download_forms.php - Handle file downloads for users
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
$fileSize = filesize($filePath);
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Set appropriate headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $fileSize);

// Prevent any output before file download
ob_clean();
flush();

// Read and output the file for download
if ($fileSize > 1024 * 1024 * 8) { // For files larger than 8MB, read in chunks
    $handle = fopen($filePath, 'rb');
    if ($handle) {
        while (!feof($handle)) {
            echo fread($handle, 8192); // Read 8KB at a time
            ob_flush();
            flush();
        }
        fclose($handle);
    }
} else {
    readfile($filePath);
}

exit;
?>