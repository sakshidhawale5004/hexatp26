# Script to fix mobile view issues in all HTML files

$htmlFiles = Get-ChildItem -Path . -Filter "*.html" -Recurse | Select-Object -ExpandProperty FullName

# Issue 1: Fix hamburger menu icon visibility
$oldMobileNavToggle = @"
        .mobile-nav-toggle { display: none; position: absolute; right: 5%; top: 20px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; width: 44px; height: 44px; min-width: 44px; min-height: 44px; }
        .mobile-nav-toggle::before { content: ''; display: block; width: 24px; height: 2px; background: #f5c400; position: relative; box-shadow: 0 8px 0 #f5c400, 0 16px 0 #f5c400; }
"@

$newMobileNavToggle = @"
        .mobile-nav-toggle { display: none; position: absolute; right: 15px; top: 15px; background: none; border: none; color: #f5c400; font-size: 28px; cursor: pointer; z-index: 1001; padding: 10px; line-height: 1; width: 50px; height: 50px; min-width: 50px; min-height: 50px; }
        .mobile-nav-toggle::before { content: ''; display: block; width: 28px; height: 3px; background: #f5c400; position: relative; box-shadow: 0 10px 0 #f5c400, 0 20px 0 #f5c400; }
"@

# Issue 2: Fix hero section mobile layout
$oldHeroMobile = @"
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 20px 60px !important;
            }
.hero-section h1 {
                font-size: 28px !important;
                line-height: 1.2 !important;
            }
.hero-section p {
                font-size: 14px !important;
            }
.hero-tag {
                font-size: 10px !important;
                padding: 5px 12px !important;
            }
        }
"@

$newHeroMobile = @"
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 20px 60px !important;
            }
.hero-section h1 {
                font-size: 28px !important;
                line-height: 1.2 !important;
                margin-bottom: 15px !important;
            }
.hero-section p {
                font-size: 14px !important;
                line-height: 1.5 !important;
                margin-bottom: 25px !important;
            }
.hero-tag {
                font-size: 10px !important;
                padding: 5px 12px !important;
            }
/* Stack buttons vertically on mobile */
            .hero-section .btn-main,
            .hero-section .btn-accent {
                display: block !important;
                width: 100% !important;
                max-width: 280px !important;
                margin: 10px auto !important;
                padding: 14px 24px !important;
            }
/* Hexa cards - 2 columns on mobile */
            .hexa-card {
                padding: 25px 15px !important;
            }
.hexa-card .letter {
                font-size: 40px !important;
                margin-bottom: 15px !important;
            }
.hexa-card .label {
                font-size: 11px !important;
            }
        }
"@

$fixedCount = 0
$errorCount = 0

foreach ($file in $htmlFiles) {
    try {
        $content = Get-Content -Path $file -Raw -Encoding UTF8
        $originalContent = $content
        
        # Fix Issue 1: Hamburger menu icon
        if ($content -match [regex]::Escape($oldMobileNavToggle)) {
            $content = $content -replace [regex]::Escape($oldMobileNavToggle), $newMobileNavToggle
            Write-Host "✓ Fixed hamburger icon in: $file"
        }
        
        # Fix Issue 2: Hero section mobile layout
        if ($content -match [regex]::Escape($oldHeroMobile)) {
            $content = $content -replace [regex]::Escape($oldHeroMobile), $newHeroMobile
            Write-Host "✓ Fixed hero section in: $file"
        }
        
        # Write back if changes were made
        if ($content -ne $originalContent) {
            Set-Content -Path $file -Value $content -Encoding UTF8
            $fixedCount++
        }
    }
    catch {
        Write-Host "✗ Error processing $file : $_"
        $errorCount++
    }
}

Write-Host "`n========== SUMMARY =========="
Write-Host "Total files processed: $($htmlFiles.Count)"
Write-Host "Files fixed: $fixedCount"
Write-Host "Errors: $errorCount"
