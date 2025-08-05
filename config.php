<?php
// Error reporting
// Ensure no previous output
if (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');


// Session configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
define('ADMIN_USERNAME', 'Cafe_staff');
define('ADMIN_PASSWORD_HASH', password_hash('Cafe_details@123', PASSWORD_DEFAULT));
function verifyAdmin($username, $password) {
    return ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH));
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}



// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=cafe_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Database connection error']));
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>