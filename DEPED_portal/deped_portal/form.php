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

// Get all form files from the database
function getFormFiles($conn) {
    $forms = [];
    
    // Define the form categories
    $formCategories = [
        'Renewal/Recognition Application',
        'New Government Permit Application',
        'Tuition Fee Increase Application',
        'Special Order Requirements',
        'Summer Classes Application'
    ];

    foreach ($formCategories as $category) {
        $stmt = $conn->prepare("SELECT filename, filepath, filesize, upload_date FROM `documents` WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categoryFiles = [];
        while ($row = $result->fetch_assoc()) {
            $fileExtension = strtolower(pathinfo($row['filename'], PATHINFO_EXTENSION));
            if (in_array($fileExtension, ['pdf', 'doc', 'docx'])) {
                $categoryFiles[] = [
                    'filename' => $row['filename'],
                    'extension' => $fileExtension,
                    'path' => $row['filepath'],
                    'size' => $row['filesize'],
                    'modified' => date("F d Y H:i:s", strtotime($row['upload_date']))
                ];
            }
        }
        
        if (!empty($categoryFiles)) {
            $forms[$category] = $categoryFiles;
        }
        
        $stmt->close();
    }

    return $forms;
}

// Get all available forms from database
$allForms = getFormFiles($conn);

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
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
    <link rel="stylesheet" href="css/form.css">  
    <title>DepEd School Application Forms Portal</title>
    <!-- PDF.js for client-side PDF rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <script>
        // Set PDF.js worker path immediately
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';
    </script>
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 1200px;
            height: 80vh;
            display: flex;
            flex-direction: column;
        }
        
        .modal-header {
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-body {
            flex: 1;
            overflow: auto;
            margin-top: 16px;
        }
        
        .close-btn {
            color: #6b7280;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-btn:hover {
            color: #374151;
        }
        
        /* Document preview container */
        #documentPreview {
            width: 100%;
            height: 100%;
            position: relative;
            background-color: #f9fafb;
            border-radius: 4px;
        }
        
        /* Zoom controls */
        .zoom-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 8px;
            padding: 8px;
            z-index: 10;
        }
        
        .zoom-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #f3f4f6;
            cursor: pointer;
        }
        
        .zoom-btn:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
<?php include 'header.php'; ?>

