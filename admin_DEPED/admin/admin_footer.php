<!-- Update admin_footer.php -->
<?php
// Close the main content container properly
?>
        </div> <!-- Closing the main-content div -->
    </div> <!-- Closing the body div from admin_header.php -->
   
    <!-- Footer content goes here -->
    <footer>
    <link rel="stylesheet" href="css/admin_footer.css">
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

    <?php if (isset($additionalJs)): ?>
        <?php foreach ($additionalJs as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const closeSidebar = document.getElementById('close-sidebar');
        const mainContent = document.getElementById('main-content');
        
        // Check if sidebar should be open by default on larger screens
        function checkScreenSize() {
            if (window.innerWidth > 992) {
                sidebar.classList.add('active');
                if (mainContent) {
                    mainContent.classList.add('sidebar-active');
                }
            } else {
                sidebar.classList.remove('active');
                if (mainContent) {
                    mainContent.classList.remove('sidebar-active');
                }
            }
        }
        
        // Run on page load
        checkScreenSize();
        
        // Listen for window resize
        window.addEventListener('resize', checkScreenSize);
        
        // Toggle sidebar when menu icon is clicked
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                if (mainContent) {
                    mainContent.classList.toggle('sidebar-active');
                }
                sidebarOverlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            });
        }
        
        // Close sidebar when overlay is clicked
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
                if (mainContent) {
                    mainContent.classList.remove('sidebar-active');
                }
            });
        }
        
        // Close sidebar when close button is clicked
        if (closeSidebar) {
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
                if (mainContent) {
                    mainContent.classList.remove('sidebar-active');
                }
            });
        }
        
        // Handle sidebar dropdown menus
        const sidebarDropdowns = document.querySelectorAll('.sidebar-dropdown');
        
        sidebarDropdowns.forEach(dropdown => {
            const dropdownToggle = dropdown.querySelector('.sidebar-dropdown-toggle');
            
            if (dropdownToggle) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                });
            }
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