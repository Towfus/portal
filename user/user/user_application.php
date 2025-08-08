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

// Define the forms categories and their corresponding directories
$formCategories = [
    'Renewal/Recognition Application' => 'renewal_recognition',
    'New Government Permit Application' => 'gov_permit',
    'Tuition Fee Increase Application' => 'tuition_increase',
    'Special Order Requirements' => 'special_order',
    'Summer Classes Application' => 'summer_classes'
];

// Function to get files from database
function getFilesFromDatabase($conn, $category) {
    $files = [];
    $stmt = $conn->prepare("SELECT * FROM `documents` WHERE category = ?");
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
    <title>School Application Forms</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #2d3748;
            background-color: #f8fafc;
            border-radius: 8px 8px 0 0;
        }
        .card-body {
            padding: 20px;
        }
        .nav-tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .nav-link {
            padding: 10px 16px;
            cursor: pointer;
            border: none;
            background: none;
            font-weight: 500;
            color: #4a5568;
            border-bottom: 2px solid transparent;
        }
        .nav-link:hover {
            color: #2b6cb0;
        }
        .nav-link.active {
            color: #2b6cb0;
            border-bottom: 2px solid #2b6cb0;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .table th {
            font-weight: 600;
            color: #4a5568;
            background-color: #f8fafc;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background-color: #4299e1;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3182ce;
        }
        .btn-success {
            background-color: #48bb78;
            color: white;
        }
        .btn-success:hover {
            background-color: #38a169;
        }
        .btn-icon {
            padding: 6px 10px;
            border-radius: 4px;
        }
        .file-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pdf-icon {
            color: #e53e3e;
        }
        .word-icon {
            color: #2b6cb0;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #718096;
        }
        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            color: #cbd5e0;
        }
        .grid {
            display: grid;
            gap: 20px;
        }
        @media (min-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include 'user_header.php'; ?>
    
    <!-- Main Content -->
    <div class="container">
        <h1 class="page-title">School Application Forms</h1>
        
        <div class="grid grid-cols-1">
            <!-- Forms Section -->
            <div class="card">
                <div class="card-header">
                    Available Forms
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
                                                        <a href="/forms/<?php echo $formCategories[$file['category']] . '/' . rawurlencode($file['filename']); ?>" 
                                                            class="btn btn-primary btn-icon" 
                                                            title="View File" 
                                                            target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="download.php?file=<?php echo urlencode($file['filepath']); ?>" class="btn btn-success btn-icon" title="Download File">
                                                            <i class="fas fa-download"></i>
                                                        </a>
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
    
    <script>
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
    </script>
</body>
</html>