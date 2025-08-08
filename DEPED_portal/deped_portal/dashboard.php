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

        // Initialize search variable and active letter
        $search = "";
        $activeLetter = "A"; // Default to show 'A' schools first

        // Initialize filter variables
        $barangayFilter = isset($_GET['barangay']) ? $_GET['barangay'] : '';
        $levelFilters = isset($_GET['level']) ? (array)$_GET['level'] : [];

        // Check if this is an AJAX request for search or letter navigation
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Base SQL query
        $sql = "SELECT *, 
                offers_elementary, offers_jhs, offers_shs, offers_sped, 
                elementary_grades, jhs_grades, shs_grades, email 
                FROM schools 
                WHERE 1=1";

        $params = [];
        $types = '';

        // Add search condition if exists
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql .= " AND (name LIKE ? OR address LIKE ? OR barangay LIKE ?)";
            $searchParam = "%" . $search . "%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            $types .= 'sss';
        }

        // Add barangay filter if exists
        if (!empty($barangayFilter)) {
            $sql .= " AND barangay = ?";
            $params[] = $barangayFilter;
            $types .= 's';
        }

        // Add level filters if any
        if (!empty($levelFilters)) {
            $levelConditions = [];
            foreach ($levelFilters as $level) {
                switch ($level) {
                    case 'elementary':
                        $levelConditions[] = "offers_elementary = 1";
                        break;
                    case 'jhs':
                        $levelConditions[] = "offers_jhs = 1";
                        break;
                    case 'shs':
                        $levelConditions[] = "offers_shs = 1";
                        break;
                    case 'sped':
                        $levelConditions[] = "offers_sped = 1";
                        break;
                }
            }
            if (!empty($levelConditions)) {
                $sql .= " AND (" . implode(" OR ", $levelConditions) . ")";
            }
        }

        // Add alphabetical filter if no search
        if (empty($search) && empty($barangayFilter) && empty($levelFilters)) {
            $activeLetter = isset($_GET['letter']) ? $_GET['letter'] : 'A';
            $sql .= " AND name LIKE ?";
            $letterParam = $activeLetter . "%";
            $params[] = $letterParam;
            $types .= 's';
        }

        // Order by
        $sql .= " ORDER BY name ASC";

        // Prepare and execute query
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Get all unique first letters for alphabetical index
        $indexSql = "SELECT DISTINCT LEFT(name, 1) as first_letter FROM schools ORDER BY first_letter ASC";
        $indexResult = $conn->query($indexSql);
        $alphabetIndex = [];
        while ($row = $indexResult->fetch_assoc()) {
            $alphabetIndex[] = $row['first_letter'];
        }

        // Get all barangays for filter
        $barangaySql = "SELECT DISTINCT barangay FROM schools WHERE barangay != '' ORDER BY barangay ASC";
        $barangayResult = $conn->query($barangaySql);
        $barangays = [];
        while ($barangay = $barangayResult->fetch_assoc()) {
            $barangays[] = $barangay['barangay'];
        }

        // Only proceed with the full HTML if this is not an AJAX request
        if (!$isAjax) {
            // Existing full page output
        } else {
            // For AJAX requests, return only the appropriate content
            if (!empty($search)) {
                include 'search_results.php';
            } elseif (isset($_GET['letter'])) {
                include 'letter_results.php';
            } else {
                // This is a filter request
                include 'filter_results.php';
            }
            exit();
        }
        error_reporting(E_ALL);
ini_set('display_errors', 1);

