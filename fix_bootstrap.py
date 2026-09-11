import os

files = [
    "article-oecd-chapter-vii.html",
    "article-form-48-replaces-form-3ceb.html",
    "article-tp-documentation.html",
    "article-benchmarking.html",
    "article-india-tp-rules.html",
    "article-audit-defense.html",
    "article-global-trends.html",
    "article-beps-2.html",
    "article-ecommerce-tp.html",
    "article-apa.html",
    "article-uae-compliance.html"
]

script_tag = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>\n</body>'

for f in files:
    if os.path.exists(f):
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        
        if "bootstrap.bundle.min.js" not in content:
            content = content.replace("</body>", script_tag)
            with open(f, 'w', encoding='utf-8') as file:
                file.write(content)
            print(f"Updated {f}")
        else:
            print(f"{f} already has bootstrap JS")
    else:
        print(f"{f} not found")
