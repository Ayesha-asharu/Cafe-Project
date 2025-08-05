<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Get form data
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        // Sanitize inputs using the function from config.php
        $name = sanitizeInput($input['name'] ?? '');
        $contact = sanitizeInput($input['contact'] ?? '');
        $quantity = sanitizeInput($input['quantity'] ?? '');
        $date = sanitizeInput($input['date'] ?? '');

        // Validate inputs
        if (empty($name) || empty($contact) || empty($quantity) || empty($date)) {
            throw new Exception('All fields are required');
        }

        if (!preg_match('/^\d{10}$/', $contact)) {
            throw new Exception('Phone number must be 10 digits');
        }

        // Check if date is in the future
        $today = new DateTime();
        $bookingDate = new DateTime($date);
        if ($bookingDate < $today) {
            throw new Exception('Booking date must be in the future');
        }

        // Insert into database
        $stmt = $db->prepare("INSERT INTO bookings (name, contact, quantity, date, created_at) VALUES (?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$name, $contact, $quantity, $date]);
        
        if (!$result) {
            throw new Exception('Failed to insert into database: ' . implode(' ', $stmt->errorInfo()));
        }
        
        $lastId = $db->lastInsertId();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Booking confirmed!',
            'booking_id' => $lastId
        ]);
        exit;
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// If not POST request
http_response_code(405);
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request method'
]);
exit;