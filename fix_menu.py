import glob
import re

for f in glob.glob('*.html'):
    with open(f, 'r', encoding='utf-8', errors='ignore') as file:
        content = file.read()
    
    original = content
    # Fix header > img.logo-img selector
    content = content.replace('header > img.logo-img', 'header > a')
    
    # Fix z-index for mobile overlay
    content = re.sub(r'\.mobile-overlay\s*\{([^\}]*)z-index:\s*999;([^\}]*)\}', r'.mobile-overlay {\1z-index: 10000;\2}', content)
    
    # Fix z-index for mobile menu
    content = re.sub(r'\.mobile-menu\s*\{([^\}]*)z-index:\s*1000;([^\}]*)\}', r'.mobile-menu {\1z-index: 10001;\2}', content)
    
    # Replace unicode hamburger with Bootstrap Icon
    # Remove the ::before pseudo-element
    content = re.sub(r'\.mobile-nav-toggle::before\s*\{[^}]*content:\s*\'[^\']+\';[^}]*\}', '', content)
    
    # Inject the icon inside the button if it doesn't have it
    if '<i class="bi bi-list"></i>' not in content:
        content = content.replace('<button class="mobile-nav-toggle" onclick="openMobileMenu()" aria-label="Open menu"></button>', '<button class="mobile-nav-toggle" onclick="openMobileMenu()" aria-label="Open menu"><i class="bi bi-list"></i></button>')
        content = content.replace('<button class="mobile-nav-toggle" onclick="openMobileMenu()">&#9776;</button>', '<button class="mobile-nav-toggle" onclick="openMobileMenu()" aria-label="Open menu"><i class="bi bi-list"></i></button>')
    
    # Fix top overlapping for index.html only, since only it has the ticker
    if f == 'index.html':
        content = content.replace('header { width: 100% !important; padding: 12px 15px !important; top: 0 !important;', 'header { width: 100% !important; padding: 12px 15px !important; top: 38px !important;')
    
    if content != original:
        with open(f, 'w', encoding='utf-8') as file:
            file.write(content)
        print(f'Updated {f}')
