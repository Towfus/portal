<?php
// Simple file download handler

// Check if a file parameter is provided
if (!isset($_GET['file'])) {
    die("No file specified");
}

$file = $_GET['file'];

// Basic security checks - prevent directory traversal
if (strpos($file, '..') !== false || strpos($file, './') !== false) {
    die("Invalid file path");
}

// Check if the file exists
if (!file_exists($file)) {
    die("File not found: " . htmlspecialchars($file));
}

// Get file info
$filename = basename($file);
$filesize = filesize($file);
$filetype = mime_content_type($file);

// Set headers for forcing download
header('Content-Description: File Transfer');
header('Content-Type: ' . $filetype);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $filesize);

// Clean output buffer
if (ob_get_level()) ob_clean();
flush();

// Output the file content and exit
readfile($file);
exit;
?>