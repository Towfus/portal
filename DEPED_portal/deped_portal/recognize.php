<?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "deped_schools";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Initialize variables for messages
    $message = $error = "";

    // Pagination setup
    $results_per_page = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max($page, 1); // Ensure page is at least 1
    
    // Count total number of schools
    $count_query = "SELECT COUNT(*) AS total FROM schools WHERE recognize = 1 AND (renewal IS NULL OR renewal = '')";
    $count_result = $conn->query($count_query);
    $total_rows = $count_result->fetch_assoc()['total'];
    $count_result->close();
    
    $total_pages = ceil($total_rows / $results_per_page);
    $offset = ($page - 1) * $results_per_page;

    // Query for recognized schools with pagination
    $query = "SELECT * FROM schools WHERE recognize = 1 AND (renewal IS NULL OR renewal = '') ORDER BY name LIMIT $offset, $results_per_page";
    $result = $conn->query($query);

    if (!$result) {
        die("Database query failed: " . $conn->error);
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Recognized Schools | DepEd Admin</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/dashboard.css">
        <style>
            .pagination {
                display: flex;
                justify-content: center;
                margin-top: 20px;
            }
            .pagination a {
                color: black;
                padding: 8px 16px;
                text-decoration: none;
                border: 1px solid #ddd;
                margin: 0 4px;
            }
            .pagination a.active {
                background-color: #4CAF50;
                color: white;
                border: 1px solid #4CAF50;
            }
            .pagination a:hover:not(.active) {
                background-color: #ddd;
            }
        </style>
    </head>
    <body>
        <?php include 'header.php'; ?>
        
        <div class="container">
            <!-- Display messages -->
            <?php if (!empty($message)): ?>
                <div class="alert success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Recognized Schools Header -->
            <h1 class="letter-heading">
                Recognized Schools
            </h1>
            
            <!-- Schools List -->
            <div id="schools-container">
                <?php if ($result->num_rows > 0): ?>
                    <ul class="school-list">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <li class="school-item">
                                <!-- Left side - School info -->
                                <div class="school-info">
                                    <h2 class="school-title"><?php echo htmlspecialchars($row['name']); ?></h2>
                                    <p class="school-address"><?php echo htmlspecialchars($row['address']); ?></p>
                                    <?php if (!empty($row['email'])): ?>
                                        <p class="school-email"><i class="fas fa-envelope email-icon"></i> <?php echo htmlspecialchars($row['email']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Right side - Grade offerings -->
                                <div class="school-grades">
                                    <?php if ($row['offers_elementary'] == 1): ?>
                                        <div class="grade-category">Elementary</div>
                                        <div class="grade-levels"><?php echo htmlspecialchars($row['elementary_grades'] ?? 'Grades not specified'); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['offers_jhs'] == 1): ?>
                                        <div class="grade-category">Junior High School</div>
                                        <div class="grade-levels"><?php echo htmlspecialchars($row['jhs_grades'] ?? 'Grades not specified'); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['offers_shs'] == 1): ?>
                                        <div class="grade-category">Senior High School</div>
                                        <div class="grade-levels"><?php echo htmlspecialchars($row['shs_grades'] ?? 'Grades not specified'); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['offers_sped'] == 1): ?>
                                        <div class="grade-category">SPED Program</div>
                                        <div class="grade-levels">Special Education Program Available</div>
                                    <?php endif; ?>
                                    
                                    <?php if (!$row['offers_elementary'] && !$row['offers_jhs'] && !$row['offers_shs'] && !$row['offers_sped']): ?>
                                        <div class="grade-category">No grade information available</div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1">&laquo; First</a>
                            <a href="?page=<?php echo $page - 1; ?>">&lsaquo; Prev</a>
                        <?php endif; ?>
                        
                        <?php 
                        // Show page numbers
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<a href="?page=1">1</a>';
                            if ($start_page > 2) echo '<span>...</span>';
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo "<a class='$active' href='?page=$i'>$i</a>";
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) echo '<span>...</span>';
                            echo "<a href='?page=$total_pages'>$total_pages</a>";
                        }
                        ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>">Next &rsaquo;</a>
                            <a href="?page=<?php echo $total_pages; ?>">Last &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <h2>No recognized schools found.</h2>
                        <p>All schools may be pending renewal or none have been marked as recognized yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php include 'footer.php' ?>
        <script>
            // Function to confirm starting renewal process
            function confirmRenewal() {
                return confirm('Are you sure you want to start the renewal process for this school?');
            }
        </script>
    </body>
    </html>

    <?php $conn->close(); ?>