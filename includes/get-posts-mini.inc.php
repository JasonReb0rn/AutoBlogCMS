<?php
session_start();
require_once 'dbh.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit();
}

try {
    $stmt = $pdo->query("
        SELECT p.PostID, p.Title, p.Status, p.CreatedAt, u.Username 
        FROM Posts p 
        JOIN Users u ON p.UserID = u.UserID 
        ORDER BY p.CreatedAt DESC
        LIMIT 10
    ");
    $posts = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($posts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}