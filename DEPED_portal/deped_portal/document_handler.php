<?php
// Document handler for School Application Forms Portal

// Database connection parameters
$servername = "localhost"; 
$username = "root";  
$password = "";  
$dbname = "deped_schools";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Sanitize input function
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Check if action is set
if (!isset($_GET['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No action specified']);
    exit;
}

$action = sanitizeInput($_GET['action']);

// Make sure required parameters are provided
if (!isset($_GET['file']) || !isset($_GET['category'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File or category not specified']);
    exit;
}

$filename = sanitizeInput($_GET['file']);
$category = sanitizeInput($_GET['category']);

// Define the forms categories and their corresponding directories
$formCategories = [
    'Renewal/Recognition Application' => 'renewal_recognition',
    'New Government Permit Application' => 'gov_permit',
    'Tuition Fee Increase Application' => 'tuition_increase',
    'Special Order Requirements' => 'special_order',
    'Summer Classes Application' => 'summer_classes'
];

// Function to get file information from the database
function getFileInfo($conn, $filename, $category) {
    $stmt = $conn->prepare("SELECT * FROM `documents` WHERE filename = ? AND category = ?");
    $stmt->bind_param("ss", $filename, $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }

    return $result->fetch_assoc();
}

// Process the action
switch ($action) {
    case 'info':
        $fileInfo = getFileInfo($conn, $filename, $category);
        
        if (!$fileInfo) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'File not found in database']);
            exit;
        }
        
        $responseData = [
            'filename' => $fileInfo['filename'],
            'size' => $fileInfo['filesize'],
            'modified' => date("F d Y H:i:s", strtotime($fileInfo['upload_date'])),
            'extension' => strtolower(pathinfo($fileInfo['filename'], PATHINFO_EXTENSION))
        ];
        
        header('Content-Type: application/json');
        echo json_encode($responseData);
        break;

    case 'preview':
        $fileInfo = getFileInfo($conn, $filename, $category);
        
        if (!$fileInfo) {
            echo "Error: File not found in database";
            exit;
        }

        $filePath = $fileInfo['filepath'];
        
        // Check if the file exists
        if (!file_exists($filePath)) {
            echo "Error: File does not exist on server";
            exit;
        }

        $fileExtension = strtolower(pathinfo($fileInfo['filename'], PATHINFO_EXTENSION));

        if ($fileExtension === 'pdf') {
            // For PDF files, just output the file with PDF headers
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="'.basename($filePath).'"');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
            exit;
        } 
        else if ($fileExtension === 'doc' || $fileExtension === 'docx') {
            // For DOCX files, we'll display the text content directly
            // First, tell the browser we're sending HTML
            header('Content-Type: text/html; charset=utf-8');
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Document Preview</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        margin: 30px;
                        background-color: #fcfcfc;
                    }
                    .preview-container {
                        background-color: white;
                        padding: 40px;
                        border: 1px solid #ddd;
                        box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    .preview-header {
                        padding-bottom: 20px;
                        margin-bottom: 20px;
                        border-bottom: 1px solid #eee;
                    }
                    .preview-notice {
                        background-color: #fff3cd;
                        color: #856404;
                        padding: 10px;
                        margin-bottom: 20px;
                        border-radius: 4px;
                        font-size: 14px;
                    }
                    .preview-content p {
                        margin-bottom: 15px;
                    }
                </style>
            </head>
            <body>
                <div class="preview-container">
                    <div class="preview-header">
                        <h2><?php echo htmlspecialchars($filename); ?></h2>
                    </div>
                    <div class="preview-notice">
                        This is a text-only preview. For full formatting, please download the original document.
                    </div>
                    <div class="preview-content">
                        <?php echo displayDocxContent($filePath); ?>
                    </div>
                </div>
            </body>
            </html>
            <?php
        } else {
            echo "Error: Unsupported file type for preview";
        }
        break;

    case 'download':
        $fileInfo = getFileInfo($conn, $filename, $category);
      
        if (!$fileInfo) {
            echo "Error: File not found in database";
            exit;
        }
      
        $filePath = $fileInfo['filepath'];
      
        if (!file_exists($filePath)) {
            echo "Error: File does not exist on server";
            exit;
        }
      
        // Force download the file
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        // Clean output buffer before sending the file
        ob_clean();
        flush();
        
        // Output the file content
        readfile($filePath);
        exit;
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
        break;
}

// Function to extract and display content from DOCX files
function displayDocxContent($filePath) {
    // Open the DOCX file as zip
    $zip = new ZipArchive;
    $content = '';
    
    if ($zip->open($filePath) === TRUE) {
        // Extract the file content
        $index = $zip->locateName('word/document.xml'); // The main document
        if ($index !== false) {
            $xmlContent = $zip->getFromIndex($index);

            // Load the XML content
            $doc = new DOMDocument();
            $doc->loadXML($xmlContent, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

            // Find all paragraphs
            $paragraphs = $doc->getElementsByTagName('p');
            
            // Loop through each paragraph
            foreach ($paragraphs as $paragraph) {
                $texts = $paragraph->getElementsByTagName('t');
                $paragraphText = '';
                foreach ($texts as $text) {
                    $paragraphText .= $text->textContent;
                }
                
                if (trim($paragraphText) != '') {
                    $content .= "<p>" . htmlspecialchars($paragraphText) . "</p>";
                }
            }
        } else {
            $content = "<p>Error: Could not locate document.xml in the DOCX file.</p>";
        }

        $zip->close();
    } else {
        $content = "<p>Error: Could not open the DOCX file.</p>";
    }
    
    return $content;
}
?>