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

// Initialize variables for messages
$message = $error = "";

// Initialize variables for edit mode
$editMode = false;
$editData = null;

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new school
    if (isset($_POST['action']) && $_POST['action'] == 'add_school') {
        $school_id = $_POST['school_id'];
        $school_name = $_POST['school_name'];
        $address = $_POST['address'];
        $barangay = $_POST['barangay'];
        $city = $_POST['city'];
        $province = $_POST['province'];
        $telephone_number = $_POST['telephone_number'];
        $mobile_number = $_POST['mobile_number'];
        $email = $_POST['email'];
        $offers_elementary = isset($_POST['offers_elementary']) ? 1 : 0;
        $offers_jhs = isset($_POST['offers_jhs']) ? 1 : 0;
        $offers_shs = isset($_POST['offers_shs']) ? 1 : 0;
        $offers_sped = isset($_POST['offers_sped']) ? 1 : 0;
        $status = $_POST['status'];
        $recognize = ($status == 'recognized') ? 1 : 0;
        $renewal = ($status == 'renewal') ? 1 : 0;
        
        $sql = "INSERT INTO schools (school_id, school_name, address, barangay, city, province, telephone_number, mobile_number, email, offers_elementary, offers_jhs, offers_shs, offers_sped, recognize, renewal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssiiiiii", $school_id, $school_name, $address, $barangay, $city, $province, $telephone_number, $mobile_number, $email, $offers_elementary, $offers_jhs, $offers_shs, $offers_sped, $recognize, $renewal);
        
        if ($stmt->execute()) {
            $message = "New school added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // Edit school
    elseif (isset($_POST['action']) && $_POST['action'] == 'edit_school') {
        $id = $_POST['id'];
        $school_id = $_POST['school_id'];
        $school_name = $_POST['school_name'];
        $address = $_POST['address'];
        $barangay = $_POST['barangay'];
        $city = $_POST['city'];
        $province = $_POST['province'];
        $telephone_number = $_POST['telephone_number'];
        $mobile_number = $_POST['mobile_number'];
        $email = $_POST['email'];
        $offers_elementary = isset($_POST['offers_elementary']) ? 1 : 0;
        $offers_jhs = isset($_POST['offers_jhs']) ? 1 : 0;
        $offers_shs = isset($_POST['offers_shs']) ? 1 : 0;
        $offers_sped = isset($_POST['offers_sped']) ? 1 : 0;
        $status = $_POST['status'];
        $recognize = ($status == 'recognized') ? 1 : 0;
        $renewal = ($status == 'renewal') ? 1 : 0;
        $sql = "UPDATE schools SET school_id=?, school_name=?, address=?, barangay=?, city=?, province=?, telephone_number=?, mobile_number=?, email=?, offers_elementary=?, offers_jhs=?, offers_shs=?, offers_sped=?, recognize=?, renewal=? WHERE id=?";
        
        $stmt = $conn->prepare($sql);
       $stmt->bind_param("sssssssssiiiiiii", $school_id, $school_name, $address, $barangay, $city, $province, $telephone_number, $mobile_number, $email, $offers_elementary, $offers_jhs, $offers_shs, $offers_sped, $recognize, $renewal, $id);
        
        if ($stmt->execute()) {
            $message = "School updated successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // Delete school
    elseif (isset($_POST['action']) && $_POST['action'] == 'delete_school') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM schools WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "School deleted successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // Bulk delete schools
    elseif (isset($_POST['action']) && $_POST['action'] == 'bulk_delete' && isset($_POST['selected_schools'])) {
        $selectedSchools = $_POST['selected_schools'];
        $deleted = 0;
        
        foreach ($selectedSchools as $id) {
            $sql = "DELETE FROM schools WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $deleted++;
            }
            
            $stmt->close();
        }
        
        if ($deleted > 0) {
            $message = "$deleted school(s) deleted successfully!";
        } else {
            $error = "Error deleting schools.";
        }
    }
}

// Check if edit_id is in GET parameters
if (isset($_GET['edit_id'])) {
    $editMode = true;
    $id = $_GET['edit_id'];
    $sql = "SELECT * FROM schools WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
    $stmt->close();
}

// Set letter filter (only used when not searching)
$letterFilter = isset($_GET['letter']) && $_GET['letter'] !== 'all' ? $_GET['letter'] : '';

// Get search query
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare SQL query based on filter
$sql = "SELECT * FROM schools WHERE 1=1";

// Add search filter if set (search overrides letter filter)
if (!empty($searchQuery)) {
    $sql .= " AND (school_name LIKE '%" . $conn->real_escape_string($searchQuery) . "%' 
               OR address LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
               OR barangay LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
               OR city LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
               OR province LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
               OR school_id LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
               OR telephone_number LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
               OR mobile_number LIKE '%" . $conn->real_escape_string($searchQuery) . "%'
               OR email LIKE '%" . $conn->real_escape_string($searchQuery) . "%')";
} 
// Only apply letter filter if not searching and a specific letter is selected
elseif (!empty($letterFilter)) {
    $sql .= " AND school_name LIKE '" . $conn->real_escape_string($letterFilter) . "%'";
}

