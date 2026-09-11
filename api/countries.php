<?php

/**
 * Countries API Endpoint
 * 
 * GET /api/countries.php - Retrieve list of all countries
 * 
 * Query Parameters:
 * - status: Filter by status ('draft', 'published', 'all')
 * - sort: Sort field ('name', 'updated_at', 'created_at')
 * - order: Sort order ('ASC', 'DESC')
 * 
 * Requirements: 3.1, 3.6, 3.7, 3.10
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../services/ContentService.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Only GET requests are supported.'
    ]);
    exit;
}

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Create content service
    $contentService = new ContentService($conn);
    
    // Get query parameters
    $filters = [];
    
    if (isset($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    
    if (isset($_GET['sort'])) {
        $filters['sort'] = $_GET['sort'];
    }
    
    if (isset($_GET['order'])) {
        $filters['order'] = $_GET['order'];
    }
    
    // Get all countries
    $countries = $contentService->getAllCountries($filters);
    
    // Convert to array format
    $countries_data = array_map(function($country) {
        return [
            'id' => $country->id,
            'country_name' => $country->country_name,
            'country_code' => $country->country_code,
            'flag_url' => $country->flag_url,
            'status' => $country->status,
            'updated_at' => $country->updated_at->format('Y-m-d H:i:s')
        ];
    }, $countries);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $countries_data,
        'count' => count($countries_data)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log('Countries API Error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error. Please try again later.'
    ]);
}
