<?php
// Admin includes for header and sidebar
// This file will be included in all admin pages

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, redirect to login page if not
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
   exit();
}

// Function to check if a page is active
function isActive($pageName) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return ($currentPage == $pageName) ? 'active-nav' : '';
}

// Function to check if a page belongs to a specific group (for dropdown)
function isInGroup($pageNames) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return in_array($currentPage, $pageNames) ? 'active-nav' : '';
}

// Get the username from session and clean it for display
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
// Clean username - remove underscores, special characters, etc.
$displayUsername = ucfirst(preg_replace('/[_\W]+/', ' ', $username));

// List of school-related pages for dropdown highlighting
$schoolPages = ['admin_school.php', 'admin_elementary.php', 'admin_secondary.php', 'admin_add_school.php'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_header.css">
 
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <input type="checkbox" id="checkbox">
     <header class="header bg-white">
    <div class="header-left">
        <label for="checkbox">
            <i id="navbtn" class="fa fa-bars text-black" aria-hidden="true"></i>
        </label>
    </div>
    <div class="deped-header-text">
    <div class="logo-container">
        <img src="images/deped_republic.png" alt="Republic of the Philippines" class="logo-republic">
        <div class="header-title">
            <div>Private School Portal</div>
            <div>Department of Education</div>
            <div>Division of General Trias City</div>
        </div>
        <img src="images/deped_logo.png" alt="DepEd Logo" class="logo-deped">
    </div>
</div>
</header>
    <div class="body">
        <nav class="side-bar">
            <div class="user-p">
            
            <ul>
    <li class="<?php echo isActive('admin_dashboard.php'); ?>">
        <a href="admin_dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
     <li class="<?php echo isInGroup($schoolPages); ?>">
        <a href="#" class="dropdown-toggle">
            <i class="fas fa-school"></i>
            <span>About School</span>
            <i class="fas fa-chevron-down dropdown-icon"></i> <!-- Added dropdown-icon class -->
        </a>
        <div class="dropdown-content">
            <a href="admin_school.php" class="dropdown-item <?php echo isActive('admin_school.php'); ?>">
                <i class="fas fa-list"></i>
                <span>List of Schools</span>
            </a>
            <a href="admin_offers.php" class="dropdown-item <?php echo isActive('admin_school.php'); ?>">
                <i class="fas fa-graduation-cap"></i>
                <span>Grade Offers</span>
            </a>
        </div>
    </li>
    <li class="<?php echo isActive('admin_process.php'); ?>">
        <a href="admin_process.php">
            <i class="fas fa-project-diagram"></i>
            <span>Process Flow</span>
        </a>
    </li>
    <li class="<?php echo isActive('admin_forms.php'); ?>">
        <a href="admin_forms.php">
            <i class="fas fa-file-alt"></i>
            <span>Forms</span>
        </a>
    </li>
    <li class="<?php echo isActive('admin_memorandum.php'); ?>">
        <a href="admin_memorandum.php">
            <i class="fas fa-file-signature"></i>
            <span>Memorandum</span>
        </a>
    </li>
    <li class="<?php echo isActive('admin_announcement.php'); ?>">
        <a href="admin_announcement.php">
            <i class="fas fa-bullhorn"></i>
            <span>Banner / Announcements</span>
        </a>
    </li>
    <li>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>
</ul>
            </div>
        </nav>
        
        <div class="main-content w-full" id="mainContent">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            const dropdownContent = this.nextElementSibling;
            dropdownContent.classList.toggle('show');
        });
    });
    
    const activeDropdownItem = document.querySelector('.dropdown-item.active-nav');
    if (activeDropdownItem) {
        const parentDropdown = activeDropdownItem.parentElement;
        parentDropdown.classList.add('show');
        const parentToggle = parentDropdown.previousElementSibling;
        if (parentToggle) {
            parentToggle.classList.add('active');
        }
    }
});
</script>
</body>
</html>