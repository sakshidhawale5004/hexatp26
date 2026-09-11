<?php

/**
 * Country API Endpoint
 * 
 * GET /api/country.php?id={id} - Retrieve complete country data
 * POST /api/country.php - Create a new country
 * PUT /api/country.php?id={id} - Update existing country
 * DELETE /api/country.php?id={id} - Delete a country
 * 
 * Requirements: 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 7.2, 10.2, 10.3
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../services/ContentService.php';
require_once __DIR__ . '/../services/AuthService.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Create services
    $contentService = new ContentService($conn);
    $authService = new AuthService($conn);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Handle GET request - Retrieve country
    if ($method === 'GET') {
        handleGetCountry($contentService);
    }
    // Handle POST request - Create country
    elseif ($method === 'POST') {
        handleCreateCountry($contentService, $authService);
    }
    // Handle PUT request - Update country
    elseif ($method === 'PUT') {
        handleUpdateCountry($contentService, $authService);
    }
    // Handle DELETE request - Delete country
    elseif ($method === 'DELETE') {
        handleDeleteCountry($contentService, $authService);
    }
    else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
    }
    
} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log('Country API Error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error. Please try again later.'
    ]);
}

/**
 * Handle GET request - Retrieve country
 */
function handleGetCountry(ContentService $contentService): void {
    // Get country ID from query parameter
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Country ID is required'
        ]);
        return;
    }
    
    $country_id = (int)$_GET['id'];
    
    // Get country with all relations
    $country = $contentService->getCountry($country_id);
    
    if (!$country) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Country not found'
        ]);
        return;
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $country->toArray()
    ], JSON_PRETTY_PRINT);
}

/**
 * Handle POST request - Create country
 */
function handleCreateCountry(ContentService $contentService, AuthService $authService): void {
    // Check authentication
    if (!$authService->checkSession()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Authentication required'
        ]);
        return;
    }
    
    // Check permission
    if (!$authService->hasPermission('create', 'country')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'You do not have permission to create countries'
        ]);
        return;
    }
    
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON input'
        ]);
        return;
    }
    
    // Verify CSRF token
    if (!isset($input['csrf_token']) || !$authService->verifyCsrfToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid CSRF token'
        ]);
        return;
    }
    
    // Create country object from input
    $country = Country::fromArray($input);
    
    // Extract additional data (overview, frameworks, cards)
    $additional_data = [];
    if (isset($input['overview_text_left'])) {
        $additional_data['overview_text_left'] = $input['overview_text_left'];
    }
    if (isset($input['overview_text_right'])) {
        $additional_data['overview_text_right'] = $input['overview_text_right'];
    }
    if (isset($input['frameworks'])) {
        $additional_data['frameworks'] = $input['frameworks'];
    }
    if (isset($input['cards'])) {
        $additional_data['cards'] = $input['cards'];
    }
    
    // Get current user ID
    $user = $authService->getCurrentUser();
    $user_id = $user ? $user->id : 0;
    
    // Create country
    $result = $contentService->createCountry($country, $user_id, $additional_data);
    
    if ($result['success']) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Country created successfully',
            'data' => [
                'id' => $result['id']
            ]
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => $result['errors']
        ]);
    }
}

/**
 * Handle PUT request - Update country
 */
function handleUpdateCountry(ContentService $contentService, AuthService $authService): void {
    // Check authentication
    if (!$authService->checkSession()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Authentication required'
        ]);
        return;
    }
    
    // Check permission
    if (!$authService->hasPermission('update', 'country')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'You do not have permission to update countries'
        ]);
        return;
    }
    
    // Get country ID from query parameter
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Country ID is required'
        ]);
        return;
    }
    
    $country_id = (int)$_GET['id'];
    
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON input'
        ]);
        return;
    }
    
    // Verify CSRF token
    if (!isset($input['csrf_token']) || !$authService->verifyCsrfToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid CSRF token'
        ]);
        return;
    }
    
    // Create country object from input
    $country = Country::fromArray($input);
    
    // Extract additional data (overview, frameworks, cards)
    $additional_data = [];
    if (isset($input['overview_text_left'])) {
        $additional_data['overview_text_left'] = $input['overview_text_left'];
    }
    if (isset($input['overview_text_right'])) {
        $additional_data['overview_text_right'] = $input['overview_text_right'];
    }
    if (isset($input['frameworks'])) {
        $additional_data['frameworks'] = $input['frameworks'];
    }
    if (isset($input['cards'])) {
        $additional_data['cards'] = $input['cards'];
    }
    
    // Get current user ID
    $user = $authService->getCurrentUser();
    $user_id = $user ? $user->id : 0;
    
    // Update country
    $result = $contentService->updateCountry($country_id, $country, $user_id, $additional_data);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Country updated successfully'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => $result['errors']
        ]);
    }
}

/**
 * Handle DELETE request - Delete country
 */
function handleDeleteCountry(ContentService $contentService, AuthService $authService): void {
    // Check authentication
    if (!$authService->checkSession()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Authentication required'
        ]);
        return;
    }
    
    // Check permission (only admins can delete)
    if (!$authService->hasPermission('delete', 'country')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'You do not have permission to delete countries'
        ]);
        return;
    }
    
    // Get country ID from query parameter
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Country ID is required'
        ]);
        return;
    }
    
    $country_id = (int)$_GET['id'];
    
    // Delete country
    $result = $contentService->deleteCountry($country_id);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Country deleted successfully'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => $result['errors']
        ]);
    }
}
