<?php
// memorandum.php - DepEd General Trias City User Portal
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

// Fetch all memorandums for display, ordered by most recent first
$sql = "SELECT * FROM memorandums ORDER BY upload_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/memorandum.css">  
    <title>Memorandums - DepEd General Trias City</title>
    
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">        
        <div class="page-title">
            <h2 class="text-xl font-bold">Memorandums and Circulars</h2>
            <p>Access the latest memorandums and circulars from DepEd General Trias City</p>
        </div>

        <?php if (isset($_GET['id'])): ?>
            <?php
            // View a specific memorandum
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM memorandums WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($memo = $result->fetch_assoc()):
            ?>
                <div class="memo-info">
                    <h3 class="text-xl font-bold text-blue-700"><?php echo htmlspecialchars($memo['title']); ?></h3>
                    <p class="text-gray-600 mt-2">Uploaded on: <?php echo date('F d, Y', strtotime($memo['upload_date'])); ?></p>
                    <?php if (!empty($memo['description'])): ?>
                        <div class="mt-4 p-4 bg-gray-50 rounded">
                            <h4 class="font-bold mb-2">Description:</h4>
                            <p><?php echo nl2br(htmlspecialchars($memo['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="bg-white p-4 rounded shadow">
                    <?php
                    // Get the file path
                    $file_path = $memo['file_path'];
                    
                    // Check if file exists directly
                    if (file_exists($file_path)) {
                        echo '<iframe src="' . htmlspecialchars($file_path) . '" class="pdf-viewer"></iframe>';
                    } else {
                        // Try relative path adjustments
                        $possible_paths = [
                            $file_path,               // Original path
                            "../" . $file_path,       // One directory up
                            "../admin/" . $file_path, // Admin directory path
                            "admin/" . $file_path,    // Admin subdirectory
                            str_replace("memorandums/", "../admin/memorandums/", $file_path) // Replace specific paths
                        ];
                        
                        $found = false;
                        foreach ($possible_paths as $path) {
                            if (file_exists($path)) {
                                echo '<iframe src="' . htmlspecialchars($path) . '" class="pdf-viewer"></iframe>';
                                $found = true;
                                break;
                            }
                        }
                        
                        // If still not found, try using the directly with browser path
                        if (!$found) {
                            // Extract filename for direct linking
                            $filename = basename($file_path);
                            
                            // Try a direct link based on common web root structures
                            $web_path = "/memorandums/" . $filename;
                            echo '<iframe src="' . htmlspecialchars($web_path) . '" class="pdf-viewer"></iframe>';
                            
                            // Add debug info in HTML comments
                            echo "<!-- Debug: Original path = " . htmlspecialchars($file_path) . " -->";
                            echo "<!-- Debug: Using web path = " . htmlspecialchars($web_path) . " -->";
                        }
                    }
                    ?>
                </div>
                
                <div class="mt-6">
                    <a href="memorandum.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to All Memorandums
                    </a>
                </div>
            <?php else: ?>
                <div class="no-memos">
                    <h3 class="text-xl font-bold text-red-600">Memorandum Not Found</h3>
                    <p class="mt-2">The requested memorandum does not exist or has been removed.</p>
                    <a href="memorandum.php" class="view-button mt-4">View All Memorandums</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- List all memorandums -->
           
            
            <div id="memorandums-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="memo-card" data-title="<?php echo strtolower(htmlspecialchars($row['title'])); ?>" data-description="<?php echo strtolower(htmlspecialchars($row['description'])); ?>">
                            <h3 class="memo-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            
                            <?php if (!empty($row['description'])): ?>
                                <p class="memo-description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="memo-meta">
                                <span>Uploaded on: <?php echo date('F d, Y', strtotime($row['upload_date'])); ?></span>
                                <a href="memorandum.php?id=<?php echo $row['id']; ?>" class="view-button">
                                    <i class="far fa-file-pdf"></i> View Memorandum
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-memos">
                        <h3 class="text-xl font-bold">No Memorandums Available</h3>
                        <p class="mt-2">There are currently no memorandums or circulars available. Please check back later.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
     <?php include 'footer.php' ?>

</body>
</html>