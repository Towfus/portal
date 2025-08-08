<?php 
include 'header.php';

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

function loadProcessData($conn) {
    $processData = [];
    
    try {
        // Get all process types with announcements
        $processSql = "SELECT process_id, title, description, announcement FROM process_types 
                      ORDER BY 
                        CASE 
                          WHEN title LIKE 'Processes on Application of New Government Permit%' THEN 1
                          WHEN title LIKE 'Application of Government Recognition%' THEN 2
                          WHEN title LIKE 'Renewal of Government Permit%' THEN 3
                          WHEN title LIKE 'Application of Increase/Acknowledgement%' THEN 4
                          ELSE 5
                        END";
        $processResult = $conn->query($processSql);
        
        if (!$processResult) {
            throw new Exception("Error loading process data: " . $conn->error);
        }
        
        while ($processRow = $processResult->fetch_assoc()) {
            $processId = $processRow['process_id'];
            $processData[$processId] = [
                'title' => $processRow['title'],
                'description' => $processRow['description'],
                'announcement' => $processRow['announcement'],
                'compliant' => [],
                'nonCompliant' => []
            ];
            
            // Get paths for this process
            $pathSql = "SELECT path_id, path_type FROM process_paths WHERE process_id = ?";
            $pathStmt = $conn->prepare($pathSql);
            $pathStmt->bind_param("s", $processId);
            $pathStmt->execute();
            $pathResult = $pathStmt->get_result();
            
            while ($pathRow = $pathResult->fetch_assoc()) {
                $pathId = $pathRow['path_id'];
                $pathType = $pathRow['path_type'] === 'compliant' ? 'compliant' : 'nonCompliant';
                
                // Get steps for this path
                $stepSql = "SELECT title FROM process_steps WHERE path_id = ? ORDER BY step_order";
                $stepStmt = $conn->prepare($stepSql);
                $stepStmt->bind_param("i", $pathId);
                $stepStmt->execute();
                $stepResult = $stepStmt->get_result();
                
                while ($stepRow = $stepResult->fetch_assoc()) {
                    $processData[$processId][$pathType][] = $stepRow['title'];
                }
            }
        }
        
        return $processData;
    } catch (Exception $e) {
        error_log("Error in loadProcessData: " . $e->getMessage());
        return [];
    }
}

// Load process data
$processData = loadProcessData($conn);

