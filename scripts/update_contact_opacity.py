import os
import glob
import re

html_files = glob.glob('*.html')

old_bg_1 = "background: linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85)), url('contactsection.bg.jpeg') center/cover no-repeat;"
old_bg_2 = "background: url('contactsection.bg.jpeg') center/cover no-repeat;"

new_css = """background: none;
            position: relative;
            z-index: 1;
        }
        .contact-footer::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('contactsectionbgfinal.jpeg') center/cover no-repeat;
            opacity: 0.15; /* Reduced opacity */
            z-index: -1;"""

count = 0
for file in html_files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    updated = False
    if old_bg_1 in content:
        content = content.replace(old_bg_1, new_css)
        updated = True
    elif old_bg_2 in content:
        content = content.replace(old_bg_2, new_css)
        updated = True
        
    if updated:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        count += 1
        print(f"Updated {file}")

print(f"Total files updated: {count}")
