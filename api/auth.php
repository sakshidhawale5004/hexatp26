<?php

/**
 * Authentication API Endpoint
 * 
 * POST /api/auth.php - Login or logout
 * 
 * Actions:
 * - login: Authenticate user and create session
 * - logout: Destroy session
 * 
 * Requirements: 5.1, 5.2, 5.4, 5.5, 5.6, 5.7
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../services/AuthService.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Only POST requests are supported.'
    ]);
    exit;
}

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Create auth service
    $authService = new AuthService($conn);
    
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request. Action is required.'
        ]);
        exit;
    }
    
    $action = $input['action'];
    
    // Handle login action
    if ($action === 'login') {
        handleLogin($authService, $input);
    }
    // Handle logout action
    elseif ($action === 'logout') {
        handleLogout($authService);
    }
    else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action. Supported actions: login, logout'
        ]);
    }
    
} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log('Auth API Error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error. Please try again later.'
    ]);
}

/**
 * Handle login action
 */
function handleLogin(AuthService $authService, array $input): void {
    // Validate required fields
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Username and password are required'
        ]);
        return;
    }
    
    $username = $input['username'];
    $password = $input['password'];
    
    // Attempt login
    $result = $authService->login($username, $password);
    
    if ($result['success']) {
        // Generate CSRF token for subsequent requests
        $csrf_token = $authService->generateCsrfToken();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $result['user'],
                'csrf_token' => $csrf_token
            ]
        ]);
    } else {
        // Get remaining attempts
        $remaining = $authService->getRemainingAttempts($username);
        
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => $result['error'],
            'remaining_attempts' => $remaining
        ]);
    }
}

/**
 * Handle logout action
 */
function handleLogout(AuthService $authService): void {
    $result = $authService->logout();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
}
