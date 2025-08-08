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

// Process form submissions for update/delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Get school id
        $school_id = mysqli_real_escape_string($conn, $_POST['school_id']);
        
        // Handle delete action
        if ($_POST['action'] === 'delete') {
            $delete_query = "DELETE FROM schools WHERE id = '$school_id'";
            
            if (mysqli_query($conn, $delete_query)) {
                $message = "School deleted successfully";
            } else {
                $error = "Error deleting school: " . mysqli_error($conn);
            }
        }
        
        // Handle update action
        if ($_POST['action'] === 'update') {
            $school_name = mysqli_real_escape_string($conn, $_POST['school_name']);
            $offers_elementary = isset($_POST['offers_elementary']) ? 1 : 0;
            $offers_jhs = isset($_POST['offers_jhs']) ? 1 : 0;
            $offers_shs = isset($_POST['offers_shs']) ? 1 : 0;
            $offers_sped = isset($_POST['offers_sped']) ? 1 : 0;
            
            $elementary_grades = mysqli_real_escape_string($conn, $_POST['elementary_grades']);
            $jhs_grades = mysqli_real_escape_string($conn, $_POST['jhs_grades']);
            $shs_grades = mysqli_real_escape_string($conn, $_POST['shs_grades']);
            
            $update_query = "UPDATE schools SET 
                            school_name = '$school_name',
                            offers_elementary = '$offers_elementary', 
                            offers_jhs = '$offers_jhs', 
                            offers_shs = '$offers_shs', 
                            offers_sped = '$offers_sped', 
                            elementary_grades = '$elementary_grades', 
                            jhs_grades = '$jhs_grades', 
                            shs_grades = '$shs_grades' 
                            WHERE id = '$school_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "School offerings updated successfully";
            } else {
                $error = "Error updating school offerings: " . mysqli_error($conn);
            }
        }
    }
}

// Query to get all schools with their offerings
$query = "SELECT 
            id, 
            school_name, 
            offers_elementary, 
            offers_jhs, 
            offers_shs, 
            offers_sped, 
            elementary_grades, 
            jhs_grades, 
            shs_grades 
          FROM schools 
          ORDER BY school_name";

