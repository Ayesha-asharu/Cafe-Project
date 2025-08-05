<?php
require_once 'config.php';

// Ensure we return JSON
header('Content-Type: application/json');

try {
    // Get the raw POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid request data');
    }

    // Sanitize inputs using the function from config.php
    $name = sanitizeInput($input['customer']['name'] ?? '');
    $email = sanitizeInput($input['customer']['email'] ?? '');
    $phone = sanitizeInput($input['customer']['phone'] ?? '');
    $address = sanitizeInput($input['customer']['address'] ?? '');
    $notes = sanitizeInput($input['customer']['notes'] ?? '');
    $paymentMethod = sanitizeInput($input['customer']['payment'] ?? '');
    $cartItems = $input['items'] ?? [];
    $totalAmount = floatval($input['totalAmount'] ?? 0);

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($paymentMethod)) {
        throw new Exception('All required fields must be filled');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (empty($cartItems)) {
        throw new Exception('Your cart is empty');
    }

    // Start transaction
    $db->beginTransaction();

    // Insert order
    $stmt = $db->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, delivery_address, special_instructions, payment_method, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$name, $email, $phone, $address, $notes, $paymentMethod, $totalAmount]);
    $orderId = $db->lastInsertId();

    // Insert order items
    $stmt = $db->prepare("INSERT INTO order_items (order_id, item_id, item_name, item_price, quantity) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($cartItems as $item) {
        $stmt->execute([
            $orderId,
            $item['id'] ?? null,
            $item['name'] ?? '',
            $item['price'] ?? 0,
            $item['quantity'] ?? 1
        ]);
    }

    // Commit transaction
    $db->commit();

    // Clear the cart
    unset($_SESSION['cart']);

    // Return success
    echo json_encode([
        'success' => true,  // Changed from 'status' => 'success'
        'message' => 'Order placed successfully!',
        'order_id' => $orderId
    ]);
} catch (Exception $e) {
    // Rollback on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}