<?php
// user_forms.php - User forms viewing and download
session_start();

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

// Define the forms categories
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
            return '<i class="fas fa-file-pdf" style="color: #dc3545;"></i>';
        case 'doc':
        case 'docx':
            return '<i class="fas fa-file-word" style="color: #007bff;"></i>';
        default:
            return '<i class="fas fa-file" style="color: #6c757d;"></i>';
    }
}

// Get total files count for dashboard
$totalFiles = 0;
foreach ($formCategories as $label => $dir) {
    $files = getFilesFromDatabase($conn, $label);
    $totalFiles += count($files);
}
include 'user_header.php';
?>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Application Forms - DepEd Schools</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e7e34;
            --secondary-color: #28a745;
            --success-color: #27ae60;
            --info-color: #3498db;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar {
            background: var(--gradient);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.5rem;
        }

        .main-container {
            padding: 2rem 0;
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(30, 126, 52, 0.9), rgba(40, 167, 69, 0.9)),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>');
            background-size: cover;
            color: white;
            border-radius: 15px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
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
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.15);
        }

        .card-header {
            background: var(--gradient);
            color: white;
            font-weight: 600;
            padding: 1.5rem;
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

        .card-body {
            padding: 1.5rem;
        }

        .nav-tabs {
            border-bottom: 3px solid var(--border-color);
            margin-bottom: 1.5rem;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--primary-color);
            padding: 1rem 1.5rem;
            margin-right: 0.5rem;
            border-radius: 10px 10px 0 0;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-tabs .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--secondary-color);
            transition: width 0.3s ease;
        }

        .nav-tabs .nav-link:hover::before {
            width: 100%;
        }

        .nav-tabs .nav-link:hover {
            background: rgba(40, 167, 69, 0.1);
            transform: translateY(-2px);
        }

        .nav-tabs .nav-link.active {
            background: var(--secondary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .nav-tabs .nav-link.active::before {
            width: 100%;
            background: rgba(255,255,255,0.3);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeInUp 0.5s ease;
        }

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

        .table {
            border-radius: 10px;
            overflow: hidden;
            background: white;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: none;
            font-weight: 600;
            color: var(--primary-color);
            padding: 1.25rem 1rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            border-color: rgba(0,0,0,0.05);
            padding: 1.25rem 1rem;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(40, 167, 69, 0.05);
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .file-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .file-name {
            font-weight: 500;
            color: #333;
        }

        .btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #219a52);
            border: none;
        }

        .btn-icon {
            width: 45px;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            border-radius: 10px;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.5;
            color: var(--secondary-color);
        }

        .empty-state h5 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-input {
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 50px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        .category-badge {
            display: inline-block;
            background: var(--gradient);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1rem;
            }
            
            .nav-tabs .nav-link {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
            
            .btn-icon {
                width: 40px;
                height: 40px;
                margin-right: 0.25rem;
            }
            
            .stats-number {
                font-size: 2rem;
            }
        }

        .floating-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: var(--gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(0,0,0,0.3);
        }
    </style>


    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                DepEd Schools - Application Forms
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-container">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <h1 class="display-5 fw-bold mb-3">
                            <i class="fas fa-file-alt me-3"></i>
                            School Application Forms
                        </h1>
                        <p class="lead mb-0">
                            Access and download all required application forms for your school needs. 
                            All forms are organized by category for easy navigation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Forms Section -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-folder-open me-2"></i>
                Available Application Forms
            </div>
            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="#renewal_recognition" onclick="openTab(event, 'renewal_recognition')">
                            <i class="fas fa-sync-alt me-2"></i>Renewal/Recognition
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gov_permit" onclick="openTab(event, 'gov_permit')">
                            <i class="fas fa-certificate me-2"></i>Government Permit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tuition_increase" onclick="openTab(event, 'tuition_increase')">
                            <i class="fas fa-chart-line me-2"></i>Tuition Increase
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#special_order" onclick="openTab(event, 'special_order')">
                            <i class="fas fa-star me-2"></i>Special Order
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#summer_classes" onclick="openTab(event, 'summer_classes')">
                            <i class="fas fa-sun me-2"></i>Summer Classes
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <?php foreach ($formCategories as $label => $dir): ?>
                    <div id="<?php echo $dir; ?>" class="tab-content <?php echo ($dir === 'renewal_recognition') ? 'active' : ''; ?>">
                        <div class="category-badge">
                            <i class="fas fa-tag me-1"></i><?php echo $label; ?>
                        </div>
                        
                        <?php 
                            $files = getFilesFromDatabase($conn, $label);
                            if (count($files) > 0):
                        ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-file me-2"></i>File Name</th>
                                            <th><i class="fas fa-tag me-2"></i>Type</th>
                                            <th><i class="fas fa-weight me-2"></i>Size</th>
                                            <th><i class="fas fa-calendar me-2"></i>Date Added</th>
                                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($files as $file): ?>
                                            <?php $fileExt = strtoupper(pathinfo($file['filename'], PATHINFO_EXTENSION)); ?>
                                            <tr class="searchable-row">
                                                <td>
                                                    <div class="file-row">
                                                        <?php echo getFileIcon($file['filename']); ?>
                                                        <span class="file-name"><?php echo htmlspecialchars($file['filename']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $fileExt; ?></span>
                                                </td>
                                                <td><?php echo formatFileSize($file['filesize']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($file['upload_date'])); ?></td>
                                                <td>
                                                    <a href="view_forms.php?id=<?php echo $file['id']; ?>" 
                                                       class="btn btn-primary btn-icon" title="View File" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="download_forms.php?id=<?php echo $file['id']; ?>" 
                                                       class="btn btn-success btn-icon" title="Download File">
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
                                <i class="fas fa-folder-open"></i>
                                <h5>No Forms Available</h5>
                                <p>There are currently no forms available in this category. Please check back later or contact the administration office for more information.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab functionality with smooth transitions
        function openTab(event, tabId) {
            event.preventDefault();
            
            // Hide all tab content with fade out
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => {
                tab.style.opacity = '0';
                setTimeout(() => {
                    tab.classList.remove('active');
                }, 150);
            });
            
            // Remove active class from all tab buttons
            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(link => {
                link.classList.remove('active');
            });
            
            // Show the selected tab content with fade in
            setTimeout(() => {
                const targetTab = document.getElementById(tabId);
                targetTab.classList.add('active');
                targetTab.style.opacity = '1';
            }, 200);
            
            // Add active class to the clicked button
            event.currentTarget.classList.add('active');
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.searchable-row');
            
            rows.forEach(row => {
                const fileName = row.querySelector('.file-name').textContent.toLowerCase();
                if (fileName.includes(searchTerm)) {
                    row.style.display = '';
                    row.style.animation = 'fadeInUp 0.3s ease';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Smooth scroll and loading effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Add click effects to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    ripple.style.left = e.offsetX + 'px';
                    ripple.style.top = e.offsetY + 'px';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
        
        // Help functionality
        function showHelp() {
            const helpText = `
                📋 How to use this portal:
                
                1. 📂 Browse forms by category using the tabs
                2. 🔍 Use the search box to find specific forms
                3. 👁️ Click the eye icon to view forms online
                4. 💾 Click the download icon to save forms to your device
                
                💡 Tips:
                • PDF files can be viewed directly in your browser
                • Word documents will be downloaded for editing
                • All forms are official DepEd documents
                
                Need additional help? Contact the school administration office.
            `;
            
            alert(helpText);
        }
        
        // Add ripple effect CSS
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                pointer-events: none;
                transform: scale(0);
                animation: rippleAnimation 0.6s linear;
                width: 20px;
                height: 20px;
                margin-left: -10px;
                margin-top: -10px;
            }
            
            @keyframes rippleAnimation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Add smooth scrolling for better UX
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

<?php
// Close database connection
$conn->close();
?>