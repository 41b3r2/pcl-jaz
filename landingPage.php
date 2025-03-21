<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL Dashboard</title>
    <link rel="stylesheet" href="assets/css/landingPage.css">
    <style>
        /* Basic Styling */
        .metric-section {
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        /* Hover Effect - Gumagalaw pataas */
        .metric-section:hover {
            transform: translateY(-5px);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }
        /* Loading Overlay (Hidden by Default) */
        .loading-screen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            font-size: 24px;
            font-weight: bold;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid red;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin-right: 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .logout-link {
            text-decoration: none;
            color: inherit;
            display: block; /* Ginagawang clickable ang buong section */
        }

    </style>
</head>
<body>
    <div class="mobile-toggle">☰</div>
    <div class="overlay"></div>
    
    <!-- Updated Loading Screen to match the login page -->
    <div class="loading-screen" id="loading-screen">
        <div class="loader"></div>
        <span>Loading...</span>
    </div>
    
    <div class="sidebar">
        <div>
            <!-- Metrics Sections -->
            <div class="metric-section" data-href="utilization.html">
                <div class="chart-container">
                    <div class="pie-chart">
                        <div class="pie-slice"></div>
                    </div>
                </div>
                <div class="metric-title">UTILIZATION</div>
            </div>
            <div class="metric-section" data-href="available-trucks.html">
                <div class="bar-container">
                    <div class="bar bar-1"></div>
                    <div class="bar bar-2"></div>
                    <div class="bar bar-3"></div>
                </div>
                <div class="metric-title">AVAILABLE TRUCKS</div>
            </div>
            <div class="metric-section" data-href="utilization.html">
                <div class="chart-container">
                    <div class="people-icon">
                        <div class="people-head"></div>
                        <div class="people-body"></div>
                    </div>
                </div>
                <div class="metric-title">Profile</div>
            </div>
        </div>
        
        <a href="logout.php" class="logout-link" id="logout-link">
            <div class="logout-section">
                <div class="logout-icon">←</div>
                <span>Log Out</span>
            </div>
        </a>
    </div>
    
    <div class="main-content">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="PCL Logo" style="margin-right: 10px; width: 320px; height: auto;">
        </div>
        
        <div class="menu-grid">
            <a href="#" class="menu-item" data-href="#">
                <div class="menu-icon sheets"></div>
                <div class="menu-label">View Sheets</div>
            </a>
            
            <a href="topsheet2.html" class="menu-item" data-href="topsheet2.html">
                <div class="menu-icon new-sheet"></div>
                <div class="menu-label">New Sheet</div>
            </a>
            
            <a href="#" class="menu-item" data-href="#">
                <div class="menu-icon queries">
                    <div class="funnel"></div>
                </div>
                <div class="menu-label">Queries</div>
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.querySelector('.mobile-toggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');
            const mainContent = document.querySelector('.main-content');
            
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
            
            function checkMobile() {
                if (window.innerWidth <= 768) {
                    mainContent.style.marginLeft = '0';
                    sidebar.classList.remove('active');
                } else {
                    mainContent.style.marginLeft = '-20px';
                }
            }
            
            checkMobile();
            window.addEventListener('resize', checkMobile);
            
            // Add loading screen to metric sections
            document.querySelectorAll('.metric-section').forEach(section => {
                section.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default link action
                    let link = this.getAttribute('data-href'); // Get target link
                    if (link) {
                        // Show loading screen
                        document.getElementById("loading-screen").style.display = "flex";
                        
                        // Navigate after 2 seconds
                        setTimeout(() => {
                            window.location.href = link;
                        }, 2000);
                    }
                });
            });
            
            // Add loading screen to menu items
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    const dataHref = this.getAttribute('data-href');
                    
                    // Create ripple effect
                    const ripple = document.createElement('div');
                    ripple.classList.add('ripple');
                    
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    ripple.style.width = ripple.style.height = `${size}px`;
                    
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    // Only proceed with navigation if href is not "#"
                    if (href !== '#') {
                        e.preventDefault();
                        
                        // Show loading screen
                        document.getElementById("loading-screen").style.display = "flex";
                        
                        setTimeout(() => {
                            ripple.remove();
                            window.location.href = href;
                        }, 2000);
                    } else {
                        setTimeout(() => {
                            ripple.remove();
                        }, 600);
                    }
                });
            });
            
            // Add loading screen to logout
            const logoutLink = document.getElementById('logout-link');
            
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const logoutButton = document.querySelector('.logout-section');
                    logoutButton.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
                    
                    // Show loading screen
                    document.getElementById("loading-screen").style.display = "flex";
                    
                    setTimeout(() => {
                        logoutButton.style.backgroundColor = '';
                        window.location.href = this.getAttribute('href');
                    }, 2000);
                });
            }
            
            // Pie chart animation
            const pieSlice = document.querySelector('.pie-slice');
            setTimeout(() => {
                pieSlice.style.transition = 'transform 1s ease-out';
                pieSlice.style.transform = 'rotate(135deg)';
            }, 500);
            
            // Bar animation
            const bars = document.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                const heights = ['30%', '70%', '50%'];
                bar.style.height = '0';
                setTimeout(() => {
                    bar.style.transition = 'height 1s ease-out';
                    bar.style.height = heights[index % 3];
                }, 300 + (index * 100));
            });
        });
    </script>
</body>
</html>