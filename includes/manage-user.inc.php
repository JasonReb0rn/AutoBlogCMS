<?php
// includes/manage-user.inc.php
session_start();
require_once 'dbh.inc.php';
require_once 'login-functions.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin"])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    $pdo->beginTransaction();
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'error' => 'Invalid action'];
    
    switch ($action) {
        case 'create':
            if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['role'])) {
                throw new Exception('Missing required fields');
            }

            // Use existing validation functions from login-functions.inc.php
            if (invalidUsername($_POST['username'])) {
                throw new Exception('Invalid username format');
            }
            if (invalidEmail($_POST['email'])) {
                throw new Exception('Invalid email format');
            }
            if (usernameOrEmailExists($pdo, $_POST['username'], $_POST['email'])) {
                throw new Exception('Username or email already exists');
            }
            
            // Create user with temporary password
            $tempPassword = bin2hex(random_bytes(8)); // Random 16-char string
            $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO Users (Username, Email, PasswordHash, Role)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $passwordHash,
                $_POST['role']
            ]);
            
            // TODO!!
            // In production, we want to email this temporary password to the user
            // We'll also need to prompt them to immediatly change it (new SQL db 'users' col, 'is_verified' type bool default 0)
            $response = [
                'success' => true,
                'tempPassword' => $tempPassword  // Probably want to remove this in production...
            ];
            break;
            
        case 'update':
            if (empty($_POST['userId']) || empty($_POST['username']) || empty($_POST['email']) || empty($_POST['role'])) {
                throw new Exception('Missing required fields');
            }

            // Validate the role enum value
            if (!in_array($_POST['role'], ['admin', 'editor', 'user'])) {
                throw new Exception('Invalid role');
            }
            
            // Check if username/email exists for other users
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM Users 
                WHERE (Username = ? OR Email = ?) 
                AND UserID != ?
            ");
            $stmt->execute([$_POST['username'], $_POST['email'], $_POST['userId']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Username or email already exists');
            }
            
            // Prevent changing own role (security measure)
            if ($_POST['userId'] == $_SESSION['userid'] && $_POST['role'] != $_SESSION['role']) {
                throw new Exception('Cannot modify your own role');
            }
            
            $stmt = $pdo->prepare("
                UPDATE Users 
                SET Username = ?, Email = ?, Role = ?
                WHERE UserID = ?
            ");
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                $_POST['role'],
                $_POST['userId']
            ]);
            
            $response = ['success' => true];
            break;
            
        case 'delete':
            if (empty($_POST['userId'])) {
                throw new Exception('User ID required');
            }
            
            // Prevent deleting self
            if ($_POST['userId'] == $_SESSION['userid']) {
                throw new Exception('Cannot delete your own account');
            }
            
            // Check if user exists before delete
            $stmt = $pdo->prepare("SELECT UserID FROM Users WHERE UserID = ?");
            $stmt->execute([$_POST['userId']]);
            if (!$stmt->fetch()) {
                throw new Exception('User not found');
            }
            
            $stmt = $pdo->prepare("DELETE FROM Users WHERE UserID = ?");
            $stmt->execute([$_POST['userId']]);
            
            $response = ['success' => true];
            break;
    }
    
    $pdo->commit();
    echo json_encode($response);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error managing user: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}