$test_sql = "SELECT COUNT(*) as total FROM announcements WHERE is_active = 1";
$test_result = $conn->query($test_sql);
$test_row = $test_result->fetch_assoc();
echo "<!-- DEBUG: Total active announcements: ".$test_row['total']." -->";
            include 'header.php';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
            <link rel="stylesheet" href="fontawesome/css/all.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="css/dashboard.css">  
            <title>School Directory - DepEd General Trias City</title>
            

        </head>
        <body>
            
            <?php if(isset($showBanner) && $showBanner === true): ?>
            <!-- MATATAG Banner -->
            <div class="matatag-banner">
                <img src="images/deped_matatag.jpg" alt="DepEd MATATAG Banner - Bansang Makabata, Batang Makabansa">
                <div class="matatag-navigation">      
                    
                </div>
            </div>
            <?php endif; ?>

            <!-- Announcement Banner Carousel -->
            <!-- Announcement Banner Carousel -->
            <div class="announcement-banner">
                <div class="announcement-carousel">
                    <?php
                    $currentDate = date('Y-m-d H:i:s');
                    
                    // Fetch active banners
                    $bannerSql = "SELECT * FROM banners 
                                WHERE is_active = 1 
                                AND banner_path IS NOT NULL
                                AND banner_path != ''
                                ORDER BY created_at DESC";
                    
                    $bannerResult = $conn->query($bannerSql);
                    
                    // Fetch active announcements (current date between start and end date)
                    $currentDate = date('Y-m-d');
                    $announcementSql = "SELECT * FROM announcements 
                                    WHERE is_active = 1 
                                    AND start_date <= ? 
                                    AND end_date >= ?
                                    ORDER BY created_at DESC 
                                    LIMIT 5";

                    $stmt = $conn->prepare($announcementSql);
                    $stmt->bind_param("ss", $currentDate, $currentDate);
                    $stmt->execute();
                    $announcementResult = $stmt->get_result();
                    
                    $allItems = [];
                    
                    // Add banners first
                    if ($bannerResult->num_rows > 0) {
                        while ($banner = $bannerResult->fetch_assoc()) {
                            $allItems[] = [
                                'type' => 'banner',
                                'data' => $banner
                            ];
                        }
                    }
                    
                    // Then add announcements
                    if ($announcementResult->num_rows > 0) {
                        while ($announcement = $announcementResult->fetch_assoc()) {
                            $allItems[] = [
                                'type' => 'announcement',
                                'data' => $announcement
                            ];
                        }
                    }
                    
                    if (count($allItems) > 0) {
                        foreach ($allItems as $index => $item) {
                            $activeClass = $index === 0 ? 'active' : '';
                            $itemData = $item['data'];
                            
                            echo '<div class="announcement-slide ' . $activeClass . '" data-index="' . $index . '">';
                            
                            // Handle banner display
                            if ($item['type'] === 'banner') {
                                // Correct path handling for banners
                                $imagePath = $itemData['banner_path'];
                                
                                // If path doesn't start with http or /, prepend the correct relative path
                                if (strpos($imagePath, 'http') !== 0 && strpos($imagePath, '/') !== 0) {
                                    $imagePath = '../banners/' . basename($imagePath);
                                }
                                
                                echo '<img src="' . htmlspecialchars($imagePath) . '" alt="Banner Image" class="banner-image">';
                            }
                            // Handle regular announcement display
                            else {
                                // Image display with proper path handling
                                if (!empty($itemData['image_path'])) {
                                    $imagePath = $itemData['image_path'];
                                    
                                    if (strpos($imagePath, 'http') !== 0 && strpos($imagePath, '/') !== 0) {
                                        $imagePath = '../banners/' . basename($imagePath);
                                    }
                                        
                                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="Announcement Image">';
                                }
                            }
                            
                            echo '</div>';
                        }
                        
                        if (count($allItems) > 1) {
                            echo '<button class="carousel-control prev" onclick="moveSlide(-1)">❮</button>';
                            echo '<button class="carousel-control next" onclick="moveSlide(1)">❯</button>';
                            
                            echo '<div class="carousel-indicators">';
                            foreach ($allItems as $index => $item) {
                                $activeClass = $index === 0 ? 'active' : '';
                                echo '<span class="indicator-dot ' . $activeClass . '" onclick="goToSlide(' . $index . ')"></span>';
                            }
                            echo '</div>';
                        }
                    } else {
                        // Default announcement when none are active
                        echo '<div class="announcement-slide active">';
                        echo '<img src="images/default_banner.jpg" alt="Default Announcement">';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
                
            <!-- New section for SDS and Announcements -->
            <div class="announcements-container">
                <!-- Left side - SDS Container -->
                <div class="sds-section-container">
                    <div class="sds-container">
                        <div class="sds-section">
                            <img src="images/inductivo.png" class="sds-image" alt="SDS Photo">
                            <div class="sds-info">
                                <div class="sds-name">IVAN BRIAN L. INDUCTIVO, CESO VI</div>
                                <div class="sds-title">ASST. SCHOOLS DIVISION SUPERINTENDENT</div>
                                <div class="sds-title">OFFICER-IN-CHARGE</div>
                                <div class="sds-title">OFFICE OF THE SCHOOLS DIVISION SUPERINTENDENT</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right side - Announcements List -->
<div class="announcements-list">
    <h3 class="announcements-title">Latest Announcement</h3> <!-- Changed title to singular -->
    <div class="announcements-scroll">
        <?php
        try {
            // Get current date in proper format for comparison (date only)
            $currentDate = date('Y-m-d');
            
            // Fetch the single most recent active announcement
            $announcementSql = "SELECT * FROM announcements 
                            WHERE is_active = 1 
                            AND ? BETWEEN start_date AND end_date
                            ORDER BY created_at DESC 
                            LIMIT 1"; // Changed from LIMIT 5 to LIMIT 1
            
            $stmt = $conn->prepare($announcementSql);
            $stmt->bind_param("s", $currentDate);
            $stmt->execute();
            $announcementResult = $stmt->get_result();
            
            if ($announcementResult->num_rows > 0):
                $announcement = $announcementResult->fetch_assoc(); // Get single announcement
                $formattedDate = date('M j, Y', strtotime($announcement['created_at']));
                ?>
                <div class="announcement-item">
                    <div class="announcement-date">
                        <?php echo $formattedDate; ?>
                    </div>
                    <h4 class="announcement-heading"><?php echo htmlspecialchars($announcement['title']); ?></h4>
                    
                    <?php if (!empty($announcement['image_path'])): ?>
                        <div class="announcement-image">
                            <img src="<?php echo htmlspecialchars($announcement['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($announcement['title']); ?>" 
                                 class="announcement-img">
                        </div>
                    <?php endif; ?>
                    
                    <p class="announcement-content">
                        <?php 
                        echo htmlspecialchars($announcement['content']); // Showing full content now
                        ?>
                    </p>
                    
                    <?php if (!empty($announcement['file_path'])): ?>
                        <a href="<?php echo htmlspecialchars($announcement['file_path']); ?>" 
                           class="announcement-link" 
                           target="_blank">
                            <i class="fas fa-file-download"></i> Download Attachment
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="no-announcements">No current announcements available</p>
                <?php
                // Debug output to help troubleshoot
                $debugSql = "SELECT id, title, start_date, end_date, is_active, created_at 
                            FROM announcements 
                            ORDER BY created_at DESC 
                            LIMIT 5";
                $debugResult = $conn->query($debugSql);
                
                if ($debugResult->num_rows > 0) {
                    echo "<!-- Debug: Announcements in database -->";
                    echo "<!-- Current date: ".$currentDate." -->";
                    while ($row = $debugResult->fetch_assoc()) {
                        $status = ($row['is_active'] && 
                                  $currentDate >= $row['start_date'] && 
                                  $currentDate <= $row['end_date']) ? "ACTIVE" : "INACTIVE";
                        echo "<!-- ID: ".$row['id']." | Title: ".htmlspecialchars($row['title'])." | Status: ".$status." | Dates: ".$row['start_date']." to ".$row['end_date']." | Created: ".$row['created_at']." -->";
                    }
                } else {
                    echo "<!-- Debug: No announcements found in database -->";
                }
            endif;
        } catch (Exception $e) {
            echo "<p class='no-announcements'>Error loading announcements</p>";
            echo "<!-- Error: ".$e->getMessage()." -->";
        }
        ?>
    </div>
</div>
                    
            <hr>
            <div class="container">
                <!-- Search container -->
                <div class="search-container">
                    <form id="search-form" class="search-form" onsubmit="return performSearch(event)">
                        <input type="text" id="search-input" name="search" class="search-bar" placeholder="Search for schools by name, address, or barangay..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-button">Search</button>
                        <i class="fas fa-filter filter-icon" id="filter-toggle"></i>
                    </form>
                </div>
                
                <!-- Filter panel (hidden by default) -->
                <div class="filter-panel" id="filter-panel">
                    <div class="filter-section" id="barangay-section">
                        <div class="filter-section-title" onclick="toggleFilterSection('barangay')">
                            Barangay <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-section-content">
                            <input type="text" class="filter-search" id="barangay-search" placeholder="Search barangays..." oninput="filterBarangays()">
                            <div class="filter-options" id="barangay-options">
                                <?php foreach ($barangays as $barangay): ?>
                                    <div class="filter-option" onclick="selectBarangay('<?php echo htmlspecialchars($barangay); ?>')">
                                        <?php echo htmlspecialchars($barangay); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-section" id="level-section">
                        <div class="filter-section-title" onclick="toggleFilterSection('level')">
                            School Level <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="filter-section-content">
                            <div class="level-filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" name="level" value="elementary" 
                                        <?php echo in_array('elementary', $levelFilters) ? 'checked' : ''; ?>
                                        onchange="applyFilters()">
                                    Elementary
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="level" value="jhs" 
                                        <?php echo in_array('jhs', $levelFilters) ? 'checked' : ''; ?>
                                        onchange="applyFilters()">
                                    Junior High School
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="level" value="shs" 
                                        <?php echo in_array('shs', $levelFilters) ? 'checked' : ''; ?>
                                        onchange="applyFilters()">
                                    Senior High School
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="level" value="sped" 
                                        <?php echo in_array('sped', $levelFilters) ? 'checked' : ''; ?>
                                        onchange="applyFilters()">
                                    SPED Program
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden select element for form submission -->
                    <select id="barangay-select" style="display: none;">
                        <option value="">All Barangays</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?php echo htmlspecialchars($barangay); ?>" <?php echo $barangayFilter === $barangay ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($barangay); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="filter-actions">
                        <button class="apply-btn" onclick="applyFilters()">Apply Filters</button>
                        <button class="clear-btn" onclick="clearAllFilters()">Clear All</button>
                    </div>
                </div>
                
                <!-- Active filters display -->
                <div id="active-filters" class="active-filters">
                    <?php if (!empty($barangayFilter)): ?>
                        <div class="filter-tag">
                            Barangay: <?php echo htmlspecialchars($barangayFilter); ?> 
                            <span class="remove" onclick="removeFilter('barangay')">×</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($levelFilters as $level): 
                        $displayText = '';
                        switch($level) {
                            case 'elementary': $displayText = 'Elementary'; break;
                            case 'jhs': $displayText = 'Junior High School'; break;
                            case 'shs': $displayText = 'Senior High School'; break;
                            case 'sped': $displayText = 'SPED Program'; break;
                        }
                    ?>
                        <div class="filter-tag">
                            <?php echo $displayText; ?> 
                            <span class="remove" onclick="removeFilter('level', '<?php echo $level; ?>')">×</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="loading-indicator">
                    <p>Loading schools...</p>
                </div>
                
                <div id="schools-container">
                    <?php if ($result->num_rows > 0): ?>
                        <?php if (!empty($search)): ?>
                            <h2 class="letter-heading">Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>
                        <?php elseif (!empty($barangayFilter) || !empty($levelFilters)): ?>
                            <h2 class="letter-heading">Filtered Results</h2>
                        <?php else: ?>
                            <h2 class="letter-heading">Schools Starting with <?php echo $activeLetter; ?></h2>
                        <?php endif; ?>
                        
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
                        
                        <?php if (!empty($search)): ?>
                            <div class="clear-search">
                                <a href="javascript:void(0);" onclick="loadLetterSchools('A')">Clear Search</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <h2>No schools found matching your criteria.</h2>
                            <p>Try adjusting your search or filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Alphabet Index at the bottom with ID -->
                <div id="alphabet-index" class="alphabet-index">
                    <?php foreach ($alphabetIndex as $letter): ?>
                        <a href="javascript:void(0);" 
                        onclick="loadLetterSchools('<?php echo $letter; ?>')" 
                        class="<?php echo ($letter == $activeLetter) ? 'active' : ''; ?>">
                            <?php echo strtoupper($letter); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
        <?php include 'footer.php'; ?>
            <!-- JavaScript for AJAX loading and navigation -->
            <script>
                // Function to show loading indicator
                function showLoading() {
                    document.getElementById('loading-indicator').style.display = 'block';
                    document.getElementById('schools-container').style.opacity = '0.5';
                }
                
                // Function to hide loading indicator
                function hideLoading() {
                    document.getElementById('loading-indicator').style.display = 'none';
                    document.getElementById('schools-container').style.opacity = '1';
                }
                
                // Toggle filter panel visibility
                document.getElementById('filter-toggle').addEventListener('click', function() {
                    const panel = document.getElementById('filter-panel');
                    panel.classList.toggle('active');
                });
                
                // Toggle filter section visibility
                function toggleFilterSection(section) {
                    const sectionElement = document.getElementById(section + '-section');
                    sectionElement.classList.toggle('active');
                    
                    // Toggle chevron icon
                    const chevron = sectionElement.querySelector('.fa-chevron-down');
                    chevron.classList.toggle('fa-chevron-up');
                }
                
                // Filter barangays based on search input
                function filterBarangays() {
                    const searchValue = document.getElementById('barangay-search').value.toLowerCase();
                    const options = document.querySelectorAll('#barangay-options .filter-option');
                    
                    options.forEach(option => {
                        const text = option.textContent.toLowerCase();
                        option.style.display = text.includes(searchValue) ? 'block' : 'none';
                    });
                }
                
                // Function to select a barangay from dropdown
                function selectBarangay(barangay) {
                    const barangaySelect = document.getElementById('barangay-select');
                    barangaySelect.value = barangay;
                    
                    // Update the selected option in the dropdown
                    const options = barangaySelect.options;
                    for (let i = 0; i < options.length; i++) {
                        options[i].selected = options[i].value === barangay;
                    }
                    
                    // Immediately apply the filter
                    applyFilters();
                }
                
                function loadLetterSchools(letter) {
                    showLoading();
                    
                    // Clear any existing filters except the letter
                    document.getElementById('barangay-select').value = '';
                    document.querySelectorAll('input[name="level"]:checked').forEach(cb => {
                        cb.checked = false;
                    });
                    
                    // Create an XMLHttpRequest object
                    const xhr = new XMLHttpRequest();
                    
                    // Configure the request
                    xhr.open('GET', 'dashboard.php?letter=' + letter, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    // Set up a callback function to handle the response
                    xhr.onload = function() {
                        hideLoading();
                        
                        if (xhr.status === 200) {
                            // Update the schools container
                            document.getElementById('schools-container').innerHTML = xhr.responseText;
                            
                            // Update the active letter in the alphabet index
                            const alphabetLinks = document.querySelectorAll('#alphabet-index a');
                            alphabetLinks.forEach(link => {
                                link.classList.remove('active');
                                if (link.textContent.trim() === letter.toUpperCase()) {
                                    link.classList.add('active');
                                }
                            });
                            
                            // Update the URL without reloading the page
                            history.pushState(null, '', 'dashboard.php?letter=' + letter);
                            
                            // Clear search input
                            document.getElementById('search-input').value = '';
                            
                            // Clear active filters display
                            document.getElementById('active-filters').innerHTML = '';
                        }
                    };
                    
                    // Send the request
                    xhr.send();
                }
                
                // Function to perform search using AJAX
                function performSearch(event) {
                    event.preventDefault();
                    showLoading();
                    
                    const searchInput = document.getElementById('search-input').value;
                    
                    // Create an XMLHttpRequest object
                    const xhr = new XMLHttpRequest();
                    
                    // Configure the request
                    xhr.open('GET', 'dashboard.php?search=' + encodeURIComponent(searchInput), true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    // Set up a callback function to handle the response
                    xhr.onload = function() {
                        hideLoading();
                        
                        if (xhr.status === 200) {
                            // Update the schools container
                            document.getElementById('schools-container').innerHTML = xhr.responseText;
                            
                            // Remove active class from all alphabet links
                            const alphabetLinks = document.querySelectorAll('#alphabet-index a');
                            alphabetLinks.forEach(link => {
                                link.classList.remove('active');
                            });
                            
                            // Update the URL without reloading the page
                            history.pushState(null, '', 'dashboard.php?search=' + encodeURIComponent(searchInput));
                            
                            // Clear active filters display
                            document.getElementById('active-filters').innerHTML = '';
                        }
                    };
                    
                    // Send the request
                    xhr.send();
                    
                    return false;
                }
                
                function applyFilters() {
                    showLoading();
                    
                    // Get active filters
                    const barangay = document.getElementById('barangay-select').value;
                    const levelCheckboxes = document.querySelectorAll('input[name="level"]:checked');
                    const levels = Array.from(levelCheckboxes).map(cb => cb.value);
                    
                    // Build query parameters
                    let params = new URLSearchParams();
                    
                    if (barangay) params.append('barangay', barangay);
                    levels.forEach(level => params.append('level', level));
                    
                    // Update active filters display immediately (don't wait for AJAX response)
                    updateActiveFiltersDisplay(barangay, levels);
                    
                    // Create an XMLHttpRequest object
                    const xhr = new XMLHttpRequest();
                    
                    // Configure the request
                    xhr.open('GET', 'dashboard.php?' + params.toString(), true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    
                    // Set up a callback function to handle the response
                    xhr.onload = function() {
                        hideLoading();
                        
                        if (xhr.status === 200) {
                            // Update the schools container
                            document.getElementById('schools-container').innerHTML = xhr.responseText;
                            
                            // Update the URL without reloading the page
                            history.pushState(null, '', 'dashboard.php?' + params.toString());
                            
                            // Clear search input if any
                            document.getElementById('search-input').value = '';
                            
                            // Remove active class from all alphabet links
                            const alphabetLinks = document.querySelectorAll('#alphabet-index a');
                            alphabetLinks.forEach(link => {
                                link.classList.remove('active');
                            });
                        }
                    };
                    
                    // Send the request
                    xhr.send();
                    
                    // Hide filter panel after applying
                    document.getElementById('filter-panel').classList.remove('active');
                }
                
                // Function to update the active filters display
                function updateActiveFiltersDisplay(barangay, levels) {
                    const activeFiltersContainer = document.getElementById('active-filters');
                    activeFiltersContainer.innerHTML = '';
                    
                    if (barangay) {
                        const tag = document.createElement('div');
                        tag.className = 'filter-tag';
                        tag.innerHTML = `Barangay: ${barangay} <span class="remove" onclick="removeFilter('barangay')">×</span>`;
                        activeFiltersContainer.appendChild(tag);
                    }
                    
                    if (levels.length > 0) {
                        levels.forEach(level => {
                            const tag = document.createElement('div');
                            tag.className = 'filter-tag';
                            
                            let displayText = '';
                            switch(level) {
                                case 'elementary': displayText = 'Elementary'; break;
                                case 'jhs': displayText = 'Junior High School'; break;
                                case 'shs': displayText = 'Senior High School'; break;
                                case 'sped': displayText = 'SPED Program'; break;
                            }
                            
                            tag.innerHTML = `${displayText} <span class="remove" onclick="removeFilter('level', '${level}')">×</span>`;
                            activeFiltersContainer.appendChild(tag);
                        });
                    }
                }
                
                // Function to remove a specific filter
                function removeFilter(type, value = null) {
                    if (type === 'barangay') {
                        document.getElementById('barangay-select').value = '';
                    } else if (type === 'level' && value) {
                        document.querySelector(`input[name="level"][value="${value}"]`).checked = false;
                    }
                    
                    applyFilters();
                }
                
                // Function to clear all filters
                function clearAllFilters() {
                    document.getElementById('barangay-select').value = '';
                    document.querySelectorAll('input[name="level"]:checked').forEach(cb => {
                        cb.checked = false;
                    });
                    
                    applyFilters();
                }
                
                // Close dropdowns when clicking outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.filter-panel') && !event.target.matches('#filter-toggle')) {
                        document.getElementById('filter-panel').classList.remove('active');
                    }
                });
                
                // Initialize dropdowns with current filter values
                document.addEventListener('DOMContentLoaded', function() {
                    // Set the selected barangay in the hidden select
                    const barangaySelect = document.getElementById('barangay-select');
                    if (barangaySelect) {
                        const currentBarangay = '<?php echo $barangayFilter; ?>';
                        if (currentBarangay) {                        barangaySelect.value = currentBarangay;
                        }
                    }
                    
                    // Set the active letter in the alphabet index
                    const activeLetter = '<?php echo $activeLetter; ?>';
                    if (activeLetter) {
                        const alphabetLinks = document.querySelectorAll('#alphabet-index a');
                        alphabetLinks.forEach(link => {
                            if (link.textContent.trim() === activeLetter.toUpperCase()) {
                                link.classList.add('active');
                            }
                        });
                    }
                    
                    // Initialize announcement carousel
                    let currentSlide = 0;
                    const slides = document.querySelectorAll('.announcement-slide');
                    const dots = document.querySelectorAll('.indicator-dot');
                    
                    function showSlide(index) {
                        // Hide all slides
                        slides.forEach(slide => {
                            slide.classList.remove('active');
                        });
                        
                        // Remove active class from all dots
                        dots.forEach(dot => {
                            dot.classList.remove('active');
                        });
                        
                        // Show current slide
                        slides[index].classList.add('active');
                        
                        // Set current dot as active
                        if (dots[index]) {
                            dots[index].classList.add('active');
                        }
                        
                        currentSlide = index;
                    }
                    
                    // Auto-advance slides every 5 seconds
                    let slideInterval = setInterval(() => {
                        moveSlide(1);
                    }, 5000);
                    
                    // Pause on hover
                    const carousel = document.querySelector('.announcement-carousel');
                    if (carousel) {
                        carousel.addEventListener('mouseenter', () => {
                            clearInterval(slideInterval);
                        });
                        
                        carousel.addEventListener('mouseleave', () => {
                            slideInterval = setInterval(() => {
                                moveSlide(1);
                            }, 5000);
                        });
                    }
                    
                    // Expose functions to global scope
                    window.moveSlide = function(n) {
                        clearInterval(slideInterval); // Reset timer on manual navigation
                        let newIndex = currentSlide + n;
                        
                        if (newIndex < 0) {
                            newIndex = slides.length - 1;
                        } else if (newIndex >= slides.length) {
                            newIndex = 0;
                        }
                        
                        showSlide(newIndex);
                        
                        // Restart timer
                        slideInterval = setInterval(() => {
                            moveSlide(1);
                        }, 5000);
                    };
                    
                    window.goToSlide = function(index) {
                        clearInterval(slideInterval); // Reset timer on manual navigation
                        showSlide(index);
                        
                        // Restart timer
                        slideInterval = setInterval(() => {
                            moveSlide(1);
                        }, 5000);
                    };
                    
                    // Show first slide initially
                    if (slides.length > 0) {
                        showSlide(0);
                    }
                });
            </script>
        </body>
        </html>
        