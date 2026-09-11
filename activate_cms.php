<?php
/**
 * CMS Activation Script
 * 
 * This script renames existing static HTML files for countries to force 
 * the server to use the dynamic CMS (via .htaccess rewrites).
 */

$countries = [
    'unitedarabemirates.html', 'Saudiarabia.html', 'Qatar.html', 'oman.html',
    'bahrain.html', 'egypt.html', 'India.html', 'bangladesh.html',
    'singapore.html', 'thailand.html', 'malaysia.html', 'australia.html',
    'indonesia.html', 'viethnam.html', 'botswana.html', 'ghana.html',
    'kenya.html', 'canada.html', 'unitedstates.html'
];

echo "<h2>CMS Activation: Renaming Static Files</h2>";
echo "<ul>";

foreach ($countries as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $newName = $file . '.bak';
        if (rename(__DIR__ . '/' . $file, __DIR__ . '/' . $newName)) {
            echo "<li style='color: green;'>Renamed <b>$file</b> to <b>$newName</b></li>";
        } else {
            echo "<li style='color: red;'>Failed to rename <b>$file</b></li>";
        }
    } else {
        echo "<li style='color: orange;'>File <b>$file</b> not found (already moved?)</li>";
    }
}

echo "</ul>";
echo "<p><b>Next Step:</b> Visit your country pages (e.g., <a href='Saudiarabia.html'>Saudiarabia.html</a>) to verify they are now loading from the CMS.</p>";
?>
