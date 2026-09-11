<?php
require_once 'db_config.php';
$id = 28; // India
$res = $conn->query("SELECT * FROM countries WHERE id = $id")->fetch_assoc();
echo "Country: " . $res['country_name'] . "\n";

$ov = $conn->query("SELECT * FROM country_overview WHERE country_id = $id")->fetch_assoc();
echo "Overview: " . ($ov ? "FOUND" : "MISSING") . "\n";
if ($ov) {
    echo "  Left: " . substr($ov['overview_text_left'], 0, 50) . "...\n";
    echo "  Right: " . substr($ov['overview_text_right'], 0, 50) . "...\n";
}

$rf = $conn->query("SELECT COUNT(*) FROM regulatory_frameworks WHERE country_id = $id")->fetch_row()[0];
echo "Frameworks: $rf\n";

$dc = $conn->query("SELECT COUNT(*) FROM documentation_cards WHERE country_id = $id")->fetch_row()[0];
echo "Cards: $dc\n";
?>
