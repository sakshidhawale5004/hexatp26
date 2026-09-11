$projectDir = "c:\Users\Sakshi\Downloads\hexatp-main\hexatp-main"
$htmlFiles = Get-ChildItem -Path $projectDir -Filter "*.html" -Recurse | 
    Where-Object { $_.Name -ne "index.html" -and $_.FullName -notlike "*\.kiro\*" -and $_.FullName -notlike "*node_modules*" -and $_.FullName -notlike "*custom_layouts*" }

$fixCount = 0

foreach ($file in $htmlFiles) {
    # Read the file
    $content = Get-Content -Path $file.FullName -Raw
    
    $modified = $false
    
    # Check if the file has the mojibake 'â˜°' or the actual '☰' in the CSS content property
    if ($content -match "\.mobile-nav-toggle::before\s*\{\s*content:\s*'â˜°'") {
        $content = $content -replace "\.mobile-nav-toggle::before\s*\{\s*content:\s*'â˜°'", ".mobile-nav-toggle::before { content: '\2630'"
        $modified = $true
    }
    elseif ($content -match "\.mobile-nav-toggle::before\s*\{\s*content:\s*'☰'") {
        $content = $content -replace "\.mobile-nav-toggle::before\s*\{\s*content:\s*'☰'", ".mobile-nav-toggle::before { content: '\2630'"
        $modified = $true
    }
    elseif ($content -match "\.mobile-nav-toggle::before\s*\{\s*content:\s*'[^\x00-\x7F]+'") {
        # Match any other non-ascii character in that specific CSS rule just in case it got encoded differently
        $content = $content -replace "\.mobile-nav-toggle::before\s*\{\s*content:\s*'[^\x00-\x7F]+'", ".mobile-nav-toggle::before { content: '\2630'"
        $modified = $true
    }

    if ($modified) {
        # Write back using UTF8 encoding
        [System.IO.File]::WriteAllText($file.FullName, $content, [System.Text.Encoding]::UTF8)
        Write-Host "Fixed encoding in $($file.Name)"
        $fixCount++
    }
}

Write-Host "Fixed $fixCount files."
