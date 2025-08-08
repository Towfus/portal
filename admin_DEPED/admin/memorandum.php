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
if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
    $target_dir = "memorandums/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $original_filename = basename($_FILES["pdf_file"]["name"]);
    $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    
    // Generate a unique filename
    $new_filename = uniqid('memo_') . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if file is a PDF
    if (strtolower($file_extension) != "pdf") {
        $error = "Sorry, only PDF files are allowed.";
    } else {
        // Debug original file info
        error_log("Attempting to upload file:");
        error_log("Original name: " . $_FILES["pdf_file"]["name"]);
        error_log("Temp location: " . $_FILES["pdf_file"]["tmp_name"]);
        error_log("Target location: " . $target_file);
        
        if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
            // Verify the file was actually moved
            if (file_exists($target_file)) {
                error_log("File successfully moved to: " . $target_file);
                
                // Insert into database
                $sql = "INSERT INTO memorandums (title, description, file_path, upload_date) VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $title, $description, $target_file);
                
                if ($stmt->execute()) {
                    $message = "Memorandum uploaded successfully";
                    error_log("Memorandum uploaded successfully: $title, Path: $target_file");
                } else {
                    $error = "Error adding memorandum to database: " . $conn->error;
                    error_log("Database error when uploading memorandum: " . $conn->error);
                    // Clean up the uploaded file if DB insert failed
                    unlink($target_file);
                }
            } else {
                $error = "File upload failed - target file not found after move operation";
                error_log("File move operation failed - target not found: " . $target_file);
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
            error_log("move_uploaded_file() failed for: " . $_FILES["pdf_file"]["tmp_name"] . " to " . $target_file);
            error_log("Upload error: " . $_FILES['pdf_file']['error']);
        }
    }
} else {
    $error = "Please select a PDF file to upload";
    if (isset($_FILES['pdf_file'])) {
        error_log("File upload error code: " . $_FILES['pdf_file']['error']);
    }
}

