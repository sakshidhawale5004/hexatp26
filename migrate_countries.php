<?php
/**
 * Country Content Migration Script
 * 
 * This script migrates existing country HTML files into the CMS database.
 * It extracts hero sections, overview content, regulatory frameworks, and documentation cards.
 * 
 * Usage: Run this file once from your browser: hexatp.com/migrate_countries.php
 * 
 * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set execution time limit (migration may take a while)
set_time_limit(300); // 5 minutes

// Load dependencies
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/models/Country.php';
require_once __DIR__ . '/repositories/CountryRepository.php';

// Initialize
$conn = getDBConnection();
$countryRepo = new CountryRepository($conn);

// Migration results
$results = [
    'success' => [],
    'errors' => [],
    'skipped' => []
];

// Country mapping: filename => [country_name, country_code, flag_url]
$countries_to_migrate = [
    // Gulf Region
    'unitedarabemirates.html' => [
        'name' => 'United Arab Emirates',
        'code' => 'AE',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/c/cb/Flag_of_the_United_Arab_Emirates.svg'
    ],
    'Saudiarabia.html' => [
        'name' => 'Saudi Arabia',
        'code' => 'SA',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/0/0d/Flag_of_Saudi_Arabia.svg'
    ],
    'Qatar.html' => [
        'name' => 'Qatar',
        'code' => 'QA',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/6/65/Flag_of_Qatar.svg'
    ],
    'oman.html' => [
        'name' => 'Oman',
        'code' => 'OM',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/d/dd/Flag_of_Oman.svg'
    ],
    'bahrain.html' => [
        'name' => 'Bahrain',
        'code' => 'BH',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/2/2c/Flag_of_Bahrain.svg'
    ],
    'egypt.html' => [
        'name' => 'Egypt',
        'code' => 'EG',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Flag_of_Egypt.svg'
    ],
    
    // Asia
    'India.html' => [
        'name' => 'India',
        'code' => 'IN',
        'flag' => 'https://upload.wikimedia.org/wikipedia/en/4/41/Flag_of_India.svg'
    ],
    'bangladesh.html' => [
        'name' => 'Bangladesh',
        'code' => 'BD',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/f/f9/Flag_of_Bangladesh.svg'
    ],
    
    // South East Asia
    'singapore.html' => [
        'name' => 'Singapore',
        'code' => 'SG',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/4/48/Flag_of_Singapore.svg'
    ],
    'thailand.html' => [
        'name' => 'Thailand',
        'code' => 'TH',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Flag_of_Thailand.svg'
    ],
    'malaysia.html' => [
        'name' => 'Malaysia',
        'code' => 'MY',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/6/66/Flag_of_Malaysia.svg'
    ],
    'australia.html' => [
        'name' => 'Australia',
        'code' => 'AU',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/8/88/Flag_of_Australia_%28converted%29.svg'
    ],
    'indonesia.html' => [
        'name' => 'Indonesia',
        'code' => 'ID',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/9/9f/Flag_of_Indonesia.svg'
    ],
    'viethnam.html' => [
        'name' => 'Vietnam',
        'code' => 'VN',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/2/21/Flag_of_Vietnam.svg'
    ],
    
    // Africa
    'botswana.html' => [
        'name' => 'Botswana',
        'code' => 'BW',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/f/fa/Flag_of_Botswana.svg'
    ],
    'ghana.html' => [
        'name' => 'Ghana',
        'code' => 'GH',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/1/19/Flag_of_Ghana.svg'
    ],
    'kenya.html' => [
        'name' => 'Kenya',
        'code' => 'KE',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/4/49/Flag_of_Kenya.svg'
    ],
    
    // Americas
    'canada.html' => [
        'name' => 'Canada',
        'code' => 'CA',
        'flag' => 'https://upload.wikimedia.org/wikipedia/commons/d/d9/Flag_of_Canada_%28Pantone%29.svg'
    ],
    'unitedstates.html' => [
        'name' => 'United States',
        'code' => 'US',
        'flag' => 'https://upload.wikimedia.org/wikipedia/en/a/a4/Flag_of_the_United_States.svg'
    ]
];

/**
 * Extract team section (cards) from HTML
 */
function extractTeamSection($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    // Find section containing team-card
    $teamCards = $xpath->query("//div[contains(@class, 'team-card')]");
    if ($teamCards->length > 0) {
        $row = $teamCards->item(0)->parentNode->parentNode; // Get the .row container
        if ($row) {
            return $dom->saveHTML($row);
        }
    }
    return '';
}

