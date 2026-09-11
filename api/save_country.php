<?php
/**
 * Save Country API Wrapper
 * This file acts as a legacy bridge for components calling api/save_country.php
 * It forwards requests to the unified api/country.php endpoint.
 */

// Define that this is a legacy call if needed
$_GET['legacy_bridge'] = true;

// Forward to the main country API
require_once __DIR__ . '/country.php';
