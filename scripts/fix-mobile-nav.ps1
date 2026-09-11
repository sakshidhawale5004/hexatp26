# Fix mobile navigation menu across all non-index HTML pages
# Issues:
# 1. Hamburger icon invisible (content: '' with yellow-on-yellow lines)
# 2. Missing mobile header layout rules (position: static, order, display: none for btn-main)
# 3. Missing full-width header rules for mobile

$projectDir = "c:\Users\Sakshi\Downloads\hexatp-main\hexatp-main"
$htmlFiles = Get-ChildItem -Path $projectDir -Filter "*.html" -Recurse | 
    Where-Object { $_.Name -ne "index.html" -and $_.FullName -notlike "*\.kiro\*" -and $_.FullName -notlike "*node_modules*" -and $_.FullName -notlike "*custom_layouts*" }

$fixCount = 0

foreach ($file in $htmlFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $modified = $false
    
    # Fix 1: Replace invisible hamburger icon (yellow lines on yellow background)
    # Change from: content: ''; display: block; width: 28px; height: 3px; background: #f5c400; position: relative; box-shadow: 0 10px 0 #f5c400, 0 20px 0 #f5c400;
    # Change to: content: '☰'; display: inline-block; font-size: 24px; font-weight: bold;
    $oldToggleBefore = ".mobile-nav-toggle::before { content: ''; display: block; width: 28px; height: 3px; background: #f5c400; position: relative; box-shadow: 0 10px 0 #f5c400, 0 20px 0 #f5c400; }"
    $newToggleBefore = ".mobile-nav-toggle::before { content: '☰'; display: inline-block; font-size: 24px; font-weight: bold; }"
    
    if ($content -match [regex]::Escape($oldToggleBefore)) {
        $content = $content -replace [regex]::Escape($oldToggleBefore), $newToggleBefore
        $modified = $true
        Write-Host "  [FIX 1] Fixed hamburger icon in $($file.Name)"
    }
    
    # Fix 2: Add missing mobile layout rules for header
    # Need to add these rules to the @media (max-width: 768px) block inside HEADER STYLES
    # Look for the pattern in the HEADER STYLES section
    $oldMobileBlock768 = 'header { width: 95% !important; padding: 8px 12px !important; top: 10px !important; } header > a.btn-main { order: -1; margin-right: auto; padding: 6px 10px !important; font-size: 10px !important; white-space: nowrap; }'
    $newMobileBlock768 = 'header { width: 100% !important; padding: 12px 15px !important; top: 0 !important; left: 0 !important; transform: none !important; border-radius: 0 !important; justify-content: space-between !important; align-items: center !important; max-width: 100% !important; }' + "`r`n" + '            header > a.btn-main { display: none !important; }' + "`r`n" + '            header > img.logo-img { order: 1; margin: 0 !important; }' + "`r`n" + '            .mobile-nav-toggle { order: 2; position: static !important; right: auto !important; top: auto !important; }'
    
    if ($content -match [regex]::Escape($oldMobileBlock768)) {
        $content = $content -replace [regex]::Escape($oldMobileBlock768), $newMobileBlock768
        $modified = $true
        Write-Host "  [FIX 2] Fixed mobile header layout in $($file.Name)"
    }

    # Fix 2b: Also handle variant without the btn-main ordering
    $oldMobileBlock768b = 'header { width: 95% !important; padding: 8px 12px !important; top: 10px !important; }'
    if (-not $modified -or ($content -match [regex]::Escape($oldMobileBlock768b) -and $content -notmatch [regex]::Escape('header > a.btn-main { display: none !important; }'))) {
        # Only do this if we haven't already fixed it above
        if ($content -match [regex]::Escape($oldMobileBlock768b) -and $content -notmatch [regex]::Escape('header > a.btn-main { display: none !important; }')) {
            $content = $content -replace [regex]::Escape($oldMobileBlock768b), $newMobileBlock768
            $modified = $true
            Write-Host "  [FIX 2b] Fixed mobile header layout (variant) in $($file.Name)"
        }
    }
    
    if ($modified) {
        [System.IO.File]::WriteAllText($file.FullName, $content, [System.Text.Encoding]::UTF8)
        $fixCount++
        Write-Host "  SAVED: $($file.Name)" -ForegroundColor Green
    } else {
        Write-Host "  SKIPPED: $($file.Name) (no matching patterns found)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "=== DONE: Fixed $fixCount files ===" -ForegroundColor Cyan
