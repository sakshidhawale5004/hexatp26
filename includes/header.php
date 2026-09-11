<?php
/**
 * Global Header for HexaTP - Mirroring India.html Premium Design
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'HexaTP | Premium Transfer Pricing Solutions'; ?></title>
    <meta name="description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'HexaTP provides premium transfer pricing solutions and global tax advisory services.'; ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-light: #ffffff;
            --bg-subtle: #f9fafb;
            --accent: #f5c400;
            --accent-glow: rgba(245, 196, 0, 0.2);
            --card-bg: #ffffff;
            --glass-border: rgba(0, 0, 0, 0.08);
            --text-main: #0f172a;
            --text-slate: #64748b;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-main);
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        header {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1200px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 100px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 10px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-img { height: 38px !important; padding: 4px 0; width: auto !important; display: block; object-fit: contain; }

        nav ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
        nav a { text-decoration: none; color: #444; font-size: 14px; font-weight: 600; transition: 0.3s; }
        nav a:hover { color: var(--accent); }

        .btn-main {
            background: var(--accent);
            color: #000;
            padding: 10px 25px;
            border-radius: 100px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 10px 20px var(--accent-glow);
            transition: 0.3s;
            display: inline-block;
        }

        .btn-main:hover { transform: translateY(-3px); background: #fff; color: #000; }

        /* Mobile Nav Styles */
        .mobile-nav-toggle {
            display: none;
            position: absolute;
            right: 5%;
            top: 20px;
            background: none;
            border: none;
            color: #f5c400;
            font-size: 28px;
            cursor: pointer;
            z-index: 1001;
            padding: 10px;
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100vh;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-left: 1px solid rgba(0, 0, 0, 0.1);
            padding: 80px 20px 20px;
            transition: right 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }

        .mobile-menu.active { right: 0; }
        .mobile-menu ul { list-style: none; padding: 0; }
        .mobile-menu ul li { margin-bottom: 15px; }
        .mobile-menu ul li a { color: var(--text-main); text-decoration: none; font-size: 18px; display: block; padding: 10px; }
        
        .mobile-submenu { padding-left: 20px; display: none; }
        .mobile-submenu.active { display: block; }
        
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
        }
        .mobile-overlay.active { display: block; }

        @media (max-width: 768px) {
            .mobile-nav-toggle { display: block; }
            header nav.d-none.d-md-block { display: none !important; }
            .logo-img { height: 38px !important; padding: 4px 0; }
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 10px;
            background: #ffffff !important;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
            border-radius: 8px;
            transition: 0.2s;
        }

        .dropdown-item:hover {
            background-color: var(--accent);
            color: #000 !important;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            position: absolute;
            top: 0;
            left: 100%;
            display: none;
            margin-top: -5px;
        }

        .dropdown-submenu:hover > .dropdown-menu {
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <button class="mobile-nav-toggle" onclick="openMobileMenu()">?</button>
        <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>
        <div class="mobile-menu" id="mobileMenu">
            <button class="btn-close  position-absolute top-0 end-0 m-4" onclick="closeMobileMenu()"></button>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="aboutus.html">About</a></li>
                <li><a href="solution.html">Solutions</a></li>
                <li>
                    <div class="text-dark p-2" onclick="toggleSubmenu('mobCountries')">Countries <i class="bi bi-chevron-down float-end"></i></div>
                    <ul class="mobile-submenu" id="mobCountries">
                        <li><a href="India.html">India</a></li>
                        <li><a href="Saudiarabia.html">Saudi Arabia</a></li>
                        <li><a href="unitedarab.html">UAE</a></li>
                        <!-- Add others as needed -->
                    </ul>
                </li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </div>

        <a href="index.html">
            <img src="logo-hexatp.jpeg" alt="HexaTP Logo" class="logo-img" />
        </a>
        <nav class="d-none d-md-block">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="aboutus.html">About</a></li>
                <li><a href="solution.html">Solutions</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Countries</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="India.html">India</a></li>
                        <li><a class="dropdown-item" href="Saudiarabia.html">Saudi Arabia</a></li>
                        <li><a class="dropdown-item" href="unitedarab.html">UAE</a></li>
                    </ul>
                </li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
        <a href="mailto:md@hexatp.com" class="btn-main py-2 px-4 fs-6">Get Started</a>
    </header>

    <script>
        function openMobileMenu() {
            document.getElementById('mobileMenu').classList.add('active');
            document.getElementById('mobileOverlay').classList.add('active');
        }
        function closeMobileMenu() {
            document.getElementById('mobileMenu').classList.remove('active');
            document.getElementById('mobileOverlay').classList.remove('active');
        }
        function toggleSubmenu(id) {
            document.getElementById(id).classList.toggle('active');
        }
    </script>
