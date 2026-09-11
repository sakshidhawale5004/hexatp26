import glob
import re

# 1. Read index.html and extract its header, JS, and CSS
with open('index.html', 'r', encoding='utf-8') as f:
    index_content = f.read()

# Extract <header> ... </header>
header_match = re.search(r'<header>.*?</header>', index_content, re.DOTALL)
if not header_match:
    print("Could not find header in index.html")
    exit(1)
index_header = header_match.group(0)

# The CSS for mobile-submenu
css_to_add = """
<style>
.mobile-submenu { padding-left: 20px; margin-top: 10px; display: none; border-left: 2px solid rgba(245,196,0,0.3); max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
.mobile-submenu.active { display: block; max-height: 2000px; animation: slideDown 0.3s ease; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.mobile-submenu li a { font-size: 16px; padding: 10px 15px; }
.mobile-dropdown-toggle { color: var(--text-main); font-size: 18px; padding: 12px 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; transition: all 0.3s ease; user-select: none; }
.mobile-dropdown-toggle:hover { background: rgba(245,196,0,0.1); color: #f5c400; }
.mobile-dropdown-toggle::after { content: '\\25BC'; font-size: 12px; transition: transform 0.3s ease; }
.mobile-dropdown-toggle.active { color: #f5c400; }
.mobile-dropdown-toggle.active::after { transform: rotate(180deg); }
</style>
"""

# The JS for toggleSubmenu
js_to_add = """
<script>
function toggleSubmenu(submenuId, toggleElement) {
    const submenu = document.getElementById(submenuId);
    if (submenu) {
        submenu.classList.toggle('active');
        toggleElement.classList.toggle('active');
    }
}
</script>
"""

for f in glob.glob('*.html'):
    if f == 'index.html':
        continue
        
    with open(f, 'r', encoding='utf-8', errors='ignore') as file:
        content = file.read()
        
    original = content
    
    # Replace header
    content = re.sub(r'<header>.*?</header>', index_header, content, flags=re.DOTALL)
    
    # Add CSS if not present
    if 'mobile-submenu' not in content:
        # insert before </head>
        content = content.replace('</head>', css_to_add + '\n</head>')
        
    # Add JS if not present
    if 'toggleSubmenu(' not in content:
        # insert before </body>
        content = content.replace('</body>', js_to_add + '\n</body>')
        
    if content != original:
        with open(f, 'w', encoding='utf-8') as file:
            file.write(content)
        print(f'Updated {f}')
    else:
        print(f'No changes for {f}')
