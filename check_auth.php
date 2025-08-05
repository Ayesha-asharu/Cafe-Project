<?php
require_once 'config.php'; // This already starts the session

header('Content-Type: application/json');

$response = [
    'loggedIn' => false,
    'user' => null
];

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $response['loggedIn'] = true;
            $response['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'userType' => $user['user_type']
            ];
        }
    } catch (PDOException $e) {
        error_log("Database error in check_auth.php: " . $e->getMessage());
    }
}

echo json_encode($response);