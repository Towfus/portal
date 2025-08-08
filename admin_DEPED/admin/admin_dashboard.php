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

// Calculate total schools
$sql_total = "SELECT COUNT(*) as total FROM schools";
$result_total = $conn->query($sql_total);
$total_schools = $result_total->fetch_assoc()['total'];

// Get top 5 barangays with most schools
$sql_barangay = "SELECT barangay, COUNT(*) as count FROM schools GROUP BY barangay ORDER BY count DESC LIMIT 5";
$result_barangay = $conn->query($sql_barangay);

$barangays = [];
$school_counts = [];

while($row = $result_barangay->fetch_assoc()) {
    $barangays[] = $row['barangay'];
    $school_counts[] = $row['count'];
}

// Get school level statistics
$sql_levels = "SELECT 
    SUM(offers_elementary) as elementary_schools,
    SUM(offers_jhs) as jhs_schools,
    SUM(offers_shs) as shs_schools,
    SUM(offers_sped) as sped_schools
    FROM schools";

$result_levels = $conn->query($sql_levels);
$level_stats = $result_levels->fetch_assoc();

// Get renewal statistics
$sql_renewal = "SELECT 
    SUM(recognize) as recognized_schools,
    SUM(renewal) as schools_needing_renewal,
    COUNT(*) as total_schools
    FROM schools";

$result_renewal = $conn->query($sql_renewal);
$renewal_stats = $result_renewal->fetch_assoc();

$conn->close();
include 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <style>
        .stat-card {
            transition: transform 0.2s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-icon {
            min-width: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <!-- Main Content -->
        <div class="dashboard-container px-4 py-6 flex-grow">
            <!-- School Level Stats -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <div class="stat-card bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="stat-icon bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                                <i class="fas fa-school text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Total Schools</h3>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $total_schools; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="stat-icon bg-purple-100 text-purple-600 p-3 rounded-full mr-4">
                                <i class="fas fa-graduation-cap text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Elementary</h3>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $level_stats['elementary_schools']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="stat-icon bg-yellow-100 text-yellow-600 p-3 rounded-full mr-4">
                                <i class="fas fa-user-graduate text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Junior High</h3>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $level_stats['jhs_schools']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="stat-icon bg-red-100 text-red-600 p-3 rounded-full mr-4">
                                <i class="fas fa-user-graduate text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Senior High</h3>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $level_stats['shs_schools']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="stat-icon bg-indigo-100 text-indigo-600 p-3 rounded-full mr-4">
                                <i class="fas fa-wheelchair text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">SPED Schools</h3>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $level_stats['sped_schools']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Renewal Stats Section -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="stat-card bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="stat-icon bg-blue-100 text-blue-600 p-3 rounded-full mr-4">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Recognized Schools</h3>
                                <p class="text-2xl font-bold text-gray-800">
                                    <?php echo $renewal_stats['recognized_schools']; ?>
                                    <span class="text-sm font-normal text-gray-500">
                                        (<?php echo round(($renewal_stats['recognized_schools'] / $renewal_stats['total_schools']) * 100, 1); ?>%)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-white rounded-lg shadow p-4">
                        <div class="flex items-center">
                            <div class="stat-icon bg-yellow-100 text-yellow-600 p-3 rounded-full mr-4">
                                <i class="fas fa-exclamation-circle text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Schools Needing Renewal</h3>
                                <p class="text-2xl font-bold text-gray-800">
                                    <?php echo $renewal_stats['schools_needing_renewal']; ?>
                                    <span class="text-sm font-normal text-gray-500">
                                        (<?php echo round(($renewal_stats['schools_needing_renewal'] / $renewal_stats['total_schools']) * 100, 1); ?>%)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- School Distribution Chart -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Schools Distribution by Barangay</h2>
                    <div class="h-64">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
                
                <!-- School Levels Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">School Level Distribution</h2>
                    <div class="h-64">
                        <canvas id="levelsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // School Distribution Chart
            const distCtx = document.getElementById('distributionChart').getContext('2d');
            new Chart(distCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($barangays); ?>,
                    datasets: [{
                        label: 'Number of Schools',
                        data: <?php echo json_encode($school_counts); ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' schools';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            precision: 0,
                            ticks: {
                                callback: function(value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                }
            });
            
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
                            'rgba(99, 102, 241, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(245, 158, 11, 0.7)'
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
                        legend: { 
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 20
                            }
                        },
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
</body>
</html>