/**
 * Extract team modals from HTML
 */
function extractTeamModals($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $modalsHtml = '';
    
    // Find all modals with id containing 'modal'
    $modals = $xpath->query("//div[contains(@class, 'modal') and contains(@id, 'modal')]");
    foreach ($modals as $modal) {
        $modalsHtml .= $dom->saveHTML($modal) . "\n";
    }
    
    return $modalsHtml;
}

/**
 * Extract hero section from HTML
 */
function extractHeroSection($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $hero = [
        'title' => '',
        'description' => ''
    ];
    
    // Find hero section
    $heroSection = $xpath->query("//section[contains(@class, 'hero')]")->item(0);
    if ($heroSection) {
        // Extract title (h1)
        $h1 = $xpath->query(".//h1", $heroSection)->item(0);
        if ($h1) {
            $hero['title'] = trim(strip_tags($dom->saveHTML($h1)));
            $hero['title'] = preg_replace('/\s+/', ' ', $hero['title']); // Normalize whitespace
        }
        
        // Extract description (first p tag)
        $p = $xpath->query(".//p", $heroSection)->item(0);
        if ($p) {
            $hero['description'] = trim(strip_tags($dom->saveHTML($p)));
        }

        // Pattern 1: .hero style
        preg_match('/\.hero\s*\{[^}]*background:.*?url\([\'"]?(.*?)[\'"]?\)/s', $html, $matches);
        
        // Pattern 2: inline style on .hero
        if (!isset($matches[1])) {
            preg_match('/class="[^"]*hero[^"]*"[^>]*style="[^"]*background-image:\s*url\([\'"]?(.*?)[\'"]?\)/i', $html, $matches);
        }
        
        // Pattern 3: Any large image in the first section (Fallback)
        if (!isset($matches[1])) {
            preg_match('/<section[^>]*class="[^"]*hero[^"]*"[^>]*>.*?<img[^>]*src="([^"]*)"/is', $html, $matches);
        }

        if (isset($matches[1])) {
            $hero['bg_image'] = trim($matches[1]);
        }
    }
    
    return $hero;
}

/**
 * Extract overview section from HTML
 */
function extractOverview($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $overview = [
        'left' => '',
        'right' => ''
    ];
    
    // Find section with "Landscape" or "Overview" in title
    $sections = $xpath->query("//section[.//h2[contains(text(), 'Landscape') or contains(text(), 'Overview')]]");
    if ($sections->length > 0) {
        $section = $sections->item(0);
        
        // Find all paragraphs in columns
        $columns = $xpath->query(".//div[contains(@class, 'col-lg-6')]", $section);
        
        if ($columns->length >= 1) {
            $leftCol = $columns->item(0);
            $leftP = $xpath->query(".//p", $leftCol);
            if ($leftP->length > 0) {
                $overview['left'] = trim($dom->saveHTML($leftP->item(0)));
            }
        }
        
        if ($columns->length >= 2) {
            $rightCol = $columns->item(1);
            $rightP = $xpath->query(".//p", $rightCol);
            if ($rightP->length > 0) {
                $overview['right'] = trim($dom->saveHTML($rightP->item(0)));
            }
        }
    }
    
    return $overview;
}

/**
 * Extract regulatory frameworks from HTML
 */
function extractRegulatoryFrameworks($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $frameworks = [];
    
    // Pattern 1: .reg-box (used in KSA)
    $nodes = $xpath->query("//div[contains(@class, 'reg-box')]");
    
    // Pattern 2: .glass-card (used in some other pages)
    if ($nodes->length === 0) {
        $nodes = $xpath->query("//section[contains(., 'Compliance') or contains(., 'Regulatory')]//div[contains(@class, 'glass-card')]");
    }
    
    // Pattern 3: Generic row/col search
    if ($nodes->length === 0) {
        $nodes = $xpath->query("//h2[contains(., 'Regulatory') or contains(., 'Compliance')]/following::div[contains(@class, 'row')][1]//div[contains(@class, 'col')]");
    }

    foreach ($nodes as $index => $node) {
        $h5 = $xpath->query(".//h5", $node)->item(0);
        $p = $xpath->query(".//p", $node)->item(0);
        
        if ($h5 && $p) {
            $frameworks[] = [
                'title' => trim(strip_tags($dom->saveHTML($h5))),
                'description' => trim($dom->saveHTML($p)),
                'display_order' => $index + 1
            ];
        }
        if (count($frameworks) >= 3) break;
    }
    
    // Fallback if still empty
    if (empty($frameworks)) {
        for ($i = 1; $i <= 3; $i++) {
            $frameworks[] = [
                'title' => "Regulatory Detail $i",
                'description' => "Local regulatory framework details coming soon.",
                'display_order' => $i
            ];
        }
    }
    
    return array_slice($frameworks, 0, 3); // Ensure exactly 3
}

