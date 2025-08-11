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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9fafb;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #4ADE80 0%, #22C55E 100%);
            color: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        
        .filter-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .school-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border-left: 4px solid #22C55E;
        }
        
        .school-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .barangay-header {
            border-bottom: 2px solid #22C55E;
            padding-bottom: 0.75rem;
        }
        
        .level-badge {
            transition: all 0.2s ease;
        }
        
        .level-badge:hover {
            transform: scale(1.05);
        }
        
        .filter-select {
            border: 2px solid #22C55E;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #16A34A;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        
        .clear-btn {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .clear-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(239, 68, 68, 0.3);
        }
        
        .stats-badge {
            background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.3);
        }
        
        .view-all-btn {
            background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%);
            color: white;
            transition: all 0.3s ease;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.3);
        }
        
        .view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(34, 197, 94, 0.4);
        }
    </style>
</head>
<body class="bg-gray-50 font-roboto flex flex-col min-h-screen">
    <!-- Main Content -->
    <div class="flex-grow px-4 py-6 md:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="hero-section p-6 mb-8">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-2/3 mb-6 md:mb-0 text-center md:text-left">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Recognized Schools by Barangay</h1>
                    <p class="text-white opacity-90 mb-4">Browse through the list of DepEd recognized schools organized by barangay in General Trias City</p>
                </div>
                <div class="md:w-1/3 flex justify-center">
                    <img src="images/deped_logo.png" alt="DepEd Logo" class="h-32 md:h-40">
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-card p-6 mb-8">
            <form method="GET" action="" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                        Filter by Barangay
                    </label>
                    <select name="barangay" class="filter-select w-full py-3 px-4 bg-white text-gray-700 focus:ring-2 focus:ring-green-300" onchange="this.form.submit()">
                        <option value="">All Barangays</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?php echo htmlspecialchars($barangay); ?>" 
                                <?php echo ($barangay == $selectedBarangay) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($barangay); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selectedBarangay): ?>
                    <button type="button" class="clear-btn px-6 py-3 text-white font-medium flex items-center gap-2" onclick="window.location.href='barangay.php'">
                        <i class="fa fa-times"></i> Clear Filter
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Schools Container -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <?php if ($selectedBarangay): ?>
                <!-- Show only selected barangay -->
                <div class="mb-8">
                    <div class="barangay-header flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2 md:mb-0">
                            <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                            <?php echo htmlspecialchars($selectedBarangay); ?>
                        </h2>
                        <span class="stats-badge"><?php echo count($filteredSchools); ?> <?php echo count($filteredSchools) === 1 ? 'school' : 'schools'; ?></span>
                    </div>
                    
                    <?php if (count($filteredSchools) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($filteredSchools as $school): ?>
                                <div class="school-card p-6">
                                    <h3 class="text-xl font-bold text-green-700 mb-3">
                                        <?php echo htmlspecialchars($school['school_name']); ?>
                                    </h3>
                                    
                                    <?php if (!empty($school['address']) || !empty($school['city']) || !empty($school['province'])): ?>
                                        <div class="text-gray-600 mb-3 flex items-start gap-2">
                                            <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                                            <span>
                                                <?php 
                                                    $addressParts = [];
                                                    if (!empty($school['address'])) $addressParts[] = $school['address'];
                                                    if (!empty($school['city'])) $addressParts[] = $school['city'];
                                                    if (!empty($school['province'])) $addressParts[] = $school['province'];
                                                    echo htmlspecialchars(implode(', ', $addressParts));
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($school['telephone_number']) || !empty($school['mobile_number'])): ?>
                                        <div class="text-gray-600 mb-2 flex items-center gap-2">
                                            <i class="fas fa-phone text-gray-400"></i>
                                            <span>
                                                <?php if (!empty($school['telephone_number'])): ?>
                                                    <?php echo htmlspecialchars($school['telephone_number']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($school['mobile_number'])): ?>
                                                    <?php if (!empty($school['telephone_number'])) echo ' | '; ?>
                                                    <?php echo htmlspecialchars($school['mobile_number']); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($school['email'])): ?>
                                        <div class="text-gray-600 mb-3 flex items-center gap-2">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                            <span><?php echo htmlspecialchars($school['email']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-4">
                                        <span class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Recognized by DepEd
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-2">
                                        <?php if ($school['offers_elementary']): ?>
                                            <span class="level-badge bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-medium">Elementary</span>
                                        <?php endif; ?>
                                        <?php if ($school['offers_jhs']): ?>
                                            <span class="level-badge bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-medium">Junior High</span>
                                        <?php endif; ?>
                                        <?php if ($school['offers_shs']): ?>
                                            <span class="level-badge bg-red-100 text-red-800 text-xs px-3 py-1 rounded-full font-medium">Senior High</span>
                                        <?php endif; ?>
                                        <?php if ($school['offers_sped']): ?>
                                            <span class="level-badge bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded-full font-medium">SPED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                            <p class="text-gray-500 text-lg">No recognized schools found in this barangay.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Show all barangays by default -->
                <?php if (count($allSchools) > 0): ?>
                    <?php foreach ($allSchools as $barangay => $schools): ?>
                        <div class="mb-10 pb-8 border-b border-gray-200 last:border-b-0">
                            <div class="barangay-header flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                                <h2 class="text-2xl font-bold text-gray-800 mb-2 md:mb-0">
                                    <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                                    <?php echo htmlspecialchars($barangay); ?>
                                </h2>
                                <span class="stats-badge"><?php echo count($schools); ?> <?php echo count($schools) === 1 ? 'school' : 'schools'; ?></span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($schools as $school): ?>
                                    <div class="school-card p-6">
                                        <h3 class="text-xl font-bold text-green-700 mb-3">
                                            <?php echo htmlspecialchars($school['school_name']); ?>
                                        </h3>
                                        
                                        <?php if (!empty($school['address']) || !empty($school['city']) || !empty($school['province'])): ?>
                                            <div class="text-gray-600 mb-3 flex items-start gap-2">
                                                <i class="fas fa-map-marker-alt text-gray-400 mt-1"></i>
                                                <span>
                                                    <?php 
                                                        $addressParts = [];
                                                        if (!empty($school['address'])) $addressParts[] = $school['address'];
                                                        if (!empty($school['city'])) $addressParts[] = $school['city'];
                                                        if (!empty($school['province'])) $addressParts[] = $school['province'];
                                                        echo htmlspecialchars(implode(', ', $addressParts));
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($school['telephone_number']) || !empty($school['mobile_number'])): ?>
                                            <div class="text-gray-600 mb-2 flex items-center gap-2">
                                                <i class="fas fa-phone text-gray-400"></i>
                                                <span>
                                                    <?php if (!empty($school['telephone_number'])): ?>
                                                        <?php echo htmlspecialchars($school['telephone_number']); ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($school['mobile_number'])): ?>
                                                        <?php if (!empty($school['telephone_number'])) echo ' | '; ?>
                                                        <?php echo htmlspecialchars($school['mobile_number']); ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($school['email'])): ?>
                                            <div class="text-gray-600 mb-3 flex items-center gap-2">
                                                <i class="fas fa-envelope text-gray-400"></i>
                                                <span><?php echo htmlspecialchars($school['email']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mb-4">
                                            <span class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Recognized by DepEd
                                            </span>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-2">
                                            <?php if ($school['offers_elementary']): ?>
                                                <span class="level-badge bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-medium">Elementary</span>
                                            <?php endif; ?>
                                            <?php if ($school['offers_jhs']): ?>
                                                <span class="level-badge bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-medium">Junior High</span>
                                            <?php endif; ?>
                                            <?php if ($school['offers_shs']): ?>
                                                <span class="level-badge bg-red-100 text-red-800 text-xs px-3 py-1 rounded-full font-medium">Senior High</span>
                                            <?php endif; ?>
                                            <?php if ($school['offers_sped']): ?>
                                                <span class="level-badge bg-yellow-100 text-yellow-800 text-xs px-3 py-1 rounded-full font-medium">SPED</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-16">
                        <i class="fas fa-school text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Schools Found</h3>
                        <p class="text-gray-500">No recognized schools found in the database.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($selectedBarangay): ?>
                <div class="text-center mt-8 pt-6 border-t border-gray-200">
                    <a href="barangay.php" class="view-all-btn inline-flex items-center gap-2 px-6 py-3 font-medium rounded-lg">
                        <i class="fas fa-list"></i> View All Barangays
                    </a>
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
                        <li><a href="barangay.php" class="text-white text-opacity-90 hover:text-white hover:text-opacity-100 transition">Schools</a></li>
                        <li><a href="user_announcement.php" class="text-white text-opacity-90 hover:text-white hover:text-opacity-100 transition">Announcements</a></li>
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