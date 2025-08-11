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
        
        $this->renderHTML($processData, $errorMessage ?? null);

    }

    private function renderHTML($processData, $errorMessage) {
                include 'user_header.php';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Process Flow Guide - DepEd General Trias City</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --primary-green: #22c55e;
                    --light-green: #86efac;
                    --pale-green: #f0fdf4;
                    --dark-green: #16a34a;
                    --accent-green: #4ade80;
                    --text-dark: #1f2937;
                    --text-light: #6b7280;
                    --white: #ffffff;
                    --gray-50: #f9fafb;
                    --gray-100: #f3f4f6;
                    --gray-200: #e5e7eb;
                    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
                    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
                    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
                }

                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Inter', sans-serif;
                    background: linear-gradient(135deg, var(--pale-green) 0%, var(--gray-50) 100%);
                    min-height: 100vh;
                    color: var(--text-dark);
                    line-height: 1.6;
                }
                
                .main-container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
                }
                
                .welcome-banner {
                    background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
                    color: var(--white);
                    padding: 3rem 2rem;
                    border-radius: 20px;
                    margin-bottom: 2rem;
                    text-align: center;
                    position: relative;
                    overflow: hidden;
                    box-shadow: var(--shadow-xl);
                }
                
                .welcome-banner::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></svg>') repeat;
                }
                
                .welcome-content {
                    position: relative;
                    z-index: 2;
                }
                
                .welcome-banner h1 {
                    font-size: 2.5rem;
                    font-weight: 700;
                    margin-bottom: 1rem;
                }
                
                .welcome-banner p {
                    font-size: 1.125rem;
                    opacity: 0.95;
                    max-width: 600px;
                    margin: 0 auto;
                }
                
                .process-card {
                    background: var(--white);
                    border-radius: 20px;
                    box-shadow: var(--shadow-lg);
                    overflow: hidden;
                    margin-bottom: 2rem;
                    border: 1px solid var(--gray-100);
                }
                
                .card-header {
                    background: linear-gradient(135deg, var(--light-green) 0%, var(--accent-green) 100%);
                    padding: 2rem;
                    text-align: center;
                }
                
                .card-header h2 {
                    font-size: 1.75rem;
                    font-weight: 600;
                    color: var(--text-dark);
                    margin: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.75rem;
                }
                
                .process-navigation {
                    padding: 2rem;
                    background: var(--gray-50);
                    border-bottom: 1px solid var(--gray-200);
                }
                
                .process-tabs {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 1rem;
                    margin-bottom: 2rem;
                }
                
                .process-tab {
                    padding: 1.25rem 1.5rem;
                    border-radius: 16px;
                    background: var(--white);
                    border: 2px solid transparent;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-weight: 500;
                    color: var(--text-dark);
                    text-align: center;
                    box-shadow: var(--shadow-sm);
                }
                
                .process-tab:hover {
                    border-color: var(--primary-green);
                    transform: translateY(-2px);
                    box-shadow: var(--shadow-md);
                }
                
                .process-tab.active {
                    background: var(--primary-green);
                    color: var(--white);
                    border-color: var(--primary-green);
                    transform: translateY(-2px);
                    box-shadow: var(--shadow-lg);
                }
                
                .path-selector {
                    display: flex;
                    background: var(--white);
                    border-radius: 16px;
                    padding: 0.5rem;
                    box-shadow: var(--shadow-sm);
                    max-width: 400px;
                    margin: 0 auto;
                    border: 1px solid var(--gray-200);
                }
                
                .path-button {
                    flex: 1;
                    padding: 1rem 1.25rem;
                    border: none;
                    border-radius: 12px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    background: transparent;
                    color: var(--text-light);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                }
                
                .path-button.active {
                    background: var(--primary-green);
                    color: var(--white);
                    box-shadow: var(--shadow-sm);
                }
                
                .content-area {
                    padding: 2rem;
                }
                
                .process-title {
                    text-align: center;
                    margin-bottom: 2rem;
                }
                
                .process-title h3 {
                    font-size: 2rem;
                    font-weight: 700;
                    color: var(--text-dark);
                    margin: 0;
                }
                
                .process-announcement {
                    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
                    color: var(--white);
                    padding: 1.5rem;
                    border-radius: 16px;
                    margin-bottom: 2rem;
                    position: relative;
                    overflow: hidden;
                    box-shadow: var(--shadow-md);
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
                
                .announcement-content {
                    position: relative;
                    z-index: 1;
                }
                
                .announcement-content h4 {
                    margin: 0 0 1rem 0;
                    font-size: 1.25rem;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                
                .announcement-content p {
                    margin: 0;
                    font-weight: 500;
                }
                
                .steps-container {
                    background: var(--white);
                    border-radius: 20px;
                    padding: 2rem;
                    box-shadow: var(--shadow-md);
                    border: 1px solid var(--gray-100);
                }
                
                .steps-header {
                    text-align: center;
                    margin-bottom: 2rem;
                    padding-bottom: 1rem;
                    border-bottom: 2px solid var(--gray-100);
                }
                
                .steps-header h4 {
                    font-size: 1.5rem;
                    font-weight: 600;
                    margin: 0;
                    color: var(--text-dark);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                }
                
                .compliant-icon {
                    color: var(--primary-green);
                }
                
                .non-compliant-icon {
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
                    left: 30px;
                    top: 70px;
                    bottom: -20px;
                    width: 2px;
                    background: linear-gradient(to bottom, var(--light-green), transparent);
                }
                
                .flow-step:last-child::before {
                    display: none;
                }
                
                .step-number {
                    width: 60px;
                    height: 60px;
                    background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
                    color: var(--white);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 700;
                    font-size: 1.25rem;
                    margin-right: 1.5rem;
                    box-shadow: var(--shadow-md);
                    flex-shrink: 0;
                    position: relative;
                    z-index: 1;
                }
                
                .step-content {
                    flex: 1;
                    background: var(--pale-green);
                    padding: 1.5rem;
                    border-radius: 16px;
                    border-left: 4px solid var(--primary-green);
                    transition: all 0.3s ease;
                    box-shadow: var(--shadow-sm);
                }
                
                .step-content:hover {
                    background: var(--light-green);
                    transform: translateX(4px);
                    box-shadow: var(--shadow-md);
                }
                
                .step-title {
                    font-size: 1.25rem;
                    font-weight: 600;
                    color: var(--text-dark);
                    margin: 0 0 0.75rem 0;
                }
                
                .step-description {
                    color: var(--text-light);
                    margin: 0;
                }
                
                .no-steps {
                    text-align: center;
                    color: var(--text-light);
                    padding: 4rem 2rem;
                    background: var(--gray-50);
                    border-radius: 16px;
                    border: 2px dashed var(--gray-200);
                }
                
                .no-steps i {
                    font-size: 3rem;
                    margin-bottom: 1rem;
                    opacity: 0.5;
                    color: var(--primary-green);
                }
                
                .error-message {
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: var(--white);
                    padding: 1rem 1.5rem;
                    border-radius: 12px;
                    margin-bottom: 2rem;
                    text-align: center;
                    font-weight: 500;
                    box-shadow: var(--shadow-md);
                }
                
                .fade-in {
                    animation: fadeInUp 0.6s ease forwards;
                }
                
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                @media (max-width: 768px) {
                    .main-container {
                        padding: 10px;
                    }
                    
                    .welcome-banner {
                        padding: 2rem 1rem;
                    }
                    
                    .welcome-banner h1 {
                        font-size: 2rem;
                    }
                    
                    .process-navigation {
                        padding: 1.5rem;
                    }
                    
                    .process-tabs {
                        grid-template-columns: 1fr;
                    }
                    
                    .content-area {
                        padding: 1.5rem;
                    }
                    
                    .steps-container {
                        padding: 1.5rem;
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
                        left: 30px;
                        top: 80px;
                    }
                    
                    .path-selector {
                        flex-direction: column;
                        gap: 0.5rem;
                        padding: 1rem;
                    }
                    
                    .path-button {
                        border-radius: 12px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="main-container">
                <?php if (isset($errorMessage)): ?>
                    <div class="error-message fade-in">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <div class="process-card fade-in">
                    <div class="card-header">
                        <h2>
                            <i class="fas fa-clipboard-list"></i>
                            Select Process Type
                        </h2>
                    </div>
                    
                    <div class="process-navigation">
                        <div class="process-tabs" id="processTabs">
                            <?php foreach ($processData as $processId => $process): ?>
                            <div class="process-tab <?php echo ($processId === array_key_first($processData)) ? 'active' : ''; ?>" 
                                 data-process="<?php echo htmlspecialchars($processId); ?>">
                                <i class="fas fa-file-alt"></i>
                                <?php echo htmlspecialchars($process['description']); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="path-selector">
                            <button class="path-button active" data-path="compliant">
                                <i class="fas fa-check-circle"></i>Compliant Path
                            </button>
                            <button class="path-button" data-path="nonCompliant">
                                <i class="fas fa-exclamation-circle"></i>Non-Compliant Path
                            </button>
                        </div>
                    </div>
                    
                    <div class="content-area">
                        <div class="process-title">
                            <h3 id="processTitle">Loading...</h3>
                        </div>
                        
                        <div id="announcementContainer" style="display: none;"></div>
                        
                        <div class="steps-container">
                            <div class="steps-header">
                                <h4 id="stepsHeader">
                                    <i class="fas fa-check-circle compliant-icon"></i>
                                    <span>Compliant Process Steps</span>
                                </h4>
                            </div>
                            
                            <div id="stepsList">
                                <div class="no-steps">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading process steps...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const processData = <?php echo json_encode($processData); ?>;
                    let currentProcessId = Object.keys(processData)[0];
                    let currentPathType = 'compliant';

                    function initializePage() {
                        updateProcessTitle();
                        updateProcessAnnouncement();
                        loadProcessSteps();
                        
                        // Add staggered animation to elements
                        setTimeout(() => {
                            document.querySelectorAll('.process-tab').forEach((tab, index) => {
                                tab.style.animation = `fadeInUp 0.5s ease ${index * 0.1}s both`;
                            });
                        }, 300);
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
                                    <div class="announcement-content">
                                        <h4><i class="fas fa-bullhorn"></i> Important Announcement</h4>
                                        <p>${process.announcement}</p>
                                    </div>
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
                                    <i class="fas fa-info-circle"></i>
                                    <h5>No Steps Available</h5>
                                    <p>No steps have been configured for this process path yet. Please check back later or contact support.</p>
                                </div>
                            `;
                            return;
                        }

                        let stepsHTML = '';
                        steps.forEach((step, index) => {
                            stepsHTML += `
                                <div class="flow-step" style="animation: fadeInUp 0.5s ease ${index * 0.1}s both;">
                                    <div class="step-number">${index + 1}</div>
                                    <div class="step-content">
                                        <h5 class="step-title">${step.title}</h5>
                                        <p class="step-description">${step.description}</p>
                                    </div>
                                </div>
                            `;
                        });
                        
                        stepsList.innerHTML = stepsHTML;
                    }

                    // Event listeners
                    document.querySelectorAll('.process-tab').forEach(tab => {
                        tab.addEventListener('click', function() {
                            const processId = this.getAttribute('data-process');
                            currentProcessId = processId;
                            
                            document.querySelectorAll('.process-tab').forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                            
                            updateProcessTitle();
                            updateProcessAnnouncement();
                            loadProcessSteps();
                        });
                    });

                    document.querySelectorAll('.path-button').forEach(button => {
                        button.addEventListener('click', function() {
                            const pathType = this.getAttribute('data-path');
                            currentPathType = pathType;
                            
                            document.querySelectorAll('.path-button').forEach(b => b.classList.remove('active'));
                            this.classList.add('active');
                            
                            loadProcessSteps();
                        });
                    });

                    // Initialize page
                    initializePage();
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