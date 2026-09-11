import re
import os

ROOT = r"c:\Users\Sakshi\Downloads\hexatp-main\hexatp-main"

# ── Canonical header HTML ───────────────────────────────────────────────────
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
                    <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('countriesMenu', this)">
                        <span>Countries</span>
                    </div>
                    <ul class="mobile-submenu" id="countriesMenu">
                        <li>
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('gulfMenu', this)">
                                <span>Gulf Region</span>
                            </div>
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
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('asiaMenu', this)">
                                <span>Asia</span>
                            </div>
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
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('africaMenu', this)">
                                <span>Africa</span>
                            </div>
                            <ul class="mobile-submenu" id="africaMenu">
                                <li><a href="kenya.html">Kenya</a></li>
                                <li><a href="ghana.html">Ghana</a></li>
                                <li><a href="botswana.html">Botswana</a></li>
                            </ul>
                        </li>
                        <li>
                            <div class="mobile-dropdown-toggle" onclick="toggleSubmenu('americasMenu', this)">
                                <span>Americas &amp; Oceania</span>
                            </div>
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
                        <!-- Gulf Region -->
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
                        <!-- Asia -->
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
                        <!-- Africa -->
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Africa</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="kenya.html">Kenya</a></li>
                                <li><a class="dropdown-item" href="ghana.html">Ghana</a></li>
                                <li><a class="dropdown-item" href="botswana.html">Botswana</a></li>
                            </ul>
                        </li>
                        <!-- Americas & Oceania -->
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

# ── Canonical header CSS block ───────────────────────────────────────────────
CANONICAL_HEADER_CSS = """        /* ========== HEADER STYLES ========== */
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
        /* Dropdown Styles */
        .dropdown-menu { border: 1px solid var(--glass-border); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding: 10px; background: #ffffff !important; min-width: 200px; z-index: 9999; }
        .dropdown-item { padding: 10px 20px; font-size: 14px; font-weight: 500; color: var(--text-main); border-radius: 8px; transition: 0.2s; }
        .dropdown-item:hover { background-color: var(--accent); color: #000 !important; }
        .dropdown-toggle::after { vertical-align: middle; margin-left: 5px; opacity: 0.7; }
        .dropdown-submenu { position: relative; }
        .dropdown-submenu .dropdown-menu { position: absolute; top: 0; left: 100%; display: none; margin-top: -5px; background: #ffffff; }
        .dropdown-submenu:hover > .dropdown-menu { display: block; }
        /* Mobile Nav */
        .mobile-nav-toggle { display: none; position: absolute; right: 5%; top: 20px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; }
        .mobile-menu { position: fixed; top: 0; right: -100%; width: 280px; height: 100vh; background: rgba(255,255,255,0.98); backdrop-filter: blur(20px); border-left: 1px solid rgba(0,0,0,0.1); padding: 80px 20px 20px; transition: right 0.3s ease; z-index: 1000; overflow-y: auto; box-shadow: -5px 0 20px rgba(0,0,0,0.5); }
        .mobile-menu.active { right: 0; }
        .mobile-menu ul { list-style: none; padding: 0; margin: 0; }
        .mobile-menu ul li { margin-bottom: 15px; }
        .mobile-menu ul li a { color: var(--text-main); text-decoration: none; font-size: 18px; display: block; padding: 12px 15px; border-radius: 8px; transition: all 0.3s ease; }
        .mobile-menu ul li a:hover { background: rgba(245,196,0,0.1); color: #f5c400; transform: translateX(5px); }
        .mobile-submenu { padding-left: 20px; margin-top: 10px; display: none; border-left: 2px solid rgba(245,196,0,0.3); }
        .mobile-submenu.active { display: block; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .mobile-submenu li a { font-size: 16px; padding: 10px 15px; }
        .mobile-dropdown-toggle { color: var(--text-main); font-size: 18px; padding: 12px 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; transition: all 0.3s ease; user-select: none; }
        .mobile-dropdown-toggle:hover { background: rgba(245,196,0,0.1); color: #f5c400; }
        .mobile-dropdown-toggle::after { content: '\\25BC'; font-size: 12px; transition: transform 0.3s ease; }
        .mobile-dropdown-toggle.active { color: #f5c400; }
        .mobile-dropdown-toggle.active::after { transform: rotate(180deg); }
        .mobile-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 999; backdrop-filter: blur(2px); }
        .mobile-overlay.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .close-menu { position: absolute; top: 20px; right: 20px; background: none; border: none; color: #f5c400; font-size: 32px; cursor: pointer; padding: 5px; line-height: 1; transition: transform 0.3s ease; }
        .close-menu:hover { transform: rotate(90deg); }
        @media (max-width: 768px) {
            .mobile-nav-toggle { display: block; }
            header nav.d-none.d-md-block { display: none !important; }
            .logo-img { height: 35px; width: auto; }
            header { width: 95% !important; padding: 10px 20px !important; top: 10px !important; }
        }
        @media (min-width: 769px) { .mobile-menu, .mobile-overlay, .mobile-nav-toggle { display: none !important; } }
        .mobile-menu::-webkit-scrollbar { width: 6px; }
        .mobile-menu::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .mobile-menu::-webkit-scrollbar-thumb { background: rgba(245,196,0,0.3); border-radius: 3px; }
        .mobile-menu::-webkit-scrollbar-thumb:hover { background: rgba(245,196,0,0.5); }
        /* ========== END HEADER STYLES ========== */"""

