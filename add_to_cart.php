<?php
// Ensure we output JSON even for errors
header('Content-Type: application/json');

try {
    session_start();
    require_once 'config.php';

    // Verify we have a valid session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new Exception('Session not active');
    }

    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (!isset($input['id']) || !isset($input['name']) || !isset($input['price'])) {
        throw new Exception('Missing required fields');
    }

    // Initialize cart if needed
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

   
    $itemId = (string)$input['id'];
    try {
        $stmt = $db->prepare("SELECT price, discount FROM menu_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $itemDetails = $stmt->fetch();
        
        if ($itemDetails) {
            if (isset($_SESSION['cart'][$itemId])) {
                $_SESSION['cart'][$itemId]['quantity'] += 1;
            } else {
                $_SESSION['cart'][$itemId] = [
                    'id' => $itemId,
                    'name' => $input['name'],
                    'price' => (float)$itemDetails['price'],
                    'discount' => isset($itemDetails['discount']) ? (float)$itemDetails['discount'] : 0.00,
                    'quantity' => 1
                ];
            }
        } else {
            throw new Exception('Item not found in database');
        }

        // Calculate new cart count
        $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

        // Return success response
        echo json_encode([
            'success' => true,
            'cartCount' => $cartCount
        ]);

    } catch (Exception $e) {
        throw $e; // Re-throw to be caught by outer catch
    }

} catch (Exception $e) {
    // Return error response in JSON format
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

?>
