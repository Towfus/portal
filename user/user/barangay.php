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

// Get all unique barangays that have recognized schools
$barangays = array();
$sql = "SELECT DISTINCT barangay FROM schools WHERE recognize = 1 ORDER BY barangay";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $barangays[] = $row['barangay'];
    }
}

// Get all recognized schools grouped by barangay
$allSchools = array();
$sql = "SELECT * FROM schools WHERE recognize = 1 ORDER BY barangay, school_name";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $barangay = $row['barangay'];
        if (!isset($allSchools[$barangay])) {
            $allSchools[$barangay] = array();
        }
        $allSchools[$barangay][] = $row;
    }
}

// Get recognized schools for a specific barangay if selected
$selectedBarangay = isset($_GET['barangay']) ? $_GET['barangay'] : '';
$filteredSchools = array();
if ($selectedBarangay && isset($allSchools[$selectedBarangay])) {
    $filteredSchools = $allSchools[$selectedBarangay];
}
include 'user_header.php';
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recognized Schools by Barangay - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --text-color: #1f2937;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .page-description {
            color: #6b7280;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .filter-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
            gap: 1rem;
            align-items: center;
        }

        .filter-select {
            padding: 0.6rem 1rem;
            border: 2px solid var(--primary-color);
            border-radius: 0.5rem;
            background-color: var(--card-bg);
            font-size: 0.95rem;
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 220px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .clear-btn {
            padding: 0.6rem 1rem;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .clear-btn:hover {
            background-color: #dc2626;
        }

        .schools-container {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .barangay-section {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .barangay-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .barangay-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
        }

        .barangay-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .schools-count {
            background-color: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .schools-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .school-card {
            background-color: var(--card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .school-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .school-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }

        .school-address {
            color: #4b5563;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .school-contact {
            color: #4b5563;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .school-contact i {
            color: #6b7280;
            width: 16px;
            text-align: center;
        }

        .school-status {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .school-levels {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .level-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .level-badge:nth-child(2) {
            background-color: #8b5cf6;
        }

        .level-badge:nth-child(3) {
            background-color: #3b82f6;
        }

        .level-badge:nth-child(4) {
            background-color: #06b6d4;
        }

        .no-schools {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            font-style: italic;
        }

        .view-all {
            text-align: center;
            margin-top: 2rem;
        }

        .view-all a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            border: 1px solid var(--primary-color);
        }

        .view-all a:hover {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .page-title {
                font-size: 1.75rem;
                padding-top: 1rem;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 1.5rem;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .schools-list {
                grid-template-columns: 1fr;
            }
            
            .barangay-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .page-description {
                font-size: 1rem;
            }
            
            .school-card {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">Recognized Schools</h1>
            <p class="page-description">Browse through the list of DepEd recognized schools organized by barangay in General Trias City</p>
        </header>

        <form method="GET" action="" class="filter-container">
            <select name="barangay" class="filter-select" onchange="this.form.submit()">
                <option value="">Filter by Barangay</option>
                <?php foreach ($barangays as $barangay): ?>
                    <option value="<?php echo htmlspecialchars($barangay); ?>" 
                        <?php echo ($barangay == $selectedBarangay) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($barangay); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($selectedBarangay): ?>
                <button type="button" class="clear-btn" onclick="window.location.href='barangay.php'">
                    <i class="fa fa-times"></i> Clear Filter
                </button>
            <?php endif; ?>
        </form>

        <div class="schools-container">
            <?php if ($selectedBarangay): ?>
                <!-- Show only selected barangay -->
                <div class="barangay-section">
                    <div class="barangay-header">
                        <h2 class="barangay-name"><?php echo htmlspecialchars($selectedBarangay); ?></h2>
                        <span class="schools-count"><?php echo count($filteredSchools); ?> <?php echo count($filteredSchools) === 1 ? 'school' : 'schools'; ?></span>
                    </div>
                    
                    <?php if (count($filteredSchools) > 0): ?>
                        <div class="schools-list">
                            <?php foreach ($filteredSchools as $school): ?>
                                <div class="school-card">
                                    <h3 class="school-name"><?php echo htmlspecialchars($school['school_name']); ?></h3>
                                    <div class="school-address">
                                        <?php 
                                            $addressParts = [];
                                            if (!empty($school['address'])) $addressParts[] = $school['address'];
                                            if (!empty($school['city'])) $addressParts[] = $school['city'];
                                            if (!empty($school['province'])) $addressParts[] = $school['province'];
                                            echo htmlspecialchars(implode(', ', $addressParts));
                                        ?>
                                    </div>
                                    <?php if (!empty($school['telephone_number']) || !empty($school['mobile_number'])): ?>
                                        <div class="school-contact">
                                            <?php if (!empty($school['telephone_number'])): ?>
                                                <i class="fa fa-phone"></i> <?php echo htmlspecialchars($school['telephone_number']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($school['mobile_number'])): ?>
                                                <?php if (!empty($school['telephone_number'])) echo ' | '; ?>
                                                <i class="fa fa-mobile"></i> <?php echo htmlspecialchars($school['mobile_number']); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($school['email'])): ?>
                                        <div class="school-contact">
                                            <i class="fa fa-envelope"></i> <?php echo htmlspecialchars($school['email']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="school-status">Recognized by DepEd</div>
                                    
                                    <div class="school-levels">
                                        <?php if ($school['offers_elementary']): ?>
                                            <span class="level-badge">Elementary</span>
                                        <?php endif; ?>
                                        <?php if ($school['offers_jhs']): ?>
                                            <span class="level-badge">Junior High</span>
                                        <?php endif; ?>
                                        <?php if ($school['offers_shs']): ?>
                                            <span class="level-badge">Senior High</span>
                                        <?php endif; ?>
                                        <?php if ($school['offers_sped']): ?>
                                            <span class="level-badge">SPED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-schools">No recognized schools found in this barangay.</div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Show all barangays by default -->
                <?php if (count($allSchools) > 0): ?>
                    <?php foreach ($allSchools as $barangay => $schools): ?>
                        <div class="barangay-section">
                            <div class="barangay-header">
                                <h2 class="barangay-name"><?php echo htmlspecialchars($barangay); ?></h2>
                                <span class="schools-count"><?php echo count($schools); ?> <?php echo count($schools) === 1 ? 'school' : 'schools'; ?></span>
                            </div>
                            
                            <div class="schools-list">
                                <?php foreach ($schools as $school): ?>
                                    <div class="school-card">
                                        <h3 class="school-name"><?php echo htmlspecialchars($school['school_name']); ?></h3>
                                        <div class="school-address">
                                            <?php 
                                                $addressParts = [];
                                                if (!empty($school['address'])) $addressParts[] = $school['address'];
                                                if (!empty($school['city'])) $addressParts[] = $school['city'];
                                                if (!empty($school['province'])) $addressParts[] = $school['province'];
                                                echo htmlspecialchars(implode(', ', $addressParts));
                                            ?>
                                        </div>
                                        <?php if (!empty($school['telephone_number']) || !empty($school['mobile_number'])): ?>
                                            <div class="school-contact">
                                                <?php if (!empty($school['telephone_number'])): ?>
                                                    <i class="fa fa-phone"></i> <?php echo htmlspecialchars($school['telephone_number']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($school['mobile_number'])): ?>
                                                    <?php if (!empty($school['telephone_number'])) echo ' | '; ?>
                                                    <i class="fa fa-mobile"></i> <?php echo htmlspecialchars($school['mobile_number']); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($school['email'])): ?>
                                            <div class="school-contact">
                                                <i class="fa fa-envelope"></i> <?php echo htmlspecialchars($school['email']); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="school-status">Recognized by DepEd</div>
                                        
                                        <div class="school-levels">
                                            <?php if ($school['offers_elementary']): ?>
                                                <span class="level-badge">Elementary</span>
                                            <?php endif; ?>
                                            <?php if ($school['offers_jhs']): ?>
                                                <span class="level-badge">Junior High</span>
                                            <?php endif; ?>
                                            <?php if ($school['offers_shs']): ?>
                                                <span class="level-badge">Senior High</span>
                                            <?php endif; ?>
                                            <?php if ($school['offers_sped']): ?>
                                                <span class="level-badge">SPED</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-schools">No recognized schools found in the database.</div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($selectedBarangay): ?>
                <div class="view-all">
                    <a href="barangay.php"><i class="fa fa-list"></i> View All Barangays</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
       <!-- Footer -->
   <footer class="bg-green-600 text-white py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Logo and Description -->
            <div class="lg:col-span-2">
                <div class="flex items-center mb-3">
                    <img src="images/deped_logo.png" alt="DepEd Logo" class="h-10 mr-3">
                    <h3 class="text-lg font-bold">DepEd General Trias City</h3>
                </div>
                <p class="text-white text-opacity-90 text-xs leading-relaxed">
                    The Department of Education in General Trias City is committed to providing quality basic education.
                </p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-base font-semibold mb-3 border-b border-white border-opacity-20 pb-1">Quick Links</h4>
                <ul class="space-y-1 text-sm">
                    <li><a href="user_dashboard.php" class="text-white text-opacity-90 hover:text-white hover:text-opacity-100 transition">Home</a></li>
                    <li><a href="barangay.php" class="text-white text-opacity-90 hover:text-white hover:text-opacity-100 transition">Shools</a></li>
                    <li><a href="user_announcement.php" class="text-white text-opacity-90 hover:text-white hover:text-opacity-100 transition">Annoucements</a></li>
                </ul>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h4 class="text-base font-semibold mb-3 border-b border-white border-opacity-20 pb-1">Contact Us</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-0.5 mr-2 text-white text-opacity-80 text-xs"></i>
                        <p class="text-white text-opacity-90">DepEd Division Office, General Trias City</p>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-phone-alt mr-2 text-white text-opacity-80 text-xs"></i>
                        <p class="text-white text-opacity-90">(046) 123-4567</p>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="mt-4">
                    <div class="flex space-x-3">
                        <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 w-7 h-7 rounded-full flex items-center justify-center transition">
                            <i class="fab fa-facebook-f text-xs text-white"></i>
                        </a>
                        <a href="#" class="bg-white bg-opacity-20 hover:bg-opacity-30 w-7 h-7 rounded-full flex items-center justify-center transition">
                            <i class="fab fa-twitter text-xs text-white"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="border-t border-white border-opacity-20 mt-6 pt-4">
            <p class="text-white text-opacity-80 text-xs text-center">
                © <?php echo date('Y'); ?> DepEd General Trias City. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>
</body>
</html>