// Fetch all memorandums for display
$memosSql = "SELECT * FROM memorandums ORDER BY upload_date DESC";
$memosResult = $conn->query($memosSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <title>Memorandum Management - DepEd General Trias City</title>
</head>
<body>
    <?php include 'admin_sidebar.php';?>
    <div class="main main-with-navbar">
        <?php include 'admin_header.php';?>
        
        <div class="container">
            <?php if (isset($message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <h1 class="text-2xl font-bold mb-4">Memorandum Management</h1>
            
            <div class="selection-header flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center" onclick="openModal('addMemoModal')">
                        <i class="fas fa-upload mr-2"></i> Upload New Memorandum
                    </button>
                    <button id="bulkDeleteMemos" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded flex items-center bulk-delete" onclick="confirmBulkDelete()" disabled>
                        <i class="fas fa-trash mr-2"></i> Delete Selected
                    </button>
                </div>
            </div>
            
            <form id="memosForm" method="POST" action="">
                <input type="hidden" name="action" value="bulk_delete_memorandums">
                
                <table class="table">
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" id="selectAllMemos" onchange="selectAll()">
                            </th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>File</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($memosResult && $memosResult->num_rows > 0): ?>
                            <?php while ($row = $memosResult->fetch_assoc()): ?>
                                <tr>
                                    <td class="checkbox-cell">
                                        <input type="checkbox" name="selected_memorandums[]" value="<?php echo $row['id']; ?>" class="memo-checkbox" onchange="updateBulkActionButtons()">
                                    </td>
                                    <td class="font-medium"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            <span class="file-badge"><?php echo basename($row['file_path']); ?></span>
                                        </a>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($row['upload_date'])); ?></td>
                                    <td class="action-buttons">
                                        <button type="button" class="btn-delete" onclick="confirmDeleteMemo(<?php echo $row['id']; ?>, '<?php echo addslashes($row['title']); ?>')">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No memorandums found. Upload a new memorandum to get started.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
            
            <!-- Add Memorandum Modal -->
            <div id="addMemoModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('addMemoModal')">&times;</span>
                    <h2 class="text-xl font-bold mb-4">Upload New Memorandum</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_memorandum">
                        
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" id="title" name="title" placeholder="Enter memorandum title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" placeholder="Enter memorandum description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>PDF File:</label>
                            <div class="file-input-wrapper">
                                <label for="pdf_file" id="file-label">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i> Choose a PDF file
                                </label>
                                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                                <div class="file-name" id="file-name"></div>
                            </div>
                        </div>
                        
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                            <i class="fas fa-upload mr-2"></i> Upload Memorandum
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Delete Memorandum Modal -->
            <div id="deleteMemoModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('deleteMemoModal')">&times;</span>
                    <div class="text-center mb-6">
                        <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4"></i>
                        <h2 class="text-xl font-bold">Delete Memorandum</h2>
                    </div>
                    <p class="text-center mb-2">Are you sure you want to delete:</p>
                    <p class="text-center font-bold mb-4" id="delete_memo_name"></p>
                    <p class="text-center text-red-600 mb-6">This action cannot be undone.</p>
                    <form method="POST" action="" class="mt-4">
                        <input type="hidden" name="action" value="delete_memorandum">
                        <input type="hidden" id="delete_memo_id" name="id">
                        <div class="flex gap-3 justify-center">
                            <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded" onclick="closeModal('deleteMemoModal')">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                <i class="fas fa-trash mr-2"></i> Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Bulk Delete Modal -->
            <div id="bulkDeleteModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal('bulkDeleteModal')">&times;</span>
                    <div class="text-center mb-6">
                        <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4"></i>
                        <h2 class="text-xl font-bold">Bulk Delete</h2>
                    </div>
                    <p class="text-center mb-2">Are you sure you want to delete all selected memorandums?</p>
                    <p class="text-center" id="bulk_delete_count"></p>
                    <p class="text-center text-red-600 mb-6">This action cannot be undone.</p>
                    <div class="flex gap-3 justify-center mt-4">
                        <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded" onclick="closeModal('bulkDeleteModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="button" id="confirmBulkDeleteBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                            <i class="fas fa-trash mr-2"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'admin_footer.php'; ?>
    </div>
    
    <script>
        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // File input display
        document.getElementById('pdf_file').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : '';
            document.getElementById('file-name').textContent = fileName;
        });
        
        // Delete memorandum confirmation
        function confirmDeleteMemo(id, title) {
            document.getElementById('delete_memo_id').value = id;
            document.getElementById('delete_memo_name').textContent = title;
            openModal('deleteMemoModal');
        }
        
        // Select all memorandums
        function selectAll() {
            const selectAllCheckbox = document.getElementById('selectAllMemos');
            const checkboxes = document.querySelectorAll('.memo-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateBulkActionButtons();
        }
        
        // Update bulk action buttons based on selection
        function updateBulkActionButtons() {
            const checkboxes = document.querySelectorAll('.memo-checkbox:checked');
            const bulkDeleteButton = document.getElementById('bulkDeleteMemos');
            
            bulkDeleteButton.disabled = checkboxes.length === 0;
        }
        
        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkboxes = document.querySelectorAll('.memo-checkbox:checked');
            const count = checkboxes.length;
            
            if (count > 0) {
                document.getElementById('bulk_delete_count').innerHTML = `<strong>${count}</strong> memorandum${count > 1 ? 's' : ''} selected for deletion`;
                openModal('bulkDeleteModal');
                
                // Set up the confirm button to submit the memos form
                document.getElementById('confirmBulkDeleteBtn').onclick = function() {
                    document.getElementById('memosForm').submit();
                };
            }
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            var modals = document.getElementsByClassName("modal");
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = "none";
                }
            }
        }
        
        // Auto-hide success and error messages after 5 seconds
        setTimeout(function() {
            var messages = document.getElementsByClassName("message");
            for (var i = 0; i < messages.length; i++) {
                messages[i].style.display = "none";
            }
        }, 5000);
    </script>
</body>
</html>