<main class="container mx-auto px-4 py-8">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        // Define the form cards with their information
        $formCards = [
            'Renewal/Recognition Application' => [
                'title' => 'Renewal/Recognition Application',
                'description' => 'Form for schools applying for renewal of government permit or recognition, with all required documentation.',
                'icon' => 'fa-file-contract',
                'color' => 'bg-blue-100 text-blue-800'
            ],
            'New Government Permit Application' => [
                'title' => 'New Government Permit Application', 
                'description' => 'Form for private schools applying for a new government permit, includes all required documentation and processing steps.',
                'icon' => 'fa-file-signature',
                'color' => 'bg-green-100 text-green-800'
            ],
            'Tuition Fee Increase Application' => [
                'title' => 'Tuition Fee Increase Application',
                'description' => 'Processing sheet for schools requesting tuition fee increases, pursuant to DECS Orders.',
                'icon' => 'fa-money-bill-wave',
                'color' => 'bg-purple-100 text-purple-800'
            ],
            'Special Order Requirements' => [
                'title' => 'Special Order Requirements',
                'description' => 'Requirements checklist for Special Orders as referenced in RM 155, S. 2019.',
                'icon' => 'fa-clipboard-list',
                'color' => 'bg-amber-100 text-amber-800'
            ],
            'Summer Classes Application' => [
                'title' => 'Summer Classes Application',
                'description' => 'Requirements for private schools\' application for end-of-school-year classes.',
                'icon' => 'fa-sun',
                'color' => 'bg-orange-100 text-orange-800'
            ]
        ];
        
        // Loop through form cards and display them
        foreach ($formCards as $category => $cardInfo) {
            $files = isset($allForms[$category]) ? $allForms[$category] : [];
            $hasFile = !empty($files);
            ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                <div class="p-6 flex flex-col h-full">
                    <div class="flex items-start mb-4">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-lg <?php echo $cardInfo['color']; ?> mr-4 flex-shrink-0">
                            <i class="fas <?php echo $cardInfo['icon']; ?> text-lg"></i>
                        </span>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 leading-tight"><?php echo $cardInfo['title']; ?></h2>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-5 flex-grow">
                        <?php echo $cardInfo['description']; ?>
                    </p>
                    
                    <?php if ($hasFile): ?>
                    <div class="text-sm text-gray-500 mb-5">
                        <div class="flex items-center bg-gray-50 rounded-lg p-3">
                            <i class="fas <?php 
                                $fileExt = strtolower(pathinfo($files[0]['filename'], PATHINFO_EXTENSION));
                                if ($fileExt == 'pdf') echo 'fa-file-pdf text-red-500';
                                else if (in_array($fileExt, ['doc', 'docx'])) echo 'fa-file-word text-blue-500';
                                else echo 'fa-file text-gray-500';
                            ?> mr-2"></i>
                            <div class="truncate">
                                <div class="font-medium truncate"><?php echo $files[0]['filename']; ?></div>
                                <div class="text-xs text-gray-400"><?php echo formatFileSize($files[0]['size']); ?> • <?php echo date("M d, Y", strtotime($files[0]['modified'])); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex space-x-3 mt-auto">
                        <?php if ($hasFile): ?>
                            <button class="flex-1 bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center"
                                onclick="previewDocument('<?php echo $files[0]['filename']; ?>', '<?php echo $category; ?>', '<?php echo $files[0]['extension']; ?>')">
                                <i class="fas fa-eye mr-2"></i> Preview
                            </button>
                            <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center"
                                onclick="downloadDocument('<?php echo $files[0]['filename']; ?>', '<?php echo $category; ?>')">
                                <i class="fas fa-download mr-2"></i> Download
                            </button>
                        <?php else: ?>
                            <button class="flex-1 bg-gray-100 text-gray-400 px-4 py-2 rounded-lg cursor-not-allowed flex items-center justify-center" disabled>
                                <i class="fas fa-eye mr-2"></i> Unavailable
                            </button>
                            <button class="flex-1 bg-gray-100 text-gray-400 px-4 py-2 rounded-lg cursor-not-allowed flex items-center justify-center" disabled>
                                <i class="fas fa-download mr-2"></i> Unavailable
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</main>

<!-- Modal for document preview -->
<div id="previewModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <h2 class="text-xl font-bold" id="modalTitle">Document Preview</h2>
            <div class="document-info text-sm text-gray-600 mt-2">
                <span id="fileName" class="font-medium"></span> • 
                <span id="fileSize"></span> • 
                <span id="fileModified"></span>
                <span id="fileFormat" class="ml-2 px-2 py-1 rounded-full text-xs font-medium"></span>
            </div>
        </div>
        <div class="modal-body">
            <div id="documentPreview">
                <!-- Preview content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
const modal = document.getElementById("previewModal");
let currentScale = 1.0;
let currentFilename = '';
let currentCategory = '';
let currentFileExtension = '';

function previewDocument(filename, category, extension) {
    currentFilename = filename;
    currentCategory = category;
    currentFileExtension = extension;
    currentScale = 1.0;
    
    // Show basic info immediately
    document.getElementById("modalTitle").textContent = category;
    document.getElementById("fileName").textContent = filename;
    
    const previewContainer = document.getElementById("documentPreview");
    previewContainer.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full">
            <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500 mb-4"></div>
            <h3 class="text-lg font-medium">Loading ${filename}...</h3>
        </div>
    `;
    
    modal.style.display = "block";
    document.body.classList.add('overflow-hidden');
    
    // Start loading the document immediately
    if (extension === 'pdf') {
        loadPdfDocument(filename, category);
    } else if (extension === 'docx' || extension === 'doc') {
        loadDocxDocument(filename, category);
    } else {
        showUnsupportedFormat();
    }
    
    // Get file info in parallel (won't block the preview)
    fetch(`document_handler.php?action=info&file=${encodeURIComponent(filename)}&category=${encodeURIComponent(category)}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            document.getElementById("fileSize").textContent = formatFileSize(data.size);
            document.getElementById("fileModified").textContent = `Modified: ${data.modified}`;
            
            // Update file format badge
            const formatBadge = document.getElementById("fileFormat");
            if (extension === 'pdf') {
                formatBadge.textContent = 'PDF';
                formatBadge.className = 'ml-2 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800';
            } else if (extension === 'docx' || extension === 'doc') {
                formatBadge.textContent = 'Word';
                formatBadge.className = 'ml-2 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
            }
        })
        .catch(error => {
            console.error('Error fetching file info:', error);
        });
}

