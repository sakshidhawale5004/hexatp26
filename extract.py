import os

files = [
    'unitedarab.html', 'Saudiarabia.html', 'Qatar.html', 'oman.html', 'bahrain.html', 'egypt.html',
    'India.html', 'singapore.html', 'malaysia.html', 'thailand.html', 'indonesia.html', 'viethnam.html',
    'bangladesh.html', 'kenya.html', 'ghana.html', 'botswana.html', 'us.html', 'canada.html', 'australia.html'
]

for f in files:
    slug = f.replace('.html', '').lower()
    if os.path.exists(f):
        with open(f, 'r', encoding='utf-8') as file: html = file.read()
        pos = html.find('class=\"team-card\"')
        if pos == -1: pos = html.find("class='team-card'")
        if pos != -1:
            sectionStart = html.rfind('<section', 0, pos)
            sectionEnd = html.find('</section>', pos)
            if sectionStart != -1 and sectionEnd != -1:
                with open(f'custom_layouts/{slug}_team.html', 'w', encoding='utf-8') as out:
                    out.write(html[sectionStart:sectionEnd + 10])
