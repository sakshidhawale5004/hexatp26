import glob
import re

for f in glob.glob('*.html'):
    with open(f, 'r', encoding='utf-8', errors='ignore') as file:
        content = file.read()
    
    # We want to find the buttons div and move it below the ul
    # The div looks like: <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;"> ... </div>
    # It is right before <ul> inside <div class="mobile-menu" id="mobileMenu">
    
    # Let's extract it safely using regex.
    pattern = re.compile(r'(<button class="close-menu"[^>]*>.*?<\/button>\s*)(<div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">\s*<a href="contact.html"[^>]*>.*?<\/a>\s*<a href="login.html"[^>]*>.*?<\/a>\s*<\/div>\s*)(<ul[^>]*>.*?<\/ul>)', re.DOTALL)
    
    if pattern.search(content):
        def replacer(match):
            close_btn = match.group(1)
            buttons_div = match.group(2)
            ul_list = match.group(3)
            
            # modify buttons_div margin
            buttons_div = buttons_div.replace('margin-bottom: 20px;', 'margin-top: 30px; margin-bottom: 30px;')
            
            return close_btn + ul_list + '\n            ' + buttons_div.strip() + '\n        '
            
        new_content = pattern.sub(replacer, content)
        
        if new_content != content:
            with open(f, 'w', encoding='utf-8') as file:
                file.write(new_content)
            print(f'Updated {f}')
    else:
        print(f'Pattern not found in {f}')
