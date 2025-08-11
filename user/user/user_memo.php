<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'deped_schools');

class UserProcessViewer {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function handleRequest() {
        $this->displayInterface();
    }

    private function loadProcessData() {
        $processData = [];
        
        $processResult = $this->conn->query("
            SELECT process_id, title, description, announcement FROM process_types 
            ORDER BY 
            CASE 
                WHEN title LIKE 'Process of Application of New Government Permit%' THEN 1
                WHEN title LIKE 'Application of Government Recognition%' THEN 2
                WHEN title LIKE 'Renewal of Government Permit%' THEN 3
                WHEN title LIKE 'Application of Increase/Acknowledgement%' THEN 4
                ELSE 5
            END
        ");
        
        if (!$processResult) {
            throw new Exception("Error loading process data: " . $this->conn->error);
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
            
            $this->loadProcessPaths($processId, $processData[$processId]);
        }
        
        return $processData;
    }

    private function loadProcessPaths($processId, &$processData) {
        $stmt = $this->conn->prepare("SELECT path_id, path_type FROM process_paths WHERE process_id = ?");
        $stmt->bind_param("s", $processId);
        $stmt->execute();
        $pathResult = $stmt->get_result();
        
        while ($pathRow = $pathResult->fetch_assoc()) {
            $this->loadProcessSteps($pathRow['path_id'], $pathRow['path_type'], $processData);
        }
    }

    private function loadProcessSteps($pathId, $pathType, &$processData) {
        $stmt = $this->conn->prepare("SELECT step_id, title, description FROM process_steps WHERE path_id = ? ORDER BY step_order");
        $stmt->bind_param("i", $pathId);
        $stmt->execute();
        $stepResult = $stmt->get_result();
        
        while ($stepRow = $stepResult->fetch_assoc()) {
            $processData[$pathType][] = [
                'stepId' => $stepRow['step_id'],
                'title' => $stepRow['title'],
                'description' => $stepRow['description']
            ];
        }
    }

    private function displayInterface() {
        try {
            $processData = $this->loadProcessData();
        } catch (Exception $e) {
            $errorMessage = "Error loading process data: " . $e->getMessage();
            $processData = [
                'default' => [
                    'title' => 'Default Process',
                    'description' => 'No processes found',
                    'announcement' => '',
                    'compliant' => [],
                    'nonCompliant' => []
                ]
            ];
        }

        if (file_exists('user_header.php')) {
            include 'user_header.php';
        }
        
        $this->renderHTML($processData, $errorMessage ?? null);
    }

    private function renderHTML($processData, $errorMessage) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City - Process Flow Guide</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                * {
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Inter', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }
                
                .main-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                    backdrop-filter: blur(10px);
                    overflow: hidden;
                }
                
                .header {
                    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                    color: white;
                    padding: 2rem;
                    text-align: center;
                    position: relative;
                }
                
                .header::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></svg>') repeat;
                }
                
                .header h1 {
                    margin: 0;
                    font-size: 2.5rem;
                    font-weight: 700;
                    position: relative;
                    z-index: 1;
                }
                
                .header p {
                    margin: 0.5rem 0 0 0;
                    font-size: 1.1rem;
                    opacity: 0.9;
                    position: relative;
                    z-index: 1;
                }
                
                .process-navigation {
                    padding: 1.5rem 2rem;
                    background: #f8fafc;
                    border-bottom: 1px solid #e2e8f0;
                }
                
                .process-tabs {
                    display: flex;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                    margin-bottom: 1rem;
                }
                
                .process-tab {
                    padding: 0.75rem 1.5rem;
                    border-radius: 12px;
                    background: white;
                    border: 2px solid #e2e8f0;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-weight: 500;
                    color: #64748b;
                    flex: 1;
                    min-width: 200px;
                    text-align: center;
                }
                
                .process-tab:hover {
                    border-color: #3b82f6;
                    color: #3b82f6;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
                }
                
                .process-tab.active {
                    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                    color: white;
                    border-color: #3b82f6;
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
                }
                
                .path-selector {
                    display: flex;
                    background: white;
                    border-radius: 12px;
                    padding: 0.5rem;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    max-width: 400px;
                    margin: 0 auto;
                }
                
                .path-button {
                    flex: 1;
                    padding: 0.75rem 1rem;
                    border: none;
                    border-radius: 8px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    background: transparent;
                    color: #64748b;
                }
                
                .path-button.compliant.active {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: white;
                    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
                }
                
                .path-button.non-compliant.active {
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: white;
                    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
                }
                
                .content-area {
                    padding: 2rem;
                }
                
                .process-title {
                    text-align: center;
                    margin-bottom: 2rem;
                }
                
