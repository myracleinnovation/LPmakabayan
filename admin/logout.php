<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Set response headers
header('Content-Type: application/json');
http_response_code(200);

// Return success response
echo json_encode([
    'status' => 'success',
    'message' => 'Logged out successfully'
]);
?> 