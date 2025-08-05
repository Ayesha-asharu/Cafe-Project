<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

session_start();

// Redirect if already logged in with proper session validation
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Verify session security tokens
    if ($_SESSION['admin_ip'] === $_SERVER['REMOTE_ADDR'] && 
        $_SESSION['admin_user_agent'] === $_SERVER['HTTP_USER_AGENT']) {
        header('Location: admin_menu.php');
        exit;
    } else {
        // Session hijacking detected, destroy session
        session_unset();
        session_destroy();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (verifyAdmin($username, $password)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            header('Location: admin_menu.php');
            exit;
        } else {
            $error = "Invalid username or password";
            sleep(2);
        }
    } catch (Exception $e) {
        error_log("Admin login error: " . $e->getMessage());
        $error = "An error occurred during login";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-box">
            <h1><i class="ri-cup-line"></i> Eat & Enjoy Admin</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Log In</button>
            </form>
        </div>
    </div>
</body>
</html>