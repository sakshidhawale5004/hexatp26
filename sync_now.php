<?php
/**
 * FORCE SYNC SCRIPT
 */
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/models/Country.php';
require_once __DIR__ . '/repositories/CountryRepository.php';

$conn = getDBConnection();
$countryRepo = new CountryRepository($conn);

$countries_to_migrate = [
    'unitedarab.html' => ['name' => 'United Arab Emirates', 'code' => 'AE', 'flag' => 'Flag_of_the_United_Arab_Emirates.jpeg'],
    'Saudiarabia.html' => ['name' => 'Saudi Arabia', 'code' => 'SA', 'flag' => 'saudi_arabia.jpeg'],
    'Qatar.html' => ['name' => 'Qatar', 'code' => 'QA', 'flag' => 'Flag_of_Qatar.jpeg'],
    'oman.html' => ['name' => 'Oman', 'code' => 'OM', 'flag' => 'Flag_of_Oman.jpeg'],
    'bahrain.html' => ['name' => 'Bahrain', 'code' => 'BH', 'flag' => 'Flag_of_Bahrain.jpeg'],
    'egypt.html' => ['name' => 'Egypt', 'code' => 'EG', 'flag' => 'Flag_of_Egypt.jpeg'],
    'India.html' => ['name' => 'India', 'code' => 'IN', 'flag' => 'Flag_of_India.jpeg'],
    'bangladesh.html' => ['name' => 'Bangladesh', 'code' => 'BD', 'flag' => 'FlagofBangladesh_MeaningColorsHistory_Britannica.jpeg'],
    'singapore.html' => ['name' => 'Singapore', 'code' => 'SG', 'flag' => 'Flag_of_Singapore.jpeg'],
    'thailand.html' => ['name' => 'Thailand', 'code' => 'TH', 'flag' => 'Flag_of_Thailand.jpeg'],
    'malaysia.html' => ['name' => 'Malaysia', 'code' => 'MY', 'flag' => 'Flag_of_Malaysia.jpeg'],
    'australia.html' => ['name' => 'Australia', 'code' => 'AU', 'flag' => 'Flag_of_Australia_converted.jpeg'],
    'indonesia.html' => ['name' => 'Indonesia', 'code' => 'ID', 'flag' => 'Flag_of_Indonesia.jpeg'],
    'viethnam.html' => ['name' => 'Vietnam', 'code' => 'VN', 'flag' => 'Flag_of_Vietnam.jpeg'],
    'botswana.html' => ['name' => 'Botswana', 'code' => 'BW', 'flag' => 'Flag_of_Botswana.jpeg'],
    'ghana.html' => ['name' => 'Ghana', 'code' => 'GH', 'flag' => 'Flag_of_Ghana.jpeg'],
    'kenya.html' => ['name' => 'Kenya', 'code' => 'KE', 'flag' => 'Flag_of_Kenya.jpeg'],
    'canada.html' => ['name' => 'Canada', 'code' => 'CA', 'flag' => 'Flag_of_Canada_3-2.jpeg'],
    'us.html' => ['name' => 'United States', 'code' => 'US', 'flag' => 'Flag_of_the_United_States_1912-1959.jpeg']
];

function clean($s) { return trim(preg_replace('/\s+/', ' ', strip_tags($s))); }

echo "SYNC STARTED...\n";
foreach ($countries_to_migrate as $file => $data) {
    if (!file_exists($file)) continue;
    $html = file_get_contents($file);
    
    // Better Hero Extraction
    preg_match('/<section class="hero">.*?<h1.*?>(.*?)<\/h1>.*?<p.*?>(.*?)<\/p>/s', $html, $m);
    $title = $m[1] ?? "";
    $desc = clean($m[2] ?? "");
    
    // Find ID
    $res = $conn->query("SELECT id FROM countries WHERE country_code = '{$data['code']}'");
    $id = ($row = $res->fetch_assoc()) ? $row['id'] : 0;
    
    if ($id) {
        $conn->query("UPDATE countries SET hero_title = '" . $conn->real_escape_string($title) . "', hero_description = '" . $conn->real_escape_string($desc) . "', status = 'published' WHERE id = $id");
        
        // Extract Overview
        preg_match('/Overview.*?<div class="col-lg-6">.*?<p.*?>(.*?)<\/p>.*?<\/div>.*?<div class="col-lg-6">.*?<p.*?>(.*?)<\/p>/s', $html, $ov);
        if ($ov) {
            $countryRepo->saveOverview($id, $ov[1], $ov[2]);
        }
        
        // Extract Frameworks
        preg_match_all('/<div class="reg-box">.*?<h5>(.*?)<\/h5>.*?<p.*?>(.*?)<\/p>/s', $html, $rf_matches, PREG_SET_ORDER);
        $frameworks = [];
        $order = 1;
        foreach ($rf_matches as $m) {
            $frameworks[] = ['title' => $m[1], 'description' => $m[2], 'display_order' => $order++];
        }
        $countryRepo->saveRegulatoryFrameworks($id, $frameworks);

        // Extract Documentation
        preg_match_all('/<div class="glass-card".*?><span class="arrow".*?>(.*?)<\/span>.*?<p.*?>(.*?)<\/p>.*?<div class="content".*?>(.*?)<\/div>/s', $html, $dc_matches, PREG_SET_ORDER);
        $cards = [];
        $order = 1;
        foreach ($dc_matches as $m) {
            $cards[] = ['title' => $m[1], 'short_description' => $m[2], 'detailed_content' => $m[3], 'display_order' => $order++];
        }
        $countryRepo->saveDocumentationCards($id, $cards);

        echo "Synced {$data['name']} (ID: $id)\n";
    }
}
echo "SYNC COMPLETE.";
?>