$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
include 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_offers.css">
</head>
<body>
    <div class="container">
        <!-- Main Content Area -->
        <main>
            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Schools Table -->
            <div class="card">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-semibold">School Offerings</h3>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>School Name</th>
                                <th>Elem</th>
                                <th>JHS</th>
                                <th>SHS</th>
                                <th>SPED</th>
                                <th>Grade Levels</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mysqli_data_seek($result, 0); ?>
                            <?php while ($school = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td class="font-medium"><?php echo htmlspecialchars($school['school_name']); ?></td>
                                    <td>
                                        <?php if ($school['offers_elementary'] == 1): ?>
                                            <i class="fas fa-check text-green-500"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times text-gray-300"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($school['offers_jhs'] == 1): ?>
                                            <i class="fas fa-check text-green-500"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times text-gray-300"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($school['offers_shs'] == 1): ?>
                                            <i class="fas fa-check text-green-500"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times text-gray-300"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($school['offers_sped'] == 1): ?>
                                            <i class="fas fa-check text-green-500"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times text-gray-300"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-sm">
                                        <?php
                                        $grades = [];
                                        
                                        if ($school['offers_elementary'] == 1 && !empty($school['elementary_grades'])) {
                                            $grades[] = "<strong>Elem:</strong> " . htmlspecialchars($school['elementary_grades']);
                                        }
                                        
                                        if ($school['offers_jhs'] == 1 && !empty($school['jhs_grades'])) {
                                            $grades[] = "<strong>JHS:</strong> " . htmlspecialchars($school['jhs_grades']);
                                        }
                                        
                                        if ($school['offers_shs'] == 1 && !empty($school['shs_grades'])) {
                                            $grades[] = "<strong>SHS:</strong> " . htmlspecialchars($school['shs_grades']);
                                        }
                                        
                                        echo implode("<br>", $grades);
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-secondary" onclick="openEditModal(<?php echo $school['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit School Offerings</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editForm" method="post">
                    <input type="hidden" id="edit_school_id" name="school_id" value="">
                    <input type="hidden" id="edit_school_name" name="school_name" value="">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit_offers_elementary" name="offers_elementary">
                            <span>Offers Elementary</span>
                        </label>
                        <input type="text" id="edit_elementary_grades" name="elementary_grades" placeholder="Grade levels (e.g., Kinder, Grade 1-6)" class="form-control mt-2">
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit_offers_jhs" name="offers_jhs">
                            <span>Offers Junior High</span>
                        </label>
                        <input type="text" id="edit_jhs_grades" name="jhs_grades" placeholder="Grade levels (e.g., Grade 7-10)" class="form-control mt-2">
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit_offers_shs" name="offers_shs">
                            <span>Offers Senior High</span>
                        </label>
                        <input type="text" id="edit_shs_grades" name="shs_grades" placeholder="Grade levels (e.g., Grade 11-12)" class="form-control mt-2">
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit_offers_sped" name="offers_sped">
                            <span>Offers Special Education</span>
                        </label>
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to delete <strong id="deleteSchoolName"></strong>?</p>
                <p class="text-red-600 font-medium mb-4"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                
                <form id="deleteForm" method="post">
                    <input type="hidden" id="delete_school_id" name="school_id" value="">
                    <input type="hidden" name="action" value="delete">
                    
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // School data for editing
        const schoolData = {};
        
        <?php
        // Reset mysqli data pointer to beginning
        mysqli_data_seek($result, 0);
        
        // Store all school data for JavaScript
        while ($school = mysqli_fetch_assoc($result)) {
            echo "schoolData[" . $school['id'] . "] = {
                school_name: '" . addslashes($school['school_name']) . "',
                offers_elementary: " . $school['offers_elementary'] . ",
                offers_jhs: " . $school['offers_jhs'] . ",
                offers_shs: " . $school['offers_shs'] . ",
                offers_sped: " . $school['offers_sped'] . ",
                elementary_grades: '" . addslashes($school['elementary_grades']) . "',
                jhs_grades: '" . addslashes($school['jhs_grades']) . "',
                shs_grades: '" . addslashes($school['shs_grades']) . "'
            };\n";
        }
        ?>
        
        // Get modal elements
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');
        
        // Open edit modal
        function openEditModal(schoolId) {
            // Get the school data
            const school = schoolData[schoolId];
            
            // Set values in the form
            document.getElementById('edit_school_id').value = schoolId;
            document.getElementById('edit_school_name').value = school.school_name;
            document.getElementById('edit_offers_elementary').checked = school.offers_elementary == 1;
            document.getElementById('edit_offers_jhs').checked = school.offers_jhs == 1;
            document.getElementById('edit_offers_shs').checked = school.offers_shs == 1;
            document.getElementById('edit_offers_sped').checked = school.offers_sped == 1;
            document.getElementById('edit_elementary_grades').value = school.elementary_grades;
            document.getElementById('edit_jhs_grades').value = school.jhs_grades;
            document.getElementById('edit_shs_grades').value = school.shs_grades;
            
            // Show the modal
            editModal.style.display = 'block';
        }
        
        // Close edit modal
        function closeEditModal() {
            editModal.style.display = 'none';
        }
        
        // Open delete modal
        function openDeleteModal(schoolId, schoolName) {
            document.getElementById('delete_school_id').value = schoolId;
            document.getElementById('deleteSchoolName').textContent = schoolName;
            deleteModal.style.display = 'block';
        }
        
        // Close delete modal
        function closeDeleteModal() {
            deleteModal.style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }
        
        // Hide alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            var alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.style.display = 'none';
                        }, 500);
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>