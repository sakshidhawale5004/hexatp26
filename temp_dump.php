<?php
require_once 'db_config.php';
$res = $conn->query("SELECT id, country_name, status FROM countries ORDER BY id ASC");
echo "<pre>";
while ($row = $res->fetch_assoc()) {
    print_r($row);
    // Check relations
    $id = $row['id'];
    $ov = $conn->query("SELECT COUNT(*) FROM country_overview WHERE country_id = $id")->fetch_row()[0];
    $rf = $conn->query("SELECT COUNT(*) FROM regulatory_frameworks WHERE country_id = $id")->fetch_row()[0];
    $dc = $conn->query("SELECT COUNT(*) FROM documentation_cards WHERE country_id = $id")->fetch_row()[0];
    echo "  - Overview: $ov, Frameworks: $rf, Cards: $dc\n";
}
echo "</pre>";
?>
