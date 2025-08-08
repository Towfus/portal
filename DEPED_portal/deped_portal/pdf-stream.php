<?php
// pdf-stream.php - Secure PDF streaming script

// Check if file parameter exists
if (!isset($_GET['file'])) {
    die('No file specified');
}

// Get the file path from the query parameter
$pdfFilePath = $_GET['file'];

// Security check - prevent directory traversal
// Only allow PDF files
if (!preg_match('/\.pdf$/i', $pdfFilePath)) {
    die('Only PDF files are allowed');
}

// Try different path variations to find the file
$paths = [
    $pdfFilePath, // Original path
    realpath($pdfFilePath), // Resolved real path
    $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($pdfFilePath, '/'), // Absolute path with document root
    dirname(__FILE__) . '/' . ltrim($pdfFilePath, '/'), // Path relative to this script
    dirname(__FILE__) . '/' . $pdfFilePath, // Direct relative path
    '../' . ltrim($pdfFilePath, '/') // One directory up
];

$fileFound = false;
foreach ($paths as $path) {
    if ($path && file_exists($path) && is_readable($path)) {
        $fileFound = true;
        $pdfFilePath = $path;
        error_log("PDF found and readable at: $path");
        break;
    } else if ($path) {
        error_log("Trying path: $path - " . (file_exists($path) ? "EXISTS but " . (is_readable($path) ? "IS" : "NOT") . " readable" : "NOT FOUND"));
    }
}

// If file is still not found
if (!$fileFound) {
    die('File not found or not readable. Please check that the file exists and has proper permissions at: ' . htmlspecialchars($pdfFilePath));
}

// Set headers to display PDF in browser
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($pdfFilePath) . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Get file size
$fileSize = filesize($pdfFilePath);
if ($fileSize) {
    header('Content-Length: ' . $fileSize);
}

// Output the file
readfile($pdfFilePath);
exit;
?>