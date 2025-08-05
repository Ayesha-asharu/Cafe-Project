<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");

require_once 'config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if ($data['action'] === 'register') {
            try {
                $name = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
                $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
                $phone = preg_replace('/[^0-9]/', '', $data['phone']);
                $password = password_hash($data['password'], PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Email already registered'
                    ]);
                    exit;
                }
                
                $stmt = $db->prepare("INSERT INTO users (name, email, phone, password_hash) 
                                   VALUES (:name, :email, :phone, :password)");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':password' => $password
                ]);
                
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Registration successful',
                    'user' => [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'userType' => 'customer'
                    ]
                ]);
                
            } catch(PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Registration failed: ' . $e->getMessage()
                ]);
            }
        }
        
        if ($data['action'] === 'login') {
            try {
                $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
                $password = $data['password'];
                
                $stmt = $db->prepare("SELECT id, name, email, phone, password_hash, user_type FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Start session if not already started
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);
                    
                    http_response_code(200);
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'phone' => $user['phone'],
                            'userType' => $user['user_type']
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid email or password'
                    ]);
                }
            } catch(PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Login failed: ' . $e->getMessage()
                ]);
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = "SELECT id, name, description, price, category, image_url FROM menu_items";
        $params = [];
        
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $query .= " WHERE category = :category";
            $params[':category'] = htmlspecialchars($_GET['category'], ENT_QUOTES, 'UTF-8');
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => $results ?: [],
            'count' => count($results)
        ]);
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}