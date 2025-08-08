<?php // footer.php - Updated with proper structure ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/footer.css">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>DepEd General Trias City</title>
   
</head>
<footer>
    <div class="footer-content">
        <div class="deped-footer">
            <div class="footer-info-container">
                <div class="footer-logo-container">
                    <img src="images/deped.png" alt="DepEd General Trias Logo" class="footer-logo">
                    <img src="images/bagong_pilipinas.png" alt="Bagong Pilipinas Logo" class="footer-logo">
                    <img src="images/deped_logo.png" alt="DepEd Logo" class="footer-logo">
                </div>
                <div class="footer-info">
                    <div><strong>Address:</strong> <a href="https://www.google.com/maps/dir//DepEd+-+Division+of+General+Trias+City+General+Trias+Cavite/@14.3748567,120.8841662,17z/data=!4m5!4m4!1m0!1m2!1m1!1s0x33962d8e69d984a1:0x5b841d8112b7b8c5" class="address-link">Brgy. Sta. Clara, General Trias City, Cavite</a></div>
                    <div><strong>Telephone No.:</strong> <a href="tel:+6346-419-8720" class="phone-link">(046) 419-8720</a></div>
                    <div><strong>Email Address:</strong> <a href="mailto:division.gentri@deped.gov.ph" class="email-link">division.gentri@deped.gov.ph</a></div>
                    <div><strong>Website:</strong> <a href="http://www.depedgentri.com" target="_blank" class="website-link">www.depedgentri.com</a></div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar Toggle Functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        // Check if sidebar should be open by default on larger screens
        function checkScreenSize() {
            if (window.innerWidth > 992) {
                sidebar.classList.add('active');
                mainContent.classList.add('sidebar-active');
            } else {
                sidebar.classList.remove('active');
                mainContent.classList.remove('sidebar-active');
            }
        }
        
        // Run on page load
        checkScreenSize();
        
        // Listen for window resize
        window.addEventListener('resize', checkScreenSize);
        
        // Toggle sidebar
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-active');
            sidebarOverlay.classList.toggle('active');
        });
        
        // Close sidebar
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Handle school year input combining (preserved from original)
        const form = document.querySelector("form");
        if (form) {
            form.addEventListener("submit", function(e) {
                const start = document.getElementById("yearStart");
                const end = document.getElementById("yearEnd");
                const combined = document.getElementById("schoolYearCombined");
                
                if (start && end && combined) {
                    combined.value = `${start.value}-${end.value}`;
                }
            });
        }
        
        // If we have a school year value already, split it (preserved from original)
        const schoolYearField = document.getElementById("schoolYearCombined");
        const yearStart = document.getElementById("yearStart");
        const yearEnd = document.getElementById("yearEnd");
        
        if (schoolYearField && schoolYearField.value && yearStart && yearEnd) {
            const yearParts = schoolYearField.value.split('-');
            if (yearParts.length === 2) {
                yearStart.value = yearParts[0];
                yearEnd.value = yearParts[1];
            }
        }
        
        // Add event listeners to year inputs (preserved from original)
        if (yearStart) {
            yearStart.addEventListener("change", function() {
                const startYear = parseInt(this.value);
                const endYearField = document.getElementById("yearEnd");
                
                if (endYearField && !endYearField.value) {
                    endYearField.value = startYear + 1;
                }
            });
        }
        
        // Validate that end year is greater than start year (preserved from original)
        if (yearEnd) {
            yearEnd.addEventListener("change", function() {
                const startYear = parseInt(document.getElementById("yearStart").value);
                const endYear = parseInt(this.value);
                
                if (endYear <= startYear) {
                    alert("End year must be greater than start year");
                    this.value = startYear + 1;
                }
            });
        }
    });
    </script>
</body>
</html>