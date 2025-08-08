<?php
// Function to check if a page is active
function isActive($pageName) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return ($currentPage == $pageName) ? 'active-nav' : '';
}

// Function to check if a page belongs to a specific group (for dropdowns, if needed in future)
function isInGroup($pageNames) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return in_array($currentPage, $pageNames) ? 'active-nav' : '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/user_header.css">
 
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
                <li class="<?php echo isActive('user_dashboard.php'); ?>">
                    <a href="user_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="<?php echo isActive('barangay.php'); ?>">
                    <a href="barangay.php">
                        <i class="fas fa-project-diagram"></i>
                        <span>Barangays</span>
                    </a>
                </li>
                <li class="<?php echo isActive('user_announcement.php'); ?>">
                    <a href="user_announcement.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Announcement</span>
                    </a>
                </li>
                <li class="<?php echo isActive('user_memorandum.php'); ?>">
                    <a href="user_memorandum.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Memorandum</span>
                    </a>
                </li>
                <li class="<?php echo isActive('user_forms.php'); ?>">
                    <a href="user_forms.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Forms</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content w-full" id="mainContent">
        <!-- Page content starts here -->
