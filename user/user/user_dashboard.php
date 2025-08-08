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

date_default_timezone_set('Asia/Manila');

// Calculate total recognized schools
$sql_total = "SELECT COUNT(*) as total FROM schools WHERE recognize = 1";
$result_total = $conn->query($sql_total);
$total_schools = $result_total->fetch_assoc()['total'];

// Get top 5 barangays with most recognized schools
$sql_barangay = "SELECT s.barangay, GROUP_CONCAT(DISTINCT s.school_name SEPARATOR ', ') as school_names, COUNT(*) as count 
                FROM schools  s
                WHERE s.recognize = 1 
                GROUP BY s.barangay 
                ORDER BY count DESC 
                LIMIT 5";
$result_barangay = $conn->query($sql_barangay);

$barangays = [];
$school_counts = [];
$school_names_list = [];

while($row = $result_barangay->fetch_assoc()) {
    $barangays[] = $row['barangay'];
    $school_counts[] = $row['count'];
    $school_names_list[] = $row['school_names'];
}

while($row = $result_barangay->fetch_assoc()) {
    $barangays[] = $row['barangay'];
    $school_counts[] = $row['count'];
}

// Fetch active announcements
$current_date = date('Y-m-d'); // Keep this
$sql_announcements = "SELECT * FROM announcements 
                     WHERE is_active = 1 
                     ORDER BY created_at DESC LIMIT 2";
$result_announcements = $conn->query($sql_announcements);

$announcements = [];
if ($result_announcements->num_rows > 0) {
    while($row = $result_announcements->fetch_assoc()) {
        $announcements[] = $row;
    }
}



// available schools for the dashboard
$sql_all_schools = "SELECT * FROM schools WHERE recognize = 1 ORDER BY school_name ASC";
$result_all_schools = $conn->query($sql_all_schools);
$all_schools = [];
if ($result_all_schools->num_rows > 0) {
    while($row = $result_all_schools->fetch_assoc()) {
        $all_schools[] = $row;
    }
}

// Get recognized school level statistics
$sql_levels = "SELECT 
    SUM(offers_elementary) as elementary_schools,
    SUM(offers_jhs) as jhs_schools,
    SUM(offers_shs) as shs_schools,
    SUM(offers_sped) as sped_schools
    FROM schools WHERE recognize = 1";

$result_levels = $conn->query($sql_levels);
$level_stats = $result_levels->fetch_assoc();

// Fetch featured schools 
$sql_featured = "SELECT * FROM schools WHERE recognize = 1 ORDER BY RAND() LIMIT 3";
$result_featured = $conn->query($sql_featured);