/**
 * Extract documentation cards from HTML
 */
function extractDocumentationCards($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $cards = [];
    
    // Find section with "Documentation" or "Pillars" in title
    $sections = $xpath->query("//section[.//h2[contains(text(), 'Documentation') or contains(text(), 'Pillars')]]");
    if ($sections->length > 0) {
        $section = $sections->item(0);
        
        // Find all glass-card divs
        $glassCards = $xpath->query(".//div[contains(@class, 'glass-card')]", $section);
        
        $order = 1;
        foreach ($glassCards as $card) {
            $title = '';
            $shortDesc = '';
            $detailedContent = '';
            
            // Extract title from arrow span
            $arrow = $xpath->query(".//span[contains(@class, 'arrow')]", $card)->item(0);
            if ($arrow) {
                $titleText = trim(strip_tags($dom->saveHTML($arrow)));
                // Remove arrow character
                $title = trim(str_replace(['▶', '►', '▼'], '', $titleText));
            }
            
            // Extract short description (first p after arrow)
            $firstP = $xpath->query(".//p[not(ancestor::div[contains(@class, 'content')])]", $card)->item(0);
            if ($firstP) {
                $shortDesc = trim($dom->saveHTML($firstP));
            }
            
            // Extract detailed content from content div
            $contentDiv = $xpath->query(".//div[contains(@class, 'content')]", $card)->item(0);
            if ($contentDiv) {
                $contentPs = $xpath->query(".//p", $contentDiv);
                $detailedParts = [];
                foreach ($contentPs as $p) {
                    $detailedParts[] = trim($dom->saveHTML($p));
                }
                $detailedContent = implode("\n", $detailedParts);
            }
            
            if ($title) {
                $cards[] = [
                    'title' => $title,
                    'short_description' => $shortDesc ?: '<p>Click to expand for details.</p>',
                    'detailed_content' => $detailedContent ?: '<p>Detailed information coming soon.</p>',
                    'display_order' => $order++
                ];
            }
        }
    }
    
    return $cards;
}

/**
 * Extract services section from HTML
 */
function extractServices($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $services = [];
    
    // Find section with "Services" in title
    $sections = $xpath->query("//section[.//h2[contains(text(), 'Services')]]");
    if ($sections->length > 0) {
        $section = $sections->item(0);
        
        // Find all glass-card divs
        $boxes = $xpath->query(".//div[contains(@class, 'glass-card')]", $section);
        
        $order = 1;
        foreach ($boxes as $box) {
            $title = '';
            $description = '';
            
            // Extract title (h5)
            $h5 = $xpath->query(".//h5", $box)->item(0);
            if ($h5) {
                $title = trim(strip_tags($dom->saveHTML($h5)));
            }
            
            // Extract description (p)
            $p = $xpath->query(".//p", $box)->item(0);
            if ($p) {
                $description = trim($dom->saveHTML($p));
            }
            
            if ($title) {
                $services[] = [
                    'title' => $title,
                    'description' => $description,
                    'display_order' => $order++
                ];
            }
        }
    }
    
    return $services;
}

/**
 * Extract CTA section from HTML
 */
function extractCTA($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $cta = [
        'title' => '',
        'button_text' => ''
    ];
    
    $section = $xpath->query("//section[contains(@class, 'cta')]")->item(0);
    if ($section) {
        $h2 = $xpath->query(".//h2", $section)->item(0);
        if ($h2) {
            $cta['title'] = trim(strip_tags($dom->saveHTML($h2)));
        }
        
        $a = $xpath->query(".//a", $section)->item(0);
        if ($a) {
            $cta['button_text'] = trim(strip_tags($dom->saveHTML($a)));
        }
    }
    
    return $cta;
}

/**
 * Extract footer/contact section from HTML
 */
