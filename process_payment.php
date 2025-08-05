<?php
$conn = new mysqli("localhost", "root", "", "cafe_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$paymentMethod = $_POST['payment_method'];
$cardHolder = $_POST['card_holder_name'] ?? null;
$cardNumber = isset($_POST['card_number']) ? password_hash($_POST['card_number'], PASSWORD_BCRYPT) : null;
$expiryDate = $_POST['expiry_date'] ?? null;
$cvv = isset($_POST['cvv']) ? password_hash($_POST['cvv'], PASSWORD_BCRYPT) : null;
$upiId = $_POST['upi_id'] ?? null;
$amount = $_POST['amount'];

$stmt = $conn->prepare("INSERT INTO payments (payment_method, card_holder_name, card_number, expiry_date, cvv, upi_id, amount, transaction_time) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssssd", $paymentMethod, $cardHolder, $cardNumber, $expiryDate, $cvv, $upiId, $amount);
$stmt->execute();

echo "Payment info stored successfully!";
$stmt->close();
$conn->close();
?>