include 'user_header.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City - School Finder</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .hero-card {
            background: linear-gradient(135deg,#4ADE80 0% , #22C55E 100%);
            color: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .search-card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .quick-link {
            transition: all 0.2s ease;
        }
        .quick-link:hover {
            transform: scale(1.05);
        }
        .footer-link {
            transition: all 0.2s ease;
        }
        .footer-link:hover {
            color: #3b82f6;
            transform: translateX(3px);
        }
    </style>
</head>
<body class="bg-gray-50 font-roboto flex flex-col min-h-screen">
    <!-- Main Content -->
    <div class="flex-grow px-4 py-6 md:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="hero-card p-6 mb-8">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-2/3 mb-6 md:mb-0 text-center md:text-left">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Private Schoool Portal of General Trias City</h1>
                    <p class="text-black-100 mb-4">Total of <?php echo $total_schools; ?> private schools of General Trias City.</p>
                    <div class="flex justify-center md:justify-start">
                    </div>
                </div>
                <div class="md:w-1/3 flex justify-center">
                    <img src="images/deped_logo.png" alt="School Illustration" class="h-40 md:h-48">
                </div>
            </div>
        </div>
        <!-- Quick Links -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="quick-link bg-white rounded-lg p-4 text-center hover:bg-blue-50 border border-gray-100">
                <div class="text-blue-600 mb-2"><i class="fas fa-child text-2xl"></i></div>
                <h3 class="font-medium text-gray-800">Elementary</h3>
                <p class="text-sm text-gray-500"><?php echo $level_stats['elementary_schools']; ?> schools</p>
            </div>
            <div class="quick-link bg-white rounded-lg p-4 text-center hover:bg-green-50 border border-gray-100">
                <div class="text-green-600 mb-2"><i class="fas fa-user-graduate text-2xl"></i></div>
                <h3 class="font-medium text-gray-800">Junior High</h3>
                <p class="text-sm text-gray-500"><?php echo $level_stats['jhs_schools']; ?> schools</p>
            </div>
            <div class="quick-link bg-white rounded-lg p-4 text-center hover:bg-red-50 border border-gray-100">
                <div class="text-red-600 mb-2"><i class="fas fa-user-graduate text-2xl"></i></div>
                <h3 class="font-medium text-gray-800">Senior High</h3>
                <p class="text-sm text-gray-500"><?php echo $level_stats['shs_schools']; ?> schools</p>
            </div>
            <div class="quick-link bg-white rounded-lg p-4 text-center hover:bg-yellow-50 border border-gray-100">
                <div class="text-yellow-600 mb-2"><i class="fas fa-wheelchair text-2xl"></i></div>
                <h3 class="font-medium text-gray-800">SPED</h3>
                <p class="text-sm text-gray-500"><?php echo $level_stats['sped_schools']; ?> schools</p>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- School Distribution -->
            <div class="stat-card bg-white rounded-lg p-6 border-l-blue-500">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                        Schools by Barangay
                    </h2>
                    <a href="barangay.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        View All <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
                <div class="space-y-3">
                    <?php foreach(array_combine($barangays, $school_counts) as $barangay => $count): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700"><?php echo $barangay; ?></span>
                        <span class="font-medium"><?php echo $count; ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" 
                             style="width: <?php echo ($count/max($school_counts))*100; ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- School Levels Chart -->
            <div class="stat-card bg-white rounded-lg p-6 border-l-green-500">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-layer-group text-green-500 mr-2"></i>
                    School Levels
                </h2>
                <div class="h-48">
                    <canvas id="levelsChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                        <span class="text-sm">Elementary</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-sm">Junior High</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span class="text-sm">Senior High</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-sm">SPED</span>
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="stat-card bg-white rounded-lg p-6 border-l-purple-500">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-bullhorn text-purple-500 mr-2"></i>
                        Announcements
                    </h2>
                    <a href="user_announcement.php" class="text-purple-600 hover:text-purple-800 text-sm">
                        View All <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
                <div class="space-y-4">
                    <?php if (!empty($announcements)): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="border-l-2 border-purple-300 pl-3">
                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($announcement['content']); ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo date('M d, Y', strtotime($announcement['start_date'])); ?> - 
                                    <?php echo date('M d, Y', strtotime($announcement['end_date'])); ?>
                                    <span class="block">Posted: <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></span>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">No current announcements</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Featured Schools -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Available Schools
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php if ($result_featured->num_rows > 0): ?>
                    <?php while($school = $result_featured->fetch_assoc()): ?>
                        <?php 
                        $bg_colors = ['blue', 'green', 'purple'];
                        $color = $bg_colors[array_rand($bg_colors)];
                        ?>
                        <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="h-32 bg-<?php echo $color; ?>-100 flex items-center justify-center">
                                <i class="fas fa-school text-4xl text-<?php echo $color; ?>-600"></i>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($school['school_name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($school['barangay']); ?></p>
                                <div class="flex flex-wrap gap-1 mb-3">
                                    <?php if ($school['offers_elementary']): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Elementary</span>
                                    <?php endif; ?>
                                    <?php if ($school['offers_jhs']): ?>
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Junior High</span>
                                    <?php endif; ?>
                                    <?php if ($school['offers_shs']): ?>
                                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">Senior High</span>
                                    <?php endif; ?>
                                    <?php if ($school['offers_sped']): ?>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">SPED</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500 col-span-3 text-center py-4">No recognized schools available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- All Schools Dropdown Section -->
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <button id="toggleSchools" class="w-full text-left p-4 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-list-ul text-blue-500 mr-2"></i>
                    View All Schools (<?php echo $total_schools; ?>)
                </h2>
                <i class="fas fa-chevron-down transition-transform duration-300" id="toggleIcon"></i>
            </button>
            <div id="schoolsDropdown" class="hidden border-t border-gray-100">
                <div class="p-4">
                    <!-- Search filter -->
                    <div class="mb-4 relative">
                        <input type="text" id="schoolSearch" placeholder="Search schools..." 
                            class="w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Schools list -->
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barangay</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Levels Offered</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="schoolsTableBody">
                                <?php if (!empty($all_schools)): ?>
                                    <?php foreach ($all_schools as $school): ?>
                                        <tr class="school-row hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($school['school_name']); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($school['barangay']); ?></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex flex-wrap gap-1">
                                                    <?php if ($school['offers_elementary']): ?>
                                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Elementary</span>
                                                    <?php endif; ?>
                                                    <?php if ($school['offers_jhs']): ?>
                                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Junior High</span>
                                                    <?php endif; ?>
                                                    <?php if ($school['offers_shs']): ?>
                                                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">Senior High</span>
                                                    <?php endif; ?>
                                                    <?php if ($school['offers_sped']): ?>
                                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">SPED</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">No schools found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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


<!-- Font Awesome for icons (include in your head tag) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Font Awesome for icons (include in your head tag) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>

        document.getElementById('toggleSchools').addEventListener('click', function() {
            const dropdown = document.getElementById('schoolsDropdown');
            const icon = document.getElementById('toggleIcon');
            
            dropdown.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });

        // School search functionality
        document.getElementById('schoolSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.school-row');
            
            rows.forEach(row => {
                const schoolName = row.querySelector('td:first-child').textContent.toLowerCase();
                const barangay = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (schoolName.includes(searchTerm) || barangay.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // School Levels Chart
            const levelsCtx = document.getElementById('levelsChart').getContext('2d');
            new Chart(levelsCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Elementary', 'Junior High', 'Senior High', 'SPED'],
                    datasets: [{
                        data: [
                            <?php echo $level_stats['elementary_schools']; ?>,
                            <?php echo $level_stats['jhs_schools']; ?>,
                            <?php echo $level_stats['shs_schools']; ?>,
                            <?php echo $level_stats['sped_schools']; ?>
                        ],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(245, 158, 11, 0.8)'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

<?php
$conn->close();
?>
</body>
</html>