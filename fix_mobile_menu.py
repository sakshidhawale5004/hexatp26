#!/usr/bin/env python3
"""
Fix mobile menu issues across all HTML files:
1. Replace hamburger HTML entity with CSS-based icon
2. Fix South East Asia visibility by improving CSS
3. Ensure all menu items are properly visible
"""

import os
import re
from pathlib import Path

# Get all HTML files
html_dir = Path('.')
html_files = list(html_dir.glob('*.html'))

print(f"Found {len(html_files)} HTML files to process")

# CSS fixes to apply
OLD_MOBILE_NAV_TOGGLE_CSS = r'\.mobile-nav-toggle \{ display: none; position: absolute; right: 5%; top: 20px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; \}'

NEW_MOBILE_NAV_TOGGLE_CSS = '.mobile-nav-toggle { display: none; position: absolute; right: 5%; top: 20px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; width: 44px; height: 44px; min-width: 44px; min-height: 44px; }\n        .mobile-nav-toggle::before { content: \'\'; display: block; width: 24px; height: 2px; background: #f5c400; position: relative; box-shadow: 0 8px 0 #f5c400, 0 16px 0 #f5c400; }'

OLD_MOBILE_SUBMENU_CSS = r'\.mobile-submenu \{ padding-left: 20px; margin-top: 10px; display: none; border-left: 2px solid rgba\(245,196,0,0\.3\); \}\s*\.mobile-submenu\.active \{ display: block; animation: slideDown 0\.3s ease; \}'

NEW_MOBILE_SUBMENU_CSS = '.mobile-submenu { padding-left: 20px; margin-top: 10px; display: none; border-left: 2px solid rgba(245,196,0,0.3); max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }\n        .mobile-submenu.active { display: block; max-height: 2000px; animation: slideDown 0.3s ease; }'

# HTML fixes
OLD_HAMBURGER_BUTTON = r'<button class="mobile-nav-toggle" onclick="openMobileMenu\(\)" aria-label="Open menu">&#9776;</button>'
NEW_HAMBURGER_BUTTON = '<button class="mobile-nav-toggle" onclick="openMobileMenu()" aria-label="Open menu"></button>'

fixed_count = 0
for html_file in html_files:
    try:
        with open(html_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        # Fix 1: Replace hamburger button HTML entity with empty button (CSS will handle icon)
        content = re.sub(OLD_HAMBURGER_BUTTON, NEW_HAMBURGER_BUTTON, content)
        
        # Fix 2: Update mobile nav toggle CSS to use CSS-based hamburger icon
        if '.mobile-nav-toggle { display: none; position: absolute; right: 5%; top: 20px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; }' in content:
            content = content.replace(
                '.mobile-nav-toggle { display: none; position: absolute; right: 5%; top: 20px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; }',
                NEW_MOBILE_NAV_TOGGLE_CSS
            )
        
        # Fix 3: Update mobile submenu CSS to ensure visibility
        if '.mobile-submenu { padding-left: 20px; margin-top: 10px; display: none; border-left: 2px solid rgba(245,196,0,0.3); }' in content:
            content = content.replace(
                '.mobile-submenu { padding-left: 20px; margin-top: 10px; display: none; border-left: 2px solid rgba(245,196,0,0.3); }\n        .mobile-submenu.active { display: block; animation: slideDown 0.3s ease; }',
                NEW_MOBILE_SUBMENU_CSS
            )
        
        # Write back if changes were made
        if content != original_content:
            with open(html_file, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✓ Fixed: {html_file.name}")
            fixed_count += 1
        else:
            print(f"- No changes needed: {html_file.name}")
    
    except Exception as e:
        print(f"✗ Error processing {html_file.name}: {e}")

print(f"\nCompleted! Fixed {fixed_count} files.")
