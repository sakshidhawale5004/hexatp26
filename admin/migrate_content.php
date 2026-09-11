<?php
/**
 * Admin Content Migration
 * HexaTP Country Content Management System
 * 
 * This script migrates existing country HTML files into the CMS database.
 * It is designed to be run from the Admin Dashboard.
 */

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../models/Country.php';
require_once __DIR__ . '/../repositories/CountryRepository.php';

// Start session and check authentication
$conn = getDBConnection();
$authService = new AuthService($conn);

if (!$authService->checkSession() || $authService->getCurrentUser()->role !== 'admin') {
    header('Location: login.php');
    exit;
}

$current_user = $authService->getCurrentUser();
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
    'unitedarab.html' => [
        'name' => 'United Arab Emirates',
        'code' => 'AE',
        'flag' => 'Flag_of_the_United_Arab_Emirates.jpeg'
    ],
    'Saudiarabia.html' => [
        'name' => 'Saudi Arabia',
        'code' => 'SA',
        'flag' => 'saudi_arabia.jpeg'
    ],
    'Qatar.html' => [
        'name' => 'Qatar',
        'code' => 'QA',
        'flag' => 'Flag_of_Qatar.jpeg'
    ],
    'oman.html' => [
        'name' => 'Oman',
        'code' => 'OM',
        'flag' => 'Flag_of_Oman.jpeg'
    ],
    'bahrain.html' => [
        'name' => 'Bahrain',
        'code' => 'BH',
        'flag' => 'Flag_of_Bahrain.jpeg'
    ],
    'egypt.html' => [
        'name' => 'Egypt',
        'code' => 'EG',
        'flag' => 'Flag_of_Egypt.jpeg'
    ],
    
    // Asia
    'India.html' => [
        'name' => 'India',
        'code' => 'IN',
        'flag' => 'Flag_of_India.jpeg'
    ],
    'bangladesh.html' => [
        'name' => 'Bangladesh',
        'code' => 'BD',
        'flag' => 'FlagofBangladesh_MeaningColorsHistory_Britannica.jpeg'
    ],
    
    // South East Asia
    'singapore.html' => [
        'name' => 'Singapore',
        'code' => 'SG',
        'flag' => 'Flag_of_Singapore.jpeg'
    ],
    'thailand.html' => [
        'name' => 'Thailand',
        'code' => 'TH',
        'flag' => 'Flag_of_Thailand.jpeg'
    ],
    'malaysia.html' => [
        'name' => 'Malaysia',
        'code' => 'MY',
        'flag' => 'Flag_of_Malaysia.jpeg'
    ],
    'australia.html' => [
        'name' => 'Australia',
        'code' => 'AU',
        'flag' => 'Flag_of_Australia_converted.jpeg'
    ],
    'indonesia.html' => [
        'name' => 'Indonesia',
        'code' => 'ID',
        'flag' => 'Flag_of_Indonesia.jpeg'
    ],
    'viethnam.html' => [
        'name' => 'Vietnam',
        'code' => 'VN',
        'flag' => 'Flag_of_Vietnam.jpeg'
    ],
    
    // Africa
    'botswana.html' => [
        'name' => 'Botswana',
        'code' => 'BW',
        'flag' => 'Flag_of_Botswana.jpeg'
    ],
    'ghana.html' => [
        'name' => 'Ghana',
        'code' => 'GH',
        'flag' => 'Flag_of_Ghana.jpeg'
    ],
    'kenya.html' => [
        'name' => 'Kenya',
        'code' => 'KE',
        'flag' => 'Flag_of_Kenya.jpeg'
    ],
    
    // Americas
    'canada.html' => [
        'name' => 'Canada',
        'code' => 'CA',
        'flag' => 'Flag_of_Canada_3-2.jpeg'
    ],
    'us.html' => [
        'name' => 'United States',
        'code' => 'US',
        'flag' => 'Flag_of_the_United_States_1912-1959.jpeg'
    ]
];

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
            $hero['title'] = preg_replace('/\s+/', ' ', $hero['title']);
        }
        
        // Extract description (first p tag)
        $p = $xpath->query(".//p", $heroSection)->item(0);
        if ($p) {
            $hero['description'] = trim($dom->saveHTML($p));
            $hero['description'] = str_replace(['<p>', '</p>'], '', $hero['description']);
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
    
    $sections = $xpath->query("//section[.//h2[contains(text(), 'Landscape') or contains(text(), 'Overview')]]");
    if ($sections->length > 0) {
        $section = $sections->item(0);
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
    $sections = $xpath->query("//section[.//h2[contains(text(), 'Regulatory') or contains(text(), 'Framework')]]");
    if ($sections->length > 0) {
        $section = $sections->item(0);
        $boxes = $xpath->query(".//div[contains(@class, 'reg-box')]", $section);
        
        $order = 1;
        foreach ($boxes as $box) {
            $h5 = $xpath->query(".//h5", $box)->item(0);
            $title = $h5 ? trim(strip_tags($dom->saveHTML($h5))) : '';
            $p = $xpath->query(".//p", $box)->item(0);
            $description = $p ? trim($dom->saveHTML($p)) : '';
            
            if ($title && $description) {
                $frameworks[] = [
                    'title' => $title,
                    'description' => $description,
                    'display_order' => $order++
                ];
            }
            if (count($frameworks) >= 3) break;
        }
    }
    
    while (count($frameworks) < 3) {
        $frameworks[] = [
            'title' => 'Framework ' . (count($frameworks) + 1),
            'description' => '<p>Information coming soon.</p>',
            'display_order' => count($frameworks) + 1
        ];
    }
    
    return array_slice($frameworks, 0, 3);
}

/**
 * Extract documentation cards from HTML
 */
function extractDocumentationCards($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($dom);
    
    $cards = [];
    $sections = $xpath->query("//section[.//h2[contains(text(), 'Documentation') or contains(text(), 'Pillars')]]");
    if ($sections->length > 0) {
        $section = $sections->item(0);
        $glassCards = $xpath->query(".//div[contains(@class, 'glass-card')]", $section);
        
        $order = 1;
        foreach ($glassCards as $card) {
            $arrow = $xpath->query(".//span[contains(@class, 'arrow')]", $card)->item(0);
            $title = $arrow ? trim(str_replace(['▶', '►', '▼'], '', trim(strip_tags($dom->saveHTML($arrow))))) : '';
            
            $firstP = $xpath->query(".//p[not(ancestor::div[contains(@class, 'content')])]", $card)->item(0);
            $shortDesc = $firstP ? trim($dom->saveHTML($firstP)) : '<p>Click to expand for details.</p>';
            
            $contentDiv = $xpath->query(".//div[contains(@class, 'content')]", $card)->item(0);
            $detailedContent = '';
            if ($contentDiv) {
                $contentPs = $xpath->query(".//p", $contentDiv);
                foreach ($contentPs as $p) {
                    $detailedContent .= trim($dom->saveHTML($p)) . "\n";
                }
            }
            
            if ($title) {
                $cards[] = [
                    'title' => $title,
                    'short_description' => $shortDesc,
                    'detailed_content' => $detailedContent ?: '<p>Detailed information coming soon.</p>',
                    'display_order' => $order++
                ];
            }
        }
    }
    
    return $cards;
}

/**
 * Migrate a single country
 */
function migrateCountry($filename, $countryData, $countryRepo, &$results) {
    $filepath = __DIR__ . '/../' . $filename;
    
    if (!file_exists($filepath)) {
        $results['errors'][] = "$filename: File not found";
        return false;
    }
    
    // Check if already exists
    $existing = $countryRepo->findAll(['status' => 'all']);
    foreach ($existing as $e) {
        if ($e->country_code === $countryData['code']) {
            $results['skipped'][] = "$filename: {$countryData['name']} already exists in database.";
            return false;
        }
    }
    
    $html = file_get_contents($filepath);
    if (!$html) {
        $results['errors'][] = "$filename: Could not read file";
        return false;
    }
    
    try {
        $hero = extractHeroSection($html);
        $overview = extractOverview($html);
        $frameworks = extractRegulatoryFrameworks($html);
        $cards = extractDocumentationCards($html);
        
        $country = new Country();
        $country->country_name = $countryData['name'];
        $country->country_code = $countryData['code'];
        $country->flag_url = $countryData['flag'];
        $country->hero_title = $hero['title'] ?: "Transfer Pricing " . $countryData['name'];
        $country->hero_description = $hero['description'] ?: "Navigate " . $countryData['name'] . "'s transfer pricing requirements.";
        $country->meta_title = $countryData['name'] . " Transfer Pricing | HexaTP";
        $country->meta_description = "Complete guide to " . $countryData['name'] . " transfer pricing regulations and compliance.";
        $country->status = 'published';
        
        $country_id = $countryRepo->create($country);
        
        if (!$country_id) {
            $results['errors'][] = "$filename: Failed to create country record";
            return false;
        }
        
        $countryRepo->saveOverview($country_id, $overview['left'], $overview['right']);
        $countryRepo->saveRegulatoryFrameworks($country_id, $frameworks);
        $countryRepo->saveDocumentationCards($country_id, $cards);
        
        $results['success'][] = "$filename: Successfully migrated {$countryData['name']} (ID: $country_id)";
        return true;
        
    } catch (Exception $e) {
        $results['errors'][] = "$filename: " . $e->getMessage();
        return false;
    }
}

// Action handling
$is_migrating = isset($_POST['action']) && $_POST['action'] === 'migrate';

if ($is_migrating) {
    foreach ($countries_to_migrate as $filename => $countryData) {
        migrateCountry($filename, $countryData, $countryRepo, $results);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrate Content | HexaTP CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-subtle: #f9fafb;
            --accent: #f5c400;
            --accent-glow: rgba(245, 196, 0, 0.2);
            --card-bg: #ffffff;
            --glass-border: rgba(0, 0, 0, 0.08);
            --text-main: #0f172a;
            --text-slate: #64748b;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-main);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.85); box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            color: var(--accent);
            font-weight: 800;
            margin: 0;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .btn-primary {
            background: var(--accent);
            color: #000;
            border: none;
            font-weight: 600;
            padding: 12px 25px;
        }

        .btn-primary:hover {
            background: #ffd700;
            color: #000;
            box-shadow: 0 0 20px var(--accent-glow);
        }

        .success-item { color: #4caf50; font-size: 0.9rem; margin-bottom: 5px; }
        .error-item { color: #ff6b6b; font-size: 0.9rem; margin-bottom: 5px; }
        .skip-item { color: #ff9800; font-size: 0.9rem; margin-bottom: 5px; }

        .nav-menu {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 20px;
        }

        .nav-menu a {
            color: var(--text-slate);
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 5px;
        }

        .nav-menu a:hover {
            background: rgba(245, 196, 0, 0.1);
            color: var(--accent);
        }

        .nav-menu a.active {
            background: rgba(245, 196, 0, 0.2);
            color: var(--accent);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-3">
                    <img src="../logo-hexatp.jpeg" alt="HexaTP Logo" style="height: 40px;">
                    <h1 class="mb-0">Content Migration</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="nav-menu">
                    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a href="countries_list.php"><i class="bi bi-globe"></i> Countries List</a>
                    <a href="migrate_content.php" class="active"><i class="bi bi-arrow-repeat"></i> Migrate Content</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <h2><i class="bi bi-cloud-arrow-up"></i> HTML to Database Migration</h2>
                    <p class="text-slate">
                        This tool will scan the existing HTML files (e.g., India.html, Qatar.html) and import their content 
                        into the CMS database. It will extract hero sections, overview paragraphs, regulatory frameworks, 
                        and documentation cards.
                    </p>
                    
                    <?php if (!$is_migrating): ?>
                        <div class="alert alert-info bg-dark border-info text-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Ready to migrate:</strong> <?php echo count($countries_to_migrate); ?> country files identified.
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="migrate">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-play-fill"></i> Start Migration Now
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="mt-4">
                            <h3>Migration Results</h3>
                            <div class="p-3 bg-black rounded border border-secondary mb-4" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($results['success'] as $msg): ?>
                                    <div class="success-item"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($msg); ?></div>
                                <?php endforeach; ?>
                                
                                <?php foreach ($results['skipped'] as $msg): ?>
                                    <div class="skip-item"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($msg); ?></div>
                                <?php endforeach; ?>
                                
                                <?php foreach ($results['errors'] as $msg): ?>
                                    <div class="error-item"><i class="bi bi-x-circle-fill"></i> <?php echo htmlspecialchars($msg); ?></div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="countries_list.php" class="btn btn-primary">View Countries</a>
                                <a href="dashboard.php" class="btn btn-outline-light">Back to Dashboard</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
