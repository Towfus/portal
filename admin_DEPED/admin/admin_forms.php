<?php
// admin_forms.php - Main forms management file
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "deped_schools";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
include 'admin_header.php';

// Check connection
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error);
}

// Create documents table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS `documents` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `category` VARCHAR(255) NOT NULL,
    `filepath` VARCHAR(255) NOT NULL,
    `filesize` INT(11) NOT NULL,
    `upload_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($createTableSQL)) {
    die("Error creating table: " . $conn->error);
}

// Define the forms categories and their corresponding directories
$formCategories = [
    'Renewal/Recognition Application' => 'renewal_recognition',
    'New Government Permit Application' => 'gov_permit',
    'Tuition Fee Increase Application' => 'tuition_increase',
    'Special Order Requirements' => 'special_order',
    'Summer Classes Application' => 'summer_classes'
];

// Create directories if they don't exist
$uploadBaseDir = __DIR__ . '/../../shared/forms/';
if (!file_exists($uploadBaseDir)) {
    mkdir($uploadBaseDir, 0755, true);
}

foreach ($formCategories as $dir) {
    $fullPath = $uploadBaseDir . $dir;
    if (!file_exists($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
}

// Handle file upload
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Handle file upload
        if ($_POST['action'] === 'upload' && isset($_FILES['formFile']) && isset($_POST['formCategory'])) {
            $category = $_POST['formCategory'];
            $uploadDir = $uploadBaseDir . $formCategories[$category] . '/'; 
            
            // Get file details
            $fileName = $_FILES['formFile']['name'];
            $fileTmpName = $_FILES['formFile']['tmp_name'];
            $fileSize = $_FILES['formFile']['size'];
            $fileError = $_FILES['formFile']['error'];
            
            // Get file extension
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx'];
            
            // Create custom filename if provided
            if (!empty($_POST['customFilename'])) {
                $baseFilename = trim($_POST['customFilename']);
                // Remove any path separators for security
                $baseFilename = basename($baseFilename);
                // Add extension if not already present
                if (!preg_match('/\.' . $fileExt . '$/', $baseFilename)) {
                    $baseFilename .= '.' . $fileExt;
                }
                $finalFileName = $baseFilename;
            } else {
                $finalFileName = $fileName;
            }
            
            // Validate file
            if (in_array($fileExt, $allowed)) {
                if ($fileError === 0) {
                    if ($fileSize < 10000000) { // Less than 10MB
                        $fileDestination = $uploadDir . $finalFileName;
                        
                        // Check if file already exists
                        if (file_exists($fileDestination)) {
                            $message = '<div class="alert alert-warning">File already exists. Please use a different filename.</div>';
                        } else {
                            // Upload file
                            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                                // Store relative path for web access
                                $relativePath = 'shared/forms/' . $formCategories[$category] . '/' . $finalFileName;
                                
                                // Save to database
                                $stmt = $conn->prepare("INSERT INTO `documents` (filename, category, filepath, filesize) VALUES (?, ?, ?, ?)");
                                $stmt->bind_param("sssi", $finalFileName, $category, $relativePath, $fileSize);
                                
                                if ($stmt->execute()) {
                                    $message = '<div class="alert alert-success">File uploaded and saved successfully!</div>';
                                } else {
                                    // Delete the uploaded file if database insert failed
                                    unlink($fileDestination);
                                    $message = '<div class="alert alert-danger">Error saving file information to database.</div>';
                                }
                                $stmt->close();
                            } else {
                                $message = '<div class="alert alert-danger">Error uploading file.</div>';
                            }
                        }
                    } else {
                        $message = '<div class="alert alert-danger">File is too large. Maximum size is 10MB.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Error uploading file: ' . $fileError . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Invalid file type. Allowed types: PDF, DOC, DOCX.</div>';
            }
        }
        
        // Handle file deletion
        else if ($_POST['action'] === 'delete' && isset($_POST['file']) && isset($_POST['category'])) {
            $category = $_POST['category'];
            $file = $_POST['file'];
            
            // Get file info from database first
            $stmt = $conn->prepare("SELECT * FROM `documents` WHERE filename = ? AND category = ?");
            $stmt->bind_param("ss", $file, $category);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $fileData = $result->fetch_assoc();
                $filePath = __DIR__ . '/../../' . $fileData['filepath'];
                
                // Delete from database first
                $deleteStmt = $conn->prepare("DELETE FROM `documents` WHERE filename = ? AND category = ?");
                $deleteStmt->bind_param("ss", $file, $category);
                $dbDeleteSuccess = $deleteStmt->execute();
                $deleteStmt->close();
                
                // Check if file exists and delete
                if (file_exists($filePath) && unlink($filePath)) {
                    if ($dbDeleteSuccess) {
                        $message = '<div class="alert alert-success">File deleted successfully!</div>';
                    } else {
                        $message = '<div class="alert alert-warning">File deleted from system but not from database.</div>';
                    }
                } else {
                    if ($dbDeleteSuccess) {
                        $message = '<div class="alert alert-warning">File record deleted from database but physical file not found.</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error deleting file.</div>';
                    }
                }
            } else {
                $message = '<div class="alert alert-danger">File not found in database.</div>';
            }
            $stmt->close();
        }
    }
}

// Function to get files from database
function getFilesFromDatabase($conn, $category) {
    $files = [];
    $stmt = $conn->prepare("SELECT * FROM `documents` WHERE category = ? ORDER BY upload_date DESC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
    
    $stmt->close();
    return $files;
}

// Function to get file size in human-readable format
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Function to get file icon based on extension
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'pdf':
            return '<i class="fas fa-file-pdf pdf-icon"></i>';
        case 'doc':
        case 'docx':
            return '<i class="fas fa-file-word word-icon"></i>';
        default:
            return '<i class="fas fa-file"></i>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forms Management - DepEd Schools</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin_forms.css" rel="stylesheet">
</head>
<body>
    <!-- Main Content -->
    <div class="container">
        <h1 class="page-title">School Application Forms Management</h1>
        
        <!-- Alert Message (if any) -->
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="grid grid-cols-2">
            <!-- Upload Form Section -->
            <div class="card">
                <div class="card-header">
                    Upload New Form
                </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload">
                        
                        <div class="form-row">
                            <label for="formCategory" class="form-label">Form Category:</label>
                            <select id="formCategory" name="formCategory" class="form-control" required>
                                <option value="" disabled selected>Select a category</option>
                                <option value="Renewal/Recognition Application">Renewal/Recognition Application</option>
                                <option value="New Government Permit Application">New Government Permit Application</option>
                                <option value="Tuition Fee Increase Application">Tuition Fee Increase Application</option>
                                <option value="Special Order Requirements">Special Order Requirements</option>
                                <option value="Summer Classes Application">Summer Classes Application</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <label for="formFile" class="form-label">Select File (PDF, DOC, or DOCX):</label>
                            <div class="file-input-container">
                                <input type="file" id="formFile" name="formFile" class="file-input" accept=".pdf,.doc,.docx" required>
                                <label for="formFile" class="file-label">
                                    <i class="fas fa-cloud-upload-alt file-icon"></i> Choose File
                                </label>
                                <span id="file-name" class="file-name">No file chosen</span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <label for="customFilename" class="form-label">Custom Filename (Optional):</label>
                            <input type="text" id="customFilename" name="customFilename" class="form-control" placeholder="Leave blank to use original filename">
                            <small class="form-text">Will automatically add file extension if not included</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload mr-2"></i> Upload Form
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Manage Forms Section -->
            <div class="card">
                <div class="card-header">
                    Manage Existing Forms
                </div>
                <div class="card-body">
                    <!-- Tabs -->
                    <div class="nav-tabs">
                        <button class="nav-link active" onclick="openTab('renewal_recognition')">
                            Renewal/Recognition
                        </button>
                        <button class="nav-link" onclick="openTab('gov_permit')">
                            New Government Permit
                        </button>
                        <button class="nav-link" onclick="openTab('tuition_increase')">
                            Tuition Fee Increase
                        </button>
                        <button class="nav-link" onclick="openTab('special_order')">
                            Special Order
                        </button>
                        <button class="nav-link" onclick="openTab('summer_classes')">
                            Summer Classes
                        </button>
                    </div>
                    
                    <!-- Tab Content -->
                    <?php foreach ($formCategories as $label => $dir): ?>
                        <div id="<?php echo $dir; ?>" class="tab-content <?php echo ($dir === 'renewal_recognition') ? 'active' : ''; ?>">
                            <?php 
                                $files = getFilesFromDatabase($conn, $label);
                                if (count($files) > 0):
                            ?>
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Filename</th>
                                                <th>Type</th>
                                                <th>Size</th>
                                                <th>Upload Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($files as $file): ?>
                                                <?php 
                                                    $fileExt = strtoupper(pathinfo($file['filename'], PATHINFO_EXTENSION));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="file-row">
                                                            <?php echo getFileIcon($file['filename']); ?>
                                                            <?php echo htmlspecialchars($file['filename']); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $fileExt; ?></td>
                                                    <td><?php echo formatFileSize($file['filesize']); ?></td>
                                                    <td><?php echo date('M d, Y h:i A', strtotime($file['upload_date'])); ?></td>
                                                    <td class="actions">
                                                        <a href="view_forms.php?id=<?php echo $file['id']; ?>" class="btn btn-primary btn-icon" title="View File" target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="download_forms.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-icon" title="Download File">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button class="btn btn-danger btn-icon delete-btn" 
                                                                title="Delete File" 
                                                                data-file="<?php echo htmlspecialchars($file['filename']); ?>" 
                                                                data-category="<?php echo htmlspecialchars($label); ?>"
                                                                onclick="confirmDelete('<?php echo htmlspecialchars($file['filename']); ?>', '<?php echo htmlspecialchars($label); ?>')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-folder-open empty-icon"></i>
                                    <p>No files found in this category</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete <span id="fileName"></span>?</p>
            <div class="modal-actions">
                <button id="cancelDelete" class="btn btn-primary">Cancel</button>
                <form id="deleteForm" method="post" action="">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="file" id="fileToDelete">
                    <input type="hidden" name="category" id="categoryToDelete">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // File input display
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('formFile');
            const fileName = document.getElementById('file-name');
            
            if (fileInput && fileName) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        fileName.textContent = this.files[0].name;
                    } else {
                        fileName.textContent = 'No file chosen';
                    }
                });
            }
            
            // Hide alert messages after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 5000);
            });
        });
        
        // Tab functionality
        function openTab(tabId) {
            // Hide all tab content
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to the clicked button
            event.currentTarget.classList.add('active');
        }
        
        // Delete confirmation functionality
        function confirmDelete(file, category) {
            const modal = document.getElementById('deleteModal');
            const fileNameSpan = document.getElementById('fileName');
            const fileToDelete = document.getElementById('fileToDelete');
            const categoryToDelete = document.getElementById('categoryToDelete');
            
            fileNameSpan.textContent = file;
            fileToDelete.value = file;
            categoryToDelete.value = category;
            
            modal.style.display = 'block';
        }
        
        // Modal close functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('deleteModal');
            const closeModal = document.querySelector('.close-modal');
            const cancelDelete = document.getElementById('cancelDelete');
            
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            cancelDelete.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>