function extractFooter($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $footer = [
        'title' => '',
        'email' => 'connect@hexatp.com'
    ];
    
    $section = $xpath->query("//section[contains(@class, 'contact-footer')]")->item(0);
    if ($section) {
        $h2 = $xpath->query(".//h2", $section)->item(0);
        if ($h2) {
            $footer['title'] = trim(strip_tags($dom->saveHTML($h2)));
        }
        
        $a = $xpath->query(".//a[contains(@href, 'mailto:')]", $section)->item(0);
        if ($a) {
            $footer['email'] = trim(strip_tags($dom->saveHTML($a)));
        }
    }
    
    return $footer;
}

/**
 * Migrate a single country
 */
function migrateCountry($filename, $countryData, $countryRepo) {
    global $results;
    
    $filepath = __DIR__ . '/' . $filename;
    
    // Check if file exists
    if (!file_exists($filepath)) {
        $results['errors'][] = "$filename: File not found";
        return false;
    }
    
    // Read HTML content
    $html = file_get_contents($filepath);
    if (!$html) {
        $results['errors'][] = "$filename: Could not read file";
        return false;
    }
    
    try {
        global $conn;
        
        // Extract content
        $hero = extractHeroSection($html);
        echo "<b>DEBUG for $filename:</b><br>";
        echo "- Hero Title: " . ($hero['title'] ?: 'Not found') . "<br>";
        echo "- BG Image: " . ($hero['bg_image'] ?: 'Not found') . "<br>";
        
        $overview = extractOverview($html);
        $frameworks = extractRegulatoryFrameworks($html);
        echo "- Frameworks Found: " . count($frameworks) . "<br>";
        
        $cards = extractDocumentationCards($html);
        $services = extractServices($html);
        $cta = extractCTA($html);
        $footer = extractFooter($html);
        
        // Create Country object
        $country = new Country();
        $country->country_name = $countryData['name'];
        $country->country_code = $countryData['code'];
        $country->flag_url = $countryData['flag'];
        $country->hero_title = $hero['title'] ?: "Transfer Pricing " . $countryData['name'];
        $country->hero_description = $hero['description'] ?: "Navigate " . $countryData['name'] . "'s transfer pricing requirements.";
        $country->hero_bg_image = $hero['bg_image'] ?? null;
        $country->meta_title = $countryData['name'] . " Transfer Pricing | HexaTP";
        $country->meta_description = "Complete guide to " . $countryData['name'] . " transfer pricing regulations and compliance.";
        $country->cta_title = $cta['title'] ?: "Seeking TP Advisory in " . $countryData['name'] . "?";
        $country->cta_button_text = $cta['button_text'] ?: "Contact Specialist";
        $country->footer_title = $footer['title'] ?: "Ready for a Professional Consultation?";
        $country->footer_email = $footer['email'] ?: "connect@hexatp.com";
        $country->status = 'published'; // Set as published
        
        // Check if country already exists and delete it for a fresh migration
        $check_stmt = $conn->prepare("SELECT id FROM countries WHERE country_name = ?");
        $check_stmt->bind_param('s', $countryData['name']);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        if ($row = $check_res->fetch_assoc()) {
            $old_id = $row['id'];
            $conn->query("DELETE FROM countries WHERE id = $old_id");
        }
        $check_stmt->close();
        
        // Save country to database
        $country_id = $countryRepo->create($country);
        
        if (!$country_id) {
            $results['errors'][] = "$filename: Failed to create country record";
            return false;
        }
        
        // Save overview
        if ($overview['left'] || $overview['right']) {
            $stmt = $conn->prepare("
                INSERT INTO country_overview (country_id, overview_text_left, overview_text_right)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param('iss', $country_id, $overview['left'], $overview['right']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Save regulatory frameworks
        foreach ($frameworks as $framework) {
            $stmt = $conn->prepare("
                INSERT INTO regulatory_frameworks (country_id, title, description, display_order)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param('issi', 
                $country_id, 
                $framework['title'], 
                $framework['description'], 
                $framework['display_order']
            );
            $stmt->execute();
            $stmt->close();
        }
        
        // Save services
        foreach ($services as $service) {
            $stmt = $conn->prepare("
                INSERT INTO country_services (country_id, title, description, display_order)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param('issi', 
                $country_id, 
                $service['title'], 
                $service['description'], 
                $service['display_order']
            );
            $stmt->execute();
            $stmt->close();
        }

        // Save Team Partials
        $slug = strtolower(str_replace('.html', '', $filename));
        $teamHtml = extractTeamSection($html);
        $modalsHtml = extractTeamModals($html);
        
        $layoutsDir = __DIR__ . "/custom_layouts";
        if (!is_dir($layoutsDir)) {
            mkdir($layoutsDir, 0755, true);
        }

        if ($teamHtml) {
            if (file_put_contents("$layoutsDir/{$slug}_team.html", $teamHtml)) {
                $results['success'][] = "Saved team layout for $slug";
            } else {
                $results['errors'][] = "Failed to save team layout for $slug (Check permissions)";
            }
        }
        if ($modalsHtml) {
            file_put_contents("$layoutsDir/{$slug}_modals.html", $modalsHtml);
        }
        
        $results['success'][] = "$filename: Successfully migrated {$countryData['name']} (ID: $country_id)";
        return true;
        
    } catch (Exception $e) {
        $results['errors'][] = "$filename: " . $e->getMessage();
        return false;
    }
}

// Start migration
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Country Migration | HexaTP CMS</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #050a14; color: var(--text-main); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px 20px; }
        .container { max-width: 900px; }
        .card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 30px; margin-bottom: 20px; }
        .success { color: #4caf50; }
        .error { color: #ff6b6b; }
        .skipped { color: #ff9800; }
        h1 { color: #f5c400; margin-bottom: 30px; }
        .progress { height: 30px; background: rgba(255,255,255,0.1); }
        .progress-bar { background: #f5c400; }
        .btn-primary { background: #f5c400; color: #000; border: none; font-weight: 600; }
        .btn-primary:hover { background: #ffd700; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🌍 Country Content Migration</h1>
        <div class='card'>
            <h3>Migration Progress</h3>
            <div class='progress mb-3'>
                <div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 0%' id='progressBar'>0%</div>
            </div>
            <div id='status'>Starting migration...</div>
        </div>
        <div class='card' id='results' style='display:none;'>
            <h3>Migration Results</h3>
            <div id='resultsContent'></div>
        </div>
    </div>
    <script>
        let processed = 0;
        const total = " . count($countries_to_migrate) . ";
        
        function updateProgress() {
            processed++;
            const percent = Math.round((processed / total) * 100);
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressBar').textContent = percent + '%';
        }
    </script>
";

echo "<script>document.getElementById('status').innerHTML = 'Migrating countries...';</script>";
flush();

// Migrate each country
foreach ($countries_to_migrate as $filename => $countryData) {
    echo "<script>document.getElementById('status').innerHTML += '<br>Processing {$countryData['name']}...';</script>";
    flush();
    
    migrateCountry($filename, $countryData, $countryRepo);
    
    echo "<script>updateProgress();</script>";
    flush();
}

// Display results
echo "<script>
    document.getElementById('results').style.display = 'block';
    let html = '';
    
    // Success
    html += '<h4 class=\"success\">✅ Successfully Migrated (" . count($results['success']) . ")</h4>';
    html += '<ul>';
";

foreach ($results['success'] as $success) {
    echo "html += '<li class=\"success\">" . addslashes($success) . "</li>';";
}

echo "html += '</ul>';";

// Errors
if (!empty($results['errors'])) {
    echo "html += '<h4 class=\"error\">❌ Errors (" . count($results['errors']) . ")</h4>';";
    echo "html += '<ul>';";
    foreach ($results['errors'] as $error) {
        echo "html += '<li class=\"error\">" . addslashes($error) . "</li>';";
    }
    echo "html += '</ul>';";
}

echo "
    html += '<hr>';
    html += '<h4>📊 Summary</h4>';
    html += '<p>Total countries processed: <strong>" . count($countries_to_migrate) . "</strong></p>';
    html += '<p>Successfully migrated: <strong class=\"success\">" . count($results['success']) . "</strong></p>';
    html += '<p>Errors: <strong class=\"error\">" . count($results['errors']) . "</strong></p>';
    html += '<hr>';
    html += '<a href=\"admin/countries_list.php\" class=\"btn btn-primary mt-3\">View Countries in CMS</a>';
    html += '<a href=\"admin/dashboard.php\" class=\"btn btn-secondary mt-3 ms-2\">Go to Dashboard</a>';
    
    document.getElementById('resultsContent').innerHTML = html;
    document.getElementById('status').innerHTML = '<strong>Migration Complete!</strong>';
</script>
";

echo "</body></html>";

// Close connection
$conn->close();
?>
