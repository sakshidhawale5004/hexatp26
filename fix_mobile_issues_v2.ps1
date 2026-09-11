# Script to fix mobile view issues in all HTML files - Version 2 (handles variations)

$htmlFiles = Get-ChildItem -Path . -Filter "*.html" -Recurse | Select-Object -ExpandProperty FullName

$fixedCount = 0
$errorCount = 0
$skippedCount = 0

foreach ($file in $htmlFiles) {
    try {
        $content = Get-Content -Path $file -Raw -Encoding UTF8
        $originalContent = $content
        $fileFixed = $false
        
        # Fix Issue 1: Hamburger menu icon - Handle multiple variations
        # Pattern 1: With width/height attributes
        if ($content -match 'mobile-nav-toggle.*?right:\s*5%.*?top:\s*20px') {
            $content = $content -replace 'right:\s*5%', 'right: 15px'
            $content = $content -replace '(?<=mobile-nav-toggle.*?)top:\s*20px', 'top: 15px'
            $fileFixed = $true
        }
        
        # Fix hamburger lines - increase thickness and spacing
        if ($content -match 'mobile-nav-toggle::before.*?width:\s*24px.*?height:\s*2px') {
            $content = $content -replace '(?<=mobile-nav-toggle::before.*?)width:\s*24px', 'width: 28px'
            $content = $content -replace '(?<=mobile-nav-toggle::before.*?)height:\s*2px', 'height: 3px'
            $content = $content -replace 'box-shadow:\s*0\s*8px\s*0\s*#f5c400,\s*0\s*16px\s*0\s*#f5c400', 'box-shadow: 0 10px 0 #f5c400, 0 20px 0 #f5c400'
            $fileFixed = $true
        }
        
        # Increase button size
        if ($content -match 'mobile-nav-toggle.*?width:\s*44px.*?height:\s*44px') {
            $content = $content -replace '(?<=mobile-nav-toggle.*?)width:\s*44px', 'width: 50px'
            $content = $content -replace '(?<=mobile-nav-toggle.*?)height:\s*44px', 'height: 50px'
            $content = $content -replace '(?<=mobile-nav-toggle.*?)min-width:\s*44px', 'min-width: 50px'
            $content = $content -replace '(?<=mobile-nav-toggle.*?)min-height:\s*44px', 'min-height: 50px'
            $fileFixed = $true
        }
        
        # Write back if changes were made
        if ($content -ne $originalContent) {
            Set-Content -Path $file -Value $content -Encoding UTF8
            Write-Host "✓ Fixed: $file"
            $fixedCount++
        } else {
            $skippedCount++
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
Write-Host "Files skipped: $skippedCount"
Write-Host "Errors: $errorCount"