                .process-title h2 {
                    font-size: 2rem;
                    font-weight: 700;
                    color: #1e293b;
                    margin: 0 0 0.5rem 0;
                }
                
                .process-announcement {
                    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
                    color: white;
                    padding: 1.5rem;
                    border-radius: 12px;
                    margin-bottom: 2rem;
                    position: relative;
                    overflow: hidden;
                }
                
                .process-announcement::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url('data:image/svg+xml,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="20" cy="20" r="1.5"/></g></svg>') repeat;
                }
                
                .process-announcement h3 {
                    margin: 0 0 1rem 0;
                    font-size: 1.25rem;
                    font-weight: 600;
                    position: relative;
                    z-index: 1;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                
                .process-announcement p {
                    margin: 0;
                    line-height: 1.6;
                    position: relative;
                    z-index: 1;
                    font-weight: 500;
                }
                
                .steps-container {
                    background: white;
                    border-radius: 16px;
                    padding: 2rem;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                    border: 1px solid #e2e8f0;
                }
                
                .steps-header {
                    text-align: center;
                    margin-bottom: 2rem;
                    padding-bottom: 1rem;
                    border-bottom: 2px solid #e2e8f0;
                }
                
                .steps-header h3 {
                    font-size: 1.5rem;
                    font-weight: 600;
                    margin: 0;
                    color: #1e293b;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                }
                
                .steps-header .compliant-icon {
                    color: #10b981;
                }
                
                .steps-header .non-compliant-icon {
                    color: #ef4444;
                }
                
                .flow-step {
                    display: flex;
                    align-items: flex-start;
                    margin-bottom: 2rem;
                    position: relative;
                }
                
                .flow-step::before {
                    content: '';
                    position: absolute;
                    left: 25px;
                    top: 60px;
                    bottom: -20px;
                    width: 2px;
                    background: linear-gradient(to bottom, #e2e8f0, transparent);
                }
                
                .flow-step:last-child::before {
                    display: none;
                }
                
                .step-number {
                    width: 50px;
                    height: 50px;
                    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 700;
                    font-size: 1.125rem;
                    margin-right: 1.5rem;
                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                    flex-shrink: 0;
                    position: relative;
                    z-index: 1;
                }
                
                .step-content {
                    flex: 1;
                    background: #f8fafc;
                    padding: 1.5rem;
                    border-radius: 12px;
                    border-left: 4px solid #3b82f6;
                    transition: all 0.3s ease;
                }
                
                .step-content:hover {
                    background: #f1f5f9;
                    transform: translateX(4px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }
                
                .step-title {
                    font-size: 1.25rem;
                    font-weight: 600;
                    color: #1e293b;
                    margin: 0 0 0.75rem 0;
                }
                
                .step-description {
                    color: #475569;
                    line-height: 1.6;
                    margin: 0;
                }
                
                .no-steps {
                    text-align: center;
                    color: #64748b;
                    font-style: italic;
                    padding: 3rem;
                    background: #f8fafc;
                    border-radius: 12px;
                    border: 2px dashed #cbd5e1;
                }
                
                .error-message {
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: white;
                    padding: 1rem;
                    border-radius: 12px;
                    margin: 1rem 0;
                    text-align: center;
                    font-weight: 500;
                }
                
                @media (max-width: 768px) {
                    body {
                        padding: 10px;
                    }
                    
                    .header h1 {
                        font-size: 2rem;
                    }
                    
                    .process-tabs {
                        flex-direction: column;
                    }
                    
                    .process-tab {
                        min-width: auto;
                    }
                    
                    .content-area {
                        padding: 1rem;
                    }
                    
                    .steps-container {
                        padding: 1rem;
                    }
                    
                    .flow-step {
                        flex-direction: column;
                        align-items: stretch;
                    }
                    
                    .step-number {
                        align-self: flex-start;
                        margin-bottom: 1rem;
                        margin-right: 0;
                    }
                    
                    .flow-step::before {
                        left: 25px;
                        top: 70px;
                    }
                }
                
                .loading {
                    text-align: center;
                    padding: 3rem;
                    color: #64748b;
                }
                
                .loading i {
                    font-size: 2rem;
                    animation: spin 1s linear infinite;
                }
                
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            </style>
        </head>
        <body>
            <div class="main-container">
                <div class="header">
                    <h1><i class="fas fa-route mr-3"></i>Process Flow Guide</h1>
                    <p>Step-by-step guide for DepEd processes and requirements</p>
                </div>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <div class="process-navigation">
                    <div class="process-tabs" id="processTabs">
                        <?php foreach ($processData as $processId => $process): ?>
                        <div class="process-tab <?php echo ($processId === array_key_first($processData)) ? 'active' : ''; ?>" 
                             data-process="<?php echo htmlspecialchars($processId); ?>">
                            <?php echo htmlspecialchars($process['description']); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="path-selector">
                        <button class="path-button compliant active" data-path="compliant">
                            <i class="fas fa-check-circle mr-2"></i>Compliant Path
                        </button>
                        <button class="path-button non-compliant" data-path="nonCompliant">
                            <i class="fas fa-exclamation-circle mr-2"></i>Non-Compliant Path
                        </button>
                    </div>
                </div>
                
                <div class="content-area">
                    <div class="process-title">
                        <h2 id="processTitle">Loading...</h2>
                    </div>
                    
                    <div id="announcementContainer" style="display: none;"></div>
                    
                    <div class="steps-container">
                        <div class="steps-header">
                            <h3 id="stepsHeader">
                                <i class="fas fa-check-circle compliant-icon"></i>
                                <span>Compliant Process Steps</span>
                            </h3>
                        </div>
                        
                        <div id="stepsList">
                            <div class="loading">
                                <i class="fas fa-spinner"></i>
                                <p>Loading process steps...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Store the process data
                    const processData = <?php echo json_encode($processData); ?>;
                    
                    // Current state variables
                    let currentProcessId = Object.keys(processData)[0];
                    let currentPathType = 'compliant';

                    // Initialize the page
                    initializePage();

                    function initializePage() {
                        updateProcessTitle();
                        updateProcessAnnouncement();
                        loadProcessSteps();
                    }

                    function updateProcessTitle() {
                        const process = processData[currentProcessId];
                        document.getElementById('processTitle').textContent = process.title;
                    }

                    function updateProcessAnnouncement() {
                        const process = processData[currentProcessId];
                        const announcementContainer = document.getElementById('announcementContainer');
                        
                        if (process.announcement && process.announcement.trim() !== '') {
                            announcementContainer.innerHTML = `
                                <div class="process-announcement">
                                    <h3><i class="fas fa-bullhorn"></i> Important Announcement</h3>
                                    <p>${process.announcement}</p>
                                </div>
                            `;
                            announcementContainer.style.display = 'block';
                        } else {
                            announcementContainer.style.display = 'none';
                        }
                    }

                    function loadProcessSteps() {
                        const steps = processData[currentProcessId][currentPathType] || [];
                        const stepsList = document.getElementById('stepsList');
                        const stepsHeader = document.getElementById('stepsHeader');
                        
                        // Update header based on path type
                        if (currentPathType === 'compliant') {
                            stepsHeader.innerHTML = `
                                <i class="fas fa-check-circle compliant-icon"></i>
                                <span>Compliant Process Steps</span>
                            `;
                        } else {
                            stepsHeader.innerHTML = `
                                <i class="fas fa-exclamation-circle non-compliant-icon"></i>
                                <span>Non-Compliant Process Steps</span>
                            `;
                        }

                        if (steps.length === 0) {
                            stepsList.innerHTML = `
                                <div class="no-steps">
                                    <i class="fas fa-info-circle mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                                    <p>No steps have been configured for this process path yet.</p>
                                </div>
                            `;
                            return;
                        }

                        let stepsHTML = '';
                        steps.forEach((step, index) => {
                            stepsHTML += `
                                <div class="flow-step">
                                    <div class="step-number">${index + 1}</div>
                                    <div class="step-content">
                                        <h4 class="step-title">${step.title}</h4>
                                        <p class="step-description">${step.description}</p>
                                    </div>
                                </div>
                            `;
                        });
                        
                        stepsList.innerHTML = stepsHTML;
                    }

                    // Process tab click handlers
                    document.querySelectorAll('.process-tab').forEach(tab => {
                        tab.addEventListener('click', function() {
                            const processId = this.getAttribute('data-process');
                            currentProcessId = processId;
                            
                            // Update UI
                            document.querySelectorAll('.process-tab').forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                            
                            updateProcessTitle();
                            updateProcessAnnouncement();
                            loadProcessSteps();
                        });
                    });

                    // Path button click handlers
                    document.querySelectorAll('.path-button').forEach(button => {
                        button.addEventListener('click', function() {
                            const pathType = this.getAttribute('data-path');
                            currentPathType = pathType;
                            
                            // Update UI
                            document.querySelectorAll('.path-button').forEach(b => b.classList.remove('active'));
                            this.classList.add('active');
                            
                            loadProcessSteps();
                        });
                    });

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
                });
            </script>
        </body>
        </html>
        <?php
    }
}

$userProcessViewer = new UserProcessViewer();
$userProcessViewer->handleRequest();
?>