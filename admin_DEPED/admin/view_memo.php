<?php
// view.php
try {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "deped_schools";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Validate ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("Invalid memorandum ID");
    }
    
    $id = (int)$_GET['id'];
    
    // Get file path
    $stmt = $conn->prepare("SELECT file_path FROM memorandums WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->bind_param("i", $id)) {
        throw new Exception("Bind failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("No memorandum found with ID: $id");
    }
    
    $row = $result->fetch_assoc();
    
    // Normalize and construct the correct file path
    $stored_path = str_replace('\\', '/', $row['file_path']);
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    
    // Remove document root if already included in stored path
    if (strpos($stored_path, $doc_root) === 0) {
        $stored_path = substr($stored_path, strlen($doc_root));
    }
    
    // Construct final path ensuring proper slashes
    $file_path = rtrim($doc_root, '/') . '/' . ltrim($stored_path, '/');
    
    // Verify file
    if (!file_exists($file_path)) {
        throw new Exception("File not found at: " . htmlspecialchars($file_path));
    }
    
    if (!is_readable($file_path)) {
        throw new Exception("File not readable");
    }
    
    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME);
    $mime = $finfo->file($file_path);
    
    if (strpos($mime, 'pdf') === false) {
        throw new Exception("Not a PDF file");
    }
    
    // Clear output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Output PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;

} catch (Exception $e) {
    // Clean buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Show error
    header("Content-Type: text/plain", true, 500);
    echo "ERROR: " . $e->getMessage();
    error_log("PDF Viewer Error: " . $e->getMessage());
    exit;
}
?>