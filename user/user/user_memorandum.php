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

// Helper function to format file sizes
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

// Function to get file icon based on extension
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'pdf':
            return '<i class="fas fa-file-pdf" style="color: #dc3545;"></i>';
        case 'doc':
        case 'docx':
            return '<i class="fas fa-file-word" style="color: #007bff;"></i>';
        case 'xls':
        case 'xlsx':
            return '<i class="fas fa-file-excel" style="color: #28a745;"></i>';
        case 'ppt':
        case 'pptx':
            return '<i class="fas fa-file-powerpoint" style="color: #fd7e14;"></i>';
        default:
            return '<i class="fas fa-file" style="color: #6c757d;"></i>';
    }
}

// Fetch all memorandums for display
$memosSql = "SELECT * FROM memorandums ORDER BY upload_date DESC";
$memosResult = $conn->query($memosSql);
$totalMemos = $memosResult ? $memosResult->num_rows : 0;

// Function to get correct file URL
function getFileUrl($file_path) {
    // Remove document root if present
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $file_path = str_replace('\\', '/', $file_path);
    
    if (strpos($file_path, $doc_root) === 0) {
        $file_path = substr($file_path, strlen($doc_root));
    }
    
    // Ensure path starts with a slash
    $file_path = '/' . ltrim($file_path, '/');
    
    return $file_path;
}

