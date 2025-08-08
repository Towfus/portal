<?php // header.php - Reusable navigation header for DepEd Portal ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
</head>
<body>
    <nav>
        <div class="logo">
            <img src="images/deped_republic.png" alt="General Trias Logo">
            <span class="logo-text">DepEd Gentri - Private School Portal</span>
        </div>
        
        <div class="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <div class="nav-links">
            <a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>>Home</a>
            
            <div class="dropdown">
                <a href="" class="dropdown-toggle <?php echo (basename($_SERVER['PHP_SELF']) == '' || basename($_SERVER['PHP_SELF']) == '' || basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">
                    About School <i class="fas fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    <a href="recognize.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'recognize.php') ? 'class="active"' : ''; ?>>Recognized School</a>
                    <a href="renewal.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'renewal.php') ? 'class="active"' : ''; ?>>Renewal School</a>
                </div>
            </div>
            
            <a href="process_flow.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'process_flow.php') ? 'class="active"' : ''; ?>>Process Flow</a>
            <a href="form.php"<?php echo (basename($_SERVER['PHP_SELF']) == 'form.php') ? 'class="active"' : ''; ?>>Requirement Forms</a>
            <a href="memorandum.php"<?php echo (basename($_SERVER['PHP_SELF']) == 'memorandum.php') ? 'class="active"' : ''; ?>>DepEd Memorandum</a>
        </div>
    </nav>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle mobile menu
            const menuToggle = document.querySelector('.menu-toggle');
            const navLinks = document.querySelector('.nav-links');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }
            
            // Handle dropdown on mobile
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                
                if (dropdownToggle) {
                    dropdownToggle.addEventListener('click', function(e) {
                        if (window.innerWidth <= 768) {
                            e.preventDefault();
                            dropdown.classList.toggle('active');
                        }
                    });
                }
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('nav') && navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                }
            });
            
            // Resize handler
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    navLinks.classList.remove('active');
                    dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
                }
            });
        });
    </script>
</body>
</html>