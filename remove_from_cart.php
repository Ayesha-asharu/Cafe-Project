<?php
session_start();
require_once 'config.php';

// Debug: Check session status
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
    session_start();
}

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    if (!isset($_SESSION['cart'])) {
        throw new Exception('Cart not initialized');
    }

    // Ensure consistent ID type (string)
    $itemId = strval($input['id']);

    if (isset($_SESSION['cart'][$itemId])) {
        unset($_SESSION['cart'][$itemId]);
        echo json_encode([
            'success' => true,
            'cartCount' => array_sum(array_column($_SESSION['cart'], 'quantity')),
            'cartItems' => $_SESSION['cart'] // Return full cart for debugging
        ]);
    } else {
        throw new Exception('Item not found in cart');
    }

} catch (Exception $e) {
    error_log('Error in remove_from_cart: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>