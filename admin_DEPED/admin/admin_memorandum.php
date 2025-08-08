<?php
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

// Check if the memorandums table exists
$checkTableSql = "SHOW TABLES LIKE 'memorandums'";
$tableExists = $conn->query($checkTableSql)->num_rows > 0;

// Create memorandums table if it doesn't exist
if (!$tableExists) {
    $createTableSql = "CREATE TABLE memorandums (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(255) NOT NULL,
        upload_date DATETIME NOT NULL
    )";
    
    if ($conn->query($createTableSql) === TRUE) {
        // Table created successfully
        $message = "Memorandums table created successfully";
    } else {
        $error = "Error creating memorandums table: " . $conn->error;
    }
}

// Handle memorandum operations
if (isset($_POST['action'])) {
    // Upload memorandum
    if ($_POST['action'] == 'upload_memorandum') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        // Handle file upload
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        // In the upload section, modify the path handling:
        $target_dir = __DIR__ . '/../../shared/memorandums/';

        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_filename = basename($_FILES["pdf_file"]["name"]);
        $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        $new_filename = 'memo_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Store this in database (relative to document root)
        $relative_path = "/php_projects/shared/memorandums/" . $new_filename;
        $sql = "INSERT INTO memorandums (file_path, ...) VALUES ('$relative_path', ...)";
            // Check if file is a PDF
            if (strtolower($file_extension) != "pdf") {
                $error = "Sorry, only PDF files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                    // Make sure we store a correct relative path
                    $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($target_file));
                    if (empty($relative_path)) {
                        $relative_path = $target_file; // Fallback to the original path
                    }
                    
                    // Insert into database
                    $sql = "INSERT INTO memorandums (title, description, file_path, upload_date) VALUES (?, ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                   $stmt->bind_param("sss", $title, $description, $relative_path);
                    
                    if ($stmt->execute()) {
                        $message = "Memorandum uploaded successfully";
                        // Log successful upload
                        error_log("Memorandum uploaded successfully: $title, Path: $target_file");
                    } else {
                        $error = "Error adding memorandum to database: " . $conn->error;
                        error_log("Database error when uploading memorandum: " . $conn->error);
                    }
                } else {
                    $error = "Sorry, there was an error uploading your file.";
                    error_log("File upload failed for memorandum: $title");
                }
            }
        } else {
            $error = "Please select a PDF file to upload";
            if (isset($_FILES['pdf_file'])) {
                error_log("File upload error code: " . $_FILES['pdf_file']['error']);
            }
        }
    }
    
    // Delete memorandum
    elseif ($_POST['action'] == 'delete_memorandum') {
        $id = $_POST['id'];
        
        // Get file path before deleting record
        $sql = "SELECT file_path FROM memorandums WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $file_path = $row['file_path'];
            
            // Delete from database
            $sql = "DELETE FROM memorandums WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Delete the file
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $message = "Memorandum deleted successfully";
            } else {
                $error = "Error deleting memorandum: " . $conn->error;
            }
        } else {
            $error = "Memorandum not found";
        }
    }
    
    // Bulk delete memorandums
    elseif ($_POST['action'] == 'bulk_delete_memorandums') {
        if (isset($_POST['selected_memorandums']) && !empty($_POST['selected_memorandums'])) {
            $selected_memorandums = $_POST['selected_memorandums'];
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($selected_memorandums as $memo_id) {
                // Get file path before deleting record
                $sql = "SELECT file_path FROM memorandums WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $memo_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $file_path = $row['file_path'];
                    
                    // Delete from database
                    $sql = "DELETE FROM memorandums WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $memo_id);
                    
                    if ($stmt->execute()) {
                        // Delete the file
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            }
            
            if ($successCount > 0 && $errorCount == 0) {
                $message = "$successCount memorandums deleted successfully";
            } elseif ($successCount > 0 && $errorCount > 0) {
                $message = "$successCount memorandums deleted successfully, but $errorCount failed";
            } else {
                $error = "Error deleting memorandums";
            }
        } else {
            $error = "No memorandums selected for deletion";
        }
    }
}



function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}

// Fetch all memorandums for display
$memosSql = "SELECT * FROM memorandums ORDER BY upload_date DESC";
$memosResult = $conn->query($memosSql);

// Helper function to get the base URL of the application
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . '/';
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_memorandum.css">
        
</head>
<body class="bg-gray-100">
    <?php include 'admin_header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-blue-800 mb-6">Memorandum Management</h1>
        
        <?php if (isset($message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Upload New Memorandum</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_memorandum">
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Title:</label>
                    <input type="text" name="title" class="w-full px-3 py-2 border rounded" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">PDF File:</label>
                    <div class="file-input-wrapper">
                        <label for="pdf_file" id="file-label" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt mr-2"></i> Choose a PDF file
                        </label>
                        <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                        <div id="file-name" class="text-sm text-gray-500 mt-1">No file chosen</div>
                    </div>
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                    <i class="fas fa-upload mr-2"></i> Upload Memorandum
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex border-b mb-4">
                <button class="px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600">All Memorandums</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Filename</th>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-left">Size</th>
                            <th class="px-4 py-2 text-left">Upload Date</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($memosResult && $memosResult->num_rows > 0): ?>
                            <?php while ($row = $memosResult->fetch_assoc()): ?>
                            <?php
                            // Get file information
                            $file_path = $row['file_path'];
                            $file_info = pathinfo($file_path);
                            $file_ext = isset($file_info['extension']) ? $file_info['extension'] : 'PDF';
                            
                            // Get file size if file exists
                            $full_path = $_SERVER['DOCUMENT_ROOT'] . $file_path;
                            $file_size = file_exists($full_path) ? formatSizeUnits(filesize($full_path)) : 'N/A';
                            ?>
                            <tr class="border-b">
                                <td class="px-4 py-3">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </td>
                                <td class="px-4 py-3"><?php echo strtoupper($file_ext); ?></td>
                                <td class="px-4 py-3"><?php echo $file_size; ?></td>
                                <td class="px-4 py-3"><?php echo date('M d, Y h:i A', strtotime($row['upload_date'])); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex space-x-2">
                                        <a href="view_memo.php?id=<?php echo $row['id']; ?>" 
                                        target="_blank"  
                                        class="action-icon bg-blue-500 hover:bg-blue-600">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="download_memo.php?id=<?php echo $row['id']; ?>" 
                                        class="action-icon bg-green-500 hover:bg-green-600">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['title']); ?>')" 
                                                class="action-icon bg-red-500 hover:bg-red-600">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">No memorandums found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close absolute top-2 right-4 text-gray-500 cursor-pointer text-2xl" onclick="closeModal()">&times;</span>
            <div class="text-center mb-6">
                <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4"></i>
                <h2 class="text-xl font-bold">Delete Memorandum</h2>
            </div>
            <p class="text-center mb-2">Are you sure you want to delete <strong id="deleteFileName"></strong>?</p>
            <p class="text-center text-red-600 mb-6">This action cannot be undone.</p>
            <div class="flex justify-center space-x-4">
                <button onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                    Cancel
                </button>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="action" value="delete_memorandum">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // File input display
        document.getElementById('pdf_file').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });

        // Delete confirmation
        function confirmDelete(id, title) {
            document.getElementById('deleteFileName').textContent = title;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        // Close modal
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('deleteModal')) {
                closeModal();
            }
        }

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(msg => {
                msg.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>