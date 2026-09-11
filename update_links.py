import os
import re

countries = {
    "KingdomofSaudiArabia.html": {
        "name": "Saudi Arabia",
        "reg_link": "https://zatca.gov.sa/en/RulesRegulations/Pages/TransferPricing.aspx",
        "reg_desc": "Official ZATCA Transfer Pricing Regulations",
        "oecd_link": "https://www.oecd.org/content/dam/oecd/en/topics/policy-sub-issues/transfer-pricing/transfer-pricing-country-profile-saudi-arabia.pdf"
    },
    "singapore.html": {
        "name": "Singapore",
        "reg_link": "https://www.iras.gov.sg/taxes/corporate-income-tax/specific-topics/transfer-pricing",
        "reg_desc": "Official IRAS Transfer Pricing Guidelines",
        "oecd_link": "https://www.oecd.org/content/dam/oecd/en/topics/policy-sub-issues/transfer-pricing/transfer-pricing-country-profile-singapore.pdf"
    },
    "unitedstates.html": {
        "name": "United States",
        "reg_link": "https://www.irs.gov/businesses/corporations/transfer-pricing",
        "reg_desc": "Official IRS Transfer Pricing Operations",
        "oecd_link": "https://www.oecd.org/content/dam/oecd/en/topics/policy-sub-issues/transfer-pricing/transfer-pricing-country-profile-united-states.pdf"
    },
    "kenya.html": {
        "name": "Kenya",
        "reg_link": "https://www.kra.go.ke/en/business/companies-partnerships/companies-partnerships-pin/large-taxpayers/transfer-pricing",
        "reg_desc": "Official KRA Transfer Pricing Rules",
        "oecd_link": "https://www.oecd.org/content/dam/oecd/en/topics/policy-sub-issues/transfer-pricing/transfer-pricing-country-profile-kenya.pdf"
    },
    "indonesia.html": {
        "name": "Indonesia",
        "reg_link": "https://www.pajak.go.id/sites/default/files/2020-08/REGULATION%20OF%20THE%20MINISTER%20OF%20FINANCE%20NUMBER%2022PMK.032020.pdf",
        "reg_desc": "Official PMK.22 Transfer Pricing Regulation (PDF)",
        "oecd_link": "https://www.oecd.org/content/dam/oecd/en/topics/policy-sub-issues/transfer-pricing/transfer-pricing-country-profile-indonesia.pdf"
    },
    "egypt.html": {
        "name": "Egypt",
        "reg_link": "https://www.eta.gov.eg/en/node/5285",
        "reg_desc": "Official ETA Transfer Pricing Guidelines",
        "oecd_link": "https://www.oecd.org/content/dam/oecd/en/topics/policy-sub-issues/transfer-pricing/transfer-pricing-country-profile-egypt.pdf"
    },
    "canada.html": {
        "name": "Canada",
        "reg_link": "https://www.canada.ca/en/revenue-agency/services/tax/international-non-residents/transfer-pricing.html",
        "reg_desc": "Official CRA Transfer Pricing Rules",
        "oecd_link": "https://www.oecd.org/content/dam/oecd/en/topics/policy-sub-issues/transfer-pricing/transfer-pricing-country-profile-canada.pdf"
    }
}

template = """    <section class="py-5" style="background-color: var(--bg-subtle);">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-6">
                    <a href="{reg_link}" target="_blank" class="glass-card d-block text-decoration-none h-100 text-center" style="background-color: var(--text-main); border: 2px solid var(--accent);">
                        <h5 class="mb-2" style="color: var(--accent); font-weight: 700;">{name} TP Regulation</h5>
                        <p class="mb-0" style="color: #e2e8f0; font-size: 0.95rem;">{reg_desc}</p>
                    </a>
                </div>
                <div class="col-lg-6">
                    <a href="{oecd_link}" target="_blank" class="glass-card d-block text-decoration-none h-100 text-center" style="background-color: var(--text-main); border: 2px solid var(--accent);">
                        <h5 class="mb-2" style="color: var(--accent); font-weight: 700;">OECD Country Profile</h5>
                        <p class="mb-0" style="color: #e2e8f0; font-size: 0.95rem;">{name} Transfer Pricing Profile (PDF)</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

"""

for file, data in countries.items():
    if not os.path.exists(file):
        print(f"File {file} not found!")
        continue
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
        
    # Prevent duplicate injection
    if "OECD Country Profile" in content and "background-color: var(--text-main)" in content:
        print(f"Skipping {file} as it seems already updated.")
        continue
        
    match = re.search(r'<section[^>]*>\s*<div class="container">\s*<h2 class="section-title text-center">Our Services', content)
    if match:
        insert_pos = match.start()
    else:
        match = re.search(r'<section class="cta"', content)
        if match:
            insert_pos = match.start()
        else:
            print(f"Could not find insertion point for {file}")
            continue
            
    new_content = content[:insert_pos] + template.format(**data) + content[insert_pos:]
    with open(file, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f"Updated {file}")
