import glob
import re

for f in glob.glob('*.html'):
    with open(f, 'r', encoding='utf-8', errors='ignore') as file:
        content = file.read()
    
    # Regex to find the buttons block
    buttons_regex = r'(\s*<div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">\s*<a href="contact\.html"[^>]*>.*?<\/a>\s*<a href="login\.html"[^>]*>.*?<\/a>\s*<\/div>\s*)'
    
    match = re.search(buttons_regex, content, flags=re.DOTALL | re.IGNORECASE)
    
    if match:
        buttons_block = match.group(1)
        # Remove it from the original place
        new_content = content.replace(buttons_block, '\n')
        
        # Modify the margin
        buttons_block = buttons_block.replace('margin-bottom: 20px;', 'margin-top: 30px; margin-bottom: 30px;')
        
        # Now find the end of the mobile menu to inject it
        # The mobile menu ends with:
        #             </ul>
        #         </div>
        #         <a href="index.html">
        
        # We can look for the closing of mobileMenu
        end_menu_regex = r'(<\/ul>\s*)(<\/div>\s*<a href="index\.html">)'
        
        if re.search(end_menu_regex, new_content):
            new_content = re.sub(end_menu_regex, r'\1' + buttons_block + r'\2', new_content)
            
            with open(f, 'w', encoding='utf-8') as file:
                file.write(new_content)
            print(f'Updated {f}')
        else:
            print(f'Could not find end of mobile menu in {f}')
    else:
        print(f'Buttons not found in {f}')
