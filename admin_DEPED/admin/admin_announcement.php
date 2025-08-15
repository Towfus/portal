<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "deped_schools");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Handle delete announcement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_announcement'])) {
    $id = intval($_POST['id']);
    
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Announcement deleted successfully!';
        header("Location: admin_announcement.php");
        exit();
    } else {
        $message = '<div class="alert error">Error: ' . $conn->error . '</div>';
    }
}

// Handle delete banner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_banner'])) {
    $id = intval($_POST['id']);
    
    // Get banner path before deletion
    $stmt = $conn->prepare("SELECT banner_path FROM banners WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['banner_path'])) unlink($row['banner_path']);
    }
    
    // Delete banner
    $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Banner deleted successfully!';
        header("Location: admin_announcement.php");
        exit();
    } else {
        $message = '<div class="alert error">Error: ' . $conn->error . '</div>';
    }
}

// Handle new banner upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['banner_image'])) {
    $upload_dir = '../banners/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);


    
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (in_array($_FILES['banner_image']['type'], $allowed_types) && $_FILES['banner_image']['size'] <= $max_size) {
        $filename = uniqid() . '_' . basename($_FILES['banner_image']['name']);
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("INSERT INTO banners (banner_path) VALUES (?)");
            $stmt->bind_param("s", $target_path);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Banner uploaded successfully!';
                header("Location: admin_announcement.php");
                exit();
            } else {
                $message = '<div class="alert error">Database error: ' . $conn->error . '</div>';
            }
        } else {
            $message = '<div class="alert error">File upload failed</div>';
        }
    } else {
        $message = '<div class="alert error">Invalid file type or size > 2MB</div>';
    }
}

// Handle new announcement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    
    // Insert announcement
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, is_active, start_date, end_date) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $title, $content, $is_active, $start_date, $end_date);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Announcement created successfully!';
        header("Location: admin_announcement.php");
        exit();
    } else {
        $message = '<div class="alert error">Error: ' . $conn->error . '</div>';
    }
}

// Check for success message from session
if (isset($_SESSION['success_message'])) {
    $message = '<div class="alert success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

// Fetch data
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$banners = $conn->query("SELECT * FROM banners ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$conn->close();
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
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            position: relative;
        }
        .file-input-wrapper {
            display: block;
            padding: 0.75rem;
            background-color: #f3f4f6;
            border: 2px dashed #cbd5e1;
            border-radius: 0.375rem;
            text-align: center;
            cursor: pointer;
        }
        #file-input-wrapper:hover {
            border-color: #93c5fd;
        }
        .file-input-wrapper input[type="file"] {
            display: none;
        }
        .action-icon {
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .banner-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .banner-item {
            position: relative;
            width: 200px;
            height: 100px;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        .banner-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .banner-item .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-weight: 500;
        }
        .alert.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }
        .alert.error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'admin_header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-blue-800 mb-6">Banner & Announcement Management</h1>
        
        <?php echo $message; ?>

        <!-- Banner Management Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Manage Banners</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_banner">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Upload New Banner:</label>
                    <div class="file-input-wrapper">
                        <label for="banner_image" id="banner-label" class="cursor-pointer">
                            <i class="fas fa-cloud-upload-alt mr-2"></i> Choose Banner Image
                        </label>
                        <input type="file" id="banner_image" name="banner_image" accept="image/*" required>
                        <div id="banner-name" class="text-sm text-gray-500 mt-1">No file chosen</div>
                    </div>
                    <p class="text-xs text-gray-500">Allowed formats: JPG, PNG, GIF. Max size: 2MB</p>
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                    <i class="fas fa-image mr-2"></i> Upload Banner
                </button>
            </form>

            <?php if (!empty($banners)): ?>
                <div class="mt-6">
                    <h3 class="text-lg font-medium mb-2">Current Banners</h3>
                    <div class="banner-container">
                        <?php foreach ($banners as $banner): ?>
                            <div class="banner-item">
                                <img src="<?php echo htmlspecialchars($banner['banner_path']); ?>" alt="Banner">
                                <button onclick="confirmDelete(<?php echo $banner['id']; ?>, 'Banner', 'banner')" 
                                    class="delete-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Regular Announcements Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Create New Announcement</h2>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Title:</label>
                    <input type="text" name="title" class="w-full px-3 py-2 border rounded" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Content:</label>
                    <textarea name="content" class="w-full px-3 py-2 border rounded" rows="4" required></textarea>
                </div>
                
                <div class="flex space-x-4 mb-4">
                    <div class="flex-1">
                        <label class="block text-gray-700 mb-2">Start Date:</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2 border rounded" required>
                    </div>
                    <div class="flex-1">
                        <label class="block text-gray-700 mb-2">End Date:</label>
                        <input type="date" name="end_date" class="w-full px-3 py-2 border rounded" required>
                    </div>
                </div>
                
                <div class="mb-4 flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" class="mr-2" checked>
                    <label for="is_active" class="text-gray-700">Active Announcement</label>
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                    <i class="fas fa-bullhorn mr-2"></i> Create Announcement
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex border-b mb-4">
                <button class="px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600">All Announcements</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Date Range</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($announcements)): ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <tr class="border-b">
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($announcement['title']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="<?php echo $announcement['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> px-2 py-1 rounded-full text-xs">
                                            <?php echo $announcement['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php echo date('M d, Y', strtotime($announcement['start_date'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($announcement['end_date'])); ?>
                                    </td>
                                    <!-- In the announcements table, fix the delete button: -->
                                <td class="px-4 py-3">
                                    <div class="flex space-x-2">
                                        <button onclick="confirmDelete(<?php echo $announcement['id']; ?>, '<?php echo addslashes($announcement['title']); ?>', 'announcement')" 
                                                class="action-icon bg-red-500 hover:bg-red-600">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">No announcements found</td>
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
            <h2 class="text-xl font-bold">Delete Confirmation</h2>
        </div>
        <p class="text-center mb-2">Are you sure you want to delete <strong id="deleteTitle"></strong>?</p>
        <p class="text-center text-red-600 mb-6">This action cannot be undone.</p>
        <div class="flex justify-center space-x-4">
            <button onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                Cancel
            </button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" name="delete_action" id="deleteButton" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // File input display for banner only
    document.getElementById('banner_image').addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
        document.getElementById('banner-name').textContent = fileName;
    });

    // Delete confirmation for both types
    function confirmDelete(id, title, type) {
        document.getElementById('deleteTitle').textContent = title;
        document.getElementById('deleteId').value = id;
        
        const form = document.getElementById('deleteForm');
        const button = document.getElementById('deleteButton');
        
        // Clear any previous form actions
        while (form.firstChild) form.removeChild(form.firstChild);
        
        // Recreate the hidden input
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);
        
        // Create the appropriate submit button
        const submitButton = document.createElement('button');
        submitButton.type = 'submit';
        submitButton.className = 'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded';
        
        if (type === 'banner') {
            submitButton.name = 'delete_banner';
            submitButton.innerHTML = 'Delete Banner';
        } else {
            submitButton.name = 'delete_announcement';
            submitButton.innerHTML = 'Delete Announcement';
        }
        
        form.appendChild(submitButton);
        
        document.getElementById('deleteModal').style.display = 'block';
    }

    // Close modal
    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Auto-hide messages after 5 seconds
    setTimeout(function() {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => {
            msg.style.display = 'none';
        });
    }, 5000);
</script>
</body>
</html>