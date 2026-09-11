import os
import re

files = [
    "KingdomofSaudiArabia.html",
    "singapore.html",
    "kenya.html",
    "egypt.html"
]

# The regex will match <section ...> that contains "Regulatory & OECD Profile Links"
# It uses non-greedy .*? to match until the closing </section>
pattern = re.compile(r'<section class="py-5" style="background-color: var\(--bg-subtle\);">(?:(?!<section).)*?<h2 class="section-title text-center">Regulatory & OECD Profile Links</h2>(?:(?!</section>).)*?</section>', re.DOTALL)

for file in files:
    if os.path.exists(file):
        with open(file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        new_content, num_subs = pattern.subn('', content)
        
        if num_subs > 0:
            with open(file, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Removed duplicate section from {file}")
        else:
            print(f"No duplicate found in {file}")
