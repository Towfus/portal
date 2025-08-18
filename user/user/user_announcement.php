<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "deped_schools";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Fetch all announcements
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);

$all_announcements = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $all_announcements[] = $row;
    }
}



// Get total announcements count
$totalAnnouncements = count($all_announcements);
$activeAnnouncements = count(array_filter($all_announcements, function($announcement) {
    return $announcement['is_active'] == 1;
}));



$conn->close();
include 'user_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - DepEd General Trias City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e7e34;
            --secondary-color: #28a745;
            --success-color: #27ae60;
            --info-color: #3498db;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --text-dark: #2c3e50;
            --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            --card-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: var(--text-dark);
        }

        .main-container {
            padding: 2rem 0;
        }

        /* Hero Section */
        .hero-section {
              background: linear-gradient(135deg, #4ADE80 0%, #22C55E 100%),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>');
            background-size: cover;
            color: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 40%;
            height: 200%;
            background: rgba(255,255,255,0.05);
            transform: rotate(15deg);
            border-radius: 50px;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .stats-card {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.3);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Navigation Tabs */
        .nav-tabs-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            padding: 1rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .nav-tab {
            padding: 1rem 2rem;
            border-radius: 50px;
            border: 2px solid var(--border-color);
            background: white;
            color: var(--text-dark);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-tab:hover,
        .nav-tab.active {
            background: var(--gradient);
            border-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        /* Search and Filter Section */
        .filter-section {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .search-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto 1.5rem;
        }

        .search-input {
            width: 100%;
            padding: 1.2rem 1.2rem 1.2rem 3.5rem;
            border: 2px solid var(--border-color);
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .search-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        .filter-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            border: 2px solid var(--border-color);
            background: white;
            color: var(--text-dark);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--gradient);
            border-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        /* Content Sections */
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        /* Banner Grid */
        .banners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .banner-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .banner-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.15);
        }

        .banner-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            cursor: pointer;
        }

        .banner-error {
            width: 100%;
            height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            color: #6c757d;
            padding: 2rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .banner-error::before {
            content: '\f071';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #e74c3c;
        }

        .banner-info {
            padding: 1.5rem;
        }

        .banner-date {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            width: 100%;
            justify-content: center;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--info-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            width: 100%;
            justify-content: center;
        }

        .view-btn:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-2px);
        }

        /* Announcements Grid */
        .announcements-grid {
            display: grid;
            gap: 2rem;
        }

        .announcement-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.15);
        }

        .announcement-header {
            padding: 2rem 2rem 1rem;
            position: relative;
        }

        .announcement-status {
            position: absolute;
            top: 1.5rem;
            right: 2rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }

        .status-inactive {
            background: linear-gradient(135deg, #95a5a6, #bdc3c7);
            color: white;
        }

        .announcement-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            line-height: 1.4;
            padding-right: 120px;
        }

        .announcement-content {
            padding: 0 2rem 1rem;
            color: #6c757d;
            line-height: 1.6;
            font-size: 1rem;
        }

        .announcement-footer {
            padding: 1rem 2rem 2rem;
            display: flex;
            justify-content: between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .announcement-dates {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date-range {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary-color);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .date-posted {
            color: #6c757d;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: relative;
            margin: 2% auto;
            padding: 0;
            width: 90%;
            max-width: 800px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .modal-header {
            background: var(--gradient);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .modal-close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }

        .modal-close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 2rem;
            text-align: center;
        }

        .modal-image {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
            color: var(--secondary-color);
        }

        .empty-state h5 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .empty-state p {
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1rem;
                text-align: center;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .stats-number {
                font-size: 2rem;
            }

            .main-container {
                padding: 1rem 0;
            }

            .filter-section {
                padding: 1.5rem;
            }

            .announcement-title {
                font-size: 1.2rem;
                padding-right: 100px;
            }

            .announcement-header,
            .announcement-content,
            .announcement-footer {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .announcement-status {
                right: 1.5rem;
                padding: 0.4rem 0.8rem;
                font-size: 0.7rem;
            }

            .filter-buttons {
                gap: 0.5rem;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .announcement-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-tabs-container {
                padding: 0.5rem;
            }

            .nav-tab {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }

            .banners-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
        }

        /* Floating Action Button */
        .floating-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: var(--gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(0,0,0,0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <!-- Hero Section -->
        <div class="hero-section fade-in">
            <div class="hero-content">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="fas fa-bullhorn me-3"></i>
                    DepEd General Trias City Hub
                </h1>
                <p class="lead mb-0">
                    Your central hub for official announcements, banners, and important updates from DepEd General Trias City. 
                    Stay informed with the latest news and downloadable resources.
                </p>
                
                <div class="stats-grid">
                    <div class="stats-card">
                        <span class="stats-number"><?php echo $totalAnnouncements; ?></span>
                        <div class="stats-label">Total Announcements</div>
                    </div>
                    <div class="stats-card">
                        <span class="stats-number"><?php echo $activeAnnouncements; ?></span>
                        <div class="stats-label">Active Announcements</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Search and Filter Section -->
        <div class="filter-section fade-in">
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Search announcements and banners...">
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-list me-1"></i> All Items
                </button>
                <button class="filter-btn" data-filter="active">
                    <i class="fas fa-check-circle me-1"></i> Active Only
                </button>
                <button class="filter-btn" data-filter="recent">
                    <i class="fas fa-clock me-1"></i> Recent
                </button>
            </div>
        </div>

        <!-- Announcements Section -->
        <div id="announcements-section" class="content-section active">
            <div class="announcements-grid fade-in">
                <?php if (!empty($all_announcements)): ?>
                    <?php foreach ($all_announcements as $index => $announcement): ?>
                        <div class="announcement-card" 
                             data-status="<?php echo $announcement['is_active'] ? 'active' : 'inactive'; ?>"
                             data-type="announcement"
                             style="animation-delay: <?php echo $index * 0.1; ?>s">
                            
                            <div class="announcement-header">
                                <div class="announcement-status <?php echo $announcement['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <i class="fas <?php echo $announcement['is_active'] ? 'fa-check-circle' : 'fa-pause-circle'; ?> me-1"></i>
                                    <?php echo $announcement['is_active'] ? 'Active' : 'Archived'; ?>
                                </div>
                                
                                <h2 class="announcement-title">
                                    <?php echo htmlspecialchars($announcement['title']); ?>
                                </h2>
                            </div>

                            <div class="announcement-content">
                                <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                            </div>

                            <div class="announcement-footer">
                                <div class="announcement-dates">
                                    <div class="date-range">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>
                                            <?php echo date('M d, Y', strtotime($announcement['start_date'])); ?> - 
                                            <?php echo date('M d, Y', strtotime($announcement['end_date'])); ?>
                                        </span>
                                    </div>
                                    <div class="date-posted">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bullhorn"></i>
                        <h5>No Announcements Available</h5>
                        <p>There are currently no announcements published. Please check back later for updates and important information from DepEd General Trias City.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>


    <!-- Floating Action Button -->
    <a href="#" class="floating-btn" title="Back to Top" onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
    </a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Section switching
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionName + '-section').classList.add('active');
            
            // Update nav tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Reset search and filters
            document.getElementById('searchInput').value = '';
            filterContent();
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const allCards = document.querySelectorAll('.announcement-card, .banner-card');

        searchInput.addEventListener('input', function() {
            filterContent();
        });

        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                filterContent();
            });
        });

        function filterContent() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const activeFilter = document.querySelector('.filter-btn.active').getAttribute('data-filter');
            const currentSection = document.querySelector('.content-section.active').id;
            
            let cardsToFilter;
            if (currentSection === 'announcements-section') {
                cardsToFilter = document.querySelectorAll('.announcement-card');
            } else {
                cardsToFilter = document.querySelectorAll('.banner-card');
            }
            
            cardsToFilter.forEach(card => {
                const cardType = card.getAttribute('data-type');
                const status = card.getAttribute('data-status');
                let textContent = '';
                
                if (cardType === 'announcement') {
                    const title = card.querySelector('.announcement-title')?.textContent.toLowerCase() || '';
                    const content = card.querySelector('.announcement-content p')?.textContent.toLowerCase() || '';
                    textContent = title + ' ' + content;
                } else if (cardType === 'banner') {
                    const date = card.querySelector('.banner-date')?.textContent.toLowerCase() || '';
                    textContent = 'banner ' + date;
                }
                
                let shouldShow = true;
                
                // Apply search filter
                if (searchTerm && !textContent.includes(searchTerm)) {
                    shouldShow = false;
                }
                
                // Apply status filter
                if (activeFilter === 'active' && status !== 'active') {
                    shouldShow = false;
                } else if (activeFilter === 'recent') {
                    // Show items from last 30 days
                    const cardDate = card.querySelector('.date-posted span, .banner-date span')?.textContent;
                    if (cardDate) {
                        const dateMatch = cardDate.match(/(\w{3} \d{1,2}, \d{4})/);
                        if (dateMatch) {
                            const itemDate = new Date(dateMatch[1]);
                            const thirtyDaysAgo = new Date();
                            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                            if (itemDate < thirtyDaysAgo) {
                                shouldShow = false;
                            }
                        }
                    }
                }
                
                if (shouldShow) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.5s ease forwards';
                } else {
                    card.style.display = 'none';
                }
            });
            
            updateEmptyState();
        }

        // Update empty state visibility
        function updateEmptyState() {
            const currentSection = document.querySelector('.content-section.active');
            const visibleCards = currentSection.querySelectorAll('.announcement-card:not([style*="display: none"]), .banner-card:not([style*="display: none"])');
            const emptyState = currentSection.querySelector('.empty-state');
            
            if (visibleCards.length === 0 && emptyState) {
                // If no original content, show empty state
                emptyState.style.display = 'block';
            }
        }

        // Banner modal functions
        function openBannerModal(imageSrc) {
            document.getElementById('modalBannerImage').src = imageSrc;
            document.getElementById('bannerModal').style.display = 'block';
        }

        function closeBannerModal() {
            document.getElementById('bannerModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bannerModal');
            if (event.target == modal) {
                closeBannerModal();
            }
        }

        // Scroll to top functionality
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/hide floating button based on scroll position
        window.addEventListener('scroll', function() {
            const floatingBtn = document.querySelector('.floating-btn');
            if (window.pageYOffset > 300) {
                floatingBtn.style.opacity = '1';
                floatingBtn.style.transform = 'scale(1)';
            } else {
                floatingBtn.style.opacity = '0';
                floatingBtn.style.transform = 'scale(0.8)';
            }
        });

        // Loading animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Staggered animation for cards
            const cards = document.querySelectorAll('.announcement-card, .banner-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects to cards
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(-5px)';
                });
            });

            // Auto-focus search input on desktop
            if (window.innerWidth > 768) {
                setTimeout(() => {
                    searchInput.focus();
                }, 1000);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Focus search on Ctrl+F or Cmd+F
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            
            // Clear search on Escape
            if (e.key === 'Escape') {
                if (document.activeElement === searchInput) {
                    searchInput.value = '';
                    filterContent();
                } else if (document.getElementById('bannerModal').style.display === 'block') {
                    closeBannerModal();
                }
            }
            
            // Switch sections with arrow keys
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                const currentTab = document.querySelector('.nav-tab.active');
                const tabs = document.querySelectorAll('.nav-tab');
                const currentIndex = Array.from(tabs).indexOf(currentTab);
                
                if (e.key === 'ArrowLeft' && currentIndex > 0) {
                    tabs[currentIndex - 1].click();
                } else if (e.key === 'ArrowRight' && currentIndex < tabs.length - 1) {
                    tabs[currentIndex + 1].click();
                }
            }
        });

        // Add ripple effect to buttons
        document.querySelectorAll('.filter-btn, .nav-tab, .download-btn, .view-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple effect styles
        const style = document.createElement('style');
        style.textContent = `
            .filter-btn, .nav-tab, .download-btn, .view-btn {
                position: relative;
                overflow: hidden;
            }
            
            .ripple {
                position: absolute;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Download tracking (optional - for analytics)
        document.querySelectorAll('.download-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                console.log('Banner downloaded:', this.getAttribute('href'));
                // You can add analytics tracking here
            });
        });

        // Lazy loading for banner images
        const observerOptions = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '0';
                    img.style.transition = 'opacity 0.3s ease';
                    img.onload = () => {
                        img.style.opacity = '1';
                    };
                    imageObserver.unobserve(img);
                }
            });
        }, observerOptions);

        // Observe banner images
        document.querySelectorAll('.banner-image').forEach(img => {
            imageObserver.observe(img);
        });

        // Initialize page
        filterContent();
    </script>
</body>
</html>

<?php
// Footer can be included here if needed
?>