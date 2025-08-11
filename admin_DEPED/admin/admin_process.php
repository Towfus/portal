<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'deped_schools');

class ProcessManager {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $this->processAjaxRequest();
            exit;
        }
        $this->displayInterface();
    }

    private function processAjaxRequest() {
        $action = $_POST['action'];
        $response = [];

        try {
            switch ($action) {
                case 'saveTitle':
                    $response = $this->saveProcessTitle(
                        $_POST['processId'],
                        $_POST['title'],
                        $_POST['announcement'] ?? ''
                    );
                    break;
                case 'saveStep':
                    $response = $this->saveProcessStep(
                        $_POST['processId'],
                        $_POST['pathType'],
                        $_POST['stepData']
                    );
                    break;
                case 'deleteStep':
                    $response = $this->deleteProcessStep($_POST['stepId']);
                    break;
                case 'reorderSteps':
                    $response = $this->reorderProcessSteps(
                        $_POST['processId'],
                        $_POST['pathType'],
                        $_POST['stepOrder']
                    );
                    break;
                default:
                    $response = ['success' => false, 'message' => 'Invalid action'];
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }

        echo json_encode($response);
    }

    private function saveProcessTitle($processId, $title, $announcement = '') {
        $stmt = $this->conn->prepare("UPDATE process_types SET title = ?, announcement = ?, updated_at = NOW() WHERE process_id = ?");
        $stmt->bind_param("sss", $title, $announcement, $processId);
        
        if (!$stmt->execute()) {
            throw new Exception('Error updating process: ' . $this->conn->error);
        }

        return [
            'success' => $stmt->affected_rows > 0,
            'message' => $stmt->affected_rows > 0 ? 'Process updated successfully' : 'No changes made'
        ];
    }

    private function saveProcessStep($processId, $pathType, $stepData) {
        $stepData = json_decode($stepData, true);
        if (!$stepData || !isset($stepData['title']) || !isset($stepData['description'])) {
            throw new Exception('Invalid step data');
        }

        $pathId = $this->getPathId($processId, $pathType);
        
        if (isset($stepData['stepId']) && $stepData['stepId'] > 0) {
            return $this->updateStep($pathId, $stepData);
        } else {
            return $this->addNewStep($pathId, $stepData);
        }
    }

    private function getPathId($processId, $pathType) {
        $stmt = $this->conn->prepare("SELECT path_id FROM process_paths WHERE process_id = ? AND path_type = ?");
        $stmt->bind_param("ss", $processId, $pathType);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Path not found');
        }
        
        return $result->fetch_assoc()['path_id'];
    }

    private function updateStep($pathId, $stepData) {
        $stmt = $this->conn->prepare("UPDATE process_steps SET title = ?, description = ?, updated_at = NOW() WHERE step_id = ? AND path_id = ?");
        $stmt->bind_param("ssii", $stepData['title'], $stepData['description'], $stepData['stepId'], $pathId);
        
        if (!$stmt->execute()) {
            throw new Exception('Error updating step: ' . $this->conn->error);
        }

        return [
            'success' => $stmt->affected_rows > 0,
            'message' => $stmt->affected_rows > 0 ? 'Step updated successfully' : 'No changes made',
            'stepId' => $stepData['stepId']
        ];
    }

    private function addNewStep($pathId, $stepData) {
        $order = $this->getNextStepOrder($pathId);
        
        $stmt = $this->conn->prepare("INSERT INTO process_steps (path_id, step_order, title, description, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("iiss", $pathId, $order, $stepData['title'], $stepData['description']);
        
        if (!$stmt->execute()) {
            throw new Exception('Error adding step: ' . $this->conn->error);
        }

        return [
            'success' => true,
            'message' => 'Step added successfully',
            'stepId' => $this->conn->insert_id,
            'stepOrder' => $order
        ];
    }

    private function getNextStepOrder($pathId) {
        $stmt = $this->conn->prepare("SELECT MAX(step_order) as max_order FROM process_steps WHERE path_id = ?");
        $stmt->bind_param("i", $pathId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return isset($row['max_order']) ? ($row['max_order'] + 1) : 1;
    }

    private function deleteProcessStep($stepId) {
        $this->conn->begin_transaction();
        
        try {
            $pathId = $this->getStepPathAndOrder($stepId)['path_id'];
            $stepOrder = $this->getStepPathAndOrder($stepId)['step_order'];
            
            $stmt = $this->conn->prepare("DELETE FROM process_steps WHERE step_id = ?");
            $stmt->bind_param("i", $stepId);
            if (!$stmt->execute()) {
                throw new Exception('Error deleting step');
            }
            
            $stmt = $this->conn->prepare("UPDATE process_steps SET step_order = step_order - 1 WHERE path_id = ? AND step_order > ?");
            $stmt->bind_param("ii", $pathId, $stepOrder);
            if (!$stmt->execute()) {
                throw new Exception('Error updating step orders');
            }
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Step deleted successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function getStepPathAndOrder($stepId) {
        $stmt = $this->conn->prepare("SELECT path_id, step_order FROM process_steps WHERE step_id = ?");
        $stmt->bind_param("i", $stepId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Step not found');
        }
        
        return $result->fetch_assoc();
    }

    private function reorderProcessSteps($processId, $pathType, $stepOrder) {
        $stepOrder = json_decode($stepOrder, true);
        if (!is_array($stepOrder) || empty($stepOrder)) {
            throw new Exception('Invalid step order data');
        }

        $pathId = $this->getPathId($processId, $pathType);
        $this->conn->begin_transaction();
        
        try {
            $stmt = $this->conn->prepare("UPDATE process_steps SET step_order = -step_order WHERE path_id = ?");
            $stmt->bind_param("i", $pathId);
            if (!$stmt->execute()) {
                throw new Exception('Error updating step order');
            }
            
            $stmt = $this->conn->prepare("UPDATE process_steps SET step_order = ?, updated_at = NOW() WHERE step_id = ? AND path_id = ?");
            
            foreach ($stepOrder as $order => $stepId) {
                $orderIndex = (int)$order + 1;
                $stmt->bind_param("iii", $orderIndex, $stepId, $pathId);
                if (!$stmt->execute()) {
                    throw new Exception('Error updating step order for step ID ' . $stepId);
                }
            }
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Step order updated successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
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
            // Initialize the path array if it doesn't exist
            if (!isset($processData[$pathRow['path_type']])) {
                $processData[$pathRow['path_type']] = [];
            }
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

        if (file_exists('admin_header.php')) {
            include 'admin_header.php';
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
            <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="css/admin_processs.css">
        </head>
        <body>
            <div class="admin-container">
                <h1 class="text-2xl font-bold mb-4">Process Flow Management</h1>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger mb-4">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <div class="admin-section">
                    <div class="alert alert-success" id="statusMessage" style="display: none;"></div>
                    
                    <div class="process-flow-admin">
                        <div class="process-tabs" id="processTabs">
                            <?php foreach ($processData as $processId => $process): ?>
                            <div class="process-tab <?php echo ($processId === array_key_first($processData)) ? 'active' : ''; ?>" 
                                 data-process="<?php echo htmlspecialchars($processId); ?>">
                                <?php echo htmlspecialchars($process['description']); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="process-content">
                            <div class="process-title-form">
                                <div class="form-row">
                                    <label for="processTitle">Process Flow Title:</label>
                                    <input type="text" id="processTitle" name="processTitle" class="focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <button class="save-title-btn" id="saveTitleBtn">
                                    <i class="fas fa-save mr-1"></i> Save Title
                                </button>
                            </div>
                            
                            <div class="process-announcement-form" id="announcementForm">
                                <div class="form-row">
                                    <label for="processAnnouncement">Announcement:</label>
                                    <textarea id="processAnnouncement" name="processAnnouncement" class="focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                </div>
                                <div class="form-buttons">
                                    <button class="cancel-btn cancel-announcement-btn" id="cancelAnnouncementBtn" type="button" style="display: none;">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </button>
                                    <button class="save-announcement-btn" id="saveAnnouncementBtn">
                                        <i class="fas fa-save mr-1"></i> Save Announcement
                                    </button>
                                </div>
                            </div>
                            
                            <div id="announcementContainer" style="display: none;"></div>
                            
                            <div class="path-tabs">
                                <div class="path-tab compliant active" data-path="compliant">
                                    <i class="fas fa-check-circle mr-1"></i> Compliant Path
                                </div>
                                <div class="path-tab non-compliant" data-path="nonCompliant">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Non-Compliant Path
                                </div>
                            </div>
                            
                            <div class="flow-steps-container">
                                <div id="flowStepsList">
                                    <!-- Flow steps will be loaded here -->
                                </div>
                                
                                <button class="add-step-btn" id="addStepBtn">
                                    <i class="fas fa-plus-circle mr-1"></i> Add New Step
                                </button>
                            </div>
                            
                            <div class="edit-form" id="editStepForm" style="display: none;">
                                <h3 id="editFormTitle" class="text-xl font-bold mb-4">Add New Step</h3>
                                <input type="hidden" id="editStepId">
                                
                                <div class="form-row">
                                    <label for="stepTitle">Step Title:</label>
                                    <input type="text" id="stepTitle" name="stepTitle" required 
                                          class="focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                
                                <div class="form-row">
                                    <label for="stepDescription">Step Description:</label>
                                    <textarea id="stepDescription" name="stepDescription" required
                                             class="focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                </div>
                                
                                <div class="form-buttons">
                                    <button class="cancel-btn" id="cancelEditBtn">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </button>
                                    <button class="save-btn" id="saveStepBtn">
                                        <i class="fas fa-save mr-1"></i> Save Step
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Dialog -->
            <div id="modalDialog" class="modal-container" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Confirm Action</h3>
                        <span class="close-btn" id="closeModalBtn">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p id="modalMessage">Are you sure you want to perform this action?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn cancel-btn" id="modalCancelBtn">Cancel</button>
                        <button class="modal-btn confirm-btn" id="modalConfirmBtn">Confirm</button>
                    </div>
                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
            <script>
                $(document).ready(function() {
                    // Store the process data
                    const processData = <?php echo json_encode($processData); ?>;
                    
                    // Current state variables
                    let currentProcessId = Object.keys(processData)[0];
                    let currentPathType = 'compliant';
                    let editingStepId = null;
                    let deleteStepId = null;

                    // Initialize the page
                    initializePage();

                    function initializePage() {
                        updateProcessTitle();
                        updateProcessAnnouncement();
                        loadProcessSteps();
                        setupSortable();
                    }

                    // Update announcement display function
                    function updateProcessAnnouncement() {
                        const process = processData[currentProcessId];
                        $('#processAnnouncement').val(process.announcement || '');
                        
                        // Display announcement above steps
                        const announcementContainer = $('#announcementContainer');
                        if (process.announcement && process.announcement.trim() !== '') {
                            announcementContainer.html(`
                                <div class="process-announcement">
                                    <div class="announcement-actions">
                                        <button class="announcement-edit-btn" id="editAnnouncementBtn">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="announcement-delete-btn" id="deleteAnnouncementBtn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                    <h3><i class="fas fa-bullhorn mr-1"></i> Announcement</h3>
                                    <p>${process.announcement}</p>
                                </div>
                            `).show();
                            $('#announcementForm').hide();
                            $('#cancelAnnouncementBtn').hide();
                        } else {
                            announcementContainer.hide();
                            $('#announcementForm').show();
                        }
                    }

                    // Edit announcement button
                    $(document).on('click', '#editAnnouncementBtn', function() {
                        const process = processData[currentProcessId];
                        $('#processAnnouncement').val(process.announcement || '');
                        $('#announcementContainer').hide();
                        $('#announcementForm').show();
                        $('#cancelAnnouncementBtn').show();
                        $('#processAnnouncement').focus();
                    });

                    // Cancel announcement edit
                    $('#cancelAnnouncementBtn').click(function() {
                        updateProcessAnnouncement();
                        $(this).hide();
                    });

                    // Delete announcement
                    $(document).on('click', '#deleteAnnouncementBtn', function() {
                        $('#modalMessage').text('Are you sure you want to delete this announcement?');
                        $('#modalDialog').show();
                        
                        // Set up confirm button for announcement deletion
                        $('#modalConfirmBtn').off('click').on('click', function() {
                            $.ajax({
                                url: window.location.href,
                                type: 'POST',
                                data: {
                                    action: 'saveTitle',
                                    processId: currentProcessId,
                                    title: processData[currentProcessId].title,
                                    announcement: ''
                                },
                                success: function(response) {
                                    const result = JSON.parse(response);
                                    showStatusMessage(result.message, result.success ? 'success' : 'error');
                                    
                                    if (result.success) {
                                        // Update local data
                                        processData[currentProcessId].announcement = '';
                                        updateProcessAnnouncement();
                                    }
                                    $('#modalDialog').hide();
                                },
                                error: function() {
                                    showStatusMessage('An error occurred while deleting the announcement', 'error');
                                    $('#modalDialog').hide();
                                }
                            });
                        });
                    });

                    // Save announcement
                    $('#saveAnnouncementBtn').click(function() {
                        const announcement = $('#processAnnouncement').val().trim();
                        
                        $.ajax({
                            url: window.location.href,
                            type: 'POST',
                            data: {
                                action: 'saveTitle',
                                processId: currentProcessId,
                                title: processData[currentProcessId].title,
                                announcement: announcement
                            },
                            success: function(response) {
                                const result = JSON.parse(response);
                                showStatusMessage(result.message, result.success ? 'success' : 'error');
                                
                                if (result.success) {
                                    // Update local data
                                    processData[currentProcessId].announcement = announcement;
                                    updateProcessAnnouncement();
                                    $('#cancelAnnouncementBtn').hide();
                                }
                            },
                            error: function() {
                                showStatusMessage('An error occurred while saving the announcement', 'error');
                            }
                        });
                    });

                    // Update the process title input field
                    function updateProcessTitle() {
                        const process = processData[currentProcessId];
                        $('#processTitle').val(process.title);
                    }

                    // Load process steps for the current process and path
                    function loadProcessSteps() {
                        const steps = processData[currentProcessId][currentPathType] || [];
                        const stepsContainer = $('#flowStepsList');
                        stepsContainer.empty();

                        if (steps.length === 0) {
                            stepsContainer.append('<div class="text-center py-4 text-gray-500">No steps added yet. Click "Add New Step" to begin.</div>');
                            return;
                        }

                        steps.forEach((step, index) => {
                            const stepItem = `
                                <div class="flow-step-item" data-step-id="${step.stepId}">
                                    <div class="drag-handle">
                                        <i class="fas fa-grip-lines"></i>
                                    </div>
                                    <div class="step-number">${index + 1}</div>
                                    <div class="step-title">${step.title}</div>
                                    <div class="step-description">${step.description}</div>
                                    <div class="step-actions">
                                        <button class="edit-btn" data-step-id="${step.stepId}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="delete-btn" data-step-id="${step.stepId}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            `;
                            stepsContainer.append(stepItem);
                        });
                    }

                    // Setup sortable functionality for drag and drop reordering
                    function setupSortable() {
                        const stepsList = document.getElementById('flowStepsList');
                        Sortable.create(stepsList, {
                            handle: '.drag-handle',
                            animation: 150,
                            onEnd: function(evt) {
                                // Get the new order of steps
                                const newOrder = [];
                                $('.flow-step-item').each(function() {
                                    newOrder.push($(this).data('step-id'));
                                });
                                
                                // Save the new order to the server
                                $.ajax({
                                    url: window.location.href,
                                    type: 'POST',
                                    data: {
                                        action: 'reorderSteps',
                                        processId: currentProcessId,
                                        pathType: currentPathType,
                                        stepOrder: JSON.stringify(newOrder)
                                    },
                                    success: function(response) {
                                        const result = JSON.parse(response);
                                        if (result.success) {
                                            showStatusMessage(result.message, 'success');
                                            // Update step numbers
                                            $('.flow-step-item .step-number').each(function(idx) {
                                                $(this).text(idx + 1);
                                            });
                                            
                                            // Update the local data structure
                                            const newSteps = [];
                                            newOrder.forEach(stepId => {
                                                const step = processData[currentProcessId][currentPathType].find(s => s.stepId == stepId);
                                                if (step) newSteps.push(step);
                                            });
                                            processData[currentProcessId][currentPathType] = newSteps;
                                        } else {
                                            showStatusMessage(result.message, 'error');
                                        }
                                    },
                                    error: function() {
                                        showStatusMessage('An error occurred while reordering steps', 'error');
                                    }
                                });
                            }
                        });
                    }

                    // Show status message
                    function showStatusMessage(message, type = 'success') {
                        const statusDiv = $('#statusMessage');
                        statusDiv.text(message);
                        statusDiv.removeClass('alert-success alert-danger');
                        statusDiv.addClass(type === 'success' ? 'alert-success' : 'alert-danger');
                        statusDiv.show();
                        setTimeout(() => statusDiv.fadeOut(500), 3000);
                    }

                    // Process tab click
                    $(document).on('click', '.process-tab', function() {
                        const processId = $(this).data('process');
                        currentProcessId = processId;
                        
                        // Update UI
                        $('.process-tab').removeClass('active');
                        $(this).addClass('active');
                        
                        updateProcessTitle();
                        updateProcessAnnouncement();
                        loadProcessSteps();
                    });

                    // Path tab click
                    $(document).on('click', '.path-tab', function() {
                        const pathType = $(this).data('path');
                        currentPathType = pathType;
                        
                        // Update UI
                        $('.path-tab').removeClass('active');
                        $(this).addClass('active');
                        
                        loadProcessSteps();
                        
                        // Update the form to create a new step
                        $('#editFormTitle').text('Add New Step');
                        $('#stepTitle').val('');
                        $('#stepDescription').val('');
                        editingStepId = null;
                    });

                    // Save process title
                    $('#saveTitleBtn').click(function() {
                        const title = $('#processTitle').val().trim();
                        if (!title) {
                            showStatusMessage('Process title cannot be empty', 'error');
                            return;
                        }
                        
                        $.ajax({
                            url: window.location.href,
                            type: 'POST',
                            data: {
                                action: 'saveTitle',
                                processId: currentProcessId,
                                title: title,
                                announcement: processData[currentProcessId].announcement || ''
                            },
                            success: function(response) {
                                const result = JSON.parse(response);
                                showStatusMessage(result.message, result.success ? 'success' : 'error');
                                
                                if (result.success) {
                                    // Update local data
                                    processData[currentProcessId].title = title;
                                    
                                    // Update tab name if it uses the title
                                    $(`.process-tab[data-process="${currentProcessId}"]`).text(title);
                                }
                            },
                            error: function() {
                                showStatusMessage('An error occurred while saving the title', 'error');
                            }
                        });
                    });

                    // Add new step button click
                    $('#addStepBtn').click(function() {
                        // Reset and show the form
                        $('#editFormTitle').text('Add New Step');
                        $('#stepTitle').val('');
                        $('#stepDescription').val('');
                        $('#editStepId').val('');
                        editingStepId = null;
                        $('#editStepForm').show();
                        $('#stepTitle').focus();
                    });

                    // Edit step button click
                    $(document).on('click', '.edit-btn', function() {
                        const stepId = $(this).data('step-id');
                        const step = processData[currentProcessId][currentPathType].find(s => s.stepId == stepId);
                        
                        if (step) {
                            $('#editFormTitle').text('Edit Step');
                            $('#stepTitle').val(step.title);
                            $('#stepDescription').val(step.description);
                            $('#editStepId').val(stepId);
                            editingStepId = stepId;
                            $('#editStepForm').show();
                            $('#stepTitle').focus();
                        }
                    });

                    // Cancel edit button click
                    $('#cancelEditBtn').click(function() {
                        $('#editStepForm').hide();
                        editingStepId = null;
                    });

                    // Save step
                    $('#saveStepBtn').click(function() {
                        const title = $('#stepTitle').val().trim();
                        const description = $('#stepDescription').val().trim();
                        
                        if (!title || !description) {
                            showStatusMessage('Step title and description are required', 'error');
                            return;
                        }
                        
                        const stepData = {
                            title: title,
                            description: description
                        };
                        
                        if (editingStepId) {
                            stepData.stepId = editingStepId;
                        }
                        
                        $.ajax({
                            url: window.location.href,
                            type: 'POST',
                            data: {
                                action: 'saveStep',
                                processId: currentProcessId,
                                pathType: currentPathType,
                                stepData: JSON.stringify(stepData)
                            },
                            success: function(response) {
                                const result = JSON.parse(response);
                                
                                if (result.success) {
                                    showStatusMessage(result.message, 'success');
                                    
                                    // Update local data
                                    if (editingStepId) {
                                        // Update existing step
                                        const stepIndex = processData[currentProcessId][currentPathType].findIndex(s => s.stepId == editingStepId);
                                        if (stepIndex !== -1) {
                                            processData[currentProcessId][currentPathType][stepIndex].title = title;
                                            processData[currentProcessId][currentPathType][stepIndex].description = description;
                                        }
                                    } else {
                                        // Add new step
                                        processData[currentProcessId][currentPathType].push({
                                            stepId: result.stepId,
                                            title: title,
                                            description: description
                                        });
                                    }
                                    
                                    // Reset form and update UI
                                    $('#editStepForm').hide();
                                    editingStepId = null;
                                    loadProcessSteps();
                                } else {
                                    showStatusMessage(result.message, 'error');
                                }
                            },
                            error: function() {
                                showStatusMessage('An error occurred while saving the step', 'error');
                            }
                        });
                    });

                    // Delete step button click
                    $(document).on('click', '.delete-btn', function() {
                        deleteStepId = $(this).data('step-id');
                        const step = processData[currentProcessId][currentPathType].find(s => s.stepId == deleteStepId);
                        
                        // Show confirmation modal
                        $('#modalMessage').text(`Are you sure you want to delete the step "${step.title}"?`);
                        $('#modalDialog').show();
                    });

                    // Close modal button
                    $('#closeModalBtn, #modalCancelBtn').click(function() {
                        $('#modalDialog').hide();
                        deleteStepId = null;
                    });

                    // Confirm delete button in modal
                    $('#modalConfirmBtn').click(function() {
                        if (deleteStepId) {
                            $.ajax({
                                url: window.location.href,
                                type: 'POST',
                                data: {
                                    action: 'deleteStep',
                                    stepId: deleteStepId
                                },
                                success: function(response) {
                                    const result = JSON.parse(response);
                                    
                                    if (result.success) {
                                        showStatusMessage(result.message, 'success');
                                        
                                        // Update local data
                                        processData[currentProcessId][currentPathType] = processData[currentProcessId][currentPathType].filter(
                                            s => s.stepId != deleteStepId
                                        );
                                        
                                        // Update UI
                                        loadProcessSteps();
                                    } else {
                                        showStatusMessage(result.message, 'error');
                                    }
                                    
                                    // Close modal
                                    $('#modalDialog').hide();
                                    deleteStepId = null;
                                },
                                error: function() {
                                    showStatusMessage('An error occurred while deleting the step', 'error');
                                    $('#modalDialog').hide();
                                    deleteStepId = null;
                                }
                            });
                        }
                    });
                });
            </script>
        </body>
        </html>
        <?php
    }
}

$processManager = new ProcessManager();
$processManager->handleRequest();
?>