// Add proper ORDER BY clause (REMOVED the duplicate AND condition)
$sql .= " ORDER BY school_name";

// Define alphabet query BEFORE using it
$alphabetQuery = "SELECT DISTINCT UPPER(LEFT(school_name, 1)) as first_letter FROM schools ORDER BY first_letter";

// Execute query
$schoolsResult = $conn->query($sql);
if (!$schoolsResult) {
    die("Query failed: " . $conn->error);
}

// Get all available first letters for alphabet index
$alphabetResult = $conn->query($alphabetQuery);
$availableLetters = [];
if ($alphabetResult && $alphabetResult->num_rows > 0) {
    while ($row = $alphabetResult->fetch_assoc()) {
        $availableLetters[] = $row['first_letter'];
    }
}

// Set page title based on filter
$pageTitle = "School Management";
if (!empty($searchQuery)) {
    $pageTitle .= " - Search Results";
} elseif (!empty($letterFilter)) {
    $pageTitle .= " - Letter " . $letterFilter;
}

if (file_exists('admin_header.php')) {
    include 'admin_header.php';
}
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
            <link rel="stylesheet" href="css/admin_schools.css">
</head>
    
<body>
    <div class="container">
        <!-- Display messages -->
        <?php if (!empty($message)): ?>
            <div class="alert success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- School Management Header -->
        <div class="action-buttons-container">
            <div class="action-buttons">
                <button onclick="openModal('addSchoolModal')" class="button button-primary">
                    <i class="fas fa-plus button-icon"></i> Add New School
                </button>
                <button id="deleteSelectedSchools" onclick="confirmBulkDelete()" class="button button-danger" disabled>
                    <i class="fas fa-trash button-icon"></i> Delete Selected
                </button>
            </div>
            
            <!-- Search Bar -->
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search schools...">
                    <button type="submit" class="button button-primary">
                        <i class="fas fa-search button-icon"></i> Search
                    </button>
                </form>
            </div>
        </div>

        <?php if (!empty($searchQuery)): ?>
            <div class="clear-search-container">
                <a href="admin_school.php" class="button button-secondary">
                    <i class="fas fa-times button-icon"></i> Clear Search
                </a>
            </div>
        <?php endif; ?>

        <!-- Schools Table -->
        <div class="table-container">
            <form id="schoolsForm" method="POST" action="">
                <input type="hidden" name="action" value="bulk_delete">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllSchools" onchange="toggleSelectAll(this)"></th>
                            <th>School ID</th>
                            <th>School Name</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($schoolsResult && $schoolsResult->num_rows > 0): ?>
                            <?php $currentLetter = ''; ?>
                            <?php while ($row = $schoolsResult->fetch_assoc()): ?>
                                <?php $schoolFirstLetter = strtoupper(substr($row['school_name'], 0, 1)); ?>
                                <?php if ($schoolFirstLetter !== $currentLetter && empty($searchQuery)): ?>
                                    <?php $currentLetter = $schoolFirstLetter; ?>
                                    <tr class="letter-row">
                                        <td colspan="8"><?php echo $currentLetter; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_schools[]" value="<?php echo $row['id']; ?>" class="school-checkbox" onchange="updateButtons()"></td>
                                    <td><?php echo htmlspecialchars($row['school_id'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['school_name']); ?></td>
                                    <td>
                                        <?php 
                                            $addressParts = [];
                                            if (!empty($row['address'])) $addressParts[] = $row['address'];
                                            if (!empty($row['barangay'])) $addressParts[] = $row['barangay'];
                                            if (!empty($row['city'])) $addressParts[] = $row['city'];
                                            if (!empty($row['province'])) $addressParts[] = $row['province'];
                                            echo htmlspecialchars(implode(', ', $addressParts));
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['recognize']): ?>
                                            <span class="status-recognized">Recognized</span>
                                        <?php elseif ($row['renewal']): ?>
                                            <span class="status-renewal">Renewal</span>
                                        <?php else: ?>
                                            <span class="status-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                    <td class="phone-numbers">
                                        <?php 
                                            if (!empty($row['telephone_number'])) {
                                                $length = strlen($row['telephone_number']) > 11 ? 'long' : 'short';
                                                echo '<div class="phone-number" data-length="'.$length.'"><i class="fas fa-phone"></i> ' . htmlspecialchars($row['telephone_number']) . '</div>';
                                            }
                                            if (!empty($row['mobile_number'])) {
                                                $length = strlen($row['mobile_number']) > 11 ? 'long' : 'short';
                                                echo '<div class="phone-number" data-length="'.$length.'"><i class="fas fa-mobile-alt"></i> ' . htmlspecialchars($row['mobile_number']) . '</div>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            // Build edit link with current filter parameters
                                            $editLink = "admin_school.php?edit_id=" . $row['id'];
                                            if (!empty($searchQuery)) {
                                                $editLink .= "&search=" . urlencode($searchQuery);
                                            } elseif (!empty($letterFilter)) {
                                                $editLink .= "&letter=" . urlencode($letterFilter);
                                            }
                                        ?>
                                        <a href="<?php echo $editLink; ?>" title="Edit" class="action-link">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">
                                    <?php if (!empty($searchQuery)): ?>
                                        No schools found matching your search.
                                    <?php else: ?>
                                        No schools found. Add a new school to get started.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>

        <!-- Alphabet Index -->
        <?php if (empty($searchQuery)): ?>
        <div class="alphabet-index-container">
            <div class="alphabet-index">
                <span class="alphabet-label"></span>
                <a href="admin_school.php" class="<?php echo empty($letterFilter) ? 'active' : ''; ?>">All</a>
                <?php foreach ($availableLetters as $letter): ?>
                    <?php $isActive = ($letter === $letterFilter); ?>
                    <a href="admin_school.php?letter=<?php echo $letter; ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
                        <?php echo $letter; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <!-- Add School Modal -->
    <div id="addSchoolModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New School</h3>
                <span class="close" onclick="closeModal('addSchoolModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_school">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="school_id">School ID</label>
                            <input type="text" id="school_id" name="school_id" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">School Name</label>
                            <input type="text" id="name" name="school_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="barangay">Barangay</label>
                            <input type="text" id="barangay" name="barangay" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="province">Province</label>
                            <input type="text" id="province" name="province" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone_number">Telephone Number</label>
                            <input type="text" id="telephone_number" name="telephone_number" class="form-control" maxlength="50">
                        </div>
                        
                        <div class="form-group">
                            <label for="mobile_number">Mobile Number</label>
                            <input type="text" id="mobile_number" name="mobile_number" class="form-control" maxlength="11">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>School Levels</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="offers_elementary" name="offers_elementary" value="1">
                                <label for="offers_elementary">Elementary</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="offers_jhs" name="offers_jhs" value="1">
                                <label for="offers_jhs">Junior High School</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="offers_shs" name="offers_shs" value="1">
                                <label for="offers_shs">Senior High School</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="offers_sped" name="offers_sped" value="1">
                                <label for="offers_sped">SPED</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                    <label>Status</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="status_recognized" name="status" value="recognized" checked>
                            <label for="status_recognized">Recognized by DepEd</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="status_renewal" name="status" value="renewal">
                            <label for="status_renewal">Needs Renewal</label>
                        </div>
                    </div>
                </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary" onclick="closeModal('addSchoolModal')">Cancel</button>
                        <button type="submit" class="button button-primary">Save School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit School Modal -->
    <div id="editSchoolModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit School</h3>
                <span class="close" onclick="closeModal('editSchoolModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="edit_school">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <!-- Add hidden fields to preserve filter state -->
                    <?php if (!empty($searchQuery)): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <?php elseif (!empty($letterFilter)): ?>
                        <input type="hidden" name="letter" value="<?php echo htmlspecialchars($letterFilter); ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_school_id">School ID</label>
                            <input type="text" id="edit_school_id" name="school_id" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_school_name">School Name</label>
                            <input type="text" id="edit_school_name" name="school_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_address">Address</label>
                            <input type="text" id="edit_address" name="address" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_barangay">Barangay</label>
                            <input type="text" id="edit_barangay" name="barangay" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_city">City</label>
                            <input type="text" id="edit_city" name="city" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_province">Province</label>
                            <input type="text" id="edit_province" name="province" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_telephone_number">Telephone Number</label>
                            <input type="text" id="edit_telephone_number" name="telephone_number" class="form-control" maxlength="50">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_mobile_number">Mobile Number</label>
                            <input type="text" id="edit_mobile_number" name="mobile_number" class="form-control" maxlength="11">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>School Levels</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="edit_offers_elementary" name="offers_elementary" value="1">
                                <label for="edit_offers_elementary">Elementary</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="edit_offers_jhs" name="offers_jhs" value="1">
                                <label for="edit_offers_jhs">Junior High School</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="edit_offers_shs" name="offers_shs" value="1">
                                <label for="edit_offers_shs">Senior High School</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="edit_offers_sped" name="offers_sped" value="1">
                                <label for="edit_offers_sped">SPED</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" id="edit_status_recognized" name="status" value="recognized" <?php echo ($editData['recognize'] || (!$editData['recognize'] && !$editData['renewal'])) ? 'checked' : ''; ?>>
                                <label for="edit_status_recognized">Recognized by DepEd</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" id="edit_status_renewal" name="status" value="renewal" <?php echo $editData['renewal'] ? 'checked' : ''; ?>>
                                <label for="edit_status_renewal">Needs Renewal</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="button button-secondary" onclick="closeModal('editSchoolModal')">Cancel</button>
                        <button type="submit" class="button button-primary">Update School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close" onclick="closeModal('deleteConfirmModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage">Are you sure you want to delete the selected schools?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-secondary" onclick="closeModal('deleteConfirmModal')">Cancel</button>
                <button type="button" class="button button-danger" onclick="deleteSelected()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // JavaScript functions for handling selections and actions
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.school-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateButtons();
        }

        // Update delete button based on selection count
        function updateButtons() {
            const checked = document.querySelectorAll('.school-checkbox:checked');
            const deleteBtn = document.getElementById('deleteSelectedSchools');
            
            deleteBtn.disabled = checked.length === 0;
        }

        // Function to open delete confirmation modal
        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.school-checkbox:checked');
            if (checked.length > 0) {
                const message = `Are you sure you want to delete ${checked.length} selected school(s)?`;
                document.getElementById('deleteConfirmMessage').innerText = message;
                openModal('deleteConfirmModal');
            }
        }

        // Perform delete operation
        function deleteSelected() {
            document.getElementById('schoolsForm').submit();
            closeModal('deleteConfirmModal');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
        }
        
        // Preserve search parameters when submitting forms
        document.addEventListener('DOMContentLoaded', function() {
            const currentSearch = '<?php echo htmlspecialchars($searchQuery); ?>';
            
            // If in edit mode, open the edit modal with the data
            // If in edit mode, open the edit modal with the data
            <?php if ($editMode && $editData): ?>
                document.getElementById('edit_id').value = '<?php echo $editData['id']; ?>';
                document.getElementById('edit_school_id').value = '<?php echo htmlspecialchars($editData['school_id'] ?? ''); ?>';
                document.getElementById('edit_school_name').value = '<?php echo htmlspecialchars($editData['school_name']); ?>';
                document.getElementById('edit_address').value = '<?php echo htmlspecialchars($editData['address']); ?>';
                document.getElementById('edit_barangay').value = '<?php echo htmlspecialchars($editData['barangay']); ?>';
                document.getElementById('edit_city').value = '<?php echo htmlspecialchars($editData['city']); ?>';
                document.getElementById('edit_province').value = '<?php echo htmlspecialchars($editData['province']); ?>';
                document.getElementById('edit_telephone_number').value = '<?php echo htmlspecialchars($editData['telephone_number'] ?? ''); ?>';
                document.getElementById('edit_mobile_number').value = '<?php echo htmlspecialchars($editData['mobile_number'] ?? ''); ?>';
                document.getElementById('edit_email').value = '<?php echo htmlspecialchars($editData['email'] ?? ''); ?>';
                
                document.getElementById('edit_offers_elementary').checked = <?php echo $editData['offers_elementary'] ? 'true' : 'false'; ?>;
                document.getElementById('edit_offers_jhs').checked = <?php echo $editData['offers_jhs'] ? 'true' : 'false'; ?>;
                document.getElementById('edit_offers_shs').checked = <?php echo $editData['offers_shs'] ? 'true' : 'false'; ?>;
                document.getElementById('edit_offers_sped').checked = <?php echo $editData['offers_sped'] ? 'true' : 'false'; ?>;
                            
                <?php echo $editData['recognize'] ? 'true' : 'false'; ?>;
                <?php echo $editData['renewal'] ? 'true' : 'false'; ?>;
                
                openModal('editSchoolModal');
            <?php endif; ?>
        });
    </script>
</body>
</html>