// Convert to JSON for JavaScript
$processFlowsJson = json_encode($processData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DepEd School Application Forms Portal - Access all necessary forms for school applications, renewals, and permits">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/process_flow.css">
    <style>
        /* Announcement styles */
        .announcement-container {
            margin: 20px 0;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .announcement-poster {
            background-color: #fff8e1;
            border-left: 5px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .announcement-poster h3 {
            color: #d32f2f;
            margin-top: 0;
            font-size: 1.5em;
            border-bottom: 1px solid #ffc107;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .announcement-poster p {
            font-size: 1.1em;
            line-height: 1.6;
            white-space: pre-line;
            margin-top: 15px;
        }
        
        .announcement-icon {
            color: #d32f2f;
            margin-right: 10px;
            font-size: 1.5em;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="process-types">
            <h2>Select Process Type</h2>
            <div class="process-buttons">
                <?php foreach ($processData as $processId => $process): ?>
                    <button class="process-btn" data-process="<?php echo htmlspecialchars($processId); ?>">
                        <?php echo htmlspecialchars($process['description']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="process-flow-container" id="processFlowContainer" style="display: none;">
            <div class="process-header">
                <h2 class="process-flow-title" id="processFlowTitle">Process Flow</h2>
                <div class="announcement-container" id="announcementContainer"></div>
                <div class="tab-container">
                    <div class="tab active compliant" data-path="compliant">
                        <i class="fas fa-check-circle"></i> Compliant Path
                    </div>
                    <div class="tab non-compliant" data-path="nonCompliant">
                        <i class="fas fa-exclamation-circle"></i> Non-Compliant Path
                    </div>
                </div>
            </div>

            <div class="path-content active" id="compliantPath">
                <div class="flow-diagram" id="compliantDiagram"></div>
            </div>
            
            <div class="path-content" id="nonCompliantPath">
                <div class="flow-diagram" id="nonCompliantDiagram"></div>
            </div>
        </div>
    </div>

    <script>
        const processFlows = <?php echo $processFlowsJson; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            const processButtons = document.querySelectorAll('.process-btn');
            const processFlowContainer = document.getElementById('processFlowContainer');
            const processFlowTitle = document.getElementById('processFlowTitle');
            const announcementContainer = document.getElementById('announcementContainer');
            const compliantDiagram = document.getElementById('compliantDiagram');
            const nonCompliantDiagram = document.getElementById('nonCompliantDiagram');
            const tabs = document.querySelectorAll('.tab');
            const pathContents = document.querySelectorAll('.path-content');
            
            function createFlowDiagram(steps, container) {
    container.innerHTML = '';
    
    if (!steps || steps.length === 0) {
        container.innerHTML = '<div class="no-steps">No steps defined for this path.</div>';
        return;
    }
    
    const flowContainer = document.createElement('div');
    flowContainer.className = 'vertical-flow';
    
    steps.forEach((step, index) => {
        const stepElement = document.createElement('div');
        stepElement.className = 'flow-step';
        stepElement.innerHTML = `
            <div class="step-content"><strong>STEP ${index + 1}:</strong> ${step}</div>
        `;
        flowContainer.appendChild(stepElement);
        
        if (index < steps.length - 1) {
            const arrow = document.createElement('div');
            arrow.className = 'flow-arrow';
            arrow.innerHTML = '<i class="fas fa-chevron-down"></i>';
            flowContainer.appendChild(arrow);
        }
    });
    
    container.appendChild(flowContainer);
}
            
            // Display process flow when button is clicked
            function displayProcessFlow(processId) {
                const process = processFlows[processId];
                
                if (process) {
                    // Update title
                    processFlowTitle.textContent = process.title;
                    
                    // Clear previous announcement
                    announcementContainer.innerHTML = '';
                    announcementContainer.style.display = 'none';
                    
                    // Display announcement if it exists and is not empty
                    if (process.announcement && process.announcement.trim() !== '') {
                        announcementContainer.innerHTML = `
                            <div class="announcement-poster">
                                <h3><i class="fas fa-bullhorn announcement-icon"></i> IMPORTANT ANNOUNCEMENT</h3>
                                <p>${process.announcement}</p>
                            </div>
                        `;
                        announcementContainer.style.display = 'block';
                    }
                    
                    // Create diagrams
                    createFlowDiagram(process.compliant, compliantDiagram);
                    createFlowDiagram(process.nonCompliant, nonCompliantDiagram);
                    
                    // Show container
                    processFlowContainer.style.display = 'block';
                    
                    // Reset to show compliant path first
                    tabs.forEach(tab => tab.classList.remove('active'));
                    pathContents.forEach(content => content.classList.remove('active'));
                    document.querySelector('.tab[data-path="compliant"]').classList.add('active');
                    document.getElementById('compliantPath').classList.add('active');
                    
                    // Scroll to the process flow
                    processFlowContainer.scrollIntoView({ behavior: 'smooth' });
                }
            }
            
            // Process button click handlers
            processButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const processId = this.getAttribute('data-process');
                    displayProcessFlow(processId);
                });
            });
            
            // Tab switching
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const path = this.getAttribute('data-path');
                    
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    pathContents.forEach(content => content.classList.remove('active'));
                    document.getElementById(path + 'Path').classList.add('active');
                });
            });
            
            // Auto-display first process if only one exists
            if (Object.keys(processFlows).length === 1) {
                const firstProcessId = Object.keys(processFlows)[0];
                displayProcessFlow(firstProcessId);
            }
        });
    </script>
</body>
</html>
<?php 
$conn->close();
include 'footer.php'; 
?>