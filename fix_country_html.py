import re

PATH = r"c:\Users\Sakshi\Downloads\hexatp-main\hexatp-main\country.html"

with open(PATH, 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

CANONICAL_HEADER = """    <header>
        <!-- Mobile Navigation -->
        <button class="mobile-nav-toggle" onclick="openMobileMenu()" aria-label="Open menu">&#9776;</button>
        <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>
        <div class="mobile-menu" id="mobileMenu">
            <button class="close-menu" onclick="closeMobileMenu()" aria-label="Close menu">&#x2715;</button>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="aboutus.html">About</a></li>
                <li><a href="solution.html">Solutions</a></li>
                <li>
                    <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('countriesMenu', this)"><span>Countries</span></div>
                    <ul class="mobile-submenu" id="countriesMenu">
                        <li>
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('gulfMenu', this)"><span>Gulf Region</span></div>
                            <ul class="mobile-submenu" id="gulfMenu">
                                <li><a href="unitedarab.html">UAE</a></li>
                                <li><a href="Saudiarabia.html">Saudi Arabia</a></li>
                                <li><a href="Qatar.html">Qatar</a></li>
                                <li><a href="oman.html">Oman</a></li>
                                <li><a href="bahrain.html">Bahrain</a></li>
                                <li><a href="egypt.html">Egypt</a></li>
                            </ul>
                        </li>
                        <li>
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('asiaMenu', this)"><span>Asia</span></div>
                            <ul class="mobile-submenu" id="asiaMenu">
                                <li><a href="India.html">India</a></li>
                                <li><a href="singapore.html">Singapore</a></li>
                                <li><a href="malaysia.html">Malaysia</a></li>
                                <li><a href="thailand.html">Thailand</a></li>
                                <li><a href="indonesia.html">Indonesia</a></li>
                                <li><a href="viethnam.html">Vietnam</a></li>
                                <li><a href="bangladesh.html">Bangladesh</a></li>
                            </ul>
                        </li>
                        <li>
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('africaMenu', this)"><span>Africa</span></div>
                            <ul class="mobile-submenu" id="africaMenu">
                                <li><a href="kenya.html">Kenya</a></li>
                                <li><a href="ghana.html">Ghana</a></li>
                                <li><a href="botswana.html">Botswana</a></li>
                            </ul>
                        </li>
                        <li>
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('americasMenu', this)"><span>Americas &amp; Oceania</span></div>
                            <ul class="mobile-submenu" id="americasMenu">
                                <li><a href="us.html">United States</a></li>
                                <li><a href="canada.html">Canada</a></li>
                                <li><a href="australia.html">Australia</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </div>
        <a href="index.html">
            <img src="logo-hexatp.jpeg" alt="HexaTP - Transfer Pricing Simplified" class="logo-img" />
        </a>
        <nav class="d-none d-md-block">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="aboutus.html">About</a></li>
                <li><a href="solution.html">Solutions</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Countries</a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-submenu position-relative">
                            <a class="dropdown-item dropdown-toggle" href="#">Gulf Region</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="unitedarab.html">UAE</a></li>
                                <li><a class="dropdown-item" href="Saudiarabia.html">Saudi Arabia</a></li>
                                <li><a class="dropdown-item" href="Qatar.html">Qatar</a></li>
                                <li><a class="dropdown-item" href="oman.html">Oman</a></li>
                                <li><a class="dropdown-item" href="bahrain.html">Bahrain</a></li>
                                <li><a class="dropdown-item" href="egypt.html">Egypt</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Asia</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="India.html">India</a></li>
                                <li><a class="dropdown-item" href="singapore.html">Singapore</a></li>
                                <li><a class="dropdown-item" href="malaysia.html">Malaysia</a></li>
                                <li><a class="dropdown-item" href="thailand.html">Thailand</a></li>
                                <li><a class="dropdown-item" href="indonesia.html">Indonesia</a></li>
                                <li><a class="dropdown-item" href="viethnam.html">Vietnam</a></li>
                                <li><a class="dropdown-item" href="bangladesh.html">Bangladesh</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Africa</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="kenya.html">Kenya</a></li>
                                <li><a class="dropdown-item" href="ghana.html">Ghana</a></li>
                                <li><a class="dropdown-item" href="botswana.html">Botswana</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Americas &amp; Oceania</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="us.html">United States</a></li>
                                <li><a class="dropdown-item" href="canada.html">Canada</a></li>
                                <li><a class="dropdown-item" href="australia.html">Australia</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
        <a href="mailto:md@hexatp.com" class="btn-main py-2 px-4 fs-6">Get Started</a>
    </header>"""

CANONICAL_CSS = """        /* ========== HEADER STYLES ========== */
        header { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1200px; z-index: 1000; background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border: 1px solid rgba(0,0,0,0.08); border-radius: 100px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo-img { height: 38px !important; padding: 4px 0; width: auto !important; display: block; object-fit: contain; }
        nav ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
        nav a { text-decoration: none; color: #444; font-size: 14px; font-weight: 600; transition: 0.3s; }
        nav a:hover { color: #f5c400; }
        .btn-main { background: #f5c400; color: #000; padding: 10px 25px; border-radius: 100px; font-weight: 700; text-decoration: none; box-shadow: 0 10px 20px rgba(245,196,0,0.2); transition: 0.3s; display: inline-block; }
        .btn-main:hover { transform: translateY(-3px); background: #fff; color: #000; }
        .dropdown-menu { border: 1px solid rgba(0,0,0,0.08); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 10px; background: #ffffff !important; min-width: 200px; z-index: 9999; }
        .dropdown-item { padding: 10px 20px; font-size: 14px; font-weight: 500; color: #0f172a; border-radius: 8px; transition: 0.2s; }
        .dropdown-item:hover { background-color: #f5c400; color: #000 !important; }
        .dropdown-toggle::after { vertical-align: middle; margin-left: 5px; opacity: 0.7; }
        .dropdown-submenu { position: relative; }
        .dropdown-submenu .dropdown-menu { position: absolute; top: 0; left: 100%; display: none; margin-top: -5px; background: #ffffff; }
        .dropdown-submenu:hover > .dropdown-menu { display: block; }
        .mobile-nav-toggle { display: none; position: absolute; right: 5%; top: 20px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; }
        .mobile-menu { position: fixed; top: 0; right: -100%; width: 280px; height: 100vh; background: rgba(255,255,255,0.98); backdrop-filter: blur(20px); border-left: 1px solid rgba(0,0,0,0.1); padding: 80px 20px 20px; transition: right 0.3s ease; z-index: 1000; overflow-y: auto; box-shadow: -5px 0 20px rgba(0,0,0,0.5); }
        .mobile-menu.active { right: 0; }
        .mobile-menu ul { list-style: none; padding: 0; margin: 0; }
        .mobile-menu ul li { margin-bottom: 15px; }
        .mobile-menu ul li a { color: #0f172a; text-decoration: none; font-size: 18px; display: block; padding: 12px 15px; border-radius: 8px; transition: all 0.3s ease; }
        .mobile-menu ul li a:hover { background: rgba(245,196,0,0.1); color: #f5c400; transform: translateX(5px); }
        .mobile-submenu { padding-left: 20px; margin-top: 10px; display: none; border-left: 2px solid rgba(245,196,0,0.3); }
        .mobile-submenu.active { display: block; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        .mobile-submenu li a { font-size: 16px; padding: 10px 15px; }
        .mobile-dropdown-toggle { color: #0f172a; font-size: 18px; padding: 12px 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; transition: all 0.3s ease; user-select: none; }
        .mobile-dropdown-toggle:hover { background: rgba(245,196,0,0.1); color: #f5c400; }
        .mobile-dropdown-toggle::after { content: '\\25BC'; font-size: 12px; transition: transform 0.3s ease; }
        .mobile-dropdown-toggle.active { color: #f5c400; }
        .mobile-dropdown-toggle.active::after { transform: rotate(180deg); }
        .mobile-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 999; backdrop-filter: blur(2px); }
        .mobile-overlay.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        .close-menu { position: absolute; top: 20px; right: 20px; background: none; border: none; color: #f5c400; font-size: 32px; cursor: pointer; padding: 5px; line-height: 1; transition: transform 0.3s ease; }
        .close-menu:hover { transform: rotate(90deg); }
        @media (max-width: 768px) { .mobile-nav-toggle { display: block; } header nav.d-none.d-md-block { display: none !important; } .logo-img { height: 35px; width: auto; } header { width: 95% !important; padding: 10px 20px !important; top: 10px !important; } }
        @media (min-width: 769px) { .mobile-menu, .mobile-overlay, .mobile-nav-toggle { display: none !important; } }
        /* ========== END HEADER STYLES ========== */"""

CANONICAL_JS = """    <script>
        function openMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileOverlay');
            menu.classList.add('active'); overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileOverlay');
            menu.classList.remove('active'); overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        function toggleSubmenu(submenuId, toggleElement) {
            const submenu = document.getElementById(submenuId);
            if (submenu) { submenu.classList.toggle('active'); toggleElement.classList.toggle('active'); }
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.mobile-menu a').forEach(link => {
                link.addEventListener('click', function() { closeMobileMenu(); });
            });
        });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeMobileMenu(); } });
    </script>"""

# 1. Remove the old floating mobile nav buttons + bootstrap navbar block
# They appear before <section class="hero-section"> 
content = re.sub(
    r'<body>\s*.*?</nav>\s*(?=\s*<section)',
    '<body>\n',
    content,
    flags=re.DOTALL
)

# 2. Insert the canonical header right after <body>
content = content.replace('<body>\n', '<body>\n' + CANONICAL_HEADER + '\n', 1)

# 3. Inject header CSS into first </style>
# First remove old duplicated canonical header CSS if any
content = re.sub(
    r'/\*\s*=+\s*HEADER STYLES\s*=+\s*\*/.*?/\*\s*=+\s*END HEADER STYLES\s*=+\s*\*/',
    '',
    content,
    flags=re.DOTALL
)
# Remove old bad dropdown styles (yellow submenu / black hover)
content = re.sub(
    r'/\*\s*---\s*Dropdown Styles\s*---\s*\*/\s*\.dropdown-submenu.*?\.dropdown-item:hover[^}]+\}',
    '',
    content,
    flags=re.DOTALL
)
content = content.replace('</style>', CANONICAL_CSS + '\n        </style>', 1)

# 4. Remove old mobile JS if present, add canonical JS before </body>
content = re.sub(
    r'<script>\s*function openMobileMenu\(\).*?</script>',
    '',
    content,
    flags=re.DOTALL
)
content = content.replace('</body>', CANONICAL_JS + '\n</body>', 1)

with open(PATH, 'w', encoding='utf-8') as f:
    f.write(content)

print("country.html updated successfully!")
