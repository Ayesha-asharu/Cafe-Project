<?php
session_start();
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['cart'][$input['id']])) {
    die(json_encode(['success' => false, 'message' => 'Item not in cart']));
}

$quantity = $_SESSION['cart'][$input['id']]['quantity'] + $input['change'];

if ($quantity <= 0) {
    unset($_SESSION['cart'][$input['id']]);
} else {
    $_SESSION['cart'][$input['id']]['quantity'] = $quantity;
}

echo json_encode(['success' => true]);
?>