function loadPdfDocument(filename, category) {
    const previewContainer = document.getElementById("documentPreview");
    
    // Create iframe for PDF preview
    const iframe = document.createElement('iframe');
    iframe.id = 'documentViewer';
    iframe.src = `document_handler.php?action=preview&file=${encodeURIComponent(filename)}&category=${encodeURIComponent(category)}`;
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.border = 'none';
    
    // Handle iframe loading errors
    iframe.onerror = function() {
        showError('Failed to load document preview');
    };
    
    iframe.onload = function() {
        // Check if the iframe loaded properly
        try {
            if (iframe.contentDocument && 
                iframe.contentDocument.body && 
                iframe.contentDocument.body.textContent &&
                (iframe.contentDocument.body.textContent.includes('Error') || 
                iframe.contentDocument.body.textContent.includes('not found'))) {
                showError(iframe.contentDocument.body.textContent);
            }
        } catch (e) {
            // Cross-origin error is expected for PDFs
            console.log('PDF loaded in iframe');
        }
    };
    
    previewContainer.innerHTML = '';
    previewContainer.appendChild(iframe);
    
    // Add controls for PDF viewer
    const controls = document.createElement('div');
    controls.className = 'zoom-controls';
    controls.innerHTML = `
        <button onclick="zoomOut()" class="zoom-btn" title="Zoom Out">
            <i class="fas fa-search-minus"></i>
        </button>
        <button onclick="resetZoom()" class="zoom-btn" title="Reset Zoom">
            ${Math.round(currentScale * 100)}%
        </button>
        <button onclick="zoomIn()" class="zoom-btn" title="Zoom In">
            <i class="fas fa-search-plus"></i>
        </button>
    `;
    previewContainer.appendChild(controls);
}

function loadDocxDocument(filename, category) {
    const previewContainer = document.getElementById("documentPreview");
    
    // Create iframe for DOCX preview
    const iframe = document.createElement('iframe');
    iframe.id = 'documentViewer';
    iframe.src = `document_handler.php?action=preview&file=${encodeURIComponent(filename)}&category=${encodeURIComponent(category)}`;
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.border = 'none';
    
    iframe.onerror = function() {
        showError('Failed to load document preview');
    };
    
    iframe.onload = function() {
        try {
            if (iframe.contentDocument && 
                iframe.contentDocument.body && 
                iframe.contentDocument.body.textContent &&
                iframe.contentDocument.body.textContent.includes('Error')) {
                showError(iframe.contentDocument.body.textContent);
            }
        } catch (e) {
            console.log('DOCX preview loaded in iframe');
        }
    };
    
    previewContainer.innerHTML = '';
    previewContainer.appendChild(iframe);
}

function showUnsupportedFormat() {
    const previewContainer = document.getElementById("documentPreview");
    previewContainer.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium mb-2">Unsupported File Format</h3>
            <p class="text-gray-600">This file format cannot be previewed. Please download the file to view it.</p>
            <button onclick="downloadDocument(currentFilename, currentCategory)" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-download mr-2"></i> Download File
            </button>
        </div>
    `;
}

function showError(message = '') {
    const previewContainer = document.getElementById("documentPreview");
    previewContainer.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full">
            <i class="fas fa-times-circle text-red-500 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium mb-2">Error Loading Document</h3>
            <p class="text-gray-600">There was an error loading the document. ${message}</p>
            <button onclick="downloadDocument(currentFilename, currentCategory)" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-download mr-2"></i> Download Original File
            </button>
        </div>
    `;
}

function downloadDocument(filename, category) {
    // Create a hidden iframe for download
    const downloadFrame = document.createElement('iframe');
    downloadFrame.style.display = 'none';
    downloadFrame.src = `document_handler.php?action=download&file=${encodeURIComponent(filename)}&category=${encodeURIComponent(category)}`;
    document.body.appendChild(downloadFrame);
    
    // Remove the iframe after a moment
    setTimeout(() => {
        document.body.removeChild(downloadFrame);
    }, 2000);
}

function closeModal() {
    modal.style.display = "none";
    document.body.classList.remove('overflow-hidden');
    
    // Clear the iframe to stop any downloads or processes
    const iframe = document.getElementById('documentViewer');
    if (iframe) {
        iframe.src = 'about:blank';
    }
}

function zoomIn() {
    if (currentScale < 2.0) {
        currentScale += 0.25;
        updateZoom();
    }
}

function zoomOut() {
    if (currentScale > 0.5) {
        currentScale -= 0.25;
        updateZoom();
    }
}

function resetZoom() {
    currentScale = 1.0;
    updateZoom();
}

function updateZoom() {
    const iframe = document.getElementById('documentViewer');
    const zoomDisplay = document.querySelector('.zoom-controls button:nth-child(2)');
    if (iframe) {
        iframe.style.transform = `scale(${currentScale})`;
        iframe.style.transformOrigin = '0 0';
        if (zoomDisplay) {
            zoomDisplay.innerHTML = `${Math.round(currentScale * 100)}%`;
        }
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Close modal if clicked outside
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

// Close with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeModal();
    }
});
</script>
</body>
</html>