# ── Canonical mobile JS ───────────────────────────────────────────────────
CANONICAL_JS = """    <script>
        function openMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileOverlay');
            menu.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileOverlay');
            menu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        function toggleSubmenu(submenuId, toggleElement) {
            const submenu = document.getElementById(submenuId);
            if (submenu) {
                submenu.classList.toggle('active');
                toggleElement.classList.toggle('active');
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.mobile-menu a').forEach(link => {
                link.addEventListener('click', function() { closeMobileMenu(); });
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') { closeMobileMenu(); }
        });
    </script>"""

# Patterns to locate old header CSS blocks we want to REMOVE (so canonical CSS won't duplicate)
OLD_NAV_CSS_PATTERNS = [
    # old dropdown with yellow/black styling (Qatar style)
    re.compile(r'/\*\s*---\s*Dropdown Styles\s*---\s*\*/.*?\.dropdown-submenu:hover\s*>\s*\.dropdown-menu\s*\{[^}]+\}', re.DOTALL),
]

def replace_header_html(content):
    """Replace <header>...</header> with canonical version."""
    # Use a non-greedy match; DOTALL so . matches newlines
    new_content, count = re.subn(
        r'<header>.*?</header>',
        CANONICAL_HEADER,
        content,
        flags=re.DOTALL
    )
    return new_content, count

def inject_header_css(content):
    """
    Remove old header/nav/dropdown/mobile CSS blocks then inject the canonical
    CSS right before the closing </style> of the FIRST <style> block.
    """
    # Remove old mobile-nav-toggle style blocks (they'll be replaced by canonical)
    content = re.sub(
        r'/\*\s*={5,}\s*MOBILE NAVIGATION STYLES\s*={5,}\s*\*/.*?(?=</style>|\Z)',
        '',
        content,
        flags=re.DOTALL
    )
    # Remove old header { ... } blocks that appear inside a <style> tag
    # (careful: keep body{}, etc.)
    # We find the first <style> block and append canonical CSS before </style>
    # But first strip any existing canonical header CSS block to avoid duplication
    content = re.sub(
        r'/\*\s*={5,}\s*HEADER STYLES\s*={5,}\s*\*/.*?/\*\s*={5,}\s*END HEADER STYLES\s*={5,}\s*\*/',
        '',
        content,
        flags=re.DOTALL
    )

    # Inject canonical CSS just before the first closing </style>
    content = content.replace('</style>', CANONICAL_HEADER_CSS + '\n        </style>', 1)
    return content

def ensure_mobile_js(content):
    """
    Make sure the canonical mobile JS exists. Replace any existing
    openMobileMenu / closeMobileMenu / toggleSubmenu script block.
    """
    # Remove existing mobile JS script blocks
    content = re.sub(
        r'<script>\s*function openMobileMenu\(\).*?</script>',
        '',
        content,
        flags=re.DOTALL
    )
    # Insert canonical JS just before </body>
    content = content.replace('</body>', CANONICAL_JS + '\n</body>', 1)
    return content

def ensure_bootstrap_js(content):
    """Make sure Bootstrap JS bundle is present before </body>."""
    bs_tag = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>'
    if bs_tag not in content and 'bootstrap.bundle.min.js' not in content:
        content = content.replace('</body>', bs_tag + '\n</body>', 1)
    return content

def process_file(filepath):
    filename = os.path.basename(filepath)
    try:
        with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
            original = f.read()
    except Exception as e:
        print(f"  SKIP (read error): {filename} — {e}")
        return

    content = original

    # 1. Replace <header>...</header>
    content, header_count = replace_header_html(content)

    # 2. Inject canonical header CSS
    content = inject_header_css(content)

    # 3. Ensure mobile JS
    content = ensure_mobile_js(content)

    # 4. Ensure Bootstrap JS
    content = ensure_bootstrap_js(content)

    if content != original:
        try:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"  UPDATED ({header_count} header(s) replaced): {filename}")
        except Exception as e:
            print(f"  ERROR writing {filename}: {e}")
    else:
        print(f"  unchanged: {filename}")


# ── Run ───────────────────────────────────────────────────────────────────
html_files = [
    f for f in os.listdir(ROOT)
    if f.lower().endswith('.html') and os.path.isfile(os.path.join(ROOT, f))
]
html_files.sort()

print(f"Processing {len(html_files)} HTML files...\n")
for fname in html_files:
    process_file(os.path.join(ROOT, fname))

print("\nDone!")
