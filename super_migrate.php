<?php
/**
 * Super Migration Script
 * Migrates content for ALL countries and updates existing records.
 */
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/models/Country.php';
require_once __DIR__ . '/repositories/CountryRepository.php';

$conn = getDBConnection();
$countryRepo = new CountryRepository($conn);

// Mapping from migrate_content.php
$countries_to_migrate = [
    'unitedarabemirates.html' => ['name' => 'United Arab Emirates', 'code' => 'AE', 'flag' => 'Flag_of_the_United_Arab_Emirates.jpeg'],
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
    'unitedstates.html' => ['name' => 'United States', 'code' => 'US', 'flag' => 'Flag_of_the_United_States_1912-1959.jpeg']
];

function extractHero($html) {
    preg_match('/<section class="hero">.*?<h1>(.*?)<\/h1>.*?<p.*?>(.*?)<\/p>/s', $html, $m);
    return ['title' => strip_tags($m[1] ?? ''), 'desc' => strip_tags($m[2] ?? '')];
}

function extractOverview($html) {
    // Look for overview section
    preg_match('/India TP Overview.*?<div class="col-lg-6">.*?<p.*?>(.*?)<\/p>.*?<\/div>.*?<div class="col-lg-6">.*?<p.*?>(.*?)<\/p>/s', $html, $m);
    if (!$m) {
        // Try generic pattern
        preg_match('/<h2 class="section-title.*?Overview.*?<p.*?>(.*?)<\/p>.*?<p.*?>(.*?)<\/p>/s', $html, $m);
    }
    return ['left' => $m[1] ?? '', 'right' => $m[2] ?? ''];
}

echo "<pre>";
foreach ($countries_to_migrate as $file => $data) {
    if (!file_exists($file)) { echo "Skipping $file (Not found)\n"; continue; }
    $html = file_get_contents($file);
    $hero = extractHero($html);
    $overview = extractOverview($html);
    
    // Find in DB
    $res = $conn->query("SELECT id FROM countries WHERE country_code = '{$data['code']}'");
    $row = $res->fetch_assoc();
    $id = $row['id'] ?? 0;
    
    if ($id) {
        echo "Updating {$data['name']} (ID: $id)...\n";
        $conn->query("UPDATE countries SET hero_title = '" . $conn->real_escape_string($hero['title']) . "', hero_description = '" . $conn->real_escape_string($hero['desc']) . "', status = 'published' WHERE id = $id");
        $countryRepo->saveOverview($id, $overview['left'], $overview['right']);
        echo "  Done.\n";
    } else {
        echo "Creating {$data['name']}...\n";
        $country = new Country();
        $country->country_name = $data['name'];
        $country->country_code = $data['code'];
        $country->flag_url = $data['flag'];
        $country->hero_title = $hero['title'] ?: "Transfer Pricing " . $data['name'];
        $country->hero_description = $hero['desc'];
        $country->status = 'published';
        $new_id = $countryRepo->create($country);
        $countryRepo->saveOverview($new_id, $overview['left'], $overview['right']);
        echo "  Created with ID: $new_id\n";
    }
}
echo "Migration Complete.";
?>
