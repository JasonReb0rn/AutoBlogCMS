<?php
session_start();
require_once 'dbh.inc.php';

if (!isset($_SESSION["userid"]) || !in_array($_SESSION["role"], ["admin", "editor"])) {
    http_response_code(403);
    exit();
}

try {
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    
    $offset = max(0, $offset);
    $limit = max(1, min(100, $limit));
    
    $stmt = $pdo->prepare("
        SELECT CategoryID, Name, Slug, CreatedAt
        FROM Categories 
        ORDER BY CreatedAt DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $categories = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode(['categories' => $categories]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}