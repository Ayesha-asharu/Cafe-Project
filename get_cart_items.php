<?php
session_start();
header('Content-Type: application/json');

$items = [];
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $item) {
        $items[] = [
            'id' => $id,
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity']
        ];
    }
}

echo json_encode([
    'success' => true,
    'items' => $items
]);
?>