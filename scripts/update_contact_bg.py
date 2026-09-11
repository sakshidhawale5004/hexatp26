import os
import glob

html_files = glob.glob('*.html')
target_str = "background: url('contactsection.bg.jpeg') center/cover no-repeat;"
replacement_str = "background: linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85)), url('contactsection.bg.jpeg') center/cover no-repeat;"

count = 0
for file in html_files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if target_str in content:
        content = content.replace(target_str, replacement_str)
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        count += 1
        print(f"Updated {file}")

print(f"Total files updated: {count}")
