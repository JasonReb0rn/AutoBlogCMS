<?php
session_start();
require_once 'dbh.inc.php';

// Check if user is admin/editor
if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit();
}

try {
    $stmt = $pdo->query("SELECT UserID, Username, Email, Role, CreatedAt FROM Users ORDER BY CreatedAt DESC");
    $users = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($users);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}