include 'user_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memorandums - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e7e34;
            --secondary-color: #28a745;
            --success-color: #27ae60;
            --info-color: #3498db;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --text-dark: #2c3e50;
            --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            --card-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: var(--text-dark);
        }

        .main-container {
            padding: 2rem 0;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(30, 126, 52, 0.9), rgba(40, 167, 69, 0.9)),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>');
            background-size: cover;
            color: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 40%;
            height: 200%;
            background: rgba(255,255,255,0.05);
            transform: rotate(15deg);
            border-radius: 50px;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .stats-card {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.3);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Main Content Card */
        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.15);
        }

        .card-header {
            background: var(--gradient);
            color: white;
            padding: 2rem;
            border: none;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, rgba(255,255,255,0.3), transparent);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .card-body {
            padding: 2rem;
        }

        /* Search Box */
        .search-container {
            position: relative;
            margin-bottom: 2rem;
        }

        .search-box {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        .search-input {
            width: 100%;
            padding: 1.2rem 1.2rem 1.2rem 3.5rem;
            border: 2px solid var(--border-color);
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .search-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        .clear-search {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .clear-search:hover {
            background: rgba(108, 117, 125, 0.1);
            color: var(--danger-color);
        }

        /* Table Styles */
        .table-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            background: white;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: none;
            font-weight: 600;
            color: var(--primary-color);
            padding: 1.5rem 1.2rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            position: relative;
        }

        .table thead th:first-child {
            border-top-left-radius: 15px;
        }

        .table thead th:last-child {
            border-top-right-radius: 15px;
        }

        .table tbody td {
            border-color: rgba(0,0,0,0.05);
            padding: 1.5rem 1.2rem;
            vertical-align: middle;
            transition: all 0.3s ease;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: linear-gradient(90deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.02));
            transform: translateX(5px);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-icon {
            font-size: 1.5rem;
            min-width: 30px;
        }

        .file-details h6 {
            margin: 0;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1rem;
        }

        .file-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .action-btn {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .btn-view {
            background: linear-gradient(135deg, var(--info-color), #2980b9);
        }

        .btn-download {
            background: linear-gradient(135deg, var(--success-color), #219a52);
        }

        /* Badges */
        .file-type-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pdf {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .badge-doc {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .badge-default {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
            color: var(--secondary-color);
        }

        .empty-state h5 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .empty-state p {
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1rem;
                text-align: center;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .stats-number {
                font-size: 2rem;
            }

            .main-container {
                padding: 1rem 0;
            }

            .card-body {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .action-btn {
                width: 40px;
                height: 40px;
            }

            .table-responsive {
                border-radius: 15px;
            }

            .file-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Loading Animation */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <!-- Hero Section -->
        <div class="hero-section fade-in">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <h1 class="display-5 fw-bold mb-3">
                            <i class="fas fa-file-text me-3"></i>
                            Official Memorandums
                        </h1>
                        <p class="lead mb-0">
                            Access and download official memorandums from DepEd General Trias City. 
                            All documents are organized chronologically for easy reference.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-card fade-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-folder-open"></i>
                    Memorandum
                </h2>
            </div>
            <div class="card-body">
                <!-- Search Box -->
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Search memorandums by title, type, or date...">
                        <span id="clearSearch" class="clear-search" style="display: none;">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="table-container">
                    <?php if ($memosResult && $memosResult->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table" id="memosTable">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file-alt me-2"></i>Document Information</th>
                                        <th><i class="fas fa-tag me-2"></i>Type</th>
                                        <th><i class="fas fa-weight me-2"></i>File Size</th>
                                        <th><i class="fas fa-calendar me-2"></i>Date Published</th>
                                        <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Reset result pointer
                                    $memosResult->data_seek(0);
                                    while ($row = $memosResult->fetch_assoc()): 
                                    ?>
                                        <?php
                                        // Get full file path and URL
                                        $full_path = $_SERVER['DOCUMENT_ROOT'] . $row['file_path'];
                                        $file_url = getFileUrl($row['file_path']);
                                        $file_ext = pathinfo($row['file_path'], PATHINFO_EXTENSION);
                                        $file_size = file_exists($full_path) ? formatSizeUnits(filesize($full_path)) : 'N/A';
                                        $upload_date = new DateTime($row['upload_date']);
                                        ?>
                                        <tr class="searchable-row">
                                            <td>
                                                <div class="file-info">
                                                    <div class="file-icon">
                                                        <?php echo getFileIcon($row['file_path']); ?>
                                                    </div>
                                                    <div class="file-details">
                                                        <h6><?php echo htmlspecialchars($row['title']); ?></h6>
                                                        <div class="file-meta">
                                                            Document ID: #MEM-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="file-type-badge <?php echo 'badge-' . strtolower($file_ext); ?>">
                                                    <?php echo strtoupper($file_ext); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo $file_size; ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo $upload_date->format('M d, Y'); ?></strong>
                                                    <div class="file-meta"><?php echo $upload_date->format('h:i A'); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank" 
                                                       class="action-btn btn-view" title="View Document">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo htmlspecialchars($file_url); ?>" download 
                                                       class="action-btn btn-download" title="Download Document">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <h5>No Memorandums Available</h5>
                            <p>There are currently no memorandums published. Please check back later or contact the administration office for more information.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced search functionality
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        
        searchInput.addEventListener('input', function() {
            const input = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.searchable-row');
            let visibleRows = 0;
            
            // Show/hide clear button
            clearSearch.style.display = input.length > 0 ? 'block' : 'none';
            
            rows.forEach((row, index) => {
                const title = row.querySelector('.file-details h6').textContent.toLowerCase();
                const fileType = row.cells[1].textContent.toLowerCase();
                const date = row.cells[3].textContent.toLowerCase();
                
                const isVisible = input === '' || title.includes(input) || 
                                fileType.includes(input) || date.includes(input);
                
                if (isVisible) {
                    row.style.display = '';
                    row.style.animation = `fadeInUp 0.3s ease ${index * 0.05}s both`;
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update results info (optional enhancement)
            updateSearchResults(visibleRows, rows.length);
        });
        
        // Clear search functionality
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
        
        // Update search results info
        function updateSearchResults(visible, total) {
            // You can add a results counter here if needed
            console.log(`Showing ${visible} of ${total} memorandums`);
        }
        
        // Loading animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add staggered animation to table rows
            const rows = document.querySelectorAll('.searchable-row');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Add click animation to action buttons
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
            
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('.searchable-row');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                    this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Focus search on Ctrl+F or Cmd+F
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            
            // Clear search on Escape
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                clearSearch.click();
            }
        });
        
        // Auto-focus search input after page load
        window.addEventListener('load', function() {
            setTimeout(() => {
                if (window.innerWidth > 768) { // Only on desktop
                    searchInput.focus();
                }
            }, 1000);
        });
        
        // Add ripple effect to buttons
        function createRipple(event) {
            const button = event.currentTarget;
            const circle = document.createElement("span");
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;
            
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
            circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
            circle.classList.add("ripple");
            
            const ripple = button.getElementsByClassName("ripple")[0];
            if (ripple) {
                ripple.remove();
            }
            
            button.appendChild(circle);
        }
        
        // Add ripple effect styles
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            .action-btn {
                position: relative;
                overflow: hidden;
            }
            
            .ripple {
                position: absolute;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);
        
        // Apply ripple effect to all action buttons
        document.querySelectorAll('.action-btn').forEach(button => {
            button.addEventListener('click', createRipple);
        });
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>