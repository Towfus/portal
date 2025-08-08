<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "deped_schools");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT file_path, title FROM memorandums WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Clean and verify path
        $filepath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $row['file_path']);
        $filepath = realpath($filepath);
        
        if ($filepath && file_exists($filepath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="'.basename($row['title']).'.pdf"');
            readfile($filepath);
            exit;
        } else {
            header("HTTP/1.0 404 Not Found");
            die("File not found. Path: " . htmlspecialchars($row['file_path']));
        }
    }
}
header("HTTP/1.0 400 Bad Request");
die("Invalid